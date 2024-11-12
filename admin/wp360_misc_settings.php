<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }   
    function wp360invoice_misc_settings_save(){
        if(isset($_POST['wp360_submit']) && isset($_POST['_wpnonce_save_misc_settings']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['_wpnonce_save_misc_settings'])), 'wp360-save-invoice-misc-settings-nonce')) {    
            $thankyoumsg = isset($_POST['wp360option']['wp360_thankyoumsg']) ? sanitize_text_field($_POST['wp360option']['wp360_thankyoumsg']) : "";
            $invoicestartnumber = isset($_POST['wp360option']['wp360_invoicestartnumber']) ? sanitize_text_field($_POST['wp360option']['wp360_invoicestartnumber']) : "";
            update_option('wp360_thankyoumsg', $thankyoumsg);
            update_option('wp360_invoicestartnumber', $invoicestartnumber);
        }
    }
    wp360invoice_misc_settings_save();
    
    function wp360invoice_render_misc_settings(){ 
        $thankyoumsg = get_option('wp360_thankyoumsg', '');
        $wp360_invoicestartnumber = get_option('wp360_invoicestartnumber', ''); ?>
        <fieldset id="invoice-invstartnumber" class="fullWidth">
            <h3><?php esc_html_e('Invoice StartNumber:', 'wp360-invoice'); ?></h3>            
            <div class="invoice-thk-row">
                <label for="" class="description_label"><?php echo esc_html__('Instructions: Your invoice number should only contain digits towards the end and not in between. Example: INV100', 'text-domain'); ?></label>
                <?php $dis = !empty($wp360_invoicestartnumber) ? 'disabled' : ''; ?>
                <input type="text" name="wp360option[wp360_invoicestartnumber]" <?php echo $dis; ?> value="<?php echo esc_html($wp360_invoicestartnumber); ?>" class="regular-text"/>
                <p><em><?php echo __('Can only be set once!'); ?></em></p>
            </div>            
        </fieldset>
        <fieldset id="wp360-invoice-thankyou-message" class="fullWidth">
            <?php wp_nonce_field('wp360-save-invoice-misc-settings-nonce', '_wpnonce_save_misc_settings'); ?>
            <h3><?php esc_html_e('Thank you message', 'wp360-invoice'); ?></h3>            
            <div class="invoice-thk-row">                
                <input type="text" name="wp360option[wp360_thankyoumsg]" value="<?php echo esc_html($thankyoumsg); ?>" class="regular-text"/>
            </div>            
        </fieldset>        
        <?php    
    }
?>