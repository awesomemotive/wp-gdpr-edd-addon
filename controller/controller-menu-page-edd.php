<?php
namespace wp_gdpr_edd\controller;

class Controller_Menu_Page_EDD {
	/**
	 * Controller_Menu_Page constructor.
	 */
	public function __construct() {
		add_action('add_on_settings_menu_page', array( $this, 'build_form_to_enter_license'), 11);
	}

    /**
     * build form to include license
     */
    public function build_form_to_enter_license() {
        require_once GDPR_EDD_DIR . 'view/admin/menu-page.php';
    }
}
