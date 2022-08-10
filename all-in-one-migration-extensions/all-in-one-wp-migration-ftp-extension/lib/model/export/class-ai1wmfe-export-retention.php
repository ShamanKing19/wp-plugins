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

class Ai1wmfe_Export_Retention {

	/**
	 * FTP client
	 *
	 * @var Ai1wmfe_FTP_Factory
	 */
	private static $ftp = null;

	public static function execute( $params, Ai1wmfe_FTP_Factory $ftp = null ) {

		// Set FTP client
		if ( is_null( $ftp ) ) {
			$ftp = Ai1wmfe_FTP_Factory::create(
				get_option( 'ai1wmfe_ftp_type', AI1WMFE_FTP_DEFAULT_TYPE ),
				get_option( 'ai1wmfe_ftp_hostname', false ),
				get_option( 'ai1wmfe_ftp_username', false ),
				get_option( 'ai1wmfe_ftp_password', false ),
				get_option( 'ai1wmfe_ftp_authentication', AI1WMFE_FTP_DEFAULT_AUTHENTICATION ),
				get_option( 'ai1wmfe_ftp_key', false ),
				get_option( 'ai1wmfe_ftp_passphrase', false ),
				get_option( 'ai1wmfe_ftp_directory', false ),
				get_option( 'ai1wmfe_ftp_port', AI1WMFE_FTP_DEFAULT_PORT ),
				get_option( 'ai1wmfe_ftp_active', false )
			);
		}

		self::$ftp = $ftp;

		// No backups, no need to apply backup retention
		$backups = self::get_files();
		if ( count( $backups ) === 0 ) {
			return $params;
		}

		// The order is very important - we delete files by date, by size, and finally by total count
		self::delete_backups_older_than();
		self::delete_backups_when_total_size_over();
		self::delete_backups_when_total_count_over();

		return $params;
	}

	private static function delete_backups_older_than() {
		$backups = self::get_files();
		$days    = intval( get_option( 'ai1wmfe_ftp_days', 0 ) );
		if ( $days > 0 ) {
			foreach ( $backups as $backup ) {
				if ( $backup['date'] <= time() - $days * 86400 ) {
					self::delete_file( $backup );
				}
			}
		}
	}

	private static function delete_backups_when_total_size_over() {
		$backups        = self::get_files();
		$retention_size = ai1wm_parse_size( get_option( 'ai1wmfe_ftp_total', 0 ) );

		// Get the size of the latest backup before we remove it
		$size_of_backups = $backups[0]['bytes'];

		// Remove the latest backup, the user should have at least one backup
		array_shift( $backups );

		if ( $retention_size > 0 ) {
			foreach ( $backups as $backup ) {
				if ( $size_of_backups + $backup['bytes'] > $retention_size ) {
					self::delete_file( $backup );
				} else {
					$size_of_backups += $backup['bytes'];
				}
			}
		}
	}

	private static function delete_backups_when_total_count_over() {
		$backups = self::get_files();
		$limit   = intval( get_option( 'ai1wmfe_ftp_backups', 0 ) );

		if ( $limit > 0 ) {
			if ( count( $backups ) > $limit ) {
				for ( $i = $limit; $i < count( $backups ); $i++ ) {
					self::delete_file( $backups[ $i ] );
				}
			}
		}
	}

	private static function get_files() {
		$items = self::$ftp->list_folder( ai1wm_archive_folder() );

		$backups = array();
		foreach ( $items as $item ) {
			if ( $item['type'] === 'file' && pathinfo( $item['name'], PATHINFO_EXTENSION ) === 'wpress' ) {
				$backups[] = $item;
			}
		}

		usort( $backups, 'Ai1wmfe_Export_Retention::sort_by_date_desc' );
		return $backups;
	}

	public static function sort_by_date_desc( $first_backup, $second_backup ) {
		return intval( $second_backup['date'] ) - intval( $first_backup['date'] );
	}

	private static function delete_file( $backup ) {
		return self::$ftp->remove_file( $backup['path'] );
	}
}
