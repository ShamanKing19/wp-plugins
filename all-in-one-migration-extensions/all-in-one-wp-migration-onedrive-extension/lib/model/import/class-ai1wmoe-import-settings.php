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

class Ai1wmoe_Import_Settings {

	public static function execute( $params ) {

		// Set progress
		Ai1wm_Status::info( __( 'Getting OneDrive settings...', AI1WMOE_PLUGIN_NAME ) );

		$settings = array(
			'ai1wmoe_onedrive_cron_timestamp'          => get_option( 'ai1wmoe_onedrive_cron_timestamp', time() ),
			'ai1wmoe_onedrive_cron'                    => get_option( 'ai1wmoe_onedrive_cron', array() ),
			'ai1wmoe_onedrive_token'                   => get_option( 'ai1wmoe_onedrive_token', false ),
			'ai1wmoe_onedrive_ssl'                     => get_option( 'ai1wmoe_onedrive_ssl', false ),
			'ai1wmoe_onedrive_folder_id'               => get_option( 'ai1wmoe_onedrive_folder_id', false ),
			'ai1wmoe_onedrive_backups'                 => get_option( 'ai1wmoe_onedrive_backups', false ),
			'ai1wmoe_onedrive_total'                   => get_option( 'ai1wmoe_onedrive_total', false ),
			'ai1wmoe_onedrive_days'                    => get_option( 'ai1wmoe_onedrive_days', false ),
			'ai1wmoe_onedrive_file_chunk_size'         => get_option( 'ai1wmoe_onedrive_file_chunk_size', AI1WMOE_DEFAULT_FILE_CHUNK_SIZE ),
			'ai1wmoe_onedrive_notify_toggle'           => get_option( 'ai1wmoe_onedrive_notify_toggle', false ),
			'ai1wmoe_onedrive_notify_error_toggle'     => get_option( 'ai1wmoe_onedrive_notify_error_toggle', false ),
			'ai1wmoe_onedrive_notify_error_subject'    => get_option( 'ai1wmoe_onedrive_notify_error_subject', false ),
			'ai1wmoe_onedrive_notify_email'            => get_option( 'ai1wmoe_onedrive_notify_email', false ),
			'ai1wmoe_onedrive_access_token'            => get_option( 'ai1wmoe_onedrive_access_token', false ),
			'ai1wmoe_onedrive_access_token_expires_in' => get_option( 'ai1wmoe_onedrive_access_token_expires_in', false ),
		);

		// Save settings.json file
		$handle = ai1wm_open( ai1wm_settings_path( $params ), 'w' );
		ai1wm_write( $handle, json_encode( $settings ) );
		ai1wm_close( $handle );

		// Set progress
		Ai1wm_Status::info( __( 'Done getting OneDrive settings.', AI1WMOE_PLUGIN_NAME ) );

		return $params;
	}
}
