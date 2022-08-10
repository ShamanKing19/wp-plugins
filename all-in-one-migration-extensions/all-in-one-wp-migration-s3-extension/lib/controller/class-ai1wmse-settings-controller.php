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

class Ai1wmse_Settings_Controller {

	public static function index() {
		$model = new Ai1wmse_Settings;

		$s3_backup_schedules   = get_option( 'ai1wmse_s3_cron', array() );
		$s3_cron_timestamp     = get_option( 'ai1wmse_s3_cron_timestamp', time() );
		$last_backup_timestamp = get_option( 'ai1wmse_s3_timestamp', false );

		$last_backup_date = $model->get_last_backup_date( $last_backup_timestamp );
		$next_backup_date = $model->get_next_backup_date( $s3_backup_schedules );

		$regions = $model->get_regions();

		try {
			if ( ( $buckets = $model->get_buckets() ) ) {
				if ( ! in_array( $model->get_bucket_name(), $buckets ) ) {
					$buckets[] = $model->get_bucket_name();
				}
			}
		} catch ( Ai1wmse_Error_Exception $e ) {
			$buckets = false;
		}

		Ai1wm_Template::render(
			'settings/index',
			array(
				's3_backup_schedules' => $s3_backup_schedules,
				's3_cron_timestamp'   => $s3_cron_timestamp,
				'notify_ok_toggle'    => get_option( 'ai1wmse_s3_notify_toggle', false ),
				'notify_error_toggle' => get_option( 'ai1wmse_s3_notify_error_toggle', false ),
				'notify_email'        => get_option( 'ai1wmse_s3_notify_email', get_option( 'admin_email', false ) ),
				'last_backup_date'    => $last_backup_date,
				'next_backup_date'    => $next_backup_date,
				'access_key'          => get_option( 'ai1wmse_s3_access_key', ai1wmse_aws_access_key() ),
				'secret_key'          => get_option( 'ai1wmse_s3_secret_key', ai1wmse_aws_secret_key() ),
				'https_protocol'      => get_option( 'ai1wmse_s3_https_protocol', true ),
				'bucket_name'         => get_option( 'ai1wmse_s3_bucket_name', ai1wm_archive_bucket() ),
				'region_name'         => get_option( 'ai1wmse_s3_region_name', ai1wmse_aws_region_name( AI1WMSE_S3_DEFAULT_REGION_NAME ) ),
				'folder_name'         => get_option( 'ai1wmse_s3_folder_name', '' ),
				'file_chunk_size'     => get_option( 'ai1wmse_s3_file_chunk_size', AI1WMSE_DEFAULT_FILE_CHUNK_SIZE ),
				'storage_class'       => get_option( 'ai1wmse_s3_storage_class', AI1WMSE_S3_DEFAULT_STORAGE_CLASS ),
				'encryption'          => get_option( 'ai1wmse_s3_encryption', false ),
				'backups'             => get_option( 'ai1wmse_s3_backups', false ),
				'total'               => get_option( 'ai1wmse_s3_total', false ),
				'days'                => get_option( 'ai1wmse_s3_days', false ),
				'regions'             => $regions,
				'buckets'             => $buckets,
			),
			AI1WMSE_TEMPLATES_PATH
		);
	}

