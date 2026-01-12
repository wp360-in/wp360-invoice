<?php
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
// add_action('admin_init', 'wp360_invoices_addresses_init');
function wp360invoice_addresses_init() {
    if (isset($_POST['wp360_submit']) && isset($_POST['_wpnonce_save_invoice_address']) 
    && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce_save_invoice_address'])), 'wp360-save-invoice-addresses-nonce')) {        
        // Initialize $saved_invoice_addresses as an empty array if $_POST['wp360option']['wp360_invoice_address'] is not set
        $saved_invoice_addresses = isset($_POST['wp360option']['wp360_invoice_address']) ? $_POST['wp360option']['wp360_invoice_address'] : array();

        // Allowed HTML tags for sanitization
        $allowed_html = array(
            'br' => array()
        );

        // Filter out empty or whitespace-only addresses and sanitize
        $filtered_addresses = array();
        foreach ($saved_invoice_addresses as $address) {
            $address = trim($address); // Remove leading and trailing whitespace
            if (!empty($address)) {
                $filtered_addresses[] = wp_kses($address, $allowed_html); // Sanitize non-empty address
            }
        }
        // Update the option with sanitized addresses
        update_option('wp360_invoice_addresses', $filtered_addresses);
    }
}
wp360invoice_addresses_init();
// Callback function to render the page
function wp360invoice_render_address_fields() {
    $saved_invoice_addresses = get_option('wp360_invoice_addresses', array());    
    if (empty($saved_invoice_addresses)) {        
        $saved_invoice_addresses[] = '';
    } ?>        
        <fieldset id="wp360-invoice-address-fields">
            <h3><?php esc_html_e('Addresses', 'wp360-invoice'); ?></h3>
            <div style="display:none;" class="dynamic-field-template">
                <div class="removeWrapper"><?php esc_html_e('Add Detail:', 'wp360-invoice'); ?>
                    <button type="button" class="remove-dynamic-field"><?php esc_html_e('Remove', 'wp360-invoice'); ?></button>
                </div>
                <textarea type="text" name="wp360option[wp360_invoice_address][]" class="regular-text" rows="5"></textarea>
            </div>
            <?php wp_nonce_field('wp360-save-invoice-addresses-nonce', '_wpnonce_save_invoice_address'); ?>
            <?php foreach ($saved_invoice_addresses as $index => $address) : ?>
                <div class="invoice-address-row is_removable_field">
                    <div class="removeWrapper"><?php esc_html_e('Add Detail:', 'wp360-invoice'); if ($index !== 0) { ?> <button type="button" class="remove-dynamic-field"><?php esc_html_e('Remove', 'wp360-invoice'); ?></button><?php } ?></div>
                    <textarea type="text" name="wp360option[wp360_invoice_address][]" class="regular-text" rows="5"><?php echo esc_html($address); ?></textarea>
                </div>
            <?php endforeach; ?>
            <div><button type="button" class="button add-dynamic-field"><?php esc_html_e('Add Address', 'wp360-invoice'); ?></button></div>
        </fieldset>
    <?php
}
?>
