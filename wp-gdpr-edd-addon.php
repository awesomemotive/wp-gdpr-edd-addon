<?php
/**
 * WP GDPR Easy Digital Downloads addon
 *
 * Help to handle GDPR regulations in Easy Digital Downloads
 *
 * @package   WP GDPR Easy Digital Downloads Addon
 * @author    Easy Digital Downloads, LLC
 * @license   proprietary
 * @link      https://easydigitaldownloads.com
 * @copyright 2018 wp-gdpr
 *
 * @wordpress-plugin
 * Plugin Name:       WP GDPR Easy Digital Downloads addon
 * Description:       Help to handle GDPR regulations in Easy Digital Downloads.
 * Version:           1.0.0
 * Text Domain:       wp_gdpr
 * Domain Path:       /languages
 * Author:            Easy Digital Downloads, LLC
 * Author URI:        https://easydigitaldownloads.com
 */

namespace wp_gdpr_edd;

use wp_gdpr\lib\Gdpr_Container;

define( 'GDPR_EDD_DIR', plugin_dir_path( __FILE__ ) );
define( 'GDPR_EDD_URL', plugin_dir_url( __FILE__ ) );
define( 'GDPR_EDD_BASE_NAME', dirname( plugin_basename( __FILE__ ) ) );
require_once GDPR_EDD_DIR . 'lib/gdpr-autoloader.php';

class Wp_Gdpr_EDD {
	public function __construct() {
		$this->run();
	}

	public function run() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active( 'wp-gdpr-core/wp-gdpr-core.php' ) && class_exists( 'Easy_Digital_Downloads' ) ) {
			if ( ! $this->check_version() ) {
				add_action( 'admin_notices', array( $this, 'activate_core_update_message' ) );
				$this->deactivate_this_plugin();

				return;
			}
			Gdpr_Container::make( 'wp_gdpr_edd\controller\Controller_EDD' );
			Gdpr_Container::make( 'wp_gdpr_edd\controller\Controller_Menu_Page_EDD' );
		} elseif ( is_plugin_active( 'wp-gdpr-core/wp-gdpr-core.php' ) ) {
			add_action( 'admin_notices', array( $this, 'activate__plugin_message' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'activate_core_plugin_message' ) );
		}
	}

	public function check_version() {
		$version = get_plugin_data( GDPR_DIR . 'wp-gdpr-core.php' );
		$version = $version['Version'];

		return '1.3.2' <= $version;
	}

	public function deactivate_this_plugin() {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

	public function activate_edd_plugin_message() {
		$class   = 'notice notice-error';
		$message = __( 'You need to install and activate Easy Digital Downloads', 'wp_gdpr' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		$this->deactivate_this_plugin();
	}

	public function activate_core_update_message() {
		$class   = 'notice notice-error';
		$message = __( 'You need to update WP-GDPR-CORE plugin to last version', 'wp_gdpr' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		$this->deactivate_this_plugin();
	}

	public function activate_core_plugin_message() {
		$class   = 'notice notice-error';
		$message = __( 'You need to install and activate the free wp-gdpr plugin before activating the add-on', 'wp_gdpr' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		$this->deactivate_this_plugin();
	}
}

add_action( 'plugins_loaded', function () {
	new Wp_Gdpr_EDD();
} );
