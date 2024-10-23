<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }

    require_once 'invoice_addresses.php';
    require_once 'invoice_bank_details.php';
    require_once 'wp360_misc_settings.php';
    require_once 'wp360_invoice_firm_details.php';

    function wp360invoice_render_settings_page(){ ?>
        <div class="wrap wp360-invoice-settings">
            <?php echo wp_kses_post(wp360invoice_admin_tabs()); ?>
            <h1 class="wp-heading-inline"><?php esc_html_e('WP360 Invoice Settings', 'wp360-invoice');?></h1>
            <?php
              if (isset($_POST['wp360_submit']) && isset($_POST['_wpnonce_save_invoice_settings']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce_save_invoice_settings'])), 'wp360-invoice-settings-nonce')){
                echo '<div class="updated"><p>' . esc_html__('Settings saved!', 'wp360-invoice') . '</p></div>';
             }
            ?>
            <div class="invoiceSettingsWrapper">                
                <form id="wp360_invoice-settings-form" action="admin.php?page=wp360_invoice&tab=settings" method="post" autocomplete="off">
                    <?php
                        wp_nonce_field('wp360-invoice-settings-nonce', '_wpnonce_save_invoice_settings');

                        wp360invoice_render_firm_details_page();
                        wp360invoice_render_address_fields();
                        wp360invoice_render_banking_page();
                        wp360invoice_render_misc_settings();
                    ?>
                    <div class="wp360_submit_wrapper">
                        <input name="wp360_submit" class="button button-primary" type="submit" value="<?php esc_html_e('Save Settings', 'wp360-invoice') ?>" />
                    </div>
                </form>
            </div>
        </div>
    <?php } wp360invoice_render_settings_page(); ?>       