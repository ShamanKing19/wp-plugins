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

class Ai1wmse_Import_Settings {

	public static function execute( $params ) {

		// Set progress
		Ai1wm_Status::info( __( 'Getting Amazon S3 settings...', AI1WMSE_PLUGIN_NAME ) );

		$settings = array(
			'ai1wmse_s3_cron_timestamp'       => get_option( 'ai1wmse_s3_cron_timestamp', time() ),
			'ai1wmse_s3_cron'                 => get_option( 'ai1wmse_s3_cron', array() ),
			'ai1wmse_s3_access_key'           => get_option( 'ai1wmse_s3_access_key', ai1wmse_aws_access_key() ),
			'ai1wmse_s3_secret_key'           => get_option( 'ai1wmse_s3_secret_key', ai1wmse_aws_secret_key() ),
			'ai1wmse_s3_bucket_name'          => get_option( 'ai1wmse_s3_bucket_name', ai1wm_archive_bucket() ),
			'ai1wmse_s3_region_name'          => get_option( 'ai1wmse_s3_region_name', ai1wmse_aws_region_name( AI1WMSE_S3_DEFAULT_REGION_NAME ) ),
			'ai1wmse_s3_https_protocol'       => get_option( 'ai1wmse_s3_https_protocol', true ),
			'ai1wmse_s3_storage_class'        => get_option( 'ai1wmse_s3_storage_class', AI1WMSE_S3_DEFAULT_STORAGE_CLASS ),
			'ai1wmse_s3_encryption'           => get_option( 'ai1wmse_s3_encryption', false ),
			'ai1wmse_s3_folder_name'          => get_option( 'ai1wmse_s3_folder_name', '' ),
			'ai1wmse_s3_backups'              => get_option( 'ai1wmse_s3_backups', false ),
			'ai1wmse_s3_total'                => get_option( 'ai1wmse_s3_total', false ),
			'ai1wmse_s3_days'                 => get_option( 'ai1wmse_s3_days', false ),
			'ai1wmse_s3_file_chunk_size'      => get_option( 'ai1wmse_s3_file_chunk_size', AI1WMSE_DEFAULT_FILE_CHUNK_SIZE ),
			'ai1wmse_s3_notify_toggle'        => get_option( 'ai1wmse_s3_notify_toggle', false ),
			'ai1wmse_s3_notify_error_toggle'  => get_option( 'ai1wmse_s3_notify_error_toggle', false ),
			'ai1wmse_s3_notify_error_subject' => get_option( 'ai1wmse_s3_notify_error_subject', false ),
			'ai1wmse_s3_notify_email'         => get_option( 'ai1wmse_s3_notify_email', false ),
		);

		// Save settings.json file
		$handle = ai1wm_open( ai1wm_settings_path( $params ), 'w' );
		ai1wm_write( $handle, json_encode( $settings ) );
		ai1wm_close( $handle );

		// Set progress
		Ai1wm_Status::info( __( 'Done getting Amazon S3 settings.', AI1WMSE_PLUGIN_NAME ) );

		return $params;
	}
}