	public static function connection( $params = array() ) {
		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Amazon S3 update
		if ( isset( $params['ai1wmse_s3_update'] ) ) {
			$model = new Ai1wmse_Settings;

			// Access key
			if ( isset( $params['ai1wmse_s3_access_key'] ) ) {
				$model->set_access_key( trim( $params['ai1wmse_s3_access_key'] ) );
			}

			// Secret key
			if ( ! empty( $params['ai1wmse_s3_secret_key'] ) ) {
				$model->set_secret_key( trim( $params['ai1wmse_s3_secret_key'] ) );
			}

			// HTTPS protocol
			if ( ! empty( $params['ai1wmse_s3_https_protocol'] ) ) {
				$model->set_https_protocol( 1 );
			} else {
				$model->set_https_protocol( 0 );
			}

			try {
				// Get buckets
				$model->get_buckets();

				// Set message
				Ai1wm_Message::flash( 'success', __( 'Amazon S3 connection is successfully established.', AI1WMSE_PLUGIN_NAME ) );
			} catch ( Ai1wmse_Error_Exception $e ) {
				Ai1wm_Message::flash( 'error', $e->getMessage() );
			}
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmse_settings' ) );
		exit;
	}

	public static function settings( $params = array() ) {
		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Amazon S3 update
		if ( isset( $params['ai1wmse_s3_update'] ) ) {
			$model = new Ai1wmse_Settings;

			// Cron timestamp update
			if ( ! empty( $params['ai1wmse_s3_cron_timestamp'] ) && ( $cron_timestamp = strtotime( $params['ai1wmse_s3_cron_timestamp'], current_time( 'timestamp' ) ) ) ) {
				$model->set_cron_timestamp( strtotime( get_gmt_from_date( date( 'Y-m-d H:i:s', $cron_timestamp ) ) ) );
			} else {
				$model->set_cron_timestamp( time() );
			}

			// Region name
			if ( isset( $params['ai1wmse_s3_region_name'] ) ) {
				$model->set_region_name( trim( $params['ai1wmse_s3_region_name'] ) );
			}

			// Bucket name
			if ( isset( $params['ai1wmse_s3_bucket_name'] ) ) {
				try {
					// Create bucket
					$model->create_bucket( trim( $params['ai1wmse_s3_bucket_name'] ) );

					// Set bucket name
					$model->set_bucket_name( trim( $params['ai1wmse_s3_bucket_name'] ) );

					// Set message
					Ai1wm_Message::flash( 'settings', __( 'Your changes have been saved.', AI1WMSE_PLUGIN_NAME ) );
				} catch ( Ai1wmse_Error_Exception $e ) {
					Ai1wm_Message::flash( 'bucket', $e->getMessage() );
				}
			}

			// Folder name
			if ( isset( $params['ai1wmse_s3_folder_name'] ) ) {
				$model->set_folder_name( trim( $params['ai1wmse_s3_folder_name'], " \t\n\r\0\x0B/" ) );
			}

			// Storage class
			if ( isset( $params['ai1wmse_s3_storage_class'] ) ) {
				$model->set_storage_class( trim( $params['ai1wmse_s3_storage_class'] ) );
			}

			// Bucket encryption
			if ( isset( $params['ai1wmse_s3_encryption'] ) ) {
				$model->set_encryption( trim( $params['ai1wmse_s3_encryption'] ) );
			} else {
				$model->set_encryption( false );
			}

			// Cron update
			if ( ! empty( $params['ai1wmse_s3_cron'] ) ) {
				$model->set_cron( (array) $params['ai1wmse_s3_cron'] );
			} else {
				$model->set_cron( array() );
			}

			// Set number of backups
			if ( ! empty( $params['ai1wmse_s3_backups'] ) ) {
				$model->set_backups( (int) $params['ai1wmse_s3_backups'] );
			} else {
				$model->set_backups( 0 );
			}

			// Set size of backups
			if ( ! empty( $params['ai1wmse_s3_total'] ) && ! empty( $params['ai1wmse_s3_total_unit'] ) ) {
				$model->set_total( (int) $params['ai1wmse_s3_total'] . trim( $params['ai1wmse_s3_total_unit'] ) );
			} else {
				$model->set_total( 0 );
			}

			// Set age of backups
			if ( ! empty( $params['ai1wmse_s3_days'] ) ) {
				$model->set_days( (int) $params['ai1wmse_s3_days'] );
			} else {
				$model->set_days( 0 );
			}

			// Set file chunk size
			if ( ! empty( $params['ai1wmse_s3_file_chunk_size'] ) ) {
				$model->set_file_chunk_size( $params['ai1wmse_s3_file_chunk_size'] );
			} else {
				$model->set_file_chunk_size( AI1WMSE_DEFAULT_FILE_CHUNK_SIZE );
			}

			// Set notify ok toggle
			$model->set_notify_ok_toggle( isset( $params['ai1wmse_s3_notify_toggle'] ) );

			// Set notify error toggle
			$model->set_notify_error_toggle( isset( $params['ai1wmse_s3_notify_error_toggle'] ) );

			// Set notify email
			$model->set_notify_email( trim( $params['ai1wmse_s3_notify_email'] ) );
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmse_settings' ) );
		exit;
	}

	public static function notify_ok_toggle() {
		$model = new Ai1wmse_Settings;
		if ( ( $notify_ok_toggle = $model->get_notify_ok_toggle() ) ) {
			return $notify_ok_toggle;
		}
	}

	public static function notify_error_toggle() {
		$model = new Ai1wmse_Settings;
		if ( ( $notify_error_toggle = $model->get_notify_error_toggle() ) ) {
			return $notify_error_toggle;
		}
	}

	public static function notify_error_subject() {
		$model = new Ai1wmse_Settings;
		if ( ( $notify_error_subject = $model->get_notify_error_subject() ) ) {
			return $notify_error_subject;
		}
	}

	public static function notify_email() {
		$model = new Ai1wmse_Settings;
		if ( ( $notify_email = $model->get_notify_email() ) ) {
			return $notify_email;
		}
	}
}
