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

class Ai1wmme_Export_Controller {

	public static function sites() {
		if ( is_multisite() ) {
			Ai1wm_Template::render( 'export/sites', array(), AI1WMME_TEMPLATES_PATH );
		}
	}

	public static function sites_paginator() {
		$sites       = ai1wmme_sites();
		$sites_short = array();
		foreach ( $sites as $k => $site ) {
			$sites_short[ $k ][] = $site['BlogID'];
			$sites_short[ $k ][] = $site['Domain'] . untrailingslashit( $site['Path'] );
		}

		$per_page = get_user_option( 'sites_network_per_page' );
		if ( empty( $per_page ) ) {
			$per_page = 10;
		}
		echo json_encode(
			array(
				'sites'          => $sites_short,
				'sites_per_page' => $per_page,
			)
		);
		exit;
	}

	public static function inactive_themes() {
		Ai1wm_Template::render(
			'export/inactive-themes',
			array(),
			AI1WMME_TEMPLATES_PATH
		);
	}

	public static function inactive_plugins() {
		Ai1wm_Template::render(
			'export/inactive-plugins',
			array(),
			AI1WMME_TEMPLATES_PATH
		);
	}

	public static function cache_files() {
		Ai1wm_Template::render(
			'export/cache-files',
			array(),
			AI1WMME_TEMPLATES_PATH
		);
	}

	public static function exclude_files() {
		Ai1wm_Template::render(
			'export/exclude-files',
			array(),
			AI1WMME_TEMPLATES_PATH
		);
	}

	public static function list_files() {
		check_ajax_referer( 'ai1wmme_list', 'security' );

		$folder_path = WP_CONTENT_DIR;
		if ( ! empty( $_POST['folder'] ) ) {
			// prevent exploring outside the wp folder
			$subfolder   = str_replace( '../', '', $_POST['folder'] );
			$folder_path = sprintf( '%s/%s', $folder_path, $subfolder );
		}
		if ( ! is_file( $folder_path ) && ! is_dir( $folder_path ) ) {
			echo json_encode( array( 'success' => false ) );
			exit;
		}

		$files = list_files( $folder_path, 1 );

		$response = array( 'success' => true, 'files' => array() );
		foreach ( $files as $file ) {
			$response['files'][] = array(
				'name'    => wp_basename( $file ),
				'path'    => trim( str_replace( WP_CONTENT_DIR, '', $file ), '/' ),
				'toggled' => false, //needed for better reactivity in vue
				'checked' => false,
				'type'    => is_dir( $file ) ? 'folder' : 'file',
				'date'    => human_time_diff( filemtime( $file ) ),
			);
		}

		usort( $response['files'], 'Ai1wmme_Export_Controller::sort_by_type_desc_name_asc' );
		echo json_encode( $response );
		exit;
	}

	public static function sort_by_type_desc_name_asc( $first_item, $second_item ) {
		$sorted_items = strcasecmp( $second_item['type'], $first_item['type'] );
		if ( $sorted_items !== 0 ) {
			return $sorted_items;
		}

		return strcasecmp( $first_item['name'], $second_item['name'] );
	}
}
