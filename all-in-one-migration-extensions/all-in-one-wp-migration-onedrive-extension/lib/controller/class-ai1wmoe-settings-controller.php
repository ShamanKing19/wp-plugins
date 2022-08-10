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

class Ai1wmoe_Settings_Controller {

	public static function index() {
		$model = new Ai1wmoe_Settings;

		$onedrive_backup_schedules = get_option( 'ai1wmoe_onedrive_cron', array() );
		$onedrive_cron_timestamp   = get_option( 'ai1wmoe_onedrive_cron_timestamp', time() );
		$last_backup_timestamp     = get_option( 'ai1wmoe_onedrive_timestamp', false );

		$last_backup_date = $model->get_last_backup_date( $last_backup_timestamp );
		$next_backup_date = $model->get_next_backup_date( $onedrive_backup_schedules );

		Ai1wm_Template::render(
			'settings/index',
			array(
				'onedrive_backup_schedules' => $onedrive_backup_schedules,
				'onedrive_cron_timestamp'   => $onedrive_cron_timestamp,
				'notify_ok_toggle'          => get_option( 'ai1wmoe_onedrive_notify_toggle', false ),
				'notify_error_toggle'       => get_option( 'ai1wmoe_onedrive_notify_error_toggle', false ),
				'notify_email'              => get_option( 'ai1wmoe_onedrive_notify_email', get_option( 'admin_email', false ) ),
				'last_backup_date'          => $last_backup_date,
				'next_backup_date'          => $next_backup_date,
				'folder_id'                 => get_option( 'ai1wmoe_onedrive_folder_id', false ),
				'file_chunk_size'           => get_option( 'ai1wmoe_onedrive_file_chunk_size', AI1WMOE_DEFAULT_FILE_CHUNK_SIZE ),
				'ssl'                       => get_option( 'ai1wmoe_onedrive_ssl', true ),
				'timestamp'                 => get_option( 'ai1wmoe_onedrive_timestamp', false ),
				'token'                     => get_option( 'ai1wmoe_onedrive_token', false ),
				'backups'                   => get_option( 'ai1wmoe_onedrive_backups', false ),
				'total'                     => get_option( 'ai1wmoe_onedrive_total', false ),
				'days'                      => get_option( 'ai1wmoe_onedrive_days', false ),
			),
			AI1WMOE_TEMPLATES_PATH
		);
	}

	public static function picker() {
		Ai1wm_Template::render(
			'settings/picker',
			array(),
			AI1WMOE_TEMPLATES_PATH
		);
	}

	public static function settings( $params = array() ) {
		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// OneDrive update
		if ( isset( $params['ai1wmoe_onedrive_update'] ) ) {
			$model = new Ai1wmoe_Settings;

			// Cron timestamp update
			if ( ! empty( $params['ai1wmoe_onedrive_cron_timestamp'] ) && ( $cron_timestamp = strtotime( $params['ai1wmoe_onedrive_cron_timestamp'], current_time( 'timestamp' ) ) ) ) {
				$model->set_cron_timestamp( strtotime( get_gmt_from_date( date( 'Y-m-d H:i:s', $cron_timestamp ) ) ) );
			} else {
				$model->set_cron_timestamp( time() );
			}

			// Cron update
			if ( ! empty( $params['ai1wmoe_onedrive_cron'] ) ) {
				$model->set_cron( (array) $params['ai1wmoe_onedrive_cron'] );
			} else {
				$model->set_cron( array() );
			}

			// Set SSL mode
			if ( ! empty( $params['ai1wmoe_onedrive_ssl'] ) ) {
				$model->set_ssl( 0 );
			} else {
				$model->set_ssl( 1 );
			}

			// Set number of backups
			if ( ! empty( $params['ai1wmoe_onedrive_backups'] ) ) {
				$model->set_backups( (int) $params['ai1wmoe_onedrive_backups'] );
			} else {
				$model->set_backups( 0 );
			}

			// Set size of backups
			if ( ! empty( $params['ai1wmoe_onedrive_total'] ) && ! empty( $params['ai1wmoe_onedrive_total_unit'] ) ) {
				$model->set_total( (int) $params['ai1wmoe_onedrive_total'] . trim( $params['ai1wmoe_onedrive_total_unit'] ) );
			} else {
				$model->set_total( 0 );
			}

			// Set age of backups
			if ( ! empty( $params['ai1wmoe_onedrive_days'] ) ) {
				$model->set_days( (int) $params['ai1wmoe_onedrive_days'] );
			} else {
				$model->set_days( 0 );
			}

			// Set file chunk size
			if ( ! empty( $params['ai1wmoe_onedrive_file_chunk_size'] ) ) {
				$model->set_file_chunk_size( $params['ai1wmoe_onedrive_file_chunk_size'] );
			} else {
				$model->set_file_chunk_size( AI1WMOE_DEFAULT_FILE_CHUNK_SIZE );
			}

			// Set folder ID
			$model->set_folder_id( trim( $params['ai1wmoe_onedrive_folder_id'] ) );

			// Set notify ok toggle
			$model->set_notify_ok_toggle( isset( $params['ai1wmoe_onedrive_notify_toggle'] ) );

			// Set notify error toggle
			$model->set_notify_error_toggle( isset( $params['ai1wmoe_onedrive_notify_error_toggle'] ) );

			// Set notify email
			$model->set_notify_email( trim( $params['ai1wmoe_onedrive_notify_email'] ) );

			// Set message
			Ai1wm_Message::flash( 'settings', __( 'Your changes have been saved.', AI1WMOE_PLUGIN_NAME ) );
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmoe_settings' ) );
		exit;
	}

	public static function revoke( $params = array() ) {
		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// OneDrive logout
		if ( isset( $params['ai1wmoe_onedrive_logout'] ) ) {
			$model = new Ai1wmoe_Settings;
			$model->revoke();
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmoe_settings' ) );
		exit;
	}

	public static function account() {
		ai1wm_setup_environment();

		try {
			$model = new Ai1wmoe_Settings;
			if ( ( $account = $model->get_account() ) ) {
				echo json_encode( $account );
				exit;
			}
		} catch ( Ai1wmoe_Error_Exception $e ) {
			status_header( 400 );
			echo json_encode( array( 'message' => $e->getMessage() ) );
			exit;
		}
	}

	public static function selector( $params = array() ) {
		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_GET );
		}

		// Set folder ID
		$folder_id = null;
		if ( isset( $params['folder_id'] ) ) {
			$folder_id = $params['folder_id'];
		}

		// Set OneDrive client
		$onedrive = new Ai1wmoe_OneDrive_Client(
			get_option( 'ai1wmoe_onedrive_token' ),
			get_option( 'ai1wmoe_onedrive_ssl', true )
		);

		// Get files and directories
		if ( $folder_id ) {
			$items = $onedrive->list_folder( $folder_id );
		} else {
			$items = $onedrive->list_drive();
		}

		// Set folder structure
		$response = array( 'items' => array(), 'num_hidden_files' => 0 );

		// Set folder items
		foreach ( $items as $item ) {
			if ( $item['type'] === 'folder' ) {
				$response['items'][] = array(
					'id'    => isset( $item['id'] ) ? $item['id'] : null,
					'name'  => isset( $item['name'] ) ? $item['name'] : null,
					'date'  => isset( $item['date'] ) ? human_time_diff( $item['date'] ) : null,
					'size'  => isset( $item['bytes'] ) ? ai1wm_size_format( $item['bytes'] ) : null,
					'bytes' => isset( $item['bytes'] ) ? $item['bytes'] : null,
					'type'  => isset( $item['type'] ) ? $item['type'] : null,
				);
			} else {
				$response['num_hidden_files']++;
			}
		}

		// Sort items by type desc and name asc
		usort( $response['items'], 'Ai1wmoe_Settings_Controller::sort_by_type_desc_name_asc' );

		echo json_encode( $response );
		exit;
	}

