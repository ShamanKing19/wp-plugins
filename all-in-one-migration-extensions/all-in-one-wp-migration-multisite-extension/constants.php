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
define( 'AI1WMME_VERSION', '3.96' );

// ===============
// = Plugin Name =
// ===============
define( 'AI1WMME_PLUGIN_NAME', 'all-in-one-wp-migration-multisite-extension' );

// ============
// = Lib Path =
// ============
define( 'AI1WMME_LIB_PATH', AI1WMME_PATH . DIRECTORY_SEPARATOR . 'lib' );

// ===================
// = Controller Path =
// ===================
define( 'AI1WMME_CONTROLLER_PATH', AI1WMME_LIB_PATH . DIRECTORY_SEPARATOR . 'controller' );

// ==============
// = Model Path =
// ==============
define( 'AI1WMME_MODEL_PATH', AI1WMME_LIB_PATH . DIRECTORY_SEPARATOR . 'model' );

// ===============
// = Export Path =
// ===============
define( 'AI1WMME_EXPORT_PATH', AI1WMME_MODEL_PATH . DIRECTORY_SEPARATOR . 'export' );

// ===============
// = Import Path =
// ===============
define( 'AI1WMME_IMPORT_PATH', AI1WMME_MODEL_PATH . DIRECTORY_SEPARATOR . 'import' );

// =============
// = View Path =
// =============
define( 'AI1WMME_TEMPLATES_PATH', AI1WMME_LIB_PATH . DIRECTORY_SEPARATOR . 'view' );

// ===============
// = Vendor Path =
// ===============
define( 'AI1WMME_VENDOR_PATH', AI1WMME_LIB_PATH . DIRECTORY_SEPARATOR . 'vendor' );

// ===========================
// = ServMask Activation URL =
// ===========================
define( 'AI1WMME_ACTIVATION_URL', 'https://servmask.com/purchase/activations' );

// =================
// = Max File Size =
// =================
define( 'AI1WMME_MAX_FILE_SIZE', 0 );

// ===============
// = Purchase ID =
// ===============
define( 'AI1WMME_PURCHASE_ID', 'cfea992c-4fb1-e0c6-72e9-35c2-5078d1aa' );
