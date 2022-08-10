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

class Ai1wmse_Import_S3 {

	public static function execute( $params, Ai1wmse_S3_Client $s3 = null ) {

		// Set progress
		Ai1wm_Status::info( __( 'Creating an empty archive...', AI1WMSE_PLUGIN_NAME ) );

		// Create empty archive file
		$archive = new Ai1wm_Compressor( ai1wm_archive_path( $params ) );
		$archive->close();

		// Set Amazon S3 client
		if ( is_null( $s3 ) ) {
			$s3 = new Ai1wmse_S3_Client(
				get_option( 'ai1wmse_s3_access_key', ai1wmse_aws_access_key() ),
				get_option( 'ai1wmse_s3_secret_key', ai1wmse_aws_secret_key() ),
				get_option( 'ai1wmse_s3_https_protocol', true )
			);
		}

		// Get region name
		$params['region_name'] = $s3->get_bucket_region( $params['bucket_name'] );

		// Set progress
		Ai1wm_Status::info( __( 'Done creating an empty archive.', AI1WMSE_PLUGIN_NAME ) );

		return $params;
	}
}
