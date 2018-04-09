<?php

namespace wp_gdpr_edd\model;

use wp_gdpr\lib\Gdpr_Container;
use wp_gdpr\lib\Gdpr_Customtables;
use wp_gdpr\lib\Gdpr_Table_Builder;

class EDD_Model {

	const ENDPOINT_ACTION_NAME = 'edit_edd_entry';
	/**
	 * Email of user.
	 *
	 * @var $email_request
	 */
	public $email_request;

	/**
	 * Allow to edit entry via AJAX.
	 *
	 * @return string
	 */
	public function edit_entry() {

		$user_id = sanitize_text_field( $_REQUEST['lead_id'] );
		$key     = sanitize_text_field( $_REQUEST['input_name'] );
		$value   = sanitize_text_field( $_REQUEST['new_value'] );

		if ( 'billing_email' === $key && ! is_email( $value ) ) {
			$message = '<h3>' . __( 'Sorry. This is not email address.', 'wp_gdpr' ) . '</h3>';

			return $message;
		}

		update_user_meta( $user_id, $key, $value );

		$message = '<h3>' . __( 'Data is changed', 'wp_gdpr' ) . '</h3>';


		return $message;
	}

	public function send_json_message( $message ) {
		wp_send_json( $message );
	}

	public function add_scripts_and_localize() {
		wp_enqueue_script( 'gdpr-edd-addon', GDPR_EDD_URL . 'assets/js/edd-addon-edit-entries.js', array( 'jquery' ) );
		wp_localize_script( 'gdpr-edd-addon', 'localized_object_wc',
			array(
				'url'             => admin_url( 'admin-ajax.php' ),
				'action'          => 'wp_gdpr',
				'endpoint_action' => self::ENDPOINT_ACTION_NAME,
			) );
	}

	public function gdpr_echo_billing_title( $email_request ) {
		echo '<h2>' . esc_html( __( 'All orders with billing email', 'wp_gdpr' ) . ': ' . $email_request ) . '</h2>';
	}

	public function gdpr_echo_customer_billing_title( $email_request ) {
		echo '<h2>' . esc_html( __( 'All billing data related with EDD Customer email', 'wp_gdpr' ) . ': ' . $email_request ) . '</h2>';
	}

	public function gdpr_echo_customer_details_title( $email_request ) {
		echo '<h2>' . esc_html( __( 'EDD Customer Record Data', 'wp_gdpr' ) . ': ' . $email_request ) . '</h2>';
	}

	public function gdpr_echo_customer_shipping_title( $email_request ) {
		echo '<h2>' . esc_html( __( 'EDD Customer Simple Shipping Addresses', 'wp_gdpr' ) . ': ' . $email_request ) . '</h2>';
	}

	public function gdpr_echo_reviews_title( $email_request ) {
		echo '<h2>' . esc_html( __( 'EDD Reviews', 'wp_gdpr' ) . ': ' . $email_request ) . '</h2>';
	}

	public function gdpr_echo_message_after_del_request( $email, $request_name ) {
		if ( isset( $_REQUEST[ $request_name ] ) ) {
			echo '<h3>' . esc_html( __( 'The site administrator received your request. Thank You.', 'wp_gdpr' ) ) . '</h3>';
		} else {
			return;
		}
	}

	public function gdpr_show_billing_entries( $email_request ) {

		$entries = $this->get_all_billings_details_with_email( $email_request );
		//$entries     = $this->add_edit_inputs( $entries );
		//$entries     = $this->add_delete_input( $entries );
		if ( ! empty( $entries ) ) {
			$form_footer = $this->get_form_footer( __FUNCTION__ );
		} else {
			return;
		}

		$table = new Gdpr_Table_Builder(
			array(
				__( 'name', 'wp_gdpr' ),
				__( 'surname', 'wp_gdpr' ),
				__( 'street part 1', 'wp_gdpr' ),
				__( 'street part 2', 'wp_gdpr' ),
				__( 'city', 'wp_gdpr' ),
				__( 'state / province', 'wp_gdpr' ),
				__( 'zipcode', 'wp_gdpr' ),
				__( 'country', 'wp_gdpr' ),
				__( 'e-mail', 'wp_gdpr' ),
				__( 'customer id', 'wp_gdpr' ),
				__( 'order id', 'wp_gdpr' ),
				__( 'IP Address', 'wp_gdpr' ),
			),
			$entries,
			array( $form_footer ),
			'gdpr_orders_email_table'
		);

		$table->print_table();
	}

