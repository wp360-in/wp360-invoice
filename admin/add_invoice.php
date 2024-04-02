<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_invoice']) 
&& isset($_POST['_wpnonce_add_invoice'])
&& wp_verify_nonce( sanitize_text_field($_POST['_wpnonce_add_invoice']), 'add-invoice-nonce-action')
) {       
    $invoiceTitle   = isset($_POST['invoice_title']) ? sanitize_text_field($_POST['invoice_title']) : "";
    $invoiceAmount  = isset($_POST['invoice_amount'])? sanitize_text_field($_POST['invoice_amount']) : "";
    $invoiceUser    = isset($_POST['invoice_user'])  ? sanitize_text_field($_POST['invoice_user']) : "";



    if ( isset( $_POST['items'] ) && is_array( $_POST['items'] ) ) {
        $invoiceItems = array_map( function( $item ) {
            return array(
                'description' => isset( $item['description'] ) ? sanitize_text_field( $item['description'] ) : '',
                'unit_price'  => isset( $item['unit_price'] ) ? sanitize_text_field( $item['unit_price'] ) : '',
                'qty'         => isset( $item['qty'] ) ? sanitize_text_field( $item['qty'] ) : '',
            );
        }, $_POST['items'] );
    }

    $invoiceType    = isset($_POST['invoice_type']) ? sanitize_text_field($_POST['invoice_type']) : "";
    $invoice_id     = false;
    if (isset($_POST['_wpnonce_add_invoice']) && wp_verify_nonce($_POST['_wpnonce_add_invoice'], 'add-invoice-nonce-action')) {            
        if(!empty($invoiceTitle) && !empty($invoiceAmount) && !empty($invoiceUser) && !empty(wp360invoice_newInvoiceID()))
            $invoice_id = wp_insert_post(array(
                'post_title'   => $invoiceTitle,
                'post_type'    => 'wp360_invoice', 
                'post_status'  => 'publish',
                'meta_input'   => [
                    'invoice_amount' => $invoiceAmount,
                    'invoice_user'   => $invoiceUser,
                    'invoice_number' => wp360invoice_newInvoiceID(),
                    'invoice_items' => $invoiceItems,
                    'invoice_type' => $invoiceType,
                    'invoice_status' => 'unpaid',
                    'invoice_createddate'=>gmdate('Y-m-d h:i:s'),
                ]
        ));
        if ($invoice_id) {
            echo '<div class="updated"><p>' . esc_html__('Invoice created successfully!', 'wp360-invoice') . '</p></div>';
        } else {
            echo '<div class="error"><p>' . esc_html__('Error creating the invoice.', 'wp360-invoice') . '</p></div>';
        }
    }elseif(isset($_POST['_wpnonce_add_invoice'])){
        echo '<div class="error"><p>' . esc_html__('Security check failed.', 'wp360-invoice') . '</p></div>';
    }       
}
?>

<div class="wp360-invoice-addInvoice">
    <form action="" method="post">
        <?php wp_nonce_field('add-invoice-nonce-action', '_wpnonce_add_invoice'); ?>
        <div class="invoiceFormInn">
            <input type="text" name="invoice_title" required placeholder="<?php esc_attr_e('Invoice Title', 'wp360-invoice'); ?>" id="" class="textFieldStyle">            
        </div>
        <div class="invoiceFormInn">
            <select name="invoice_user" id="" class="selectFieldStyle halfWidth" required>
                <option value=""><?php esc_html_e('Select user', 'wp360-invoice'); ?></option>
                <?php
                    $customers = wp360invoice_wooCustomersList();
                    if ($customers && is_array($customers) && !empty($customers)) {
                        foreach ($customers as $key => $customerID) {
                             $customer = get_userdata($customerID);
                             $user_email = $customer->user_email;
                             $user_display_name = $customer->display_name;
                             echo '<option value="'.esc_html($customerID).'">'.esc_html($user_display_name).' ('.esc_html($user_email).')'.'</option>';
                        }
                    }
                ?>
            </select>
            <input type="number" name="invoice_amount" required placeholder="<?php esc_attr_e('Invoice Total Amount', 'wp360-invoice'); ?>" id="totalAmountField" readonly class="textFieldStyle disableField halfWidth">
        </div>
        <div class="invoiceFormInn radioButtonCon">
            <h3><?php esc_html_e('Invoice Type', 'wp360-invoice'); ?></h3>
            <div class="radioButtons">
                <label>
                    <input type="radio" name="invoice_type" value="hourly" checked required id="">
                    <?php esc_html_e('Hourly', 'wp360-invoice'); ?>
                </label>
                <label>
                    <input type="radio" name="invoice_type" value="fixed" id="">
                    <?php esc_html_e('Fixed', 'wp360-invoice'); ?>
                </label>
            </div>
        </div>
        <div class="invoiceFormInn wp360_invoice_itemsCon">
            <div class="wp360_invoiceItem">
                <input type="text"   name="items[0][description]" required class="oneThirdWidth textFieldStyle itemDescField"  placeholder="<?php esc_attr_e('Item Description', 'wp360-invoice'); ?>">
                <input type="number" name="items[0][qty]" required class="oneThirdWidth textFieldStyle qtyField" placeholder="<?php esc_attr_e('Item QTY', 'wp360-invoice'); ?>"  step="0.1">
                <input type="number" name="items[0][unit_price]" required class="oneThirdWidth textFieldStyle unitPriceField"  placeholder="<?php esc_attr_e('Item Unit Price', 'wp360-invoice'); ?>"  step="0.1">
            </div>
            <div class="wp360_invoice_addInvoiceItemCon">
                <a href="javascript:;" class="wp360_invoice_addItem"><?php esc_html_e('Add Item/Service', 'wp360-invoice'); ?></a>
                <a href="javascript:;" class="wp360_invoice_removeInvoiceItem" style="display:none"><?php esc_html_e('Remove Item/Service', 'wp360-invoice'); ?></a>
            </div>
        </div>

        <div class="invoiceFormInn">
            <input type="submit" value="<?php esc_attr_e('Publish', 'wp360-invoice'); ?>" name="create_invoice" class="button is-primary">
        </div>
    </form>
</div>
