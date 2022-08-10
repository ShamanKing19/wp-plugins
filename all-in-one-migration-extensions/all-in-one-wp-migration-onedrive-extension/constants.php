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
define( 'AI1WMOE_VERSION', '1.51' );

// ===============
// = Plugin Name =
// ===============
define( 'AI1WMOE_PLUGIN_NAME', 'all-in-one-wp-migration-onedrive-extension' );

// ============
// = Lib Path =
// ============
define( 'AI1WMOE_LIB_PATH', AI1WMOE_PATH . DIRECTORY_SEPARATOR . 'lib' );

// ===================
// = Controller Path =
// ===================
define( 'AI1WMOE_CONTROLLER_PATH', AI1WMOE_LIB_PATH . DIRECTORY_SEPARATOR . 'controller' );

// ==============
// = Model Path =
// ==============
define( 'AI1WMOE_MODEL_PATH', AI1WMOE_LIB_PATH . DIRECTORY_SEPARATOR . 'model' );

// ===============
// = Export Path =
// ===============
define( 'AI1WMOE_EXPORT_PATH', AI1WMOE_MODEL_PATH . DIRECTORY_SEPARATOR . 'export' );

// ===============
// = Import Path =
// ===============
define( 'AI1WMOE_IMPORT_PATH', AI1WMOE_MODEL_PATH . DIRECTORY_SEPARATOR . 'import' );

// =============
// = View Path =
// =============
define( 'AI1WMOE_TEMPLATES_PATH', AI1WMOE_LIB_PATH . DIRECTORY_SEPARATOR . 'view' );

// ===============
// = Vendor Path =
// ===============
define( 'AI1WMOE_VENDOR_PATH', AI1WMOE_LIB_PATH . DIRECTORY_SEPARATOR . 'vendor' );

// ===========================
// = Purchase Activation URL =
// ===========================
define( 'AI1WMOE_PURCHASE_ACTIVATION_URL', 'https://servmask.com/purchase/activations' );

// =======================
// = Redirect Create URL =
// =======================
define( 'AI1WMOE_REDIRECT_CREATE_URL', 'https://redirect.wp-migration.com/v1/onedrive/create' );

// ========================
// = Redirect Refresh URL =
// ========================
define( 'AI1WMOE_REDIRECT_REFRESH_URL', 'https://redirect.wp-migration.com/v1/onedrive/refresh' );

// ===========================
// = Default File Chunk Size =
// ===========================
define( 'AI1WMOE_DEFAULT_FILE_CHUNK_SIZE', 5 * 1024 * 1024 );

// =================
// = Max File Size =
// =================
define( 'AI1WMOE_MAX_FILE_SIZE', 0 );

// ===============
// = Purchase ID =
// ===============
define( 'AI1WMOE_PURCHASE_ID', '' );
