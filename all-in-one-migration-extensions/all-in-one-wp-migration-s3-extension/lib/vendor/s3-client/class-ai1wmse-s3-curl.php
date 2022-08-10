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

class Ai1wmse_S3_Curl {

	/**
	 * Base URL
	 *
	 * @var string
	 */
	protected $base_url = null;

	/**
	 * Base path
	 *
	 * @var string
	 */
	protected $path = '/';

	/**
	 * Base query
	 *
	 * @var array
	 */
	protected $query = array();

	/**
	 * Bucket name
	 *
	 * @var string
	 */
	protected $bucket_name = null;

	/**
	 * Region name
	 *
	 * @var string
	 */
	protected $region_name = 'us-east-1';

	/**
	 * Access key
	 *
	 * @var string
	 */
	protected $access_key = null;

	/**
	 * Secret key
	 *
	 * @var string
	 */
	protected $secret_key = null;

	/**
	 * HTTPS protocol
	 *
	 * @var boolean
	 */
	protected $https_protocol = true;

	/**
	 * HTTP method
	 *
	 * @var string
	 */
	protected $method = 'GET';

	/**
	 * HMAC algorithm
	 *
	 * @var string
	 */
	protected $hmac_algorithm = 'AWS4-HMAC-SHA256';

	/**
	 * AWS4 request
	 *
	 * @var string
	 */
	protected $aws4_request = 'aws4_request';

	/**
	 * Service name
	 *
	 * @var string
	 */
	protected $service_name = 's3';

	/**
	 * Signed headers
	 *
	 * @var string
	 */
	protected $signed_headers = null;

	/**
	 * Current date (header)
	 *
	 * @var string
	 */
	protected $x_amz_date = null;

	/**
	 * Current date (request)
	 *
	 * @var string
	 */
	protected $current_date = null;

	/**
	 * cURL handler
	 *
	 * @var resource
	 */
	protected $handler = null;

	/**
	 * cURL options
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * cURL headers
	 *
	 * @var array
	 */
	protected $headers = array( 'User-Agent' => 'All-in-One WP Migration' );

	/**
	 * cURL raw headers
	 *
	 * @var array
	 */
	protected $raw_headers = array();

	/**
	 * cURL messages
	 *
	 * @var array
	 */
	protected $messages = array(
		// [Informational 1xx]
		100 => '100 Continue',
		101 => '101 Switching Protocols',

		// [Successful 2xx]
		200 => '200 OK',
		201 => '201 Created',
		202 => '202 Accepted',
		203 => '203 Non-Authoritative Information',
		204 => '204 No Content',
		205 => '205 Reset Content',
		206 => '206 Partial Content',

		// [Redirection 3xx]
		300 => '300 Multiple Choices',
		301 => '301 Moved Permanently',
		302 => '302 Found',
		303 => '303 See Other',
		304 => '304 Not Modified',
		305 => '305 Use Proxy',
		306 => '306 (Unused)',
		307 => '307 Temporary Redirect',


		// [Client Error 4xx]
		400 => '400 Bad Request',
		401 => '401 Unauthorized',
		402 => '402 Payment Required',
		403 => '403 Forbidden',
		404 => '404 Not Found',
		405 => '405 Method Not Allowed',
		406 => '406 Not Acceptable',
		407 => '407 Proxy Authentication Required',
		408 => '408 Request Timeout',
		409 => '409 Conflict',
		410 => '410 Gone',
		411 => '411 Length Required',
		412 => '412 Precondition Failed',
		413 => '413 Request Entity Too Large',
		414 => '414 Request-URI Too Long',
		415 => '415 Unsupported Media Type',
		416 => '416 Requested Range Not Satisfiable',
		417 => '417 Expectation Failed',

		// [Server Error 5xx]
		500 => '500 Internal Server Error',
		501 => '501 Not Implemented',
		502 => '502 Bad Gateway',
		503 => '503 Service Unavailable',
		504 => '504 Gateway Timeout',
		505 => '505 HTTP Version Not Supported',
	);

