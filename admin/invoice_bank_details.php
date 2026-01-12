<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Handle form submission
// add_action('admin_init', 'wp360_invoice_banking_details_init');
function wp360invoice_banking_details_init() {
    if (isset($_POST['wp360_submit']) && isset($_POST['_wpnonce_save_invoice_banking']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce_save_invoice_banking'])), 'wp360-save-invoice-banking-nonce')) {        
        // Initialize $saved_invoice_banking as an empty array if $_POST['wp360option']['wp360_invoice_banking'] is not set
        $saved_invoice_banking = isset($_POST['wp360option']['wp360_invoice_banking']) ? $_POST['wp360option']['wp360_invoice_banking'] : array();

        // Allowed HTML tags for sanitization
        $allowed_html = array(
            'br' => array()
        );

        // Filter out empty or whitespace-only banking details and sanitize
        $filtered_banking = array();
        foreach ($saved_invoice_banking as $bank_detail) {
            $bank_detail = trim($bank_detail); // Remove leading and trailing whitespace
            if (!empty($bank_detail)) {
                $filtered_banking[] = wp_kses($bank_detail, $allowed_html); // Sanitize non-empty banking detail
            }
        }
        // Update the option with sanitized banking details
        update_option('wp360_invoice_banking', $filtered_banking);
    }
}
wp360invoice_banking_details_init();
// Callback function to render the banking details page
function wp360invoice_render_banking_page() {
    $saved_invoice_banking = get_option('wp360_invoice_banking', array());
    // If no banking details are saved, initialize with one empty textarea
    if (empty($saved_invoice_banking)) {
        $saved_invoice_banking[] = '';
    }
    ?>
    <fieldset id="wp360-invoice-banking-table">
        <h3><?php esc_html_e('Banking Details', 'wp360-invoice'); ?></h3>
        <div style="display:none;" class="dynamic-field-template">
            <div class="removeWrapper"><?php esc_html_e('Add Detail:', 'wp360-invoice'); ?> 
                <button type="button" class="remove-dynamic-field"><?php esc_html_e('Remove', 'wp360-invoice'); ?></button>
            </div>
            <textarea type="text" name="wp360option[wp360_invoice_banking][]" class="regular-text" rows="5"></textarea>            
        </div>
        <?php wp_nonce_field('wp360-save-invoice-banking-nonce', '_wpnonce_save_invoice_banking'); ?>
        <?php foreach ($saved_invoice_banking as $index => $bank_detail) : ?>
            <div class="invoice-banking-row is_removable_field">
                <div class="removeWrapper"><?php esc_html_e('Add Detail:', 'wp360-invoice'); if ($index !== 0) { ?> <button type="button" class="remove-dynamic-field"><?php esc_html_e('Remove', 'wp360-invoice'); ?></button><?php } ?></div>
                <textarea type="text" name="wp360option[wp360_invoice_banking][]" class="regular-text" rows="5"><?php echo esc_html($bank_detail); ?></textarea>
            </div>
        <?php endforeach; ?>
        <div><button type="button" class="button add-dynamic-field"><?php esc_html_e('Add Banking Detail', 'wp360-invoice'); ?></button></div>
    </fieldset>
    <?php
}
?>
