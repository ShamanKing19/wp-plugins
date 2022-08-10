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

class Ai1wmoe_Import_Controller {

	public static function button() {
		return Ai1wm_Template::get_content(
			'import/button',
			array( 'token' => get_option( 'ai1wmoe_onedrive_token', false ) ),
			AI1WMOE_TEMPLATES_PATH
		);
	}

	public static function picker() {
		Ai1wm_Template::render(
			'import/picker',
			array(),
			AI1WMOE_TEMPLATES_PATH
		);
	}

	public static function browser( $params = array() ) {
		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_GET );
		}

		// Set folder ID
		$folder_id = null;
		if ( isset( $params['folder_id'] ) ) {
			$folder_id = trim( $params['folder_id'] );
		}

		// Set OneDrive client
		$onedrive = new Ai1wmoe_OneDrive_Client(
			get_option( 'ai1wmoe_onedrive_token', false ),
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
			if ( $item['type'] === 'folder' || pathinfo( $item['name'], PATHINFO_EXTENSION ) === 'wpress' ) {
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
		usort( $response['items'], 'Ai1wmoe_Import_Controller::sort_by_type_desc_name_asc' );

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

	public static function pro() {
		return Ai1wm_Template::get_content( 'import/pro', array(), AI1WMOE_TEMPLATES_PATH );
	}
}