	public function __construct() {
		if ( ! extension_loaded( 'curl' ) ) {
			throw new Ai1wmse_Error_Exception( __( 'Amazon S3 Extension requires PHP cURL extension. <a href="https://help.servmask.com/knowledgebase/curl-missing-in-php-installation/" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ) );
		}

		if ( ! extension_loaded( 'libxml' ) ) {
			throw new Ai1wmse_Error_Exception( __( 'Amazon S3 Extension requires PHP libxml extension. <a href="https://help.servmask.com/knowledgebase/libxml-missing-in-php-installation/" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ) );
		}

		if ( ! extension_loaded( 'simplexml' ) ) {
			throw new Ai1wmse_Error_Exception( __( 'Amazon S3 Extension requires PHP SimpleXML extension. <a href="https://help.servmask.com/knowledgebase/simplexml-missing-in-php-installation/" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ) );
		}

		// Default configuration
		$this->set_option( CURLOPT_HEADER, false );
		$this->set_option( CURLOPT_RETURNTRANSFER, true );
		$this->set_option( CURLOPT_FOLLOWLOCATION, true );
		$this->set_option( CURLOPT_SSL_VERIFYHOST, false );
		$this->set_option( CURLOPT_SSL_VERIFYPEER, false );
		$this->set_option( CURLOPT_CONNECTTIMEOUT, 120 );
		$this->set_option( CURLOPT_TIMEOUT, 0 );

		// Enable WP proxy
		$proxy = new WP_HTTP_Proxy();
		if ( $proxy->is_enabled() ) {
			$this->set_option( CURLOPT_PROXY, $proxy->host() );
			$this->set_option( CURLOPT_PROXYPORT, $proxy->port() );
			if ( $proxy->use_authentication() ) {
				$this->set_option( CURLOPT_PROXYUSERPWD, $proxy->authentication() );
			}
		}

		// Get current timestamp value (UTC)
		$this->x_amz_date   = $this->get_timestamp();
		$this->current_date = $this->get_date();
	}

	/**
	 * Set access key
	 *
	 * @param  string $value Access key
	 * @return object
	 */
	public function set_access_key( $value ) {
		$this->access_key = $value;
		return $this;
	}

	/**
	 * Get access key
	 *
	 * @return string
	 */
	public function get_access_key() {
		return $this->access_key;
	}

	/**
	 * Set secret key
	 *
	 * @param  string $value Secret key
	 * @return object
	 */
	public function set_secret_key( $value ) {
		$this->secret_key = $value;
		return $this;
	}

	/**
	 * Get secret key
	 *
	 * @return string
	 */
	public function get_secret_key() {
		return $this->secret_key;
	}

	/**
	 * Set HTTPS protocol
	 *
	 * @param  boolean $value HTTPS protocol
	 * @return object
	 */
	public function set_https_protocol( $value ) {
		$this->https_protocol = $value;
		return $this;
	}

	/**
	 * Get HTTPS protocol
	 *
	 * @return boolean
	 */
	public function get_https_protocol() {
		return $this->https_protocol;
	}

	/**
	 * Set base URL
	 *
	 * @param  string $value Base URL
	 * @return object
	 */
	public function set_base_url( $value ) {
		$this->base_url = sprintf( '%s://%s', ( $this->https_protocol ? 'https' : 'http' ), $value );
		return $this;
	}

	/**
	 * Get base URL
	 *
	 * @return string
	 */
	public function get_base_url() {
		if ( isset( $this->bucket_name, $this->region_name ) ) {
			return sprintf( $this->base_url, $this->bucket_name, $this->region_name );
		}

		return sprintf( $this->base_url, $this->bucket_name );
	}

	/**
	 * Set base path
	 *
	 * @param  string $value Base path
	 * @return object
	 */
	public function set_path( $value ) {
		$this->path = $value;
		return $this;
	}

	/**
	 * Get base path
	 *
	 * @return string
	 */
	public function get_path() {
		return $this->path;
	}

	/**
	 * Set base query
	 *
	 * @param  array  $value Base query
	 * @return object
	 */
	public function set_query( $value ) {
		$this->query = $value;
		return $this;
	}

	/**
	 * Get base query
	 *
	 * @return array
	 */
	public function get_query() {
		return $this->query;
	}

	/**
	 * Set bucket name
	 *
	 * @param  string $value Bucket name
	 * @return object
	 */
	public function set_bucket_name( $value ) {
		$this->bucket_name = $value;
		return $this;
	}

	/**
	 * Get bucket name
	 *
	 * @return string
	 */
	public function get_bucket_name() {
		return $this->bucket_name;
	}

	/**
	 * Set region name
	 *
	 * @param  string $value Region name
	 * @return object
	 */
	public function set_region_name( $value ) {
		$this->region_name = $value;
		return $this;
	}

	/**
	 * Get region name
	 *
	 * @return string
	 */
	public function get_region_name() {
		return $this->region_name;
	}

	/**
	 * Set cURL option
	 *
	 * @param  mixed  $name  cURL option name
	 * @param  mixed  $value cURL option value
	 * @return object
	 */
	public function set_option( $name, $value ) {
		$this->options[ $name ] = $value;
		return $this;
	}

	/**
	 * Get cURL option
	 *
	 * @param  mixed $name cURL option name
	 * @return mixed
	 */
	public function get_option( $name ) {
		return isset( $this->options[ $name ] ) ? $this->options[ $name ] : null;
	}

	/**
	 * Set cURL header
	 *
	 * @param  string $name  cURL header name
	 * @param  string $value cURL header value
	 * @return object
	 */
	public function set_header( $name, $value ) {
		$this->headers[ $name ] = $value;
		return $this;
	}

	/**
	 * Get cURL header
	 *
	 * @param  string $name cURL header name
	 * @return string
	 */
	public function get_header( $name ) {
		return isset( $this->headers[ $name ] ) ? $this->headers[ $name ] : null;
	}

	/**
	 * Set cURL raw header
	 *
	 * @param  string $name  cURL header name
	 * @param  string $value cURL header value
	 * @return object
	 */
	public function set_raw_header( $name, $value ) {
		$this->raw_headers[ $name ] = $value;
		return $this;
	}

	/**
	 * Get cURL raw header
	 *
	 * @param  string $name cURL header name
	 * @return string
	 */
	public function get_raw_header( $name ) {
		return isset( $this->raw_headers[ $name ] ) ? $this->raw_headers[ $name ] : null;
	}

	/**
	 * Make cURL request
	 *
	 * @param  boolean $parse_as_xml XML parse
	 * @return mixed
	 */
	public function make_request( $parse_as_xml = false ) {
		$this->handler = curl_init();

		// Set base URL
		if ( $this->get_query() ) {
			$this->set_option( CURLOPT_URL, $this->get_base_url() . $this->get_path() . '?' . build_query( $this->get_query() ) );
		} else {
			$this->set_option( CURLOPT_URL, $this->get_base_url() . $this->get_path() );
		}

		// HTTP Method
		if ( ( $method = $this->get_option( CURLOPT_CUSTOMREQUEST ) ) ) {
			$this->method = strtoupper( $method );
		} elseif ( $this->get_option( CURLOPT_NOBODY ) ) {
			$this->method = 'HEAD';
		} elseif ( $this->get_option( CURLOPT_POST ) ) {
			$this->method = 'POST';
		} elseif ( $this->get_option( CURLOPT_PUT ) ) {
			$this->method = 'PUT';
		}

		// Add current host
		$this->set_header( 'Host', parse_url( $this->get_base_url(), PHP_URL_HOST ) );

		// Set current timestamp
		$this->set_header( 'x-amz-date', $this->x_amz_date );

		// Set current SHA256
		$this->set_header( 'x-amz-content-sha256', $this->generate_hex( $this->get_option( CURLOPT_POSTFIELDS ) ) );

		// Execute Task 1: Create a Canonical Request for Signature Version 4
		$canonical_url = $this->prepare_canonical_request( $this->method, $this->path, $this->query, $this->headers );

		// Execute Task 2: Create a String to Sign for Signature Version 4
		$string_to_sign = $this->prepare_string_to_sign( $canonical_url );

		// Execute Task 3: Calculate the AWS Signature Version 4
		$signature = $this->calculate_signature( $string_to_sign );

		// Set authorization
		$this->set_header( 'Authorization', $this->build_authorization_string( $signature ) );

		// Apply cURL headers
		$http_headers = array();
		foreach ( $this->headers as $name => $value ) {
			$http_headers[] = "$name: $value";
		}

		// Apply cURL raw headers
		foreach ( $this->raw_headers as $name => $value ) {
			$http_headers[] = "$name: $value";
		}

		$this->set_option( CURLOPT_HTTPHEADER, $http_headers );

		// Apply cURL options
		foreach ( $this->options as $name => $value ) {
			curl_setopt( $this->handler, $name, $value );
		}

		// HTTP request
		$response = curl_exec( $this->handler );
		if ( $response === false ) {
			if ( ( $errno = curl_errno( $this->handler ) ) ) {
				throw new Ai1wmse_Connect_Exception( sprintf( __( 'Unable to connect to Amazon S3. Error code: %s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#%s" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $errno, $errno ) );
			}
		}

		// HTTP headers
		if ( $this->get_option( CURLOPT_HEADER ) ) {
			$headers  = substr( $response, 0, curl_getinfo( $this->handler, CURLINFO_HEADER_SIZE ) );
			$response = substr( $response, curl_getinfo( $this->handler, CURLINFO_HEADER_SIZE ) );
		}

		// Disable libxml errors
		libxml_use_internal_errors( true );

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$http_code = curl_getinfo( $this->handler, CURLINFO_HTTP_CODE );
		if ( $http_code >= 400 ) {
			if ( ( $data = simplexml_load_string( $response ) ) ) {
				if ( isset( $data->Code ) ) {
					switch ( $data->Code ) {
						case 'AuthorizationHeaderMalformed':
							if ( isset( $data->Message ) ) {
								throw new Ai1wmse_Authorization_Header_Malformed_Exception( sprintf( __( 'Please check your connection settings. %s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#AuthorizationHeaderMalformed" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $data->Message ) );
							} else {
								throw new Ai1wmse_Authorization_Header_Malformed_Exception( sprintf( __( 'Please check your connection settings. Error code: %s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#AuthorizationHeaderMalformed" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $data->Code ) );
							}

						case 'InvalidAccessKeyId':
							if ( isset( $data->Message ) ) {
								throw new Ai1wmse_Invalid_Access_Key_Id_Exception( sprintf( __( 'The access key that you have provided is incorrect. %s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#InvalidAccessKeyId" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $data->Message ) );
							} else {
								throw new Ai1wmse_Invalid_Access_Key_Id_Exception( sprintf( __( 'The access key that you have provided is incorrect. Error code: %s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#InvalidAccessKeyId" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $data->Code ) );
							}

						case 'InvalidBucketName':
							if ( isset( $data->Message ) ) {
								throw new Ai1wmse_Invalid_Bucket_Name_Exception( sprintf( __( 'The bucket name contains invalid characters. %s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#InvalidBucketName" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $data->Message ) );
							} else {
								throw new Ai1wmse_Invalid_Bucket_Name_Exception( sprintf( __( 'The bucket name contains invalid characters. Error code: %s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#InvalidBucketName" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $data->Code ) );
							}

						case 'SignatureDoesNotMatch':
							if ( isset( $data->Message ) ) {
								throw new Ai1wmse_Signature_Does_Not_Match_Exception( sprintf( __( 'The secret key that you have provided is incorrect. %s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#SignatureDoesNotMatch" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $data->Message ) );
							} else {
								throw new Ai1wmse_Signature_Does_Not_Match_Exception( sprintf( __( 'The secret key that you have provided is incorrect. Error code: %s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#SignatureDoesNotMatch" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $data->Code ) );
							}

						case 'AccessDenied':
							if ( isset( $data->Message ) ) {
								throw new Ai1wmse_Access_Denied_Exception( sprintf( __( 'Please check your bucket policy. %s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#AccessDenied" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $data->Message ) );
							} else {
								throw new Ai1wmse_Access_Denied_Exception( sprintf( __( 'Please check your bucket policy. Error code: %s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#AccessDenied" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $data->Code ) );
							}

						case 'AllAccessDisabled':
							if ( isset( $data->Message ) ) {
								throw new Ai1wmse_All_Access_Disabled_Exception( sprintf( __( 'Please check your bucket policy. %s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#AllAccessDisabled" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $data->Message ) );
							} else {
								throw new Ai1wmse_All_Access_Disabled_Exception( sprintf( __( 'Please check your bucket policy. Error code: %s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#AllAccessDisabled" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $data->Code ) );
							}

						case 'NoSuchBucket':
							if ( isset( $data->Message ) ) {
								throw new Ai1wmse_No_Such_Bucket_Exception( sprintf( __( 'Please check your bucket name. %s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#NoSuchBucket" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $data->Message ) );
							} else {
								throw new Ai1wmse_No_Such_Bucket_Exception( sprintf( __( 'Please check your bucket name. Error code: %s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#NoSuchBucket" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $data->Code ) );
							}

						default:
							if ( isset( $data->Message ) ) {
								throw new Ai1wmse_Error_Exception( sprintf( __( '%s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#%s" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $data->Message, $data->Code ) );
							} else {
								throw new Ai1wmse_Error_Exception( sprintf( __( 'Error code: %s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#%s" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $data->Code, $data->Code ) );
							}
					}
				}
			}
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		// HTTP errors
		if ( $http_code >= 400 ) {
			if ( isset( $this->messages[ $http_code ] ) ) {
				throw new Ai1wmse_Error_Exception( sprintf( __( '%s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#%s" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $this->messages[ $http_code ], $http_code ) );
			} else {
				throw new Ai1wmse_Error_Exception( sprintf( __( 'Error code: %s. <a href="https://help.servmask.com/knowledgebase/amazon-s3-error-codes/#%s" target="_blank">Technical details</a>', AI1WMSE_PLUGIN_NAME ), $http_code, $http_code ) );
			}
		}

		// HTTP headers
		if ( $this->get_option( CURLOPT_HEADER ) ) {
			return $this->http_parse_headers( $headers );
		}

		// XML response
		if ( $parse_as_xml ) {
			return simplexml_load_string( $response );
		}

		return $response;
	}

	/**
	 * Parse HTTP headers
	 *
	 * @param  string $headers HTTP headers
	 * @return array
	 */
	public function http_parse_headers( $headers ) {
		$headers = preg_split( '/(\r|\n)+/', $headers, -1, PREG_SPLIT_NO_EMPTY );

		$parse_headers = array();
		for ( $i = 1; $i < count( $headers ); $i++ ) {
			if ( strpos( $headers[ $i ], ':' ) !== false ) {
				list( $key, $raw_value ) = explode( ':', $headers[ $i ], 2 );

				$key   = trim( $key );
				$value = trim( $raw_value );
				if ( array_key_exists( $key, $parse_headers ) ) {
					// See HTTP RFC Sec 4.2 Paragraph 5
					// http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.2
					// If a header appears more than once, it must also be able to
					// be represented as a single header with a comma-separated
					// list of values.  We transform accordingly.
					$parse_headers[ $key ] .= ',' . $value;
				} else {
					$parse_headers[ $key ] = $value;
				}
			}
		}

		return $parse_headers;
	}

	/**
	 * Destroy cURL handler
	 *
	 * @return void
	 */
	public function __destruct() {
		if ( $this->handler !== null ) {
			curl_close( $this->handler );
		}
	}

	/**
	 * Task 1: Create a Canonical Request for Signature Version 4
	 *
	 * @param  string $method  HTTP request method (GET, PUT, POST, etc.)
	 * @param  string $path    HTTP path
	 * @param  array  $query   HTTP query
	 * @param  array  $headers HTTP headers
	 * @return string
	 */
	protected function prepare_canonical_request( $method, $path, $query = array(), $headers = array() ) {
		$canonical_url = array();

		// Step 1.1 Start with the HTTP request method (GET, PUT, POST, etc.)
		$canonical_url[] = $method;

		// Step 1.2 Add the canonical URI parameter
		$canonical_url[] = $path;

		// Step 1.3 Sort the query parameters
		ksort( $query );

		// Step 1.4 Add the canonical query string
		$canonical_url[] = build_query( $query );

		// Step 1.5 Set the canonical headers
		$canonical_headers = array();
		foreach ( $headers as $key => $value ) {
			$canonical_headers[ strtolower( $key ) ] = trim( $value );
		}

		// Step 1.6 Sort the canonical headers
		ksort( $canonical_headers );

		// Step 1.7 Add the canonical headers
		foreach ( $canonical_headers as $key => $value ) {
			$canonical_url[] = sprintf( '%s:%s', $key, $value );
		}

		// Step 1.8 Add the empty line
		$canonical_url[] = null;

		// Step 1.9 Set the signed headers
		$this->signed_headers = implode( ';', array_keys( $canonical_headers ) );

		// Step 1.10 Add the signed headers
		$canonical_url[] = $this->signed_headers;

		// Step 1.11 Use a hash (digest) function like SHA256 to create a hashed value from the payload in the body of the HTTP or HTTPS
		$canonical_url[] = $this->generate_hex( $this->get_option( CURLOPT_POSTFIELDS ) );

		return implode( "\n", $canonical_url );
	}

	/**
	 * Task 2: Create a String to Sign for Signature Version 4
	 *
	 * @param  string $canonical_url Canonical URL
	 * @return string
	 */
	protected function prepare_string_to_sign( $canonical_url ) {
		$string_to_sign = array();

		// Step 2.1 Start with the algorithm designation
		$string_to_sign[] = $this->hmac_algorithm;

		// Step 2.2 Append the request date value
		$string_to_sign[] = $this->x_amz_date;

		// Step 2.3 Append the credential scope value
		$string_to_sign[] = $this->current_date . '/' . $this->region_name . '/' . $this->service_name . '/' . $this->aws4_request;

		// Step 2.4 Append the hash of the canonical request that you created in Task 1: Create a Canonical Request for Signature Version 4
		$string_to_sign[] = $this->generate_hex( $canonical_url );

		return implode( "\n", $string_to_sign );
	}

	/**
	 * Task 3: Calculate the AWS Signature Version 4
	 *
	 * @param  string $string_to_sign String to sign
	 * @return string
	 */
	protected function calculate_signature( $string_to_sign ) {
		// Step 3.1 Derive your signing key
		$signature_key = $this->get_signature_key( $this->secret_key, $this->current_date, $this->region_name, $this->service_name );

		// Step 3.2 Calculate the signature
		$signature = hash_hmac( 'sha256', $string_to_sign, $signature_key );

		// Step 3.3 Lowercase the signature
		$str_hex_signature = strtolower( $signature );

		return $str_hex_signature;
	}

	/**
	 * Build string for Authorization header
	 *
	 * @param  string $str_signature Signature
	 * @return string
	 */
	protected function build_authorization_string( $str_signature ) {
		return $this->hmac_algorithm . ' '
				. 'Credential=' . $this->access_key . '/' . $this->current_date . '/' . $this->region_name . '/' . $this->service_name . '/' . $this->aws4_request . ','
				. 'SignedHeaders=' . $this->signed_headers . ','
				. 'Signature=' . $str_signature;
	}

	/**
	 * Generate Hex code of String
	 *
	 * @param  string $data Encode data into hex
	 * @return string
	 */
	private function generate_hex( $data ) {
		return strtolower( hash( 'sha256', $data ) );
	}

	/**
	 * Generate AWS signature key
	 *
	 * @param  string $key          Secret key
	 * @param  string $date         Current date
	 * @param  string $region_name  Region name
	 * @param  string $service_name Service name
	 * @return string
	 */
	protected function get_signature_key( $key, $date, $region_name, $service_name ) {
		$k_secret  = sprintf( 'AWS4%s', $key );
		$k_date    = hash_hmac( 'sha256', $date, $k_secret, true );
		$k_region  = hash_hmac( 'sha256', $region_name, $k_date, true );
		$k_service = hash_hmac( 'sha256', $service_name, $k_region, true );
		$k_signing = hash_hmac( 'sha256', $this->aws4_request, $k_service, true );

		return $k_signing;
	}

	/**
	 * Get timestamp (format: yyyyMMdd'T'HHmmss'Z')
	 *
	 * @return string
	 */
	protected function get_timestamp() {
		return gmdate( 'Ymd\THis\Z' );
	}

	/**
	 * Get date (format: yyyyMMdd)
	 *
	 * @return string
	 */
	protected function get_date() {
		return gmdate( 'Ymd' );
	}
}
