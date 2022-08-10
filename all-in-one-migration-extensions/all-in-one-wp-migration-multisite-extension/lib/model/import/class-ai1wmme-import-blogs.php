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

class Ai1wmme_Import_Blogs {

	public static function execute( $params ) {

		// Set progress
		Ai1wm_Status::info( __( 'Preparing blogs...', AI1WMME_PLUGIN_NAME ) );

		$sites = array();
		$blogs = array();

		// Read package.json file
		$handle = ai1wm_open( ai1wm_package_path( $params ), 'r' );

		// Parse package.json file
		$config = ai1wm_read( $handle, filesize( ai1wm_package_path( $params ) ) );
		$config = json_decode( $config, true );

		// Close handle
		ai1wm_close( $handle );

		// Single site
		if ( ! is_file( ai1wm_multisite_path( $params ) ) ) {

			// Set internal Site URL (backward compatibility)
			if ( empty( $config['InternalSiteURL'] ) ) {
				$config['InternalSiteURL'] = null;
			}

			// Set internal Home URL (backward compatibility)
			if ( empty( $config['InternalHomeURL'] ) ) {
				$config['InternalHomeURL'] = null;
			}

			// Set active plugins (backward compatibility)
			if ( empty( $config['Plugins'] ) ) {
				$config['Plugins'] = array();
			}

			// Set active template (backward compatibility)
			if ( empty( $config['Template'] ) ) {
				$config['Template'] = null;
			}

			// Set active stylesheet (backward compatibility)
			if ( empty( $config['Stylesheet'] ) ) {
				$config['Stylesheet'] = null;
			}

			// Set uploads path (backward compatibility)
			if ( empty( $config['Uploads'] ) ) {
				$config['Uploads'] = null;
			}

			// Set uploads URL path (backward compatibility)
			if ( empty( $config['UploadsURL'] ) ) {
				$config['UploadsURL'] = null;
			}

			// Set uploads path (backward compatibility)
			if ( empty( $config['WordPress']['Uploads'] ) ) {
				$config['WordPress']['Uploads'] = null;
			}

			// Set uploads URL path (backward compatibility)
			if ( empty( $config['WordPress']['UploadsURL'] ) ) {
				$config['WordPress']['UploadsURL'] = null;
			}

			// Set multisite.json file
			$multisite = array(
				'Network' => false,
				'Sites'   => array(
					array(
						'BlogID'          => null,
						'SiteID'          => null,
						'LangID'          => null,
						'SiteURL'         => $config['SiteURL'],
						'HomeURL'         => $config['HomeURL'],
						'InternalSiteURL' => $config['InternalSiteURL'],
						'InternalHomeURL' => $config['InternalHomeURL'],
						'Plugins'         => $config['Plugins'],
						'Template'        => $config['Template'],
						'Stylesheet'      => $config['Stylesheet'],
						'Domain'          => parse_url( trailingslashit( $config['HomeURL'] ), PHP_URL_HOST ),
						'Path'            => parse_url( trailingslashit( $config['HomeURL'] ), PHP_URL_PATH ),
						'Uploads'         => $config['Uploads'],
						'UploadsURL'      => $config['UploadsURL'],
						'WordPress'       => $config['WordPress'],
					),
				),
			);

			// Save multisite.json file
			$handle = ai1wm_open( ai1wm_multisite_path( $params ), 'w' );
			ai1wm_write( $handle, json_encode( $multisite ) );
			ai1wm_close( $handle );
		}

		// Read multisite.json file
		$handle = ai1wm_open( ai1wm_multisite_path( $params ), 'r' );

		// Parse multisite.json file
		$multisite = ai1wm_read( $handle, filesize( ai1wm_multisite_path( $params ) ) );
		$multisite = json_decode( $multisite, true );

		// Close handle
		ai1wm_close( $handle );

		// Loop over user subsites
		if ( isset( $multisite['Sites'] ) && ( $subsites = $multisite['Sites'] ) ) {
			foreach ( $subsites as $i => $subsite ) {

				// Get new subsite domain
				$new_subsite_domain = parse_url( home_url(), PHP_URL_HOST );

				// Get new subsite path
				$new_subsite_path = parse_url( home_url(), PHP_URL_PATH );

				// Get old blog domain
				$old_blog_domain = parse_url( $config['HomeURL'], PHP_URL_HOST );

				// Get old blog path
				$old_blog_path = parse_url( $config['HomeURL'], PHP_URL_PATH );

				// Get new blog domain
				$new_blog_domain = parse_url( home_url(), PHP_URL_HOST );

				// Get new blog path
				$new_blog_path = parse_url( home_url(), PHP_URL_PATH );

				// Get old blog domain without www subdomain
				$old_home_url_www_inversion = parse_url( str_ireplace( '//www.', '//', $config['HomeURL'] ), PHP_URL_HOST );

				// Get new blog domain without www subdomain
				$new_home_url_www_inversion = parse_url( str_ireplace( '//www.', '//', home_url() ), PHP_URL_HOST );

				// Get standalone domain and path
				if ( is_null( $subsite['BlogID'] ) ) {

					// Get blog sub domain
					if ( ( $old_blog_subdomain = explode( '.', untrailingslashit( $old_home_url_www_inversion ) ) ) ) {
						if ( ( $old_blog_subdomain = array_filter( $old_blog_subdomain ) ) ) {
							if ( ( $new_blog_name = array_shift( $old_blog_subdomain ) ) ) {
								if ( is_subdomain_install() ) {
									$new_subsite_domain = sprintf( '%s.%s', strtolower( $new_blog_name ), strtolower( $new_home_url_www_inversion ) );
									$new_subsite_path   = sprintf( '%s', untrailingslashit( $new_blog_path ) );
								} else {
									$new_subsite_domain = sprintf( '%s', strtolower( $new_blog_domain ) );
									$new_subsite_path   = sprintf( '%s/%s', untrailingslashit( $new_blog_path ), strtolower( $new_blog_name ) );
								}
							}
						}
					}

					// Get blog sub path
					if ( ( $old_blog_subpath = explode( '/', untrailingslashit( $old_blog_path ) ) ) ) {
						if ( ( $old_blog_subpath = array_filter( $old_blog_subpath ) ) ) {
							if ( ( $new_blog_name = array_pop( $old_blog_subpath ) ) ) {
								if ( is_subdomain_install() ) {
									$new_subsite_domain = sprintf( '%s.%s', strtolower( $new_blog_name ), strtolower( $new_home_url_www_inversion ) );
									$new_subsite_path   = sprintf( '%s', untrailingslashit( $new_blog_path ) );
								} else {
									$new_subsite_domain = sprintf( '%s', strtolower( $new_blog_domain ) );
									$new_subsite_path   = sprintf( '%s/%s', untrailingslashit( $new_blog_path ), strtolower( $new_blog_name ) );
								}
							}
						}
					}
				} else {

					// Get subsite domain and path (auto)
					if ( strripos( $subsite['Domain'], $old_blog_domain ) !== false ) {

						// Get blog sub domain
						if ( ( $old_blog_subdomain = substr_replace( $subsite['Domain'], '', strripos( $subsite['Domain'], $old_blog_domain ), strlen( $old_blog_domain ) ) ) ) {
							if ( ( $new_blog_name = trim( $old_blog_subdomain, '.' ) ) ) {
								if ( is_subdomain_install() ) {
									$new_subsite_domain = sprintf( '%s.%s', strtolower( $new_blog_name ), strtolower( $new_blog_domain ) );
									$new_subsite_path   = sprintf( '%s', untrailingslashit( $new_blog_path ) );
								} else {
									$new_subsite_domain = sprintf( '%s', strtolower( $new_blog_domain ) );
									$new_subsite_path   = sprintf( '%s/%s', untrailingslashit( $new_blog_path ), strtolower( $new_blog_name ) );
								}
							}
						}

						// Get blog sub path
						if ( ( $old_blog_subpath = substr_replace( trailingslashit( $subsite['Path'] ), '', stripos( trailingslashit( $subsite['Path'] ), trailingslashit( $old_blog_path ) ), strlen( trailingslashit( $old_blog_path ) ) ) ) ) {
							if ( ( $old_blog_subpath = explode( '/', untrailingslashit( $old_blog_subpath ) ) ) ) {
								if ( ( $old_blog_subpath = array_filter( $old_blog_subpath ) ) ) {
									if ( ( $new_blog_name = array_pop( $old_blog_subpath ) ) ) {
										if ( is_subdomain_install() ) {
											$new_subsite_domain = sprintf( '%s.%s', strtolower( $new_blog_name ), strtolower( $new_blog_domain ) );
											$new_subsite_path   = sprintf( '%s', untrailingslashit( $new_blog_path ) );
										} else {
											$new_subsite_domain = sprintf( '%s', strtolower( $new_blog_domain ) );
											$new_subsite_path   = sprintf( '%s/%s', untrailingslashit( $new_blog_path ), strtolower( $new_blog_name ) );
										}
									}
								}
							}
						}
					} elseif ( strripos( $subsite['Domain'], $old_home_url_www_inversion ) !== false ) {

						// Get blog sub domain
						if ( ( $old_blog_subdomain = substr_replace( $subsite['Domain'], '', strripos( $subsite['Domain'], $old_home_url_www_inversion ), strlen( $old_home_url_www_inversion ) ) ) ) {
							if ( ( $new_blog_name = trim( $old_blog_subdomain, '.' ) ) ) {
								if ( is_subdomain_install() ) {
									$new_subsite_domain = sprintf( '%s.%s', strtolower( $new_blog_name ), strtolower( $new_home_url_www_inversion ) );
									$new_subsite_path   = sprintf( '%s', untrailingslashit( $new_blog_path ) );
								} else {
									$new_subsite_domain = sprintf( '%s', strtolower( $new_blog_domain ) );
									$new_subsite_path   = sprintf( '%s/%s', untrailingslashit( $new_blog_path ), strtolower( $new_blog_name ) );
								}
							}
						}

						// Get blog sub path
						if ( ( $old_blog_subpath = substr_replace( trailingslashit( $subsite['Path'] ), '', stripos( trailingslashit( $subsite['Path'] ), trailingslashit( $old_blog_path ) ), strlen( trailingslashit( $old_blog_path ) ) ) ) ) {
							if ( ( $old_blog_subpath = explode( '/', untrailingslashit( $old_blog_subpath ) ) ) ) {
								if ( ( $old_blog_subpath = array_filter( $old_blog_subpath ) ) ) {
									if ( ( $new_blog_name = array_pop( $old_blog_subpath ) ) ) {
										if ( is_subdomain_install() ) {
											$new_subsite_domain = sprintf( '%s.%s', strtolower( $new_blog_name ), strtolower( $new_home_url_www_inversion ) );
											$new_subsite_path   = sprintf( '%s', untrailingslashit( $new_blog_path ) );
										} else {
											$new_subsite_domain = sprintf( '%s', strtolower( $new_blog_domain ) );
											$new_subsite_path   = sprintf( '%s/%s', untrailingslashit( $new_blog_path ), strtolower( $new_blog_name ) );
										}
									}
								}
							}
						}
					} else {

						// Get subsite domain and path (custom)
						$new_subsite_domain = strtolower( $subsite['Domain'] );
						$new_subsite_path   = untrailingslashit( $subsite['Path'] );
					}
				}

				$new_subsite_url = null;

				// Set subsite scheme
				if ( ( $new_subsite_scheme = parse_url( home_url(), PHP_URL_SCHEME ) ) ) {
					$new_subsite_url .= "{$new_subsite_scheme}://";
				}

				// Set subsite domain
				$new_subsite_url .= $new_subsite_domain;

				// Set subsite port
				if ( ( $new_subsite_port = parse_url( home_url(), PHP_URL_PORT ) ) ) {
					$new_subsite_url .= ":{$new_subsite_port}";
				}

				// Set subsite path
				$new_subsite_url .= $new_subsite_path;

				// Set internal Site URL (backward compatibility)
				if ( empty( $subsite['InternalSiteURL'] ) ) {
					$subsite['InternalSiteURL'] = null;
				}

				// Set internal Home URL (backward compatibility)
				if ( empty( $subsite['InternalHomeURL'] ) ) {
					$subsite['InternalHomeURL'] = null;
				}

				// Set active plugins (backward compatibility)
				if ( empty( $subsite['Plugins'] ) ) {
					$subsite['Plugins'] = array();
				}

				// Set active template (backward compatibility)
				if ( empty( $subsite['Template'] ) ) {
					$subsite['Template'] = null;
				}

				// Set active stylesheet (backward compatibility)
				if ( empty( $subsite['Stylesheet'] ) ) {
					$subsite['Stylesheet'] = null;
				}

				// Set uploads path (backward compatibility)
				if ( empty( $subsite['Uploads'] ) ) {
					$subsite['Uploads'] = null;
				}

				// Set uploads URL path (backward compatibility)
				if ( empty( $subsite['UploadsURL'] ) ) {
					$subsite['UploadsURL'] = null;
				}

				// Set uploads path (backward compatibility)
				if ( empty( $subsite['WordPress']['Uploads'] ) ) {
					$subsite['WordPress']['Uploads'] = null;
				}

				// Set uploads URL path (backward compatibility)
				if ( empty( $subsite['WordPress']['UploadsURL'] ) ) {
					$subsite['WordPress']['UploadsURL'] = null;
				}

				// Set site or blog items
				if ( empty( $multisite['Network'] ) ) {
					$sites[] = array(
						'Old' => array(
							'BlogID'          => $subsite['BlogID'],
							'SiteURL'         => $subsite['SiteURL'],
							'HomeURL'         => $subsite['HomeURL'],
							'InternalSiteURL' => $subsite['InternalSiteURL'],
							'InternalHomeURL' => $subsite['InternalHomeURL'],
							'Plugins'         => $subsite['Plugins'],
							'Template'        => $subsite['Template'],
							'Stylesheet'      => $subsite['Stylesheet'],
							'Uploads'         => $subsite['Uploads'],
							'UploadsURL'      => $subsite['UploadsURL'],
							'WordPress'       => $subsite['WordPress'],
						),
						'New' => array(
							'BlogID'          => null,
							'SiteURL'         => $new_subsite_url,
							'HomeURL'         => $new_subsite_url,
							'InternalSiteURL' => $new_subsite_url,
							'InternalHomeURL' => $new_subsite_url,
							'Plugins'         => $subsite['Plugins'],
							'Template'        => $subsite['Template'],
							'Stylesheet'      => $subsite['Stylesheet'],
						),
					);
				} else {
					if ( defined( 'UPLOADBLOGSDIR' ) ) {
						$blogs[] = array(
							'Old' => array(
								'BlogID'          => $subsite['BlogID'],
								'SiteURL'         => $subsite['SiteURL'],
								'HomeURL'         => $subsite['HomeURL'],
								'InternalSiteURL' => $subsite['InternalSiteURL'],
								'InternalHomeURL' => $subsite['InternalHomeURL'],
								'Plugins'         => $subsite['Plugins'],
								'Template'        => $subsite['Template'],
								'Stylesheet'      => $subsite['Stylesheet'],
								'Uploads'         => $subsite['Uploads'],
								'UploadsURL'      => $subsite['UploadsURL'],
								'WordPress'       => $subsite['WordPress'],
							),
							'New' => array(
								'BlogID'          => $subsite['BlogID'],
								'SiteURL'         => $new_subsite_url,
								'HomeURL'         => $new_subsite_url,
								'InternalSiteURL' => $new_subsite_url,
								'InternalHomeURL' => $new_subsite_url,
								'Plugins'         => $subsite['Plugins'],
								'Template'        => $subsite['Template'],
								'Stylesheet'      => $subsite['Stylesheet'],
								'Uploads'         => trim( ai1wm_blog_files_url( $subsite['BlogID'] ), '/' ),
								'UploadsURL'      => '',
								'WordPress'       => array(
									'UploadsURL' => untrailingslashit( $new_subsite_url ) . ai1wm_blog_files_url( $subsite['BlogID'] ),
								),
							),
						);
					} else {
						$blogs[] = array(
							'Old' => array(
								'BlogID'          => $subsite['BlogID'],
								'SiteURL'         => $subsite['SiteURL'],
								'HomeURL'         => $subsite['HomeURL'],
								'InternalSiteURL' => $subsite['InternalSiteURL'],
								'InternalHomeURL' => $subsite['InternalHomeURL'],
								'Plugins'         => $subsite['Plugins'],
								'Template'        => $subsite['Template'],
								'Stylesheet'      => $subsite['Stylesheet'],
								'Uploads'         => $subsite['Uploads'],
								'UploadsURL'      => $subsite['UploadsURL'],
								'WordPress'       => $subsite['WordPress'],
							),
							'New' => array(
								'BlogID'          => $subsite['BlogID'],
								'SiteURL'         => $new_subsite_url,
								'HomeURL'         => $new_subsite_url,
								'InternalSiteURL' => $new_subsite_url,
								'InternalHomeURL' => $new_subsite_url,
								'Plugins'         => $subsite['Plugins'],
								'Template'        => $subsite['Template'],
								'Stylesheet'      => $subsite['Stylesheet'],
								'Uploads'         => '',
								'UploadsURL'      => '',
								'WordPress'       => array(
									'UploadsURL' => untrailingslashit( $new_subsite_url ) . ai1wm_blog_uploads_url( $subsite['BlogID'] ),
								),
							),
						);
					}
				}
			}
		}

		// Get WP CLI subsites
		if ( defined( 'WP_CLI' ) ) {
			for ( $i = 0; $i < count( $sites ); ) {

				// Print old subsite URL
				WP_CLI::log( sprintf( 'Old site URL is: %s', $sites[ $i ]['Old']['HomeURL'] ) );

				// Enter new subsite URL
				if ( ( $url = trim( readline( sprintf( 'Enter new site URL (%s): ', $sites[ $i ]['New']['HomeURL'] ) ) ) ) ) {
					if ( filter_var( $url, FILTER_VALIDATE_URL ) === false ) {
						WP_CLI::log( sprintf( '"%s" is not a valid site URL', $url ) );
						continue;
					}

					$params['options']['sites'][] = $url;
				} else {
					$params['options']['sites'][] = $sites[ $i ]['New']['HomeURL'];
				}

				$i++;
			}

			$params['options']['subsite'] = count( $sites );
		}

		// Get user subsites
		if ( isset( $params['options']['sites'] ) && ( $subsites = $params['options']['sites'] ) ) {

			// Read blogs.json file
			if ( is_file( ai1wm_blogs_path( $params ) ) ) {
				$handle = ai1wm_open( ai1wm_blogs_path( $params ), 'r' );

				// Parse blogs.json file
				$blogs = ai1wm_read( $handle, filesize( ai1wm_blogs_path( $params ) ) );
				$blogs = json_decode( $blogs, true );

				// Close handle
				ai1wm_close( $handle );
			}

			// Loop over user subsites
			foreach ( $subsites as $i => $url ) {

				// Get blog scheme
				$blog_scheme = parse_url( trim( $url ), PHP_URL_SCHEME );

				// Get blog domain
				$blog_domain = parse_url( trim( $url ), PHP_URL_HOST );

				// Get blog path
				$blog_path = parse_url( trim( $url ), PHP_URL_PATH );

				// Create empty blog
				if ( domain_exists( $blog_domain, trailingslashit( $blog_path ), $params['options']['network'] ) ) {
					if ( ( $blog_id = get_blog_id_from_url( $blog_domain, trailingslashit( $blog_path ) ) ) ) {
						switch_to_blog( $blog_id );

						// Add new blog details
						$blogs[] = array(
							'Old' => array(
								'BlogID'          => $sites[ $i ]['Old']['BlogID'],
								'SiteURL'         => $sites[ $i ]['Old']['SiteURL'],
								'HomeURL'         => $sites[ $i ]['Old']['HomeURL'],
								'InternalSiteURL' => $sites[ $i ]['Old']['InternalSiteURL'],
								'InternalHomeURL' => $sites[ $i ]['Old']['InternalHomeURL'],
								'Plugins'         => $sites[ $i ]['Old']['Plugins'],
								'Template'        => $sites[ $i ]['Old']['Template'],
								'Stylesheet'      => $sites[ $i ]['Old']['Stylesheet'],
								'Uploads'         => $sites[ $i ]['Old']['Uploads'],
								'UploadsURL'      => $sites[ $i ]['Old']['UploadsURL'],
								'WordPress'       => $sites[ $i ]['Old']['WordPress'],
							),
							'New' => array(
								'BlogID'          => $blog_id,
								'SiteURL'         => get_site_url( $blog_id, null, $blog_scheme ),
								'HomeURL'         => get_home_url( $blog_id, null, $blog_scheme ),
								'InternalSiteURL' => get_site_url( $blog_id, null, $blog_scheme ),
								'InternalHomeURL' => get_home_url( $blog_id, null, $blog_scheme ),
								'Plugins'         => $sites[ $i ]['New']['Plugins'],
								'Template'        => $sites[ $i ]['New']['Template'],
								'Stylesheet'      => $sites[ $i ]['New']['Stylesheet'],
								'Uploads'         => get_option( 'upload_path' ),
								'UploadsURL'      => get_option( 'upload_url_path' ),
								'WordPress'       => array(
									'UploadsURL' => ai1wm_get_uploads_url(),
								),
							),
						);

						restore_current_blog();
					}
				} else {
					// This function has been deprecated. Use wp_insert_site() instead (WordPress >= 5.1)
					if ( function_exists( 'wp_insert_site' ) ) {
						if ( ( $blog_id = wp_insert_site( array( 'domain' => $blog_domain, 'path' => trailingslashit( $blog_path ), 'network_id' => $params['options']['network'] ) ) ) ) {
							switch_to_blog( $blog_id );

							// Add new blog details
							$blogs[] = array(
								'Old' => array(
									'BlogID'          => $sites[ $i ]['Old']['BlogID'],
									'SiteURL'         => $sites[ $i ]['Old']['SiteURL'],
									'HomeURL'         => $sites[ $i ]['Old']['HomeURL'],
									'InternalSiteURL' => $sites[ $i ]['Old']['InternalSiteURL'],
									'InternalHomeURL' => $sites[ $i ]['Old']['InternalHomeURL'],
									'Plugins'         => $sites[ $i ]['Old']['Plugins'],
									'Template'        => $sites[ $i ]['Old']['Template'],
									'Stylesheet'      => $sites[ $i ]['Old']['Stylesheet'],
									'Uploads'         => $sites[ $i ]['Old']['Uploads'],
									'UploadsURL'      => $sites[ $i ]['Old']['UploadsURL'],
									'WordPress'       => $sites[ $i ]['Old']['WordPress'],
								),
								'New' => array(
									'BlogID'          => $blog_id,
									'SiteURL'         => get_site_url( $blog_id, null, $blog_scheme ),
									'HomeURL'         => get_home_url( $blog_id, null, $blog_scheme ),
									'InternalSiteURL' => get_site_url( $blog_id, null, $blog_scheme ),
									'InternalHomeURL' => get_home_url( $blog_id, null, $blog_scheme ),
									'Plugins'         => $sites[ $i ]['New']['Plugins'],
									'Template'        => $sites[ $i ]['New']['Template'],
									'Stylesheet'      => $sites[ $i ]['New']['Stylesheet'],
									'Uploads'         => get_option( 'upload_path' ),
									'UploadsURL'      => get_option( 'upload_url_path' ),
									'WordPress'       => array(
										'UploadsURL' => ai1wm_get_uploads_url(),
									),
								),
							);

							restore_current_blog();
						}
					} else {
						if ( ( $blog_id = create_empty_blog( $blog_domain, trailingslashit( $blog_path ), null, $params['options']['network'] ) ) ) {
							switch_to_blog( $blog_id );

							// Add new blog details
							$blogs[] = array(
								'Old' => array(
									'BlogID'          => $sites[ $i ]['Old']['BlogID'],
									'SiteURL'         => $sites[ $i ]['Old']['SiteURL'],
									'HomeURL'         => $sites[ $i ]['Old']['HomeURL'],
									'InternalSiteURL' => $sites[ $i ]['Old']['InternalSiteURL'],
									'InternalHomeURL' => $sites[ $i ]['Old']['InternalHomeURL'],
									'Plugins'         => $sites[ $i ]['Old']['Plugins'],
									'Template'        => $sites[ $i ]['Old']['Template'],
									'Stylesheet'      => $sites[ $i ]['Old']['Stylesheet'],
									'Uploads'         => $sites[ $i ]['Old']['Uploads'],
									'UploadsURL'      => $sites[ $i ]['Old']['UploadsURL'],
									'WordPress'       => $sites[ $i ]['Old']['WordPress'],
								),
								'New' => array(
									'BlogID'          => $blog_id,
									'SiteURL'         => get_site_url( $blog_id, null, $blog_scheme ),
									'HomeURL'         => get_home_url( $blog_id, null, $blog_scheme ),
									'InternalSiteURL' => get_site_url( $blog_id, null, $blog_scheme ),
									'InternalHomeURL' => get_home_url( $blog_id, null, $blog_scheme ),
									'Plugins'         => $sites[ $i ]['New']['Plugins'],
									'Template'        => $sites[ $i ]['New']['Template'],
									'Stylesheet'      => $sites[ $i ]['New']['Stylesheet'],
									'Uploads'         => get_option( 'upload_path' ),
									'UploadsURL'      => get_option( 'upload_url_path' ),
									'WordPress'       => array(
										'UploadsURL' => ai1wm_get_uploads_url(),
									),
								),
							);

							restore_current_blog();
						}
					}
				}
			}
		}

		// Save blogs.json file
		$handle = ai1wm_open( ai1wm_blogs_path( $params ), 'w' );
		ai1wm_write( $handle, json_encode( $blogs ) );
		ai1wm_close( $handle );

		// Set current subsite
		if ( isset( $params['options']['subsite'] ) ) {
			$subsite = (int) $params['options']['subsite'] + 1;
		} else {
			$subsite = 0;
		}

		// User-defined subsite URL
		if ( $subsite < count( $sites ) ) {

			// Get networks
			$networks = ai1wmme_get_networks();

			// Set progress
			Ai1wm_Status::blogs(
				Ai1wm_Template::get_content(
					'import/titles',
					array(
						'networks' => $networks,
						'sites'    => $sites,
						'subsite'  => $subsite,
					),
					AI1WMME_TEMPLATES_PATH
				),
				Ai1wm_Template::get_content(
					'import/sites',
					array(
						'networks' => $networks,
						'sites'    => $sites,
						'subsite'  => $subsite,
					),
					AI1WMME_TEMPLATES_PATH
				)
			);
			exit;
		}

		// Set progress
		Ai1wm_Status::info( __( 'Done preparing blogs.', AI1WMME_PLUGIN_NAME ) );

		return $params;
	}
}
