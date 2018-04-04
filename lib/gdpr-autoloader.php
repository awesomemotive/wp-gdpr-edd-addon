<?php

namespace wp_gdpr_edd\lib;


final class Gdpr_Autoloader {

	const ADDON_NAMESPACE = 'wp_gdpr_edd';

	/**
	 * Gdpr_Autoloader constructor.
	 */
	public function __construct() {
		add_filter( 'autoloader_' . self::ADDON_NAMESPACE, array( $this, 'autoloader_callback' ) );
	}

	public function autoloader_callback( $class ) {
		$path = substr( $class, strlen( self::ADDON_NAMESPACE . '\\' ) );
		$path = strtolower( $path );
		$path = str_replace( '_', '-', $path );
		$path = str_replace( '\\', DIRECTORY_SEPARATOR, $path ) . '.php';

		$path = GDPR_EDD_DIR . DIRECTORY_SEPARATOR . $path;

		return $path;
	}
}

new Gdpr_Autoloader();
