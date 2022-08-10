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

class Ai1wmme_Import_Done {

	public static function execute( $params ) {

		// Check multisite.json file
		if ( true === is_file( ai1wm_multisite_path( $params ) ) ) {

			// Read multisite.json file
			$handle = ai1wm_open( ai1wm_multisite_path( $params ), 'r' );

			// Parse multisite.json file
			$multisite = ai1wm_read( $handle, filesize( ai1wm_multisite_path( $params ) ) );
			$multisite = json_decode( $multisite, true );

			// Close handle
			ai1wm_close( $handle );

			// Activate WordPress plugins
			if ( isset( $multisite['Plugins'] ) && ( $plugins = $multisite['Plugins'] ) ) {
				ai1wm_activate_sitewide_plugins( $plugins );
			}

			// Deactivate WordPress SSL plugins
			if ( ! is_ssl() ) {
				ai1wm_deactivate_sitewide_plugins(
					array(
						ai1wm_discover_plugin_basename( 'really-simple-ssl/rlrsssl-really-simple-ssl.php' ),
						ai1wm_discover_plugin_basename( 'wordpress-https/wordpress-https.php' ),
						ai1wm_discover_plugin_basename( 'wp-force-ssl/wp-force-ssl.php' ),
						ai1wm_discover_plugin_basename( 'force-https-littlebizzy/force-https.php' ),
					)
				);
			}

			// Deactivate WordPress plugins
			ai1wm_deactivate_sitewide_plugins(
				array(
					ai1wm_discover_plugin_basename( 'invisible-recaptcha/invisible-recaptcha.php' ),
					ai1wm_discover_plugin_basename( 'wps-hide-login/wps-hide-login.php' ),
					ai1wm_discover_plugin_basename( 'hide-my-wp/index.php' ),
					ai1wm_discover_plugin_basename( 'hide-my-wordpress/index.php' ),
					ai1wm_discover_plugin_basename( 'mycustomwidget/my_custom_widget.php' ),
					ai1wm_discover_plugin_basename( 'lockdown-wp-admin/lockdown-wp-admin.php' ),
					ai1wm_discover_plugin_basename( 'rename-wp-login/rename-wp-login.php' ),
					ai1wm_discover_plugin_basename( 'wp-simple-firewall/icwp-wpsf.php' ),
					ai1wm_discover_plugin_basename( 'join-my-multisite/joinmymultisite.php' ),
					ai1wm_discover_plugin_basename( 'multisite-clone-duplicator/multisite-clone-duplicator.php' ),
					ai1wm_discover_plugin_basename( 'wordpress-mu-domain-mapping/domain_mapping.php' ),
					ai1wm_discover_plugin_basename( 'wordpress-starter/siteground-wizard.php' ),
					ai1wm_discover_plugin_basename( 'pro-sites/pro-sites.php' ),
				)
			);

			// Deactivate Revolution Slider
			ai1wm_deactivate_sitewide_revolution_slider( ai1wm_discover_plugin_basename( 'revslider/revslider.php' ) );

			// Deactivate Jetpack modules
			ai1wm_deactivate_jetpack_modules( array( 'photon', 'sso' ) );

			// Flush Elementor cache
			ai1wm_elementor_cache_flush();

			// Initial DB version
			ai1wm_initial_db_version();
		}

		// Check blogs.json file
		if ( true === is_file( ai1wm_blogs_path( $params ) ) ) {

			// Read blogs.json file
			$handle = ai1wm_open( ai1wm_blogs_path( $params ), 'r' );

			// Parse blogs.json file
			$blogs = ai1wm_read( $handle, filesize( ai1wm_blogs_path( $params ) ) );
			$blogs = json_decode( $blogs, true );

			// Close handle
			ai1wm_close( $handle );

			// Loop over blogs
			foreach ( $blogs as $blog ) {
				switch_to_blog( $blog['New']['BlogID'] );

				// Activate WordPress plugins
				if ( isset( $blog['New']['Plugins'] ) && ( $plugins = $blog['New']['Plugins'] ) ) {
					ai1wm_activate_plugins( $plugins );
				}

				// Activate WordPress template
				if ( isset( $blog['New']['Template'] ) && ( $template = $blog['New']['Template'] ) ) {
					ai1wm_activate_template( $template );
				}

				// Activate WordPress stylesheet
				if ( isset( $blog['New']['Stylesheet'] ) && ( $stylesheet = $blog['New']['Stylesheet'] ) ) {
					ai1wm_activate_stylesheet( $stylesheet );
				}

				// Deactivate WordPress SSL plugins
				if ( ! is_ssl() ) {
					ai1wm_deactivate_plugins(
						array(
							ai1wm_discover_plugin_basename( 'really-simple-ssl/rlrsssl-really-simple-ssl.php' ),
							ai1wm_discover_plugin_basename( 'wordpress-https/wordpress-https.php' ),
							ai1wm_discover_plugin_basename( 'wp-force-ssl/wp-force-ssl.php' ),
							ai1wm_discover_plugin_basename( 'force-https-littlebizzy/force-https.php' ),
						)
					);
				}

				// Deactivate WordPress plugins
				ai1wm_deactivate_plugins(
					array(
						ai1wm_discover_plugin_basename( 'invisible-recaptcha/invisible-recaptcha.php' ),
						ai1wm_discover_plugin_basename( 'wps-hide-login/wps-hide-login.php' ),
						ai1wm_discover_plugin_basename( 'hide-my-wp/index.php' ),
						ai1wm_discover_plugin_basename( 'hide-my-wordpress/index.php' ),
						ai1wm_discover_plugin_basename( 'mycustomwidget/my_custom_widget.php' ),
						ai1wm_discover_plugin_basename( 'lockdown-wp-admin/lockdown-wp-admin.php' ),
						ai1wm_discover_plugin_basename( 'rename-wp-login/rename-wp-login.php' ),
						ai1wm_discover_plugin_basename( 'wp-simple-firewall/icwp-wpsf.php' ),
						ai1wm_discover_plugin_basename( 'join-my-multisite/joinmymultisite.php' ),
						ai1wm_discover_plugin_basename( 'multisite-clone-duplicator/multisite-clone-duplicator.php' ),
						ai1wm_discover_plugin_basename( 'wordpress-mu-domain-mapping/domain_mapping.php' ),
						ai1wm_discover_plugin_basename( 'wordpress-starter/siteground-wizard.php' ),
						ai1wm_discover_plugin_basename( 'pro-sites/pro-sites.php' ),
					)
				);

				// Deactivate Revolution Slider
				ai1wm_deactivate_revolution_slider( ai1wm_discover_plugin_basename( 'revslider/revslider.php' ) );

				// Deactivate Jetpack modules
				ai1wm_deactivate_jetpack_modules( array( 'photon', 'sso' ) );

				// Flush Elementor cache
				ai1wm_elementor_cache_flush();

				// Initial DB version
				ai1wm_initial_db_version();

				restore_current_blog();
			}
		}

		// Set progress
		if ( ai1wm_validate_plugin_basename( 'oxygen/functions.php' ) ) {
			Ai1wm_Status::done( __( 'Your site has been imported successfully!', AI1WMME_PLUGIN_NAME ), Ai1wm_Template::get_content( 'import/oxygen', array(), AI1WMME_TEMPLATES_PATH ) );
		} else {
			Ai1wm_Status::done( __( 'Your site has been imported successfully!', AI1WMME_PLUGIN_NAME ), Ai1wm_Template::get_content( 'import/done', array(), AI1WMME_TEMPLATES_PATH ) );
		}

		return $params;
	}
}
