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

class Ai1wmse_Import_Download {

	public static function execute( $params, Ai1wmse_S3_Client $s3 = null ) {

		$params['completed'] = false;

		// Validate file path
		if ( ! isset( $params['file_path'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Amazon S3 File Path is not specified.', AI1WMSE_PLUGIN_NAME ) );
		}

		// Validate file size
		if ( ! isset( $params['file_size'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Amazon S3 File Size is not specified.', AI1WMSE_PLUGIN_NAME ) );
		}

		// Validate bucket name
		if ( ! isset( $params['bucket_name'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Amazon S3 Bucket Name is not specified.', AI1WMSE_PLUGIN_NAME ) );
		}

		// Validate region name
		if ( ! isset( $params['region_name'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Amazon S3 Region Name is not specified.', AI1WMSE_PLUGIN_NAME ) );
		}

		// Set file chunk size for download
		$file_chunk_size = get_option( 'ai1wmse_s3_file_chunk_size', AI1WMSE_DEFAULT_FILE_CHUNK_SIZE );

		// Set file range start
		if ( ! isset( $params['file_range_start'] ) ) {
			$params['file_range_start'] = 0;
		}

		// Set file range end
		if ( ! isset( $params['file_range_end'] ) ) {
			$params['file_range_end'] = $file_chunk_size - 1;
		}

		// Set download retries
		if ( ! isset( $params['download_retries'] ) ) {
			$params['download_retries'] = 0;
		}

		// Set Amazon S3 client
		if ( is_null( $s3 ) ) {
			$s3 = new Ai1wmse_S3_Client(
				get_option( 'ai1wmse_s3_access_key', ai1wmse_aws_access_key() ),
				get_option( 'ai1wmse_s3_secret_key', ai1wmse_aws_secret_key() ),
				get_option( 'ai1wmse_s3_https_protocol', true )
			);
		}

		// Open the archive file for writing
		$archive = fopen( ai1wm_archive_path( $params ), 'ab' );

		try {

			$params['download_retries'] += 1;

			// Download file chunk data
			$s3->get_file( $archive, $params['file_path'], $params['bucket_name'], $params['region_name'], $params['file_range_start'], $params['file_range_end'] );

			// Unset download retries
			unset( $params['download_retries'] );

		} catch ( Ai1wmse_Connect_Exception $e ) {
			if ( $params['download_retries'] <= 3 ) {
				return $params;
			}

			throw $e;
		}

		// Set file range start
		if ( $params['file_size'] <= ( $params['file_range_start'] + $file_chunk_size ) ) {
			$params['file_range_start'] = $params['file_size'] - 1;
		} else {
			$params['file_range_start'] = $params['file_range_start'] + $file_chunk_size;
		}

		// Set file range end
		if ( $params['file_size'] <= ( $params['file_range_end'] + $file_chunk_size ) ) {
			$params['file_range_end'] = $params['file_size'] - 1;
		} else {
			$params['file_range_end'] = $params['file_range_end'] + $file_chunk_size;
		}

		// Get progress
		$progress = (int) ( ( $params['file_range_start'] / $params['file_size'] ) * 100 );

		// Set progress
		if ( defined( 'WP_CLI' ) ) {
			WP_CLI::log(
				sprintf(
					__( 'Downloading %s (%s) [%d%% complete]', AI1WMSE_PLUGIN_NAME ),
					$params['file_path'],
					$params['file_size'],
					$progress
				)
			);
		} else {
			Ai1wm_Status::progress( $progress );
		}

		// Completed?
		if ( $params['file_range_start'] === ( $params['file_size'] - 1 ) ) {

			// Unset file path
			unset( $params['file_path'] );

			// Unset file size
			unset( $params['file_size'] );

			// Unset bucket name
			unset( $params['bucket_name'] );

			// Unset region name
			unset( $params['region_name'] );

			// Unset file range start
			unset( $params['file_range_start'] );

			// Unset file range end
			unset( $params['file_range_end'] );

			// Unset completed
			unset( $params['completed'] );
		}

		// Close the archive file
		fclose( $archive );

		return $params;
	}
}
