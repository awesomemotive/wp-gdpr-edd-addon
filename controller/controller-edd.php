<?php

namespace wp_gdpr_edd\controller;

use wp_gdpr\lib\Gdpr_Container;
use wp_gdpr_edd\lib\Gdpr_EDD_Translation;
use wp_gdpr_edd\model\EDD_Model;

class Controller_EDD {
	const REQUEST_TYPE = 3;

	/**
	 * Controller_Form_Submit constructor.
	 */
	public function __construct() {
		$this->include_translation();
		//build table
		add_action( 'gdpr_show_entries', array( $this, 'gdpr_edd_show_customer_details' ), 10 );
		add_action( 'gdpr_show_entries', array( $this, 'gdpr_edd_simple_shipping_addresses' ), 12 );
		add_action( 'gdpr_show_entries', array( $this, 'gdpr_edd_show_billing_details' ), 12 );
		add_action( 'gdpr_show_entries', array( $this, 'gdpr_edd_show_customer_billing_details' ), 12 );
		add_action( 'gdpr_show_entries', array( $this, 'gdpr_edd_show_email_reviews' ), 12 );

		//save delete request
		add_action( 'gdpr_save_del_req', array( $this, 'save_del_request' ) );
		//execute deletion request
		add_action( 'gdpr_execute_del_req_' . self::REQUEST_TYPE, array( $this, 'execute_del_request' ), 11, 2 );
		//execute making entries anonymous
		add_action( 'gdpr_execute_anonymous_req_' . self::REQUEST_TYPE, array(
			$this,
			'execute_anonymous_request',
		), 10, 2 );
		//pad data for email
		add_filter( 'gdpr_map_data_for_email_' . self::REQUEST_TYPE, array( $this, 'map_data_for_email' ), 11, 2 );
		//get message for email when entries are deleted
		add_filter( 'gdpr_get_del_message_' . self::REQUEST_TYPE, array(
			$this,
			'gdpr_get_message_after_delete',
		), 10, 2 );
		//get message for email when entries are made anonymous
		add_filter( 'gdpr_get_anonymous_message_' . self::REQUEST_TYPE, array(
			$this,
			'gdpr_get_message_after_anonymous',
		), 10, 2 );
		//download csv action
		add_action( 'download_csv', array( $this, 'download_csv' ) );
		//add scripts on page where are tables with comments and entries from gf
		add_action( 'gdpr_addons_req_scripts', array( $this, 'register_scripts_in_gdpr_page' ) );
		//edit entry this action is extension for action added in comments controller in ajax enpoint
		add_action( EDD_Model::ENDPOINT_ACTION_NAME, array( $this, 'edit_entry' ) );
		add_action( 'save_input_type', array( $this, 'save_input_type' ) );
	}

	/**
	 * include translation
	 */
	public function include_translation() {
		new Gdpr_EDD_Translation();
	}

	/**
	 * @throws \Exception
	 */
	public function save_input_type() {
		$model = gdpr_container::make( 'wp_gdpr_edd\model\EDD_model' );
		$model->save_input_type();
		$message = 'success';
		$model->send_json_message( $message );
	}

	/**
	 * @throws \Exception
	 * Allows to update user_metadata via ajax.
	 */
	public function edit_entry() {
		$model   = gdpr_container::make( 'wp_gdpr_edd\model\EDD_model' );
		$message = $model->edit_entry();
		$model->send_json_message( $message );
	}

	/**
	 * @throws \Exception
	 *
	 * Register scripts where is table with entries to allow via js.
	 */
	public function register_scripts_in_gdpr_page() {
		$model = gdpr_container::make( 'wp_gdpr_edd\model\EDD_model' );
		$model->add_scripts_and_localize();
	}

	/**
	 * @param $unserialized_data
	 * @param $request_data
	 *
	 * @return string
	 * @throws \Exception
	 *
	 * When data is anonymized than user see this info.
	 */
	public function gdpr_get_message_after_anonymous( $unserialized_data, $request_data ) {
		$model = gdpr_container::make( 'wp_gdpr_edd\model\EDD_model' );

		return $model->get_anonymous_message( $unserialized_data, $request_data );
	}

	/**
	 * @throws \Exception
	 * Allows to download csv with rows from selected table.
	 */
	public function download_csv() {
		if ( isset( $_REQUEST['wc_gdpr_download_csv'] ) ) {
			$model = gdpr_container::make( 'wp_gdpr_edd\model\EDD_model' );
			$model->download_csv($_REQUEST['table']);
		}
	}

