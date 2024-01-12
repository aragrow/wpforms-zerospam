<?php
/**
 * Plugin Name:       WPForms - Zero Spam
 * Plugin URI:        https://aragrow.com/WPForms-zerosamp
 * Description:       Plugin to that extends the WPForms-lite to prevent spam
 * Requires at least: 5.5
 * Requires PHP:      7.0
 * Author:            Aragrow
 * Author URI:        https://aragrow.com
 * Version:           1.1
 * Text Domain:       wpforms-zerospam
 * Domain Path:       assets/languages
 *
 * WPForms-Zero Spam is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WPForms Zero Spam is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WPForms Zero Spam. If not, see <https://www.gnu.org/licenses/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined( 'WPFORMS_ZEROSPAM_VERSION' ) ) {
	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 */
	define( 'WPFORMS_ZEROSPAM_VERSION', '1.0.0.0' );
}

// Plugin Folder Path.
if ( ! defined( 'WPFORMS_ZEROSPAM_PLUGIN_DIR' ) ) {
	define( 'WPFORMS_ZEROSPAM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Plugin Folder URL.
if ( ! defined( 'WPFORMS_ZEROSPAM_PLUGIN_URL' ) ) {
	define( 'WPFORMS_ZEROSPAM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Plugin Root File.
if ( ! defined( 'WPFORMS_ZEROSPAM_PLUGIN_FILE' ) ) {
	define( 'WPFORMS_ZEROSPAM_PLUGIN_FILE', __FILE__ );
}

// Define the class and the function.
require_once dirname( __FILE__ ) . '/src/WPForms-zerospam.php';
wpforms_zerospam();