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

class Ai1wmoe_OneDrive_Client {

	const API_URL = 'https://graph.microsoft.com/v1.0';

	/**
	 * OAuth refresh token
	 *
	 * @var string
	 */
	protected $refresh_token = null;

	/**
	 * Upload URL
	 *
	 * @var string
	 */
	protected $upload_url = null;

	/**
	 * SSL mode
	 *
	 * @var boolean
	 */
	protected $ssl = null;

	public function __construct( $refresh_token, $ssl = true ) {
		$this->refresh_token = $refresh_token;
		$this->ssl           = $ssl;
	}

	/**
	 * Load upload URL
	 *
	 * @param  string $url Upload URL
	 * @return void
	 */
	public function load_upload_url( $url ) {
		$this->upload_url = $url;
	}

	/**
	 * List root drive
	 *
	 * @return array
	 */
	public function list_drive() {
		$api = new Ai1wmoe_OneDrive_Curl;
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( '/me/drive/root/children' );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmoe_Error_Exception $e ) {
			throw $e;
		}

		$items = array();
		if ( isset( $response['value'] ) ) {
			foreach ( $response['value'] as $value ) {
				$items[] = array(
					'id'    => isset( $value['id'] ) ? $value['id'] : null,
					'name'  => isset( $value['name'] ) ? $value['name'] : null,
					'date'  => isset( $value['createdDateTime'] ) ? strtotime( $value['createdDateTime'] ) : null,
					'bytes' => isset( $value['size'] ) ? $value['size'] : null,
					'type'  => isset( $value['folder'] ) ? 'folder' : 'file',
				);
			}
		}

