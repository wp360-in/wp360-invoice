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

            // Require at least firm name or logo
            if (!empty($firm_name) || !empty($logo_url)) {
                $filtered_firm_details[] = array(
                    'id' => $id,
                    'type' => $type,
                    'firm_name' => wp_kses($firm_name, $allowed_html),
                    'logo_url' => $logo_url,
                    'tagline' => wp_kses($tagline, $allowed_html),
                    'text_logo' => wp_kses($text_logo, $allowed_html),
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
    print_r($saved_firm_details);
    // If no firm details are saved, initialize with one empty set
    if (empty($saved_firm_details)) {
        $saved_firm_details[] = array('id' => '', 'type' => 'name', 'firm_name' => '', 'logo_url' => '', 'tagline' => '', 'text_logo'=>'');
    }
    ?>
    <fieldset id="wp360-firm-details-fields" class="fullWidth">
        <h3><?php esc_html_e('Firm Details', 'wp360-invoice'); ?></h3>

        <?php wp_nonce_field('wp360-save-firm-details-nonce', '_wpnonce_save_firm_details'); ?>

        <div style="display:none;" class="dynamic-field-template">
            <hr>
            <div class="firm-details-row">
                <div>
                    <div class="removeWrapper"><?php esc_html_e('Firm Name', 'wp360-invoice'); ?>: <button type="button" class="remove-dynamic-field"><?php esc_html_e('Remove', 'wp360-invoice'); ?></button></div>
                    <input type="text" name="wp360option[wp360_firm_details][__index__][firm_name]" value="" class="regular-text">
                </div>
                <br>
                <div class="toggle_firm_logo">
                    <label class="form-label"><?php esc_html_e('Logo type', 'wp360-invoice'); ?></label>
                    <label><input type="radio" name="wp360option[wp360_firm_details][__index__][type]" value="name" class="toggle-input-type"> <?php esc_html_e('Text Logo', 'wp360-invoice'); ?></label>
                    <label><input type="radio" name="wp360option[wp360_firm_details][__index__][type]" value="logo" class="toggle-input-type"> <?php esc_html_e('Firm Logo', 'wp360-invoice'); ?></label>
                </div>
                
                
                <br>
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
                <br>
                <div>
                    <label class="form-label"><?php esc_html_e('Tagline', 'wp360-invoice'); ?> (<?php esc_html_e('Optional', 'wp360-invoice'); ?>):</label>
                    <input type="text" name="wp360option[wp360_firm_details][__index__][tagline]" value="" class="regular-text">
                </div>
                <input type="hidden" name="wp360option[wp360_firm_details][__index__][id]" value="">
            </div>
        </div>

        <!-- Existing firm details -->
        <?php foreach ($saved_firm_details as $index => $firm_detail) : ?>
            <?php if ($index !== 0) { 
                echo '<hr>';
            } ?>
            <div class="firm-details-row is_removable_field">
                <div>
                    <div class="removeWrapper">
                        <?php esc_html_e('Firm Name', 'wp360-invoice'); ?>:
                        <?php if ($index !== 0) { ?>                        
                            <button type="button" class="remove-dynamic-field">
                                <?php esc_html_e('Remove', 'wp360-invoice'); ?>
                            </button>
                        <?php } ?>
                    </div>
                    <input type="text" name="wp360option[wp360_firm_details][<?php echo $index; ?>][firm_name]" value="<?php echo esc_attr($firm_detail['firm_name']); ?>" class="regular-text">
                </div>
                <br>
                <div class="toggle_firm_logo">
                    <label class="form-label">
                        <?php esc_html_e('Logo type', 'wp360-invoice'); ?>                        
                    </label>
                    <label>
                        <input type="radio" name="wp360option[wp360_firm_details][<?php echo $index; ?>][type]" value="name" class="toggle-input-type" <?php checked($firm_detail['type'], 'name'); ?>> <?php esc_html_e('Text Logo', 'wp360-invoice'); ?>
                    </label>
                    <label>
                        <input type="radio" name="wp360option[wp360_firm_details][<?php echo $index; ?>][type]" value="logo" class="toggle-input-type" <?php checked($firm_detail['type'], 'logo'); ?>> <?php esc_html_e('Firm Logo', 'wp360-invoice'); ?>
                    </label>
                </div>               
                
                <br>
                <div class="firm-name-field" style="<?php echo $firm_detail['type'] === 'name' ? 'display: block;' : 'display: none;'; ?>">
                    <label class="form-label"><?php esc_html_e('Text Logo', 'wp360-invoice'); ?>:</label>
                    <input type="text" name="wp360option[wp360_firm_details][<?php echo $index; ?>][text_logo]" value="<?php echo esc_attr($firm_detail['text_logo']); ?>" class="regular-text">
                </div>
                <div class="firm-logo-field" style="<?php echo $firm_detail['type'] === 'logo' ? 'display: block;' : 'display: none;'; ?>">
                    <label class="form-label"><?php esc_html_e('Firm Logo', 'wp360-invoice'); ?>:</label>
                    <input type="hidden" name="wp360option[wp360_firm_details][<?php echo $index; ?>][logo_url]" value="<?php echo esc_url($firm_detail['logo_url']); ?>" class="logo-url-field">
                    <button type="button" class="button upload-logo-button"><?php esc_html_e('Upload Logo', 'wp360-invoice'); ?></button>
                    <img src="<?php echo esc_url($firm_detail['logo_url']); ?>" class="logo-preview" style="display: block; max-width: 150px; margin-top: 10px;">
                </div>
                <br>
                <div>
                    <label class="form-label"><?php esc_html_e('Tagline', 'wp360-invoice'); ?> (<?php esc_html_e('Optional', 'wp360-invoice'); ?>):</label>
                    <input type="text" name="wp360option[wp360_firm_details][<?php echo $index; ?>][tagline]" value="<?php echo esc_attr($firm_detail['tagline']); ?>" class="regular-text">
                </div>
                <input type="hidden" name="wp360option[wp360_firm_details][<?php echo $index; ?>][id]" value="<?php echo esc_attr($firm_detail['id']); ?>">
            </div>
        <?php endforeach; ?>

        <div><button type="button" class="button add-dynamic-field"><?php esc_html_e('Add Firm Detail', 'wp360-invoice'); ?></button></div>

    </fieldset>
<?php
}
?>
