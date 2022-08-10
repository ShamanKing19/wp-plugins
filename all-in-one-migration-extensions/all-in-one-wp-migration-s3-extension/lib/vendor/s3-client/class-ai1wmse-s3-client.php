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

class Ai1wmse_S3_Client {

	const API_URL        = 's3.amazonaws.com';
	const API_BUCKET_URL = '%s.s3.amazonaws.com';
	const API_REGION_URL = '%s.s3.%s.amazonaws.com';

	/**
	 * Amazon S3 access key
	 *
	 * @var string
	 */
	protected $access_key = null;

	/**
	 * Amazon S3 secret key
	 *
	 * @var string
	 */
	protected $secret_key = null;

	/**
	 * S3 Client HTTPS protocol
	 *
	 * @var boolean
	 */
	protected $https_protocol = true;

	public function __construct( $access_key, $secret_key, $https_protocol = true ) {
		$this->access_key     = $access_key;
		$this->secret_key     = $secret_key;
		$this->https_protocol = $https_protocol;
	}

	/**
	 * Get account info
	 *
	 * @return mixed
	 */
	public function get_account_info() {
	}

	/**
	 * Add a new bucket
	 *
	 * @param  string  $bucket_name Bucket name
	 * @param  string  $region_name Region name
	 * @return boolean
	 */
	public function create_bucket( $bucket_name, $region_name = null ) {
		// Set bucket region
		if ( $region_name ) {
			$post = sprintf( '<CreateBucketConfiguration><LocationConstraint>%s</LocationConstraint></CreateBucketConfiguration>', $region_name );
		} else {
			$post = null;
		}

		// Create new bucket
		$api = new Ai1wmse_S3_Curl;
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_https_protocol( $this->https_protocol );
		$api->set_base_url( self::API_BUCKET_URL );
		$api->set_bucket_name( $bucket_name );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'PUT' );
		$api->set_option( CURLOPT_POSTFIELDS, $post );
		$api->set_header( 'Content-Type', 'application/xml' );

