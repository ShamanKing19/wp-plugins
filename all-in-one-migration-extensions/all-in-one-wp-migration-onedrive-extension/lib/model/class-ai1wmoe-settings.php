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

class Ai1wmoe_Settings {

	public function revoke() {
		// Remove token option
		delete_option( 'ai1wmoe_onedrive_token' );
		delete_option( 'ai1wmoe_onedrive_access_token' );
		delete_option( 'ai1wmoe_onedrive_access_token_expires_in' );

		// Remove cron option
		delete_option( 'ai1wmoe_onedrive_cron' );

		// Reset cron schedules
		Ai1wm_Cron::clear( 'ai1wmoe_onedrive_hourly_export' );
		Ai1wm_Cron::clear( 'ai1wmoe_onedrive_daily_export' );
		Ai1wm_Cron::clear( 'ai1wmoe_onedrive_weekly_export' );
		Ai1wm_Cron::clear( 'ai1wmoe_onedrive_monthly_export' );
	}

	public function get_last_backup_date( $last_backup_timestamp ) {
		if ( $last_backup_timestamp ) {
			$last_backup_date = get_date_from_gmt( date( 'Y-m-d H:i:s', $last_backup_timestamp ), 'F j, Y g:i a' );
		} else {
			$last_backup_date = __( 'None', AI1WMOE_PLUGIN_NAME );
		}

		return $last_backup_date;
	}

	public function get_next_backup_date( $schedules ) {
		$future_backup_timestamps = array();

		foreach ( $schedules as $schedule ) {
			$future_backup_timestamps[] = wp_next_scheduled(
				"ai1wmoe_onedrive_{$schedule}_export",
				array(
					array(
						'secret_key' => get_option( AI1WM_SECRET_KEY ),
						'onedrive'   => 1,
					),
				)
			);
		}

		sort( $future_backup_timestamps );

		if ( isset( $future_backup_timestamps[0] ) ) {
			$next_backup_date = get_date_from_gmt( date( 'Y-m-d H:i:s', $future_backup_timestamps[0] ), 'F j, Y g:i a' );
		} else {
			$next_backup_date = __( 'None', AI1WMOE_PLUGIN_NAME );
		}

		return $next_backup_date;
	}

	public function get_account( Ai1wmoe_OneDrive_Client $onedrive = null ) {
		// Set OneDrive client
		if ( is_null( $onedrive ) ) {
			$onedrive = new Ai1wmoe_OneDrive_Client(
				get_option( 'ai1wmoe_onedrive_token', false ),
				get_option( 'ai1wmoe_onedrive_ssl', true )
			);
		}

		// Get user information
		$user = $onedrive->get_user_info();

		// Set user email
		$email = null;
		if ( isset( $user['userPrincipalName'] ) ) {
			$email = $user['userPrincipalName'];
		}

		// Get account info
		$account = $onedrive->get_account_info();

		// Set account name
		$name = null;
		if ( isset( $account['owner']['user']['displayName'] ) ) {
			$name = $account['owner']['user']['displayName'];
		}

		// Set used quota
		$used = null;
		if ( isset( $account['quota']['used'] ) ) {
			$used = $account['quota']['used'];
		}

		// Set total quota
		$total = null;
		if ( isset( $account['quota']['total'] ) ) {
			$total = $account['quota']['total'];
		}

		return array(
			'name'     => $name,
			'email'    => $email,
			'used'     => ai1wm_size_format( $used ),
			'total'    => ai1wm_size_format( $total ),
			'progress' => ceil( ( $used / $total ) * 100 ),
		);
	}

	public function set_cron_timestamp( $timestamp ) {
		return update_option( 'ai1wmoe_onedrive_cron_timestamp', $timestamp );
	}

	public function get_cron_timestamp() {
		return get_option( 'ai1wmoe_onedrive_cron_timestamp', time() );
	}