	/**
	 * @param $unserialized_data
	 * @param $request_data
	 *
	 * @throws \Exception
	 *
	 * Trigger anonymous request.
	 */
	public function execute_anonymous_request( $unserialized_data, $request_data ) {
		//get entry by id and find email name and surname , make anonymous
		$model = Gdpr_Container::make( 'wp_gdpr_edd\model\EDD_Model' );
		$model->change_entries_in_anonymous( $unserialized_data, $request_data );
		$model->update_status( $request_data['ID'], 1 );
	}

	/**
	 * @param $unseriallized_data
	 * @param $request_data
	 *
	 * @return string
	 * @throws \Exception
	 *
	 * Get message after delete request.
	 *
	 */
	public function gdpr_get_message_after_delete( $unseriallized_data, $request_data ) {
		$model = gdpr_container::make( 'wp_gdpr_edd\model\EDD_model' );

		return $model->get_delete_message( $unseriallized_data, $request_data );
	}

	/**
	 * @param $usnerialized_data
	 * @param $request_data
	 *
	 * @return array
	 * @throws \Exception
	 *
	 * Map informations to show in email.
	 *
	 */
	public function map_data_for_email( $usnerialized_data, $request_data ) {
		$model = gdpr_container::make( 'wp_gdpr_edd\model\EDD_model' );

		return $model->map_data_for_email( $usnerialized_data, $request_data );
	}

	/**
	 * @param $unserialized_data
	 * @param $request_data
	 *
	 * @throws \Exception
	 *
	 * Execute deletion request.
	 *
	 */
	public function execute_del_request( $unserialized_data, $request_data ) {
		$model = Gdpr_Container::make( 'wp_gdpr_edd\model\EDD_Model' );
		$model->execute_del_request( $unserialized_data, $request_data );
		$model->update_status( $request_data['ID'], 1 );
	}

	public function gdpr_edd_show_customer_details( $email_request ) {
		$model = Gdpr_Container::make( 'wp_gdpr_edd\model\EDD_Model' );
		$model->gdpr_echo_customer_details_title( $email_request );
		$model->gdpr_show_customer_details_details( $email_request );
	}

	public function gdpr_edd_simple_shipping_addresses( $email_request ) {
		$model = Gdpr_Container::make( 'wp_gdpr_edd\model\EDD_Model' );
		$model->gdpr_echo_customer_shipping_title( $email_request );
		$model->gdpr_show_customer_shipping_details( $email_request );
	}

	/**
	 * @param $email_request
	 *
	 * @throws \Exception
	 *
	 * Show all details about billings related with request email.
	 *
	 */
	public function gdpr_edd_show_billing_details( $email_request ) {
		$model = Gdpr_Container::make( 'wp_gdpr_edd\model\EDD_Model' );
		$model->gdpr_echo_billing_title( $email_request );
		$model->gdpr_show_billing_entries( $email_request );
	}

	/**
	 * @param $email_request
	 *
	 * @throws \Exception
	 *
	 * Show all details about billing related with request email and user metadata.
	 *
	 */
	public function gdpr_edd_show_customer_billing_details( $email_request ) {
		$model = Gdpr_Container::make( 'wp_gdpr_edd\model\EDD_Model' );
		$model->gdpr_echo_customer_billing_title( $email_request );
		$model->gdpr_show_customer_billing_details( $email_request );
	}

	/**
	 * @param $email_request
	 *
	 * @throws \Exception
	 *
	 * Show all details about shipping related with request email and user metadata.
	 *
	 */
	public function gdpr_edd_show_email_reviews( $email_request ) {
		$model = Gdpr_Container::make( 'wp_gdpr_edd\model\EDD_Model' );
		$model->gdpr_echo_reviews_title( $email_request );
		$model->gdpr_show_reviews_by_email( $email_request );
	}

	/**
	 * @throws \Exception
	 */
	public function save_del_request() {
		if ( isset( $_REQUEST['gdpr_email'] ) && isset( $_REQUEST['wc_send_gdpr_del_request'] ) ) {
			$email = sanitize_email( $_REQUEST['gdpr_email'] );
			$model = Gdpr_Container::make( 'wp_gdpr_edd\model\EDD_Model' );
			$model->save_del_request( self::REQUEST_TYPE, $email );
			$this->message = '<h3>' . __( 'The site administrator received your request. Thank You.', 'wp_gdpr' ) . '</h3>';
			$model->send_email_to_admin( $email );
		}
	}
}