	/**
	 * @param $email_request
	 *
	 * @return array
	 */
	public function get_all_billings_details_with_email( $email_request ) {

		$entries = $this->find_order_with_billing_email_details( $email_request );

		$this->email_request = $email_request;

		$data = array();
		foreach ( $entries as $payment ) {
			array_push( $data, array(
				'billing_first_name' => ! empty( $payment->user_info['first_name'] ) ? $payment->user_info['first_name'] : '',
				'billing_last_name'  => ! empty( $payment->user_info['last_name'] ) ? $payment->user_info['last_name'] : '',
				'billing_address_1'  => ! empty( $payment->address['line1'] ) ? $payment->address['line1'] : '',
				'billing_address_2'  => ! empty( $payment->address['line2'] ) ? $payment->address['line2'] : '',
				'billing_city'       => ! empty( $payment->address['city'] ) ? $payment->address['city'] : '',
				'billing_state'      => ! empty( $payment->address['state'] ) ? $payment->address['state'] : '',
				'billing_postcode'   => ! empty( $payment->address['zip'] ) ? $payment->address['zip'] : '',
				'billing_country'    => ! empty( $payment->address['country'] ) ? $payment->address['country'] : '',
				'billing_email'      => ! empty( $payment->user_info['email'] ) ? $payment->user_info['email'] : '',
				'customer_id'        => $payment->customer_id,
				'order_id'           => $payment->ID,
				'ip_address'         => $payment->ip,
			) );
		}

		return $data;
	}

	public function find_order_with_billing_email_details( $email ) {
		$entries = get_posts( array(
			'post_type'      => 'edd_payment',
			'meta_query'     => array(
				array(
					'compare' => 'LIKE',
					'key'     => '_edd_payment_meta',
					'value'   => $email,
				)
			),
			'post_status'    => 'any',
			'posts_per_page' => - 1,
			'fields'         => 'ids',
		) );

		if ( empty( $entries ) ) {
			return array();
		}

		$payments = edd_get_payments( array(
			'post__in' => $entries,
			'number'   => -1,
			'output'   => 'payments'
		) );

		return $payments;
	}

	public function gdpr_show_customer_billing_details( $email_request ) {

		$entries = $this->get_all_billing_details_from_customer_with_email( $email_request );

		if ( ! empty( $entries ) ) {
			$this->email_request = $email_request;
			$form_footer = $this->get_form_footer( __FUNCTION__ );
		} else {
			return;
		}

		$table = new Gdpr_Table_Builder(
			array(
				__( 'name', 'wp_gdpr' ),
				__( 'surname', 'wp_gdpr' ),
				__( 'street part 1', 'wp_gdpr' ),
				__( 'street part 2', 'wp_gdpr' ),
				__( 'city', 'wp_gdpr' ),
				__( 'state / province', 'wp_gdpr' ),
				__( 'zipcode', 'wp_gdpr' ),
				__( 'country', 'wp_gdpr' ),
				__( 'e-mail', 'wp_gdpr' ),
				__( 'customer id', 'wp_gdpr' ),
				__( 'order id', 'wp_gdpr' ),
				__( 'IP Address', 'wp_gdpr' ),
			),
			$entries,
			array( $form_footer ),
			'gdpr_customer_billing_table'
		);

		$table->print_table();
	}

	public function get_all_billing_details_from_customer_with_email( $email_request ) {

		$entries = $this->find_customer_billing_details( $email_request );

		$this->email_request = $email_request;

		$data = array();
		foreach ( $entries as $payment ) {
			array_push( $data, array(
				'billing_first_name' => ! empty( $payment->user_info['first_name'] ) ? $payment->user_info['first_name'] : '',
				'billing_last_name'  => ! empty( $payment->user_info['last_name'] ) ? $payment->user_info['last_name'] : '',
				'billing_address_1'  => ! empty( $payment->address['line1'] ) ? $payment->address['line1'] : '',
				'billing_address_2'  => ! empty( $payment->address['line2'] ) ? $payment->address['line2'] : '',
				'billing_city'       => ! empty( $payment->address['city'] ) ? $payment->address['city'] : '',
				'billing_state'      => ! empty( $payment->address['state'] ) ? $payment->address['state'] : '',
				'billing_postcode'   => ! empty( $payment->address['zip'] ) ? $payment->address['zip'] : '',
				'billing_country'    => ! empty( $payment->address['country'] ) ? $payment->address['country'] : '',
				'billing_email'      => ! empty( $payment->user_info['email'] ) ? $payment->user_info['email'] : '',
				'customer'           => $payment->customer_id,
				'payment_id'         => $payment->ID,
				'ip_address'         => $payment->ip,
			) );
		}

		return $data;
	}

