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

class Ai1wmse_Export_S3 {

	public static function execute( $params, Ai1wmse_S3_Client $s3 = null ) {

		// Set progress
		Ai1wm_Status::info( __( 'Connecting to Amazon S3...', AI1WMSE_PLUGIN_NAME ) );

		// Open the archive file for writing
		$archive = new Ai1wm_Compressor( ai1wm_archive_path( $params ) );

		// Append EOF block
		$archive->close( true );

		// Set Amazon S3 client
		if ( is_null( $s3 ) ) {
			$s3 = new Ai1wmse_S3_Client(
				get_option( 'ai1wmse_s3_access_key', ai1wmse_aws_access_key() ),
				get_option( 'ai1wmse_s3_secret_key', ai1wmse_aws_secret_key() ),
				get_option( 'ai1wmse_s3_https_protocol', true )
			);
		}

		// Get storage class
		$params['storage_class'] = get_option( 'ai1wmse_s3_storage_class', AI1WMSE_S3_DEFAULT_STORAGE_CLASS );

		// Get bucket encryption
		$params['encryption'] = get_option( 'ai1wmse_s3_encryption', false );

		// Get bucket name
		$params['bucket_name'] = get_option( 'ai1wmse_s3_bucket_name', ai1wm_archive_bucket() );

		// Get region name
		$params['region_name'] = $s3->get_bucket_region( $params['bucket_name'] );

		// Get folder name
		$params['folder_name'] = get_option( 'ai1wmse_s3_folder_name', '' );

		// Create bucket if does not exist
		if ( ! $s3->is_bucket_available( $params['bucket_name'], $params['region_name'] ) ) {
			$s3->create_bucket( $params['bucket_name'] );
		}

		$file_path = ! empty( $params['folder_name'] ) ? $params['folder_name'] . '/' . ai1wm_archive_name( $params ) : ai1wm_archive_name( $params );

		// Get upload ID
		$params['upload_id'] = $s3->upload_multipart( $file_path, $params['bucket_name'], $params['region_name'], $params['storage_class'], $params['encryption'] );

		// Set progress
		Ai1wm_Status::info( __( 'Done connecting to Amazon S3.', AI1WMSE_PLUGIN_NAME ) );

		return $params;
	}
}
