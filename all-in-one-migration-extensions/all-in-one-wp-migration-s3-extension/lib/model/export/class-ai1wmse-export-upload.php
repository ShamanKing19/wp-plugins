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

class Ai1wmse_Export_Upload {

	public static function execute( $params, Ai1wmse_S3_Client $s3 = null ) {

		$params['completed'] = false;

		// Validate bucket name
		if ( ! isset( $params['bucket_name'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Amazon S3 Bucket Name is not specified.', AI1WMSE_PLUGIN_NAME ) );
		}

		// Validate region name
		if ( ! isset( $params['region_name'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Amazon S3 Region Name is not specified.', AI1WMSE_PLUGIN_NAME ) );
		}

		// Validate upload ID
		if ( ! isset( $params['upload_id'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Amazon S3 Upload ID is not specified.', AI1WMSE_PLUGIN_NAME ) );
		}

		// Set archive offset
		if ( ! isset( $params['archive_offset'] ) ) {
			$params['archive_offset'] = 0;
		}

		// Set archive size
		if ( ! isset( $params['archive_size'] ) ) {
			$params['archive_size'] = ai1wm_archive_bytes( $params );
		}

		// Set file chunk number
		if ( ! isset( $params['file_chunk_number'] ) ) {
			$params['file_chunk_number'] = 1;
		}

		// Set upload retries
		if ( ! isset( $params['upload_retries'] ) ) {
			$params['upload_retries'] = 0;
		}

		// Set Amazon S3 client
		if ( is_null( $s3 ) ) {
			$s3 = new Ai1wmse_S3_Client(
				get_option( 'ai1wmse_s3_access_key', ai1wmse_aws_access_key() ),
				get_option( 'ai1wmse_s3_secret_key', ai1wmse_aws_secret_key() ),
				get_option( 'ai1wmse_s3_https_protocol', true )
			);
		}

		// Set file path
		$file_path = ! empty( $params['folder_name'] ) ? $params['folder_name'] . '/' . ai1wm_archive_name( $params ) : ai1wm_archive_name( $params );

		// Open the archive file for reading
		$archive = fopen( ai1wm_archive_path( $params ), 'rb' );

		// Set file chunk size for upload
		$file_chunk_size = get_option( 'ai1wmse_s3_file_chunk_size', AI1WMSE_DEFAULT_FILE_CHUNK_SIZE );

		// Read file chunk data
		if ( ( fseek( $archive, $params['archive_offset'] ) !== -1 )
				&& ( $file_chunk_data = fread( $archive, $file_chunk_size ) ) ) {

			try {

				$params['upload_retries'] += 1;

				// Upload file chunk data
				$file_chunk_etag = $s3->upload_file_chunk( $file_chunk_data, $file_path, $params['upload_id'], $params['bucket_name'], $params['region_name'], $params['file_chunk_number'] );

				// Add file chunk ETag
				if ( ( $multipart = fopen( ai1wm_multipart_path( $params ), 'a' ) ) ) {
					fwrite( $multipart, $file_chunk_etag . PHP_EOL );
					fclose( $multipart );
				}

				// Unset upload retries
				unset( $params['upload_retries'] );

			} catch ( Ai1wmse_Connect_Exception $e ) {
				if ( $params['upload_retries'] <= 3 ) {
					return $params;
				}

				throw $e;
			}

			// Set archive offset
			$params['archive_offset'] = ftell( $archive );

			// Set file chunk number
			$params['file_chunk_number'] += 1;

			// Set archive details
			$name = ai1wm_archive_name( $params );
			$size = ai1wm_archive_size( $params );

			// Get progress
			$progress = (int) ( ( $params['archive_offset'] / $params['archive_size'] ) * 100 );

			// Set progress
			if ( defined( 'WP_CLI' ) ) {
				WP_CLI::log(
					sprintf(
						__( 'Uploading %s (%s) [%d%% complete]', AI1WMSE_PLUGIN_NAME ),
						$name,
						$size,
						$progress
					)
				);
			} else {
				Ai1wm_Status::info(
					sprintf(
						__(
							'<i class="ai1wmse-icon-s3"></i> ' .
							'Uploading <strong>%s</strong> (%s)<br />%d%% complete',
							AI1WMSE_PLUGIN_NAME
						),
						$name,
						$size,
						$progress
					)
				);
			}
		} else {

			// Add file chunk ETag
			$file_chunks = array();
			if ( ( $multipart = fopen( ai1wm_multipart_path( $params ), 'r' ) ) ) {
				while ( $file_chunk_sha1 = trim( fgets( $multipart ) ) ) {
					$file_chunks[] = $file_chunk_sha1;
				}

				fclose( $multipart );
			}

			// Complete upload file chunk data
			$s3->upload_complete( $file_chunks, $file_path, $params['upload_id'], $params['bucket_name'], $params['region_name'] );

			// Set last backup date
			update_option( 'ai1wmse_s3_timestamp', time() );

			// Unset storage class
			unset( $params['storage_class'] );

			// Unset encryption
			unset( $params['encryption'] );

			// Unset upload ID
			unset( $params['upload_id'] );

			// Unset archive offset
			unset( $params['archive_offset'] );

			// Unset archive size
			unset( $params['archive_size'] );

			// Unset file chunk number
			unset( $params['file_chunk_number'] );

			// Unset completed
			unset( $params['completed'] );
		}

		// Close the archive file
		fclose( $archive );

		return $params;
	}
}