	public function find_customer_billing_details( $email ) {
		$customer = new \EDD_Customer( $email );
		$payments = edd_get_payments( array(
			'customer' => $customer->id,
			'output' => 'payments',
			'number' => -1,
		) );

		return $payments;
	}

	public function gdpr_show_customer_details_details( $email_request ) {
		$customer = new \EDD_Customer( $email_request );

		if ( ! empty( $customer->id ) ) {
			$this->email_request = $email_request;
			$form_footer = $this->get_form_footer( __FUNCTION__ );
		} else {
			return;
		}

		$customer_data = array(
			array(
				'id'            => $customer->id,
				'primary_email' => $customer->email,
				'name'          => $customer->name,
				'date_created'  => $customer->date_created,
				'all_emails'    => implode( ', ', $customer->emails ),
			)
		);

		$table = new Gdpr_Table_Builder(
			array(
				__( 'id', 'wp_gdpr' ),
				__( 'primary email', 'wp_gdpr' ),
				__( 'name', 'wp_gdpr' ),
				__( 'date created', 'wp_gdpr' ),
				__( 'all emails', 'wp_gdpr' ),
			),
			$customer_data,
			array( $form_footer ),
			'gdpr_customer_data_table'
		);

		$table->print_table();
	}

	public function gdpr_show_customer_shipping_details( $email_request ) {
		$customer = new \EDD_Customer( $email_request );

		if ( ! empty( $customer->id ) ) {
			$this->email_request = $email_request;
			$form_footer = $this->get_form_footer( __FUNCTION__ );
		} else {
			return;
		}

		$entries = $this->get_customer_shipping_addresses( $email_request );

		$table = new Gdpr_Table_Builder(
			array(
				__( 'Address 1', 'wp_gdpr' ),
				__( 'Address 2', 'wp_gdpr' ),
				__( 'City', 'wp_gdpr' ),
				__( 'State', 'wp_gdpr' ),
				__( 'Zip', 'wp_gdpr' ),
				__( 'Country', 'wp_gdpr' ),
			),
			$entries,
			array( $form_footer ),
			'gdpr_customer_data_table'
		);

		$table->print_table();
	}

	function get_customer_shipping_addresses( $email ) {
		$customer = new \EDD_Customer( $email );
		$entries = array();

		$shipping_addresses = $customer->get_meta( 'shipping_address', false );
		if ( ! empty( $shipping_addresses ) ) {
			$entries = $shipping_addresses;
		}

		return $entries;
	}

	public function gdpr_show_reviews_by_email( $email_request ) {
		$reviews = $this->get_reviews_by_email( $email_request );

		if ( ! empty( $reviews ) ) {
			$this->email_request = $email_request;
			$form_footer = $this->get_form_footer( __FUNCTION__ );
		} else {
			return;
		}

		$table = new Gdpr_Table_Builder(
			array(
				__( 'Review Date', 'wp_gdpr' ),
				__( 'Review Email', 'wp_gdpr' ),
				__( 'Review Name', 'wp_gdpr' ),
				__( 'Review Title', 'wp_gdpr' ),
				__( 'Review Content', 'wp_gdpr' ),
				__( 'Review Rating', 'wp_gdpr' ),
				__( 'Product ID', 'wp_gdpr' ),
				__( 'IP Address', 'wp_gdpr' ),
				__( 'Delete', 'wp_gdpr' ),
			),
			$reviews,
			array( $form_footer ),
			'gdpr_edd_review_data_table'
		);

		$table->print_table();
	}

