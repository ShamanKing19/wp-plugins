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

// ==================
// = Plugin Version =
// ==================
define( 'AI1WMFE_VERSION', '2.62' );

// ===============
// = Plugin Name =
// ===============
define( 'AI1WMFE_PLUGIN_NAME', 'all-in-one-wp-migration-ftp-extension' );

// ============
// = Lib Path =
// ============
define( 'AI1WMFE_LIB_PATH', AI1WMFE_PATH . DIRECTORY_SEPARATOR . 'lib' );

// ===================
// = Controller Path =
// ===================
define( 'AI1WMFE_CONTROLLER_PATH', AI1WMFE_LIB_PATH . DIRECTORY_SEPARATOR . 'controller' );

// ==============
// = Model Path =
// ==============
define( 'AI1WMFE_MODEL_PATH', AI1WMFE_LIB_PATH . DIRECTORY_SEPARATOR . 'model' );

// ===============
// = Export Path =
// ===============
define( 'AI1WMFE_EXPORT_PATH', AI1WMFE_MODEL_PATH . DIRECTORY_SEPARATOR . 'export' );

// ===============
// = Import Path =
// ===============
define( 'AI1WMFE_IMPORT_PATH', AI1WMFE_MODEL_PATH . DIRECTORY_SEPARATOR . 'import' );

// =============
// = View Path =
// =============
define( 'AI1WMFE_TEMPLATES_PATH', AI1WMFE_LIB_PATH . DIRECTORY_SEPARATOR . 'view' );

// ===============
// = Vendor Path =
// ===============
define( 'AI1WMFE_VENDOR_PATH', AI1WMFE_LIB_PATH . DIRECTORY_SEPARATOR . 'vendor' );

// ===========================
// = ServMask Activation URL =
// ===========================
define( 'AI1WMFE_ACTIVATION_URL', 'https://servmask.com/purchase/activations' );

// ================
// = PHPSecLib Path =
// ================
define( 'AI1WMFE_PHPSECLIB_PATH', AI1WMFE_VENDOR_PATH . DIRECTORY_SEPARATOR . 'phpseclib' );

// ====================
// = FTP Default Type =
// ====================
define( 'AI1WMFE_FTP_DEFAULT_TYPE', 'ftp' );

// ==============================
// = FTP Default Authentication =
// ==============================
define( 'AI1WMFE_FTP_DEFAULT_AUTHENTICATION', 'password' );

// ====================
// = FTP Default Port =
// ====================
define( 'AI1WMFE_FTP_DEFAULT_PORT', 21 );

// ===========================
// = Default File Chunk Size =
// ===========================
define( 'AI1WMFE_DEFAULT_FILE_CHUNK_SIZE', 5 * 1024 * 1024 );

// =================
// = Max File Size =
// =================
define( 'AI1WMFE_MAX_FILE_SIZE', 0 );

// ===============
// = Purchase ID =
// ===============
define( 'AI1WMFE_PURCHASE_ID', 'a917567a-70ff-4b87-b1c5-130a5ecde482' );