		// Set Base URL
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$api->make_request();
		} catch ( Ai1wmse_Error_Exception $e ) {
			throw $e;
		}

		return true;
	}

	/**
	 * Get region name
	 *
	 * @param  string $bucket_name Bucket name
	 * @return string
	 */
	public function get_bucket_region( $bucket_name ) {
		$api = new Ai1wmse_S3_Curl;
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_https_protocol( $this->https_protocol );
		$api->set_base_url( self::API_URL );
		$api->set_path( "/{$bucket_name}" );
		$api->set_query( $this->rawurlencode_query( array( 'location' => '' ) ) );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmse_No_Such_Bucket_Exception $e ) {
		}

		if ( isset( $response ) ) {
			return strval( $response );
		}
	}

	/**
	 * Get regions
	 *
	 * @return array
	 */
	public function get_regions() {
		// TODO: When Amazon S3 provides a method that returns all regions
		// we should refactor the code below with proper API request
		$regions = array(
			''               => 'US East (N. Virginia)', // If you are creating a bucket on the US East (N. Virginia) region (us-east-1), you do not need to specify the location constraint
			'us-east-2'      => 'US East (Ohio)',
			'us-west-1'      => 'US West (N. California)',
			'us-west-2'      => 'US West (Oregon)',
			'ap-east-1'      => 'Asia Pacific (Hong Kong)',
			'ap-south-1'     => 'Asia Pacific (Mumbai)',
			'ap-northeast-2' => 'Asia Pacific (Seoul)',
			'ap-southeast-1' => 'Asia Pacific (Singapore)',
			'ap-southeast-2' => 'Asia Pacific (Sydney)',
			'ap-northeast-1' => 'Asia Pacific (Tokyo)',
			'ca-central-1'   => 'Canada (Central)',
			'eu-central-1'   => 'Europe (Frankfurt)',
			'eu-west-1'      => 'Europe (Ireland)',
			'eu-west-2'      => 'Europe (London)',
			'eu-west-3'      => 'Europe (Paris)',
			'eu-north-1'     => 'Europe (Stockholm)',
			'me-south-1'     => 'Middle East (Bahrain)',
			'sa-east-1'      => 'South America (São Paulo)',
		);

		return $regions;
	}

	/**
	 * Check if a given bucket name is available
	 *
	 * @param  string  $bucket_name Bucket name
	 * @param  string  $region_name Region name
	 * @return boolean
	 */
	public function is_bucket_available( $bucket_name, $region_name = null ) {
		$api = new Ai1wmse_S3_Curl;
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_https_protocol( $this->https_protocol );
		$api->set_bucket_name( $bucket_name );
		$api->set_option( CURLOPT_HEADER, true );
		$api->set_option( CURLOPT_NOBODY, true );

		// Set Base URL
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$api->make_request();
		} catch ( Ai1wmse_Access_Denied_Exception $e ) {
			throw new Ai1wmse_Access_Denied_Exception( __( 'Please check your bucket policy. Access Denied. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#AccessDenied" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ) );
		} catch ( Ai1wmse_All_Access_Disabled_Exception $e ) {
			throw new Ai1wmse_All_Access_Disabled_Exception( __( 'Please check your bucket policy. All Access Disabled. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#AllAccessDisabled" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ) );
		} catch ( Ai1wmse_Error_Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Remove a given bucket (all objects in the bucket must be removed prior to removing the bucket)
	 *
	 * @param  string  $bucket_name Bucket name
	 * @param  string  $region_name Region name
	 * @return boolean
	 */
	public function remove_bucket( $bucket_name, $region_name = null ) {
		$api = new Ai1wmse_S3_Curl;
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_https_protocol( $this->https_protocol );
		$api->set_bucket_name( $bucket_name );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'DELETE' );

		// Set Base URL
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$api->make_request();
		} catch ( Ai1wmse_Error_Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * List the Amazon S3 buckets
	 *
	 * @return array
	 */
	public function get_buckets() {
		$api = new Ai1wmse_S3_Curl;
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_https_protocol( $this->https_protocol );
		$api->set_base_url( self::API_URL );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmse_Invalid_Access_Key_Id_Exception $e ) {
			throw new Ai1wmse_Invalid_Access_Key_Id_Exception( __( 'The access key that you have provided is incorrect. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#InvalidAccessKeyId" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ) );
		} catch ( Ai1wmse_Signature_Does_Not_Match_Exception $e ) {
			throw new Ai1wmse_Signature_Does_Not_Match_Exception( __( 'The secret key that you have provided is incorrect. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#SignatureDoesNotMatch" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ) );
		} catch ( Ai1wmse_Access_Denied_Exception $e ) {
			// In case user doesn't have ListAllMyBuckets permission
		} catch ( Ai1wmse_All_Access_Disabled_Exception $e ) {
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$buckets = array();
		if ( isset( $response->Buckets->Bucket ) ) {
			foreach ( $response->Buckets->Bucket as $bucket ) {
				$buckets[] = strval( $bucket->Name );
			}
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		return $buckets;
	}

	/**
	 * List the objects in a bucket
	 *
	 * @param  string $bucket_name Bucket name
	 * @param  string $region_name Region name
	 * @param  array  $query       Query options
	 * @return array
	 */
	public function get_objects_by_bucket( $bucket_name, $region_name = null, $query = array() ) {
		$api = new Ai1wmse_S3_Curl;
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_https_protocol( $this->https_protocol );
		$api->set_bucket_name( $bucket_name );
		$api->set_query( $this->rawurlencode_query( $query ) );

		// Set Base URL
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmse_Error_Exception $e ) {
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$objects = array();
		if ( isset( $response->Contents ) ) {
			foreach ( $response->Contents as $item ) {
				if ( substr( $item->Key, -1 ) !== '/' ) {
					$objects[] = array(
						'name'  => isset( $item->Key ) ? basename( $item->Key ) : null,
						'path'  => isset( $item->Key ) ? strval( $item->Key ) : null,
						'date'  => isset( $item->LastModified ) ? strtotime( $item->LastModified ) : null,
						'bytes' => isset( $item->Size ) ? strval( $item->Size ) : null,
						'type'  => 'file',
					);
				}
			}
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		if ( isset( $response->CommonPrefixes ) ) {
			foreach ( $response->CommonPrefixes as $item ) {
				$objects[] = array(
					'name' => isset( $item->Prefix ) ? basename( $item->Prefix ) : null,
					'path' => isset( $item->Prefix ) ? strval( $item->Prefix ) : null,
					'type' => 'folder',
				);
			}
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		return $objects;
	}

	/**
	 * Upload file
	 *
	 * @param  resource $file_stream File stream
	 * @param  string   $file_path   File path
	 * @param  integer  $file_size   File size
	 * @param  string   $bucket_name Bucket name
	 * @param  string   $region_name Region name
	 * @return boolean
	 */
	public function upload_file( $file_stream, $file_path, $file_size, $bucket_name, $region_name = null ) {
		$api = new Ai1wmse_S3_Curl;
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_https_protocol( $this->https_protocol );
		$api->set_bucket_name( $bucket_name );
		$api->set_path( sprintf( '/%s', $this->rawurlencode_path( $file_path ) ) );
		$api->set_option( CURLOPT_PUT, true );
		$api->set_option( CURLOPT_INFILE, $file_stream );
		$api->set_option( CURLOPT_INFILESIZE, $file_size );
		$api->set_header( 'Expect', '100-continue' );
		$api->set_header( 'Content-Type', 'application/octet-stream' );

		// Set Base URL
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$api->make_request();
		} catch ( Ai1wmse_Error_Exception $e ) {
			throw $e;
		}

		return true;
	}

	/**
	 * Upload multipart file on Amazon S3
	 *
	 * @param  string $file_path     File path
	 * @param  string $bucket_name   Bucket name
	 * @param  string $region_name   Region name
	 * @param  string $storage_class Storage class
	 * @param  string $encryption    Bucket encryption
	 * @return string
	 */
	public function upload_multipart( $file_path, $bucket_name, $region_name = null, $storage_class = null, $encryption = null ) {
		$api = new Ai1wmse_S3_Curl;
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_https_protocol( $this->https_protocol );
		$api->set_bucket_name( $bucket_name );
		$api->set_path( sprintf( '/%s', $this->rawurlencode_path( $file_path ) ) );
		$api->set_query( $this->rawurlencode_query( array( 'uploads' => '' ) ) );
		$api->set_option( CURLOPT_POST, true );
		$api->set_header( 'Content-Type', 'application/octet-stream' );
		$api->set_raw_header( 'Content-Length', null );
		$api->set_raw_header( 'Transfer-Encoding', null );

		// Set storage class
		if ( $storage_class ) {
			$api->set_header( 'x-amz-storage-class', $storage_class );
		}

		// Set bucket encryption
		if ( $encryption ) {
			$api->set_header( 'x-amz-server-side-encryption', $encryption );
		}

		// Set Base URL
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmse_Error_Exception $e ) {
			throw $e;
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		if ( isset( $response->UploadId ) ) {
			return strval( $response->UploadId );
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Upload file chunk
	 *
	 * @param  string  $file_chunk_data   File chunk data
	 * @param  string  $file_path         File path
	 * @param  string  $upload_id         Upload ID
	 * @param  string  $bucket_name       Bucket name
	 * @param  string  $region_name       Region name
	 * @param  integer $file_chunk_number File chunk number
	 * @return string
	 */
	public function upload_file_chunk( $file_chunk_data, $file_path, $upload_id, $bucket_name, $region_name = null, $file_chunk_number = 1 ) {
		$api = new Ai1wmse_S3_Curl;
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_https_protocol( $this->https_protocol );
		$api->set_bucket_name( $bucket_name );
		$api->set_path( sprintf( '/%s', $this->rawurlencode_path( $file_path ) ) );
		$api->set_query( $this->rawurlencode_query( array( 'partNumber' => $file_chunk_number, 'uploadId' => $upload_id ) ) );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'PUT' );
		$api->set_option( CURLOPT_POSTFIELDS, $file_chunk_data );
		$api->set_option( CURLOPT_HEADER, true );
		$api->set_header( 'Content-Type', 'application/octet-stream' );

		// Set Base URL
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$response = $api->make_request();
		} catch ( Ai1wmse_Error_Exception $e ) {
			throw $e;
		}

		if ( isset( $response['ETag'] ) ) {
			return $response['ETag'];
		}
	}

	/**
	 * Upload complete file on Amazon S3
	 *
	 * @param  array  $file_chunks File chunks
	 * @param  string $file_path   File path
	 * @param  string $upload_id   Upload ID
	 * @param  string $bucket_name Bucket name
	 * @param  string $region_name Region name
	 * @return object
	 */
	public function upload_complete( $file_chunks, $file_path, $upload_id, $bucket_name, $region_name = null ) {
		// Combine parts
		$post = '<CompleteMultipartUpload>';

		// Add file chunk ETag
		foreach ( $file_chunks as $i => $etag ) {
			$post .= sprintf( '<Part><PartNumber>%d</PartNumber><ETag>%s</ETag></Part>', $i + 1, $etag );
		}

		$post .= '</CompleteMultipartUpload>';

		// Upload complete
		$api = new Ai1wmse_S3_Curl;
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_https_protocol( $this->https_protocol );
		$api->set_bucket_name( $bucket_name );
		$api->set_path( sprintf( '/%s', $this->rawurlencode_path( $file_path ) ) );
		$api->set_query( $this->rawurlencode_query( array( 'uploadId' => $upload_id ) ) );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option( CURLOPT_POSTFIELDS, $post );
		$api->set_header( 'Content-Type', 'application/octet-stream' );

		// Set Base URL
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmse_Error_Exception $e ) {
			throw $e;
		}

		return $response;
	}

	/**
	 * Download file from Amazon S3
	 *
	 * @param  resource $file_stream      File stream
	 * @param  string   $file_path        File path
	 * @param  string   $bucket_name      Bucket name
	 * @param  string   $region_name      Region name
	 * @param  integer  $file_range_start File range start
	 * @param  integer  $file_range_end   File range end
	 * @return boolean
	 */
	public function get_file( $file_stream, $file_path, $bucket_name, $region_name = null, $file_range_start = 0, $file_range_end = 0 ) {
		$api = new Ai1wmse_S3_Curl;
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_https_protocol( $this->https_protocol );
		$api->set_bucket_name( $bucket_name );
		$api->set_path( sprintf( '/%s', $this->rawurlencode_path( $file_path ) ) );
		$api->set_header( 'Range', sprintf( 'bytes=%d-%d', $file_range_start, $file_range_end ) );

		// Set Base URL
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$file_chunk_data = $api->make_request();
		} catch ( Ai1wmse_Error_Exception $e ) {
			throw $e;
		}

		// Copy file chunk data into file stream
		if ( fwrite( $file_stream, $file_chunk_data ) === false ) {
			throw new Ai1wmse_Error_Exception( __( 'Unable to save the file from Amazon S3', AI1WMSE_PLUGIN_NAME ) );
		}

		return true;
	}

	/**
	 * Remove file
	 *
	 * @param  string  $file_path   File path
	 * @param  string  $bucket_name Bucket name
	 * @param  string  $region_name Region name
	 * @return boolean
	 */
	public function remove_file( $file_path, $bucket_name, $region_name = null ) {
		$api = new Ai1wmse_S3_Curl;
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_https_protocol( $this->https_protocol );
		$api->set_bucket_name( $bucket_name );
		$api->set_path( sprintf( '/%s', $this->rawurlencode_path( $file_path ) ) );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'DELETE' );

		// Set Base URL
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$api->make_request();
		} catch ( Ai1wmse_Error_Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Encode URL path
	 *
	 * @param  string $path Base path
	 * @return string
	 */
	public function rawurlencode_path( $path ) {
		return str_replace( '%7E', '~', implode( '/', array_map( 'rawurlencode', explode( '/', $path ) ) ) );
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