	/**
	 * Set cron schedules
	 *
	 * @param  array   $schedules List of schedules
	 * @return boolean
	 */
	public function set_cron( $schedules ) {
		ai1wm_cache_flush();

		// Reset cron schedules
		Ai1wm_Cron::clear( 'ai1wmoe_onedrive_hourly_export' );
		Ai1wm_Cron::clear( 'ai1wmoe_onedrive_daily_export' );
		Ai1wm_Cron::clear( 'ai1wmoe_onedrive_weekly_export' );
		Ai1wm_Cron::clear( 'ai1wmoe_onedrive_monthly_export' );

		// Update cron schedules
		foreach ( $schedules as $schedule ) {
			Ai1wm_Cron::add(
				"ai1wmoe_onedrive_{$schedule}_export",
				$schedule,
				$this->get_cron_timestamp(),
				array(
					array(
						'secret_key' => get_option( AI1WM_SECRET_KEY ),
						'onedrive'   => 1,
					),
				)
			);
		}

		return update_option( 'ai1wmoe_onedrive_cron', $schedules );
	}

	public function get_cron() {
		return get_option( 'ai1wmoe_onedrive_cron', array() );
	}

	public function set_token( $token ) {
		return update_option( 'ai1wmoe_onedrive_token', $token );
	}

	public function get_token() {
		return get_option( 'ai1wmoe_onedrive_token', false );
	}

	public function set_ssl( $mode ) {
		return update_option( 'ai1wmoe_onedrive_ssl', $mode );
	}

	public function get_ssl() {
		return get_option( 'ai1wmoe_onedrive_ssl', false );
	}

	public function set_backups( $number ) {
		return update_option( 'ai1wmoe_onedrive_backups', $number );
	}

	public function get_backups() {
		return get_option( 'ai1wmoe_onedrive_backups', false );
	}

	public function set_total( $size ) {
		return update_option( 'ai1wmoe_onedrive_total', $size );
	}

	public function get_total() {
		return get_option( 'ai1wmoe_onedrive_total', false );
	}

	public function set_days( $days ) {
		return update_option( 'ai1wmoe_onedrive_days', $days );
	}

	public function get_days() {
		return get_option( 'ai1wmoe_onedrive_days', false );
	}

	public function set_folder_id( $folder_id ) {
		return update_option( 'ai1wmoe_onedrive_folder_id', $folder_id );
	}

	public function get_folder_id() {
		return get_option( 'ai1wmoe_onedrive_folder_id', false );
	}

	public function set_file_chunk_size( $file_chunk_size ) {
		return update_option( 'ai1wmoe_onedrive_file_chunk_size', $file_chunk_size );
	}

	public function get_file_chunk_size() {
		return get_option( 'ai1wmoe_onedrive_file_chunk_size', false );
	}

	public function set_notify_ok_toggle( $toggle ) {
		return update_option( 'ai1wmoe_onedrive_notify_toggle', $toggle );
	}

	public function get_notify_ok_toggle() {
		return get_option( 'ai1wmoe_onedrive_notify_toggle', false );
	}

	public function set_notify_error_toggle( $toggle ) {
		return update_option( 'ai1wmoe_onedrive_notify_error_toggle', $toggle );
	}

	public function get_notify_error_toggle() {
		return get_option( 'ai1wmoe_onedrive_notify_error_toggle', false );
	}

	public function set_notify_error_subject( $subject ) {
		return update_option( 'ai1wmoe_onedrive_notify_error_subject', $subject );
	}

	public function get_notify_error_subject() {
		return get_option( 'ai1wmoe_onedrive_notify_error_subject', sprintf( __( '❌ Backup to OneDrive has failed (%s)', AI1WMOE_PLUGIN_NAME ), parse_url( site_url(), PHP_URL_HOST ) . parse_url( site_url(), PHP_URL_PATH ) ) );
	}

	public function set_notify_email( $email ) {
		return update_option( 'ai1wmoe_onedrive_notify_email', $email );
	}

	public function get_notify_email() {
		return get_option( 'ai1wmoe_onedrive_notify_email', false );
	}

	public function set_access_token( $access_token ) {
		return update_option( 'ai1wmoe_onedrive_access_token', $access_token );
	}

	public function get_access_token() {
		return get_option( 'ai1wmoe_onedrive_access_token', false );
	}

	public function set_access_token_expires_in( $expires_in ) {
		return update_option( 'ai1wmoe_onedrive_access_token_expires_in', $expires_in );
	}

	public function get_access_token_expires_in() {
		return get_option( 'ai1wmoe_onedrive_access_token_expires_in', false );
	}
}
