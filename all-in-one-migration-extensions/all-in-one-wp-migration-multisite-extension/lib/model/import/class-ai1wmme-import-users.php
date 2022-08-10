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

class Ai1wmme_Import_Users {

	public static function execute( $params ) {
		global $wpdb;

		// Skip users import
		if ( ! is_file( ai1wm_database_path( $params ) ) ) {
			return $params;
		}

		// Read multisite.json file
		$handle = ai1wm_open( ai1wm_multisite_path( $params ), 'r' );

		// Parse multisite.json file
		$multisite = ai1wm_read( $handle, filesize( ai1wm_multisite_path( $params ) ) );
		$multisite = json_decode( $multisite, true );

		// Close handle
		ai1wm_close( $handle );

		// Network
		if ( empty( $multisite['Network'] ) ) {

			// Read blogs.json file
			$handle = ai1wm_open( ai1wm_blogs_path( $params ), 'r' );

			// Set progress
			Ai1wm_Status::info( __( 'Preparing users...', AI1WMME_PLUGIN_NAME ) );

			// Parse blogs.json file
			$blogs = ai1wm_read( $handle, filesize( ai1wm_blogs_path( $params ) ) );
			$blogs = json_decode( $blogs, true );

			// Close handle
			ai1wm_close( $handle );

			// Insert users
			if ( $blogs ) {

				// Get database client
				if ( empty( $wpdb->use_mysqli ) ) {
					$mysql = new Ai1wm_Database_Mysql( $wpdb );
				} else {
					$mysql = new Ai1wm_Database_Mysqli( $wpdb );
				}

				$meta_value = array();

				// Get base prefix
				$base_prefix = ai1wm_table_prefix();

				// Get mainsite prefix
				$mainsite_prefix = ai1wm_table_prefix( 'mainsite' );

				// Set meta value
				foreach ( $blogs as $blog ) {

					$home_urls = array();

					// Add Home URL
					if ( isset( $blog['Old']['HomeURL'] ) ) {
						$home_urls[] = $blog['Old']['HomeURL'];
					}

					// Add Internal Home URL
					if ( isset( $blog['Old']['InternalHomeURL'] ) ) {
						$home_urls[] = $blog['Old']['InternalHomeURL'];
					}

					// Get Home URL
					foreach ( $home_urls as $home_url ) {

						// Set primary blog
						$meta_value[] = sprintf( " WHEN mum.meta_key = 'primary_blog' AND mum.meta_value = '%s' THEN '%s' ", $blog['Old']['BlogID'], $blog['New']['BlogID'] );

						// Set source domain
						$meta_value[] = sprintf( " WHEN mum.meta_key = 'source_domain' AND mum.meta_value = '%s' THEN '%s' ", parse_url( $home_url, PHP_URL_HOST ), parse_url( $blog['New']['HomeURL'], PHP_URL_HOST ) );
					}
				}

				// Set main primary blog
				$meta_value[] = sprintf( " WHEN mum.meta_key = 'primary_blog' THEN '%s' ", 1 );

				// Set main source domain
				$meta_value[] = sprintf( " WHEN mum.meta_key = 'source_domain' THEN '%s' ", parse_url( site_url(), PHP_URL_HOST ) );

				// Replace meta value
				$meta_replace = sprintf( ' CASE %s ELSE mum.meta_value END ', implode( ' ', $meta_value ) );

				// Insert users
				$mysql->query(
					"INSERT INTO
						{$base_prefix}users (
							user_login,
							user_pass,
							user_nicename,
							user_email,
							user_url,
							user_registered,
							user_activation_key,
							user_status,
							display_name
						)
					SELECT
						mu.user_login,
						mu.user_pass,
						mu.user_nicename,
						mu.user_email,
						mu.user_url,
						mu.user_registered,
						mu.user_activation_key,
						mu.user_status,
						mu.display_name
					FROM
						{$mainsite_prefix}users AS mu
					LEFT JOIN
						{$base_prefix}users AS u ON (CONVERT(mu.user_login USING utf8) = CONVERT(u.user_login USING utf8))
					WHERE
						u.ID IS NULL"
				);

				// Update user meta
				$mysql->query( "UPDATE {$mainsite_prefix}usermeta SET meta_key = REPLACE(meta_key, '{$mainsite_prefix}', '{$base_prefix}')" );

				// Insert user meta
				$mysql->query(
					"INSERT INTO
						{$base_prefix}usermeta (
							user_id,
							meta_key,
							meta_value
						)
					SELECT
						u.ID,
						mum.meta_key,
						{$meta_replace} AS meta_value
					FROM
						{$mainsite_prefix}usermeta AS mum
					INNER JOIN
						{$mainsite_prefix}users AS mu ON (mum.user_id = mu.ID)
					INNER JOIN
						{$base_prefix}users AS u ON (CONVERT(mu.user_login USING utf8) = CONVERT(u.user_login USING utf8))
					LEFT JOIN
						{$base_prefix}usermeta AS um ON (um.user_id = u.ID AND CONVERT(mum.meta_key USING utf8) = CONVERT(um.meta_key USING utf8))
					WHERE
						um.meta_key IS NULL"
				);

				$tables = $mysql->get_tables();

				// Update posts and comments
				foreach ( $blogs as $blog ) {

					// Get table prefix
					$table_prefix = ai1wm_table_prefix( $blog['New']['BlogID'] );

					// Update WordPress comments
					if ( in_array( "{$table_prefix}comments", $tables ) ) {
						$mysql->query(
							"UPDATE
								{$table_prefix}comments AS c
							INNER JOIN
								{$mainsite_prefix}users AS mu ON (c.user_id = mu.ID)
							INNER JOIN
								{$base_prefix}users AS u ON (CONVERT(u.user_login USING utf8) = CONVERT(mu.user_login USING utf8))
							SET
								c.user_id = u.ID"
						);
					}

					// Update WordPress posts
					if ( in_array( "{$table_prefix}posts", $tables ) ) {
						$mysql->query(
							"UPDATE
								{$table_prefix}posts AS p
							INNER JOIN
								{$mainsite_prefix}users AS mu ON (p.post_author = mu.ID)
							INNER JOIN
								{$base_prefix}users AS u ON (CONVERT(u.user_login USING utf8) = CONVERT(mu.user_login USING utf8))
							SET
								p.post_author = u.ID"
						);
					}

					// Update AutomateWoo abandoned carts
					if ( in_array( "{$table_prefix}automatewoo_abandoned_carts", $tables ) ) {
						$mysql->query(
							"UPDATE
								{$table_prefix}automatewoo_abandoned_carts AS aac
							INNER JOIN
								{$mainsite_prefix}users AS mu ON (aac.user_id = mu.ID)
							INNER JOIN
								{$base_prefix}users AS u ON (CONVERT(u.user_login USING utf8) = CONVERT(mu.user_login USING utf8))
							SET
								aac.user_id = u.ID"
						);
					}

					// Update AutomateWoo customers
					if ( in_array( "{$table_prefix}automatewoo_customers", $tables ) ) {
						$mysql->query(
							"UPDATE
								{$table_prefix}automatewoo_customers AS ac
							INNER JOIN
								{$mainsite_prefix}users AS mu ON (ac.user_id = mu.ID)
							INNER JOIN
								{$base_prefix}users AS u ON (CONVERT(u.user_login USING utf8) = CONVERT(mu.user_login USING utf8))
							SET
								ac.user_id = u.ID"
						);
					}

					// Update Gravity Forms entry notes
					if ( in_array( "{$table_prefix}gf_entry_notes", $tables ) ) {
						$mysql->query(
							"UPDATE
								{$table_prefix}gf_entry_notes AS gen
							INNER JOIN
								{$mainsite_prefix}users AS mu ON (gen.user_id = mu.ID)
							INNER JOIN
								{$base_prefix}users AS u ON (CONVERT(u.user_login USING utf8) = CONVERT(mu.user_login USING utf8))
							SET
								gen.user_id = u.ID"
						);
					}

					// Update MailPoet subscribers
					if ( in_array( "{$table_prefix}mailpoet_subscribers", $tables ) ) {
						$mysql->query(
							"UPDATE
								{$table_prefix}mailpoet_subscribers AS ms
							INNER JOIN
								{$mainsite_prefix}users AS mu ON (ms.wp_user_id = mu.ID)
							INNER JOIN
								{$base_prefix}users AS u ON (CONVERT(u.user_login USING utf8) = CONVERT(mu.user_login USING utf8))
							SET
								ms.wp_user_id = u.ID"
						);
					}

					// Update MailPoet user flags
					if ( in_array( "{$table_prefix}mailpoet_user_flags", $tables ) ) {
						$mysql->query(
							"UPDATE
								{$table_prefix}mailpoet_user_flags AS muf
							INNER JOIN
								{$mainsite_prefix}users AS mu ON (muf.user_id = mu.ID)
							INNER JOIN
								{$base_prefix}users AS u ON (CONVERT(u.user_login USING utf8) = CONVERT(mu.user_login USING utf8))
							SET
								muf.user_id = u.ID"
						);
					}

					// Update WooCommerce CRM customer list
					if ( in_array( "{$table_prefix}wc_crm_customer_list", $tables ) ) {
						$mysql->query(
							"UPDATE
								{$table_prefix}wc_crm_customer_list AS wccl
							INNER JOIN
								{$mainsite_prefix}users AS mu ON (wccl.user_id = mu.ID)
							INNER JOIN
								{$base_prefix}users AS u ON (CONVERT(u.user_login USING utf8) = CONVERT(mu.user_login USING utf8))
							SET
								wccl.user_id = u.ID"
						);
					}

					// Update WooCommerce CRM log
					if ( in_array( "{$table_prefix}wc_crm_log", $tables ) ) {
						$mysql->query(
							"UPDATE
								{$table_prefix}wc_crm_log AS wcl
							INNER JOIN
								{$mainsite_prefix}users AS mu ON (wcl.user_id = mu.ID)
							INNER JOIN
								{$base_prefix}users AS u ON (CONVERT(u.user_login USING utf8) = CONVERT(mu.user_login USING utf8))
							SET
								wcl.user_id = u.ID"
						);
					}

					// Update WooCommerce download log
					if ( in_array( "{$table_prefix}wc_download_log", $tables ) ) {
						$mysql->query(
							"UPDATE
								{$table_prefix}wc_download_log AS wdl
							INNER JOIN
								{$mainsite_prefix}users AS mu ON (wdl.user_id = mu.ID)
							INNER JOIN
								{$base_prefix}users AS u ON (CONVERT(u.user_login USING utf8) = CONVERT(mu.user_login USING utf8))
							SET
								wdl.user_id = u.ID"
						);
					}

					// Update WooCommerce web hooks
					if ( in_array( "{$table_prefix}wc_webhooks", $tables ) ) {
						$mysql->query(
							"UPDATE
								{$table_prefix}wc_webhooks AS wwh
							INNER JOIN
								{$mainsite_prefix}users AS mu ON (wwh.user_id = mu.ID)
							INNER JOIN
								{$base_prefix}users AS u ON (CONVERT(u.user_login USING utf8) = CONVERT(mu.user_login USING utf8))
							SET
								wwh.user_id = u.ID"
						);
					}

					// Update WooCommerce API keys
					if ( in_array( "{$table_prefix}woocommerce_api_keys", $tables ) ) {
						$mysql->query(
							"UPDATE
								{$table_prefix}woocommerce_api_keys AS wak
							INNER JOIN
								{$mainsite_prefix}users AS mu ON (wak.user_id = mu.ID)
							INNER JOIN
								{$base_prefix}users AS u ON (CONVERT(u.user_login USING utf8) = CONVERT(mu.user_login USING utf8))
							SET
								wak.user_id = u.ID"
						);
					}

					// Update WooCommerce downloadable product permissions
					if ( in_array( "{$table_prefix}woocommerce_downloadable_product_permissions", $tables ) ) {
						$mysql->query(
							"UPDATE
								{$table_prefix}woocommerce_downloadable_product_permissions AS wdpp
							INNER JOIN
								{$mainsite_prefix}users AS mu ON (wdpp.user_id = mu.ID)
							INNER JOIN
								{$base_prefix}users AS u ON (CONVERT(u.user_login USING utf8) = CONVERT(mu.user_login USING utf8))
							SET
								wdpp.user_id = u.ID"
						);
					}

					// Update WooCommerce payment tokens
					if ( in_array( "{$table_prefix}woocommerce_payment_tokens", $tables ) ) {
						$mysql->query(
							"UPDATE
								{$table_prefix}woocommerce_payment_tokens AS wpt
							INNER JOIN
								{$mainsite_prefix}users AS mu ON (wpt.user_id = mu.ID)
							INNER JOIN
								{$base_prefix}users AS u ON (CONVERT(u.user_login USING utf8) = CONVERT(mu.user_login USING utf8))
							SET
								wpt.user_id = u.ID"
						);
					}
				}
			}

			// Set progress
			Ai1wm_Status::info( __( 'Done preparing users.', AI1WMME_PLUGIN_NAME ) );
		}

		return $params;
	}
}
