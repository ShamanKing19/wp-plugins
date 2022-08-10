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

class Ai1wmse_Import_Database {

	public static function execute( $params ) {

		$model = new Ai1wmse_Settings;

		// Set progress
		Ai1wm_Status::info( __( 'Updating Amazon S3 settings...', AI1WMSE_PLUGIN_NAME ) );

		// Read settings.json file
		$handle = ai1wm_open( ai1wm_settings_path( $params ), 'r' );

		// Parse settings.json file
		$settings = ai1wm_read( $handle, filesize( ai1wm_settings_path( $params ) ) );
		$settings = json_decode( $settings, true );

		// Close handle
		ai1wm_close( $handle );

		// Update Amazon S3 settings
		$model->set_cron_timestamp( $settings['ai1wmse_s3_cron_timestamp'] );
		$model->set_cron( $settings['ai1wmse_s3_cron'] );
		$model->set_access_key( $settings['ai1wmse_s3_access_key'] );
		$model->set_secret_key( $settings['ai1wmse_s3_secret_key'] );
		$model->set_bucket_name( $settings['ai1wmse_s3_bucket_name'] );
		$model->set_region_name( $settings['ai1wmse_s3_region_name'] );
		$model->set_https_protocol( $settings['ai1wmse_s3_https_protocol'] );
		$model->set_storage_class( $settings['ai1wmse_s3_storage_class'] );
		$model->set_encryption( $settings['ai1wmse_s3_encryption'] );
		$model->set_folder_name( $settings['ai1wmse_s3_folder_name'] );
		$model->set_backups( $settings['ai1wmse_s3_backups'] );
		$model->set_total( $settings['ai1wmse_s3_total'] );
		$model->set_days( $settings['ai1wmse_s3_days'] );
		$model->set_file_chunk_size( $settings['ai1wmse_s3_file_chunk_size'] );
		$model->set_notify_ok_toggle( $settings['ai1wmse_s3_notify_toggle'] );
		$model->set_notify_error_toggle( $settings['ai1wmse_s3_notify_error_toggle'] );
		$model->set_notify_error_subject( $settings['ai1wmse_s3_notify_error_subject'] );
		$model->set_notify_email( $settings['ai1wmse_s3_notify_email'] );

		// Set progress
		Ai1wm_Status::info( __( 'Done updating Amazon S3 settings.', AI1WMSE_PLUGIN_NAME ) );

		return $params;
	}
}
