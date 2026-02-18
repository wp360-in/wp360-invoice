<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
// Handle form submission
function wp360invoice_firm_details_init() {
    if (isset($_POST['wp360_submit']) && isset($_POST['_wpnonce_save_firm_details']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce_save_firm_details'])), 'wp360-save-firm-details-nonce')) {
        $saved_firm_details = isset($_POST['wp360option']['wp360_firm_details']) ? $_POST['wp360option']['wp360_firm_details'] : array();

        // Initialize sanitized firm details array
        $filtered_firm_details = array();
        // Allowed HTML tags for sanitization (limited, as needed)
        $allowed_html = array(
            'br' => array()
        );

        foreach ($saved_firm_details as $firm_detail) {
            $type = isset($firm_detail['type']) ? sanitize_text_field($firm_detail['type']) : 'name';
            $firm_name = isset($firm_detail['firm_name']) ? trim($firm_detail['firm_name']) : '';
            $logo_url = isset($firm_detail['logo_url']) ? esc_url($firm_detail['logo_url']) : '';
            $tagline = isset($firm_detail['tagline']) ? trim($firm_detail['tagline']) : '';
            $text_logo = isset($firm_detail['text_logo']) ? trim($firm_detail['text_logo']) : '';
            $id = isset($firm_detail['id']) && !empty($firm_detail['id']) ? sanitize_text_field($firm_detail['id']) : uniqid();

            // Sanitize addresses
            $addresses = array();
            if (isset($firm_detail['addresses']) && is_array($firm_detail['addresses'])) {
                foreach ($firm_detail['addresses'] as $address) {
                    $trimmed_address = trim($address);
                    if (!empty($trimmed_address)) {
                        $addresses[] = wp_kses($trimmed_address, $allowed_html);
                    }
                }
            }

            // Sanitize bank details
            $bank_details = array();
            if (isset($firm_detail['bank_details']) && is_array($firm_detail['bank_details'])) {
                foreach ($firm_detail['bank_details'] as $bank_detail) {
                    $trimmed_bank = trim($bank_detail);
                    if (!empty($trimmed_bank)) {
                        $bank_details[] = wp_kses($trimmed_bank, $allowed_html);
                    }
                }
            }

            // Require at least firm name or logo
            if (!empty($firm_name) || !empty($logo_url)) {
                $filtered_firm_details[] = array(
                    'id' => $id,
                    'type' => $type,
                    'firm_name' => wp_kses($firm_name, $allowed_html),
                    'logo_url' => $logo_url,
                    'tagline' => wp_kses($tagline, $allowed_html),
                    'text_logo' => wp_kses($text_logo, $allowed_html),
                    'addresses' => $addresses,
                    'bank_details' => $bank_details,
                );
            }
        }

        // Update the option with sanitized firm details
        update_option('wp360_firm_details', $filtered_firm_details);
    }
}
wp360invoice_firm_details_init();

// Callback function to render the firm details page
function wp360invoice_render_firm_details_page() {
    $saved_firm_details = get_option('wp360_firm_details', array());
    // print_r($saved_firm_details);
    // If no firm details are saved, initialize with one empty set
    if (empty($saved_firm_details)) {
        $saved_firm_details[] = array(
            'id' => '', 
            'type' => 'name', 
            'firm_name' => '', 
            'logo_url' => '', 
            'tagline' => '', 
            'text_logo'=>'',
            'addresses' => array(''),
            'bank_details' => array('')
        );
    }
    ?>
    <fieldset id="wp360-firm-details-fields" class="fullWidth">
        <h3><?php esc_html_e('Firm Details', 'wp360-invoice'); ?></h3>

        <?php wp_nonce_field('wp360-save-firm-details-nonce', '_wpnonce_save_firm_details'); ?>

        <div style="display:none;" class="dynamic-field-template">
            <div class="firm-details-row dflex">
                <div>
                    <div class="removeWrapper"><?php esc_html_e('Firm Name', 'wp360-invoice'); ?>: </div>
                    <input type="text" name="wp360option[wp360_firm_details][__index__][firm_name]" value="" class="regular-text">
                </div>
                <div class="toggle_firm_logo">
                    <label class="form-label"><?php esc_html_e('Logo type', 'wp360-invoice'); ?></label>
                    <label><input type="radio" name="wp360option[wp360_firm_details][__index__][type]" value="name" class="toggle-input-type" checked> <?php esc_html_e('Text Logo', 'wp360-invoice'); ?></label>
                    <label style="margin-top: 8px;display: block;"><input type="radio" name="wp360option[wp360_firm_details][__index__][type]" value="logo" class="toggle-input-type"><?php esc_html_e('Firm Logo', 'wp360-invoice'); ?></label>
                </div>
                <div>
                    <div class="firm-name-field" style="display: block;">
                        <label class="form-label"><?php esc_html_e('Text Logo', 'wp360-invoice'); ?>:</label>
                        <input type="text" name="wp360option[wp360_firm_details][__index__][text_logo]" value="" class="regular-text">
                    </div>
                    <div class="firm-logo-field" style="display: none;">
                        <label class="form-label"><?php esc_html_e('Firm Logo', 'wp360-invoice'); ?>:</label>
                        <input type="hidden" name="wp360option[wp360_firm_details][__index__][logo_url]" value="" class="logo-url-field">
                        <button type="button" class="button upload-logo-button"><?php esc_html_e('Upload Logo', 'wp360-invoice'); ?></button>
                        <img src="" class="logo-preview" style="display: none; max-width: 150px; margin-top: 10px;">
                    </div>
                </div>
                <div>
                    <label class="form-label"><?php esc_html_e('Tagline', 'wp360-invoice'); ?> (<?php esc_html_e('Optional', 'wp360-invoice'); ?>):</label>
                    <input type="text" name="wp360option[wp360_firm_details][__index__][tagline]" value="" class="regular-text">
                </div>
                <input type="hidden" name="wp360option[wp360_firm_details][__index__][id]" value="">

                <!-- Addresses Section -->
                <div class="add-addresss">
                    <label  class="form-label"><?php esc_html_e('Addresses', 'wp360-invoice'); ?></label>
                    <div class="addresses-container">
                        <div class="address-item">
                            <textarea name="wp360option[wp360_firm_details][__index__][addresses][]" class="regular-text" rows="5"></textarea>
                        </div>
                    </div>
                 
                </div>

                <!-- Bank Details Section -->
                <div class="add-bankdetails">
                    <label  class="form-label"><?php esc_html_e('Bank Details', 'wp360-invoice'); ?></label>
                    <div class="bank-details-container">
                        <div class="bank-detail-item">
                            <textarea name="wp360option[wp360_firm_details][__index__][bank_details][]" class="regular-text" rows="5"></textarea>
                        </div>
                    </div>
                      
                </div>

                <div class="removeFieldWrapper">
                    <button type="button" class="remove-dynamic-field"><?php esc_html_e('Remove', 'wp360-invoice'); ?></button>
                </div>
            </div>
        </div>

        <!-- Existing firm details -->
        <?php foreach ($saved_firm_details as $index => $firm_detail) : 
            // Ensure addresses and bank_details exist as arrays
            $addresses = isset($firm_detail['addresses']) && is_array($firm_detail['addresses']) ? $firm_detail['addresses'] : array();
            $bank_details = isset($firm_detail['bank_details']) && is_array($firm_detail['bank_details']) ? $firm_detail['bank_details'] : array();
            
            // Ensure at least one empty field
            if (empty($addresses)) $addresses = array('');
            if (empty($bank_details)) $bank_details = array('');
        ?>
            <div class="firm-details-row is_removable_field dflex" data-index="<?php echo $index; ?>">
                <div>
                    <label class="form-label">
                        <?php esc_html_e('Firm Name', 'wp360-invoice'); ?>:                        
                    </label>
                    <input type="text" name="wp360option[wp360_firm_details][<?php echo $index; ?>][firm_name]" value="<?php echo esc_attr($firm_detail['firm_name']); ?>" class="regular-text">
                </div>
                <div class="toggle_firm_logo">
                    <label class="form-label">
                        <?php esc_html_e('Logo type', 'wp360-invoice'); ?>                        
                    </label>
                    <label>
                        <input type="radio" name="wp360option[wp360_firm_details][<?php echo $index; ?>][type]" value="name" class="toggle-input-type" <?php checked($firm_detail['type'], 'name'); ?>> <?php esc_html_e('Text Logo', 'wp360-invoice'); ?>
                    </label>
                    <label style="margin-top: 8px;display: block;">
                        <input type="radio" name="wp360option[wp360_firm_details][<?php echo $index; ?>][type]" value="logo" class="toggle-input-type" <?php checked($firm_detail['type'], 'logo'); ?>> <?php esc_html_e('Firm Logo', 'wp360-invoice'); ?>
                    </label>                        
                </div>
                <div>
                    <div class="firm-name-field" style="<?php echo $firm_detail['type'] === 'name' ? 'display: block;' : 'display: none;'; ?>">
                        <label class="form-label"><?php esc_html_e('Text Logo', 'wp360-invoice'); ?>:</label>
                        <input type="text" name="wp360option[wp360_firm_details][<?php echo $index; ?>][text_logo]" value="<?php echo esc_attr($firm_detail['text_logo']); ?>" class="regular-text">
                    </div>
                    <div class="firm-logo-field" style="<?php echo $firm_detail['type'] === 'logo' ? 'display: block;' : 'display: none;'; ?>">
                        <label class="form-label"><?php esc_html_e('Firm Logo', 'wp360-invoice'); ?>:</label>
                        <input type="hidden" name="wp360option[wp360_firm_details][<?php echo $index; ?>][logo_url]" value="<?php echo esc_url($firm_detail['logo_url']); ?>" class="logo-url-field">
                        <button type="button" class="button upload-logo-button"><?php esc_html_e('Upload Logo', 'wp360-invoice'); ?></button>
                        <img src="<?php echo esc_url($firm_detail['logo_url']); ?>" class="logo-preview" style="<?php echo !empty($firm_detail['logo_url']) ? 'display: block;' : 'display: none;'; ?> max-width: 150px; margin-top: 10px;">
                    </div>
                </div>
                <div>
                    <label class="form-label"><?php esc_html_e('Tagline', 'wp360-invoice'); ?> (<?php esc_html_e('Optional', 'wp360-invoice'); ?>):</label>
                    <input type="text" name="wp360option[wp360_firm_details][<?php echo $index; ?>][tagline]" value="<?php echo esc_attr($firm_detail['tagline']); ?>" class="regular-text">
                </div>
                <input type="hidden" name="wp360option[wp360_firm_details][<?php echo $index; ?>][id]" value="<?php echo esc_attr($firm_detail['id']); ?>">
                
                <!-- Addresses Section -->
                <div class="add-addresss">
                    <label  class="form-label"><?php esc_html_e('Addresses', 'wp360-invoice'); ?></label>
                    <div class="addresses-container">
                        <?php foreach ($addresses as $addr_index => $address) : ?>
                            <div class="address-item">
                                <textarea name="wp360option[wp360_firm_details][<?php echo $index; ?>][addresses][]" class="regular-text" rows="5"><?php echo esc_textarea($address); ?></textarea>
                            </div>
                        <?php endforeach; ?>
                    </div>
                  
                </div>

                <!-- Bank Details Section -->
                <div class="add-bankdetails">
                    <label  class="form-label"><?php esc_html_e('Bank Details', 'wp360-invoice'); ?></label>
                    <div class="bank-details-container">
                        <?php foreach ($bank_details as $bank_index => $bank_detail) : ?>
                            <div class="bank-detail-item">
                                <textarea name="wp360option[wp360_firm_details][<?php echo $index; ?>][bank_details][]" class="regular-text" rows="5"><?php echo esc_textarea($bank_detail); ?></textarea>
                            </div>
                        <?php endforeach; ?>
                    </div>
                         
                </div>

                <?php if ($index !== 0) { ?>
                <div class="removeFieldWrapper">
                    <button type="button" class="remove-dynamic-field">
                        <?php esc_html_e('Remove', 'wp360-invoice'); ?>
                    </button>
                </div>
                <?php } ?>
            </div>            
        <?php endforeach; ?>

    <!-- OLD BCP TEMPORARY -->
        <?php

            $saved_invoice_banking = get_option('wp360_invoice_banking', array());
            echo '<pre>', print_r($saved_invoice_banking , true ); echo '</pre>';
            $saved_invoice_addresses = get_option('wp360_invoice_addresses', array());
            echo '<pre>', print_r($saved_invoice_addresses , true ); echo '</pre>';
        ?>
 <!-- OLD BCP -->

        <div class="add_firm_new">
            <button type="button" class="button add-dynamic-field"><?php esc_html_e('Add Firm Detail', 'wp360-invoice'); ?></button>
        </div>

    </fieldset>
<?php
}
?>