	function get_reviews_by_email( $email ) {
		global $wpdb;
		$reviews = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT comment_ID, comment_date, comment_author_email, comment_author, comment_content, comment_post_ID, comment_author_IP
				 FROM $wpdb->comments
				 WHERE comment_author_email = '%s'",
				$email
			)
		);

		$entries = array();
		if ( ! empty( $reviews ) ) {
			foreach ( $reviews as $review ) {
				array_push( $entries, array(
					'review_date'    => $review->comment_date,
					'review_email'   => $review->comment_author_email,
					'review_name'    => $review->comment_author,
					'review_title'   => get_comment_meta( $review->comment_ID, 'edd_review_title', true ),
					'review_content' => $review->comment_content,
					'review_rating'  => get_comment_meta( $review->comment_ID, 'edd_rating', true ),
					'review_product' => $review->comment_post_ID,
					'review_ip'      => $review->comment_author_IP,
				) );
			}
		}

		return $entries;
	}


	public function add_edit_inputs( $entries ) {
		$input  = $this->get_edit_input();
		$result = array();
		foreach ( $entries as $entry ) {
			$userdata = array(
				'input'    => $input,
				'order_id' => $entry['order_id'],
			);
			array_walk( $entry, function ( &$data, $key, $userdata ) {
				if ( ! empty( $data ) && 'order_id' !== $key ) {
					$data = sprintf( $userdata['input'], $key, $userdata['order_id'], $data );
				}
			}, $userdata );
			$result[] = $entry;
		}

		return $result;
	}

	public function get_edit_input() {
		return '<input class="js-edd-entry-edit" data-name="%s" data-lead="%s" type="text"  value="%s">';

	}

	public function add_delete_input( $entries, $table_name ) {
		$input = $this->get_checkbox( $table_name );

		$entries = array_map( function ( $data ) use ( $input ) {
			$input = sprintf( $input, $data['order_id'] );
			array_push( $data, $input );

			return $data;
		}, $entries );

		return $entries;
	}

	public function get_checkbox( $table_name ) {
		switch ( $table_name ) {
			case 'gdpr_show_billing_entries':
				break;
			case 'gdpr_show_user_billing_details':
				return '<input type="checkbox" form="' . $table_name . '" name="wc_billing_request[]" value="%s">';
			case 'gdpr_show_shipping_entries':
				break;
			case 'gdpr_show_user_shipping_details':
				return '<input type="checkbox" form="' . $table_name . '" name="wc_shipping_request[]" value="%s">';
			default:
				return '<input type="checkbox" form="' . $table_name . '" name="wc_request[]" value="%s">';

		}
	}

	public function get_form_footer( $function ) {
		ob_start();
		$email      = $this->email_request;
		$table_name = $function;

		include GDPR_EDD_DIR . 'view/front/form-footer.php';

		return ob_get_clean();
	}

	public function download_csv( $table_name ) {
		//save in database
		$user_email = sanitize_email( $_REQUEST['gdpr_email'] );

		$table_name = sanitize_text_field( $table_name );

		//DOWNLOAD all
		if ( ! empty( $user_email ) ) {
			switch ( $table_name ) {
				case 'gdpr_show_billing_entries':
					$all_entries = $this->get_all_billings_details_with_email( $user_email );
					$file_name   = 'billing-details-from-orders';
					break;
				case 'gdpr_show_customer_billing_details':
					$all_entries = $this->get_all_billing_details_from_customer_with_email( $user_email );
					$file_name   = 'billing-details-from-customer-with-email';
					break;
				case 'gdpr_show_customer_shipping_details':
					$all_entries = $this->get_customer_shipping_addresses( $user_email );
					$file_name   = 'shipping-addresses-for-customer';
					break;
				case 'gdpr_show_reviews_by_email':
					$all_entries = $this->get_reviews_by_email( $user_email );
					$file_name   = 'product-reviews-by-email';
					break;
				default:
					return;
			}
		}

		if ( ! empty( $all_entries ) ) {
			$headers = array_keys( $all_entries[0] );

			//create csv object and download comments
			try {
				$csv = Gdpr_Container::make( 'wp_gdpr\model\Csv_Downloader' );
			} catch ( \Exception $e ) {
			}

			$csv->add_headers(
				$headers
			);

			$csv->set_filename( $file_name );
			$csv->set_data( $all_entries );
			$csv->download_csv();
		}
	}

	/**
	 * @param $unserialized_data
	 * @param $request_data
	 */
	public function change_entries_in_anonymous( $unserialized_data, $request_data ) {
		$this->execute_del_request( $unserialized_data, $request_data );
	}

	public function execute_del_request( $entry_id, $request ) {
		global $wpdb;

		$table = $wpdb->usermeta;

		if ( in_array( 'billing', $entry_id ) ) {
			$entry_id = array_diff( $entry_id, array( 'billing' ) );
			$query    = "DELETE FROM $table WHERE user_id=%d AND meta_key 
		IN ( 'billing_first_name','billing_last_name','billing_company',
			'billing_address_1','billing_address_2','billing_city',
			'billing_postcode','billing_country','billing_email','billing_phone'	)";
		} elseif ( in_array( 'shipping', $entry_id ) ) {
			$entry_id = array_diff( $entry_id, array( 'shipping' ) );
			$query    = "DELETE FROM $table WHERE user_id=%d AND meta_key 
		IN ( 'shipping_first_name','shipping_last_name','shipping_company',
			'shipping_address_1','shipping_address_2','shipping_city',
			'shipping_postcode','shipping_country','shipping_phone'	)";
		}

		$query = $wpdb->prepare( $query, $entry_id );


		$wpdb->query( $query );
	}

	public function get_entry( $entry_id ) {
		global $wpdb;

		$entry_id = (int) $entry_id;
		$table    = $wpdb->prefix . 'db7_forms';
		$query    = "SELECT * FROM $table WHERE form_id=$entry_id";

		return $wpdb->get_results( $query, ARRAY_A );
	}

	public function get_all_fields_ids_by_form_id( $form_id ) {

		return get_option( 'gdpr_inp_cf_' . $form_id, array() );
	}

	/**
	 * update status
	 */
	public function update_status( $request_id, $status ) {
		global $wpdb;
		$table_name = $wpdb->prefix . Gdpr_Customtables::DELETE_REQUESTS_TABLE_NAME;
		$where      = array( 'ID' => $request_id );
		$data       = array( 'status' => $status );
		$wpdb->update( $table_name, $data, $where );
	}

	/**
	 * @param $unserialized_data
	 * @param $request_data
	 *
	 * @return string
	 */
	public function get_anonymous_message( $unserialized_data, $request_data ) {
		return __( 'Woocommerce entries are anonymous', 'wp_gdpr' );
	}

	/**
	 * @param $unseriallized_data
	 * @param $request_data
	 *
	 * @return string
	 */
	public function get_delete_message( $unseriallized_data, $request_data ) {
		return __( 'Woocommerce details deleted.', 'wp_gdpr' );
	}

	/**
	 * @param $usnerialized_data
	 * @param $request_data
	 *
	 * @return array
	 */
	public function map_data_for_email( $usnerialized_data, $request_data ) {
		$info = __( 'We deleted data with lead id: ', 'wp_gdpr' );

		return array_map( function ( $data ) use ( $info ) {
			return $info . $data;
		}, $usnerialized_data );
	}

	public function save_del_request( $r_type, $email ) {
		global $wpdb;

		if ( isset( $_REQUEST['wc_billing_request'] ) ) {
			$data_request = $_REQUEST['wc_billing_request'];
			$tmp_array    = array( 'billing' );
		} elseif ( isset( $_REQUEST['wc_shipping_request'] ) ) {
			$data_request = $_REQUEST['wc_shipping_request'];
			$tmp_array    = array( 'shipping' );
		} else {
			return;
		}

		$lead_ids = array_filter( $data_request, array(
			$this,
			'sanitize_comments_input',
		) );

		$lead_ids = array_merge( $lead_ids, $tmp_array );

		$table_name = $wpdb->prefix . Gdpr_Customtables::DELETE_REQUESTS_TABLE_NAME;
		$wpdb->insert(
			$table_name,
			array(
				'email'     => $email,
				'data'      => serialize( $lead_ids ),
				'status'    => 0,
				'r_type'    => $r_type,
				'timestamp' => current_time( 'mysql' ),
			) );

	}

	/**
	 * @param $requested_email
	 */
	public function send_email_to_admin( $requested_email ) {
		$subject     = __( 'New delete request', 'wp_gdpr' );
		$admin_email = get_option( 'admin_email', true );
		$content     = $this->get_email_content( $requested_email );
		$headers     = array( 'Content-Type: text/html; charset=UTF-8' );
		wp_mail( $admin_email, $subject, $content, $headers );
	}

	/**
	 * @param $requested_email
	 *
	 * @return string
	 */
	public function get_email_content( $requested_email ) {
		ob_start();

		include GDPR_DIR . 'view/admin/email-delete-request.php';

		return ob_get_clean();
	}

	/**
	 * @param $comment
	 *
	 * @return bool
	 *
	 * check if input value is numeric
	 */
	public function sanitize_comments_input( $comment ) {
		return is_numeric( $comment );
	}


	public function save_input_type() {
		if ( isset( $_REQUEST['addon_action'] ) && 'save_input_type' == $_REQUEST['addon_action'] ) {
			//save
			$form_id = $_REQUEST['post_id'];

			//get saved options
			$saved_options = get_option( 'gdpr_inp_cf_' . $form_id, array() );

			$input_type = $_REQUEST['dropdown_value'];
			$input_name = $_REQUEST['input_name'];

			$saved_options[ $input_type ] = $input_name;

			update_option( 'gdpr_inp_cf_' . $form_id, $saved_options );
		}
	}

	public function get_dropdown_html() {
		ob_start();
		include GDPR_EDD_DIR . 'view/admin/cf-dropdown.php';

		return ob_get_clean();
	}
}
