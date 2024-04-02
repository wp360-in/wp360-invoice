<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if(isset($_POST['wp360_submit']) && isset($_POST['_wpnonce_save_company_address']) && wp_verify_nonce( sanitize_text_field($_POST['_wpnonce_save_company_address']), 'save-company-address-nonce')) {
    $allowed_html = array(
        'br' => array()
    );
    $company_address    = isset($_POST['wp360option']['wp360_company_address']) ? wp_kses($_POST['wp360option']['wp360_company_address'], $allowed_html) : "";
    $thankyoumsg        = isset($_POST['wp360option']['wp360_thankyoumsg']) ? sanitize_text_field($_POST['wp360option']['wp360_thankyoumsg']) : "";
    $invoicestartnumber = isset($_POST['wp360option']['wp360_invoicestartnumber']) ? sanitize_text_field($_POST['wp360option']['wp360_invoicestartnumber']) : "";
    update_option('wp360_company_address', $company_address);
    update_option('wp360_thankyoumsg', $thankyoumsg);
    update_option('wp360_invoicestartnumber', $invoicestartnumber);
    echo '<div class="updated"><p>' . esc_html__('Field saved successfully!', 'wp360-invoice') . '</p></div>';
}
$saved_company_address    = get_option('wp360_company_address', '');
$thankyoumsg              = get_option('wp360_thankyoumsg', '');
$wp360_invoicestartnumber = get_option('wp360_invoicestartnumber', '');
?>
<div class="wrap wp360-invoice-settings">
    <h1 class="wp-heading-inline"><?php esc_html_e('wp360 Option', 'wp360-invoice');?></h1>
    <div class="_CISettingIn">
        <form action="#" method="post" autocomplete="off">
            <?php wp_nonce_field('save-company-address-nonce', '_wpnonce_save_company_address'); ?>
            <table>
                <tr>
                    <th><?php esc_html_e('Company Address:', 'wp360-invoice'); ?> </th>
                    <td><textarea type="text" name="wp360option[wp360_company_address]" class="regular-text"  rows="5"><?php echo esc_html($saved_company_address); ?></textarea></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Thank you message:', 'wp360-invoice'); ?> </th>
                    <td><input type="text" name="wp360option[wp360_thankyoumsg]" value="<?php echo esc_html($thankyoumsg); ?>" class="regular-text"/></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Invoice StartNumber:', 'wp360-invoice'); ?> </th>
                    <td><input type="text" name="wp360option[wp360_invoicestartnumber]" value="<?php echo esc_html($wp360_invoicestartnumber); ?>" class="regular-text"/></td>
                </tr>
                <tr>
                    <th></th>
                    <td><input name="wp360_submit" class="button button-primary" type="submit" value="<?php esc_html_e('Save' , 'wp360-invoice') ?>" /></td>
                </tr>
            </table>
        </form>
    </div>
</div>