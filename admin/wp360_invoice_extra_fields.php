<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    function wp360invoice_extfields_init(){
        // Hook to show extra fields in user profile
        add_action('show_user_profile', 'wp360invoice_extra_user_fields', 100);
        add_action('edit_user_profile', 'wp360invoice_extra_user_fields', 100);
        add_action('user_new_form', 'wp360invoice_extra_user_fields', 100);
        // Hook to save extra fields
        add_action('personal_options_update', 'wp360invoice_save_extra_user_fields');
        add_action('edit_user_profile_update', 'wp360invoice_save_extra_user_fields');
        add_action('user_register', 'wp360invoice_save_extra_user_fields');
    }
    wp360invoice_extfields_init();
    

    function wp360invoice_extra_user_fields($user) {
        wp_nonce_field('wp360_invoice_user_extra_fields', 'wp360_invoice_user_extra_fields_nonce');
        ?>
        <h2><?php esc_attr_e("WP360 Invoice Extra Fields", "wp360-invoice"); ?></h2>
        <table class="form-table" id="wp360_invoice_extra_fields">
            <tr>
                <th><label for="wp360_invoice_fields"><?php esc_attr_e("Invoice Fields", "wp360-invoice"); ?></label></th>
                <td>
                    <div id="wp360-invoice-fields-container">
                        <?php
                            $invoice_fields = '';
                            if(!empty($user->ID)){
                                $invoice_fields = get_user_meta($user->ID, 'wp360_invoice_user_extra_fields', true);
                            }
                        if (!empty($invoice_fields)) {
                            foreach ($invoice_fields as $field) {
                                ?>
                                <div class="wp360-invoice-field">
                                    <input type="text" name="wp360_invoice_field_names[]" placeholder="Field Name" value="<?php echo esc_attr($field['name']); ?>" />
                                    <input type="text" name="wp360_invoice_field_values[]" placeholder="Value" value="<?php echo esc_attr($field['value']); ?>" />
                                    <button type="button" class="remove-field">Remove</button>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                    <button type="button" id="wp360_invoice_user_extra_add"><?php esc_attr_e("Add Field", "wp360-invoice"); ?></button>
                </td>
            </tr>
        </table>
        <?php
    }   

    function wp360invoice_save_extra_user_fields($user_id) {
        if (!isset($_POST['wp360_invoice_user_extra_fields_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wp360_invoice_user_extra_fields_nonce'])), 'wp360_invoice_user_extra_fields')) {
            return;
        }
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }

        $field_names = isset($_POST['wp360_invoice_field_names']) && is_array($_POST['wp360_invoice_field_names']) ? array_map('sanitize_text_field', $_POST['wp360_invoice_field_names']) : [];
        $field_values = isset($_POST['wp360_invoice_field_values']) && is_array($_POST['wp360_invoice_field_values']) ? array_map('sanitize_text_field', $_POST['wp360_invoice_field_values']) : [];
        $invoice_fields = [];

        for ($i = 0; $i < count($field_values); $i++) {
            if (!empty($field_values[$i]) || !empty($field_names[$i])) {
                $invoice_fields[] = [
                    'name' => sanitize_text_field($field_names[$i]),
                    'value' => sanitize_text_field($field_values[$i]),
                ];
            }
        }

        update_user_meta($user_id, 'wp360_invoice_user_extra_fields', $invoice_fields);
    }
?>