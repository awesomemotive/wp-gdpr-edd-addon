<?php

namespace wp_gdpr_edd\lib;

Class Gdpr_EDD_Translation{

    public function __construct()
    {
        add_action( 'plugins_loaded', array($this, 'edd_addon_load_textdomain') );
    }

    public function edd_addon_load_textdomain(){
        load_plugin_textdomain( 'wp_gdpr', false,  GDPR_EDD_BASE_NAME . '/languages/'  );
    }
}