		return $items;
	}

	/**
	 * Get folder ID by path
	 *
	 * @param  string $folder_path Folder path
	 * @return string
	 */
	public function get_folder_id_by_path( $folder_path ) {
		$api = new Ai1wmoe_OneDrive_Curl;
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/me/drive/root:/%s', $this->rawurlencode_path( $folder_path ) ) );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmoe_Error_Exception $e ) {
			$response = array();
		}

		if ( ! isset( $response['deleted'] ) ) {
			if ( isset( $response['id'] ) ) {
				return $response['id'];
			}
		}
	}

	/**
	 * Get folder path by ID
	 *
	 * @param  string $folder_id
	 * @return string $folder_path Folder path
	 */
	public function get_folder_path_by_id( $folder_id ) {
		$api = new Ai1wmoe_OneDrive_Curl;
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/me/drive/items/%s', $this->rawurlencode_path( $folder_id ) ) );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmoe_Error_Exception $e ) {
			$response = array();
		}

		if ( isset( $response['name'] ) ) {
			return $response['name'];
		}
	}

	/**
	 * Get folder ID by ID
	 *
	 * @param  string $folder_id Folder ID
	 * @return string
	 */
	public function get_folder_id_by_id( $folder_id ) {
		$api = new Ai1wmoe_OneDrive_Curl;
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/me/drive/items/%s', $this->rawurlencode_path( $folder_id ) ) );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmoe_Error_Exception $e ) {
			$response = array();
		}

		if ( isset( $response['id'] ) ) {
			return $response['id'];
		}
	}

	/**
	 * List specific folder
	 *
	 * @param  string $folder_id Folder ID
	 * @return array
	 */
	public function list_folder( $folder_id ) {
		$api = new Ai1wmoe_OneDrive_Curl;
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/me/drive/items/%s/children', $this->rawurlencode_path( $folder_id ) ) );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmoe_Error_Exception $e ) {
			throw $e;
		}

		$items = array();
		if ( isset( $response['value'] ) ) {
			foreach ( $response['value'] as $value ) {
				$items[] = array(
					'id'    => isset( $value['id'] ) ? $value['id'] : null,
					'name'  => isset( $value['name'] ) ? $value['name'] : null,
					'date'  => isset( $value['createdDateTime'] ) ? strtotime( $value['createdDateTime'] ) : null,
					'bytes' => isset( $value['size'] ) ? $value['size'] : null,
					'type'  => isset( $value['folder'] ) ? 'folder' : 'file',
				);
			}
		}

		return $items;
	}

	/**
	 * Create folder (in root)
	 *
	 * @param  string $folder_name Folder name
	 * @return string
	 */
	public function create_folder( $folder_name ) {
		$api = new Ai1wmoe_OneDrive_Curl;
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( '/me/drive/root/children' );
		$api->set_header( 'Content-Type', 'application/json' );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'name'                              => $folder_name,
					'folder'                            => (object) array(),
					'@microsoft.graph.conflictBehavior' => 'fail',
				)
			)
		);

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmoe_Error_Exception $e ) {
			throw $e;
		}

		if ( isset( $response['id'] ) ) {
			return $response['id'];
		}
	}

	/**
	 * Download file
	 *
	 * @param  resource $file_stream File stream
	 * @param  string   $file_id     File ID
	 * @return boolean
	 */
	public function download_file( $file_stream, $file_id ) {
		$api = new Ai1wmoe_OneDrive_Curl;
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_option( CURLOPT_FILE, $file_stream );
		$api->set_path( sprintf( '/me/drive/items/%s/content', $this->rawurlencode_path( $file_id ) ) );

		try {
			$api->make_request();
		} catch ( Ai1wmoe_Error_Exception $e ) {
			throw $e;
		}

		return true;
	}

	/**
	 * Download file chunk
	 *
	 * @param  resource $file_stream      File stream
	 * @param  string   $file_id          File ID
	 * @param  integer  $file_range_start File range start
	 * @param  integer  $file_range_end   File range end
	 * @return boolean
	 */
	public function download_file_chunk( $file_stream, $file_id, $file_range_start = 0, $file_range_end = 0 ) {
		$api = new Ai1wmoe_OneDrive_Curl;
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/me/drive/items/%s/content', $this->rawurlencode_path( $file_id ) ) );
		$api->set_header( 'Range', sprintf( 'bytes=%d-%d', $file_range_start, $file_range_end ) );

		try {
			$file_chunk_data = $api->make_request();
		} catch ( Ai1wmoe_Error_Exception $e ) {
			throw $e;
		}

		// Copy file chunk data into file stream
		if ( fwrite( $file_stream, $file_chunk_data ) === false ) {
			throw new Ai1wmoe_Error_Exception( __( 'Unable to save the file from OneDrive', AI1WMOE_PLUGIN_NAME ) );
		}

		return true;
	}

	/**
	 * Upload file
	 *
	 * @param  resource $file_stream File stream
	 * @param  string   $file_name   File name
	 * @param  integer  $file_size   File size
	 * @param  string   $folder_id   Folder ID
	 * @return array
	 */
	public function upload_file( $file_stream, $file_name, $file_size, $folder_id ) {
		$api = new Ai1wmoe_OneDrive_Curl;
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_option( CURLOPT_PUT, true );
		$api->set_option( CURLOPT_INFILE, $file_stream );
		$api->set_option( CURLOPT_INFILESIZE, $file_size );
		$api->set_path( sprintf( '/me/drive/items/%s:/%s:/content', $this->rawurlencode_path( $folder_id ), $this->rawurlencode_path( $file_name ) ) );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmoe_Error_Exception $e ) {
			throw $e;
		}

		return $response;
	}

	/**
	 * Create upload session
	 *
	 * @param  string $file_name File name
	 * @param  string $folder_id Folder ID
	 * @return string
	 */
	public function upload_resumable( $file_name, $folder_id ) {
		$api = new Ai1wmoe_OneDrive_Curl;
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/me/drive/items/%s:/%s:/createUploadSession', $this->rawurlencode_path( $folder_id ), $this->rawurlencode_path( $file_name ) ) );
		$api->set_header( 'Content-Type', 'application/json' );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'name'                              => $file_name,
					'description'                       => '',
					'@microsoft.graph.conflictBehavior' => 'fail',
				)
			)
		);

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmoe_Error_Exception $e ) {
			throw $e;
		}

		if ( isset( $response['uploadUrl'] ) ) {
			return $response['uploadUrl'];
		}
	}

	/**
	 * Upload file chunk
	 *
	 * @param  string  $file_chunk_data  File chunk data
	 * @param  integer $file_size        File size
	 * @param  integer $file_range_start File range start
	 * @param  integer $file_range_end   File range end
	 * @return array
	 */
	public function upload_file_chunk( $file_chunk_data, $file_size, $file_range_start = 0, $file_range_end = 0 ) {
		$api = new Ai1wmoe_OneDrive_Curl;
		$api->set_ssl( $this->ssl );
		$api->set_base_url( $this->upload_url );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'PUT' );
		$api->set_option( CURLOPT_POSTFIELDS, $file_chunk_data );
		$api->set_header( 'Content-Length', strlen( $file_chunk_data ) );
		$api->set_header( 'Content-Range', sprintf( 'bytes %d-%d/%d', $file_range_start, $file_range_end, $file_size ) );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmoe_Error_Exception $e ) {
			throw $e;
		}

		return $response;
	}

	/**
	 * Delete a file or folder
	 *
	 * @param  string  $file_id File ID
	 * @return boolean
	 */
	public function delete( $file_id ) {
		$api = new Ai1wmoe_OneDrive_Curl;
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/me/drive/items/%s', $this->rawurlencode_path( $file_id ) ) );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'DELETE' );

		try {
			$api->make_request();
		} catch ( Ai1wmoe_Error_Exception $e ) {
			return true;
		}

		return false;
	}

	/**
	 * Get account info
	 *
	 * @return array
	 */
	public function get_account_info() {
		$api = new Ai1wmoe_OneDrive_Curl;
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( '/me/drive' );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmoe_Error_Exception $e ) {
			throw $e;
		}

		return $response;
	}

	/**
	 * Get user information
	 *
	 * @return array
	 */
	public function get_user_info() {
		$api = new Ai1wmoe_OneDrive_Curl;
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( '/me' );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmoe_Error_Exception $e ) {
			throw $e;
		}

		return $response;
	}

	/**
	 * Get access token
	 *
	 * @return string
	 */
	public function get_access_token() {
		if ( time() < get_option( 'ai1wmoe_onedrive_access_token_expires_in', false ) ) {
			return get_option( 'ai1wmoe_onedrive_access_token', false );
		}

		$api = new Ai1wmoe_OneDrive_Curl;
		$api->set_header( 'Content-Type', 'application/json' );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( AI1WMOE_REDIRECT_REFRESH_URL );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'token' => $this->refresh_token,
				)
			)
		);

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmoe_Error_Exception $e ) {
			throw $e;
		}

		if ( isset( $response['access_token'] ) ) {
			update_option( 'ai1wmoe_onedrive_access_token', $response['access_token'] );
		}

		if ( isset( $response['expires_in'] ) ) {
			update_option( 'ai1wmoe_onedrive_access_token_expires_in', time() + ( $response['expires_in'] - 10 * 60 ) );
		}

		ai1wm_cache_flush();

		return get_option( 'ai1wmoe_onedrive_access_token', false );
	}

	/**
	 * Encode URL path
	 *
	 * @param  string $path Base path
	 * @return string
	 */
	public function rawurlencode_path( $path ) {
		return str_replace( '%7E', '~', rawurlencode( $path ) );
	}

	/**
	 * Encode URL query
	 *
	 * @param  array  $query Base query
	 * @return string
	 */
	public function rawurlencode_query( $query ) {
		return str_replace( '%7E', '~', array_map( 'rawurlencode', array_filter( $query, 'is_scalar' ) ) );
	}
}
