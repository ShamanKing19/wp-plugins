<?php
/**
 * Copyright (C) 2014-2020 ServMask Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Kangaroos cannot jump here' );
}

class Ai1wmme_Export_Enumerate_Media {

	public static function execute( $params ) {

		$include_filters = $exclude_filters = array();

		// Get total media files count
		if ( isset( $params['total_media_files_count'] ) ) {
			$total_media_files_count = (int) $params['total_media_files_count'];
		} else {
			$total_media_files_count = 0;
		}

		// Get total media files size
		if ( isset( $params['total_media_files_size'] ) ) {
			$total_media_files_size = (int) $params['total_media_files_size'];
		} else {
			$total_media_files_size = 0;
		}

		// Set progress
		Ai1wm_Status::info( __( 'Retrieving a list of WordPress media files...', AI1WMME_PLUGIN_NAME ) );

		// Exclude media
		if ( isset( $params['options']['sites'] ) ) {
			foreach ( ai1wmme_include_sites( $params ) as $site ) {
				if ( ai1wm_is_mainsite( $site['BlogID'] ) === false ) {
					$include_filters[] = ai1wm_blog_sites_abspath( $site['BlogID'] );
				}
			}

			$exclude_filters[] = 'sites';
		}

		$user_filters = array();

		// Exclude selected files
		if ( isset( $params['options']['exclude_files'], $params['excluded_files'] ) ) {
			$excluded_files = explode( ',', $params['excluded_files'] );
			if ( $excluded_files ) {
				foreach ( $excluded_files as $excluded_path ) {
					$user_filters[] = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . untrailingslashit( $excluded_path );
				}
			}

			$exclude_filters = array_merge( $exclude_filters, $user_filters );
		}

		// Create media list file
		$media_list = ai1wm_open( ai1wm_media_list_path( $params ), 'w' );

		// Exclude media
		if ( isset( $params['options']['no_media'] ) === false ) {

			// Loop over sites directory
			if ( $include_filters ) {
				foreach ( $include_filters as $path ) {
					if ( is_dir( $path ) ) {

						// Iterate over sites directory
						$iterator = new Ai1wm_Recursive_Directory_Iterator( $path );

						// Exclude new line file names
						$iterator = new Ai1wm_Recursive_Exclude_Filter( $iterator, apply_filters( 'ai1wm_exclude_media_from_export', $user_filters ) );

						// Recursively iterate over sites directory
						$iterator = new Ai1wm_Recursive_Iterator_Iterator( $iterator, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD );

						// Write path line
						foreach ( $iterator as $item ) {
							if ( $item->isFile() ) {
								if ( ai1wm_write( $media_list, substr_replace( $iterator->getPathname(), '', 0, strlen( ai1wm_get_uploads_dir() ) + 1 ) . PHP_EOL ) ) {
									$total_media_files_count++;

									// Add current file size
									$total_media_files_size += $iterator->getSize();
								}
							}
						}
					}
				}

				// Loop over media directory
				if ( ai1wmme_has_mainsite( $params ) ) {
					if ( is_dir( ai1wm_get_uploads_dir() ) ) {

						// Iterate over media directory
						$iterator = new Ai1wm_Recursive_Directory_Iterator( ai1wm_get_uploads_dir() );

						// Exclude media files
						$iterator = new Ai1wm_Recursive_Exclude_Filter( $iterator, apply_filters( 'ai1wm_exclude_media_from_export', $exclude_filters ) );

						// Recursively iterate over content directory
						$iterator = new Ai1wm_Recursive_Iterator_Iterator( $iterator, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD );

						// Write path line
						foreach ( $iterator as $item ) {
							if ( $item->isFile() ) {
								if ( ai1wm_write( $media_list, $iterator->getSubPathname() . PHP_EOL ) ) {
									$total_media_files_count++;

									// Add current file size
									$total_media_files_size += $iterator->getSize();
								}
							}
						}
					}
				}
			} elseif ( is_dir( ai1wm_get_uploads_dir() ) ) {

				// Iterate over media directory
				$iterator = new Ai1wm_Recursive_Directory_Iterator( ai1wm_get_uploads_dir() );

				// Exclude media files
				$iterator = new Ai1wm_Recursive_Exclude_Filter( $iterator, apply_filters( 'ai1wm_exclude_media_from_export', $exclude_filters ) );

				// Recursively iterate over content directory
				$iterator = new Ai1wm_Recursive_Iterator_Iterator( $iterator, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD );

				// Write path line
				foreach ( $iterator as $item ) {
					if ( $item->isFile() ) {
						if ( ai1wm_write( $media_list, $iterator->getSubPathname() . PHP_EOL ) ) {
							$total_media_files_count++;

							// Add current file size
							$total_media_files_size += $iterator->getSize();
						}
					}
				}
			}
		}

		// Set progress
		Ai1wm_Status::info( __( 'Done retrieving a list of WordPress media files.', AI1WMME_PLUGIN_NAME ) );

		// Set total media files count
		$params['total_media_files_count'] = $total_media_files_count;

		// Set total media files size
		$params['total_media_files_size'] = $total_media_files_size;

		// Close the media list file
		ai1wm_close( $media_list );

		return $params;
	}
}