	public static function folder() {
		ai1wm_setup_environment();

		try {
			// Set OneDrive client
			$onedrive = new Ai1wmoe_OneDrive_Client(
				get_option( 'ai1wmoe_onedrive_token' ),
				get_option( 'ai1wmoe_onedrive_ssl', true )
			);

			// Get folder ID
			$folder_id = get_option( 'ai1wmoe_onedrive_folder_id', false );

			// Create folder
			if ( ! ( $folder_id = $onedrive->get_folder_id_by_id( $folder_id ) ) ) {
				if ( ! ( $folder_id = $onedrive->get_folder_id_by_path( ai1wm_archive_folder() ) ) ) {
					$folder_id = $onedrive->create_folder( ai1wm_archive_folder() );
				}
			}

			// Set folder ID
			update_option( 'ai1wmoe_onedrive_folder_id', $folder_id );

			// Get folder name
			if ( ! ( $folder_name = $onedrive->get_folder_path_by_id( $folder_id ) ) ) {
				status_header( 400 );
				echo json_encode(
					array(
						'message' => __(
							'We were unable to retrieve your backup folder details. ' .
							'Microsoft servers are overloaded at the moment. ' .
							'Please wait for a few minutes and try again by refreshing the page.',
							AI1WMOE_PLUGIN_NAME
						),
					)
				);
				exit;
			}
		} catch ( Ai1wmoe_Error_Exception $e ) {
			status_header( 400 );
			echo json_encode( array( 'message' => $e->getMessage() ) );
			exit;
		}

		echo json_encode( array( 'id' => $folder_id, 'name' => $folder_name ) );
		exit;
	}

	public static function sort_by_type_desc_name_asc( $first_item, $second_item ) {
		$sorted_items = strcasecmp( $second_item['type'], $first_item['type'] );
		if ( $sorted_items !== 0 ) {
			return $sorted_items;
		}

		return strcasecmp( $first_item['name'], $second_item['name'] );
	}

	public static function notify_ok_toggle() {
		$model = new Ai1wmoe_Settings;
		if ( ( $notify_ok_toggle = $model->get_notify_ok_toggle() ) ) {
			return $notify_ok_toggle;
		}
	}

	public static function notify_error_toggle() {
		$model = new Ai1wmoe_Settings;
		if ( ( $notify_error_toggle = $model->get_notify_error_toggle() ) ) {
			return $notify_error_toggle;
		}
	}

	public static function notify_error_subject() {
		$model = new Ai1wmoe_Settings;
		if ( ( $notify_error_subject = $model->get_notify_error_subject() ) ) {
			return $notify_error_subject;
		}
	}

	public static function notify_email() {
		$model = new Ai1wmoe_Settings;
		if ( ( $notify_email = $model->get_notify_email() ) ) {
			return $notify_email;
		}
	}
}
