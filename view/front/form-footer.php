<?php
/**
 *  form to delete or download edd entries
 */
?>
<form method="post" id="<?php echo $table_name; ?>">
    <input type="hidden" name="gdpr_email" value="<?php echo $email; ?>">
    <input type="hidden" name="table" value="<?php echo $table_name; ?>">
	<?php if ( in_array( $table_name, array(
		'gdpr_show_user_shipping_details',
		'gdpr_show_user_billing_details',
	) ) ): ?>
        <input type="submit" class="button button-primary" name="wc_send_gdpr_del_request"
               value="<?php _e( 'Send delete request', 'wp_gdpr' ); ?>">
	<?php endif; ?>
    <input type="submit" class="button button-primary" name="wc_gdpr_download_csv"
           value="<?php _e( 'Download CSV', 'wp_gdpr' ); ?>">
</form>
