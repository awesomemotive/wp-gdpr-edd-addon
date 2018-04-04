<?php namespace wp_gdpr_edd\view\admin;

use wp_gdpr_edd\lib\Gdpr_Form_License;

/**
 * this template will show in wp-gdpr admin-menu
 */
?>
<div class="wrap">
    <h2><?php echo 'CFDB7 ' . __('License', 'wp_gdpr'); ?></h2>
    <?php
    $controller = new Gdpr_Form_License();
    $controller->print_form();
    ?>

</div>

