<?php namespace wp_gdpr_edd\view\admin;

/**
 * dropdown to help handle inputs
 */
?>
<tr>
    <th scope="row"><label><?php echo __( 'GDPR personal data type', 'wp-gdpr' ); ?></label></th>
    <td><select id="gdpr_dropdown">\n
            <option selected value="empty">----</option>
            \n
            <option value="street address"><?php echo __( 'Address Street Part 1', 'wp-gdpr' ); ?></option>
            \n
            <option value="address line 2"><?php echo __( 'Address Street Part 2', 'wp-gdpr' ); ?></option>
            \n
            <option value="city"><?php echo __( 'City', 'wp-gdpr' ); ?></option>
            \n
            <option value="country"><?php echo __( 'Country', 'wp-gdpr' ); ?></option>
            \n
            <option value="state / province"><?php echo __( 'State/Province', 'wp-gdpr' ); ?></option>
            \n
            <option value="first"><?php echo __( 'Name', 'wp-gdpr' ); ?></option>
            \n
            <option value="email"><?php echo __( 'E-mail', 'wp-gdpr' ); ?></option>
            \n
            <option value="last"><?php echo __( 'Surname', 'wp-gdpr' ); ?></option>
            \n
            <option value="website"><?php echo __( 'Website', 'wp-gdpr' ); ?></option>
            \n
            <option value="zip / postal code"><?php echo __( 'Zipcode', 'wp-gdpr' ); ?></option>
            \n
            <option value="phone"><?php echo __( 'Phone', 'wp-gdpr' ); ?></option>
            \n
            <option value="other"><?php echo __( 'Other', 'wp-gdpr' ); ?></option>
            \n
        </select></td>
</tr>

