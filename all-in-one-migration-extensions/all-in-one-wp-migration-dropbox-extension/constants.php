<?php
/**
 * Copyright (C) 2014-2019 ServMask Inc.
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

// ==================
// = Plugin Version =
// ==================
define( 'AI1WMDE_VERSION', '3.40' );

// ===============
// = Plugin Name =
// ===============
define( 'AI1WMDE_PLUGIN_NAME', 'all-in-one-wp-migration-dropbox-extension' );

// ============
// = Lib Path =
// ============
define( 'AI1WMDE_LIB_PATH', AI1WMDE_PATH . DIRECTORY_SEPARATOR . 'lib' );

// ===================
// = Controller Path =
// ===================
define( 'AI1WMDE_CONTROLLER_PATH', AI1WMDE_LIB_PATH . DIRECTORY_SEPARATOR . 'controller' );

// ==============
// = Model Path =
// ==============
define( 'AI1WMDE_MODEL_PATH', AI1WMDE_LIB_PATH . DIRECTORY_SEPARATOR . 'model' );

// ===============
// = Export Path =
// ===============
define( 'AI1WMDE_EXPORT_PATH', AI1WMDE_MODEL_PATH . DIRECTORY_SEPARATOR . 'export' );

// ===============
// = Import Path =
// ===============
define( 'AI1WMDE_IMPORT_PATH', AI1WMDE_MODEL_PATH . DIRECTORY_SEPARATOR . 'import' );

// =============
// = View Path =
// =============
define( 'AI1WMDE_TEMPLATES_PATH', AI1WMDE_LIB_PATH . DIRECTORY_SEPARATOR . 'view' );

// ===============
// = Vendor Path =
// ===============
define( 'AI1WMDE_VENDOR_PATH', AI1WMDE_LIB_PATH . DIRECTORY_SEPARATOR . 'vendor' );

// ========================
// = ServMask Dropbox URL =
// ========================
define( 'AI1WMDE_DROPBOX_URL', 'https://servmask.com/redirect/dropbox/create' );

// ===================
// = File Chunk Size =
// ===================
define( 'AI1WMDE_FILE_CHUNK_SIZE', ( defined( 'WP_CLI' ) ? 10 : 5 ) * 1024 * 1024 );

// =================
// = Max File Size =
// =================
define( 'AI1WMDE_MAX_FILE_SIZE', 0 );

// ===============
// = Purchase ID =
// ===============
define( 'AI1WMDE_PURCHASE_ID', '7a3522d6-f795-4233-b4e6-63b887aaf8bc' );
