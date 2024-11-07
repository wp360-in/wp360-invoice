_<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
$invoiceID = '';
if(!isset($_GET['invoice_id']) || empty($_GET['invoice_id'])){
    echo 'No invoice found';
    return;
}else{
    $invoiceID = sanitize_text_field( $_GET['invoice_id'] );
}
$allowed_html = array(
    'br' => array()
);
if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_invoice']) && isset($_POST['_wpnonce_add_invoice']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce_add_invoice'])), 'add-invoice-nonce-action')) {

    $invoiceTitle   = isset($_POST['invoice_title']) ? sanitize_text_field($_POST['invoice_title']) : "";
    $invoiceAmount  = isset($_POST['invoice_amount'])? sanitize_text_field($_POST['invoice_amount']) : "";
    $invoiceUser    = isset($_POST['invoice_user'])  ? sanitize_text_field($_POST['invoice_user']) : "";
    $invoiceAddress = isset($_POST['invoice_address'])  ? wp_kses($_POST['invoice_address'], $allowed_html) : "";
    $invoiceBank    = isset($_POST['invoice_bank'])  ? wp_kses($_POST['invoice_bank'], $allowed_html) : "";    

    if (isset($_POST['items']) && is_array($_POST['items']) ) {
        $invoiceItems = array_map(function( $item ) {
            return array(
                'description' => isset( $item['description'] ) ? sanitize_text_field( $item['description'] ) : '',
                'unit_price'  => isset( $item['unit_price'] ) ? sanitize_text_field( $item['unit_price'] ) : '',
                'qty'         => isset( $item['qty'] ) ? sanitize_text_field( $item['qty'] ) : '',
            );
        }, $_POST['items'] );
    }
    $invoiceFirm = array();

    if (isset($_POST['wp360_invoice_firm'])) {
        $invoiceFirm['id'] = sanitize_text_field($_POST['wp360_invoice_firm_id']);
        $invoiceFirm['name'] = sanitize_text_field($_POST['wp360_invoice_firm']);
        $invoiceFirm['tagline'] = isset($_POST['wp360_invoice_firm_tagline']) ? sanitize_text_field($_POST['wp360_invoice_firm_tagline']) : '';
        
        $invoiceFirm['logo_url'] = isset($_POST['wp360_invoice_firm_logo']) ? esc_url($_POST['wp360_invoice_firm_logo']) : '';
        $invoiceFirm['text_logo'] = isset($_POST['wp360_invoice_firm_text_logo']) ? sanitize_text_field($_POST['wp360_invoice_firm_text_logo']) : '';
    }



    $invoiceType    = isset($_POST['invoice_type']) ? sanitize_text_field($_POST['invoice_type']) : "";
    $invoice_id     = false;
    if (isset($_POST['_wpnonce_add_invoice']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce_add_invoice'])), 'add-invoice-nonce-action')) {            
        if(!empty($invoiceTitle) && !empty($invoiceAmount) && !empty($invoiceUser))
            $invoice_id = wp_update_post(array(
                'ID'           => sanitize_text_field($_POST['invoice_id']),
                'post_title'   => $invoiceTitle,
                'post_type'    => 'wp360_invoice', 
                'post_status'  => 'publish',
                'meta_input'   => [
                    'invoice_amount' => $invoiceAmount,
                    'invoice_user'   => $invoiceUser,
                    'invoice_items' => $invoiceItems,
                    'invoice_type' => $invoiceType,
                    'invoice_status' => 'unpaid',
                    'invoice_address' => $invoiceAddress,
                    'invoice_bank' => $invoiceBank,
                    'invoice_createddate'=>gmdate('Y-m-d h:i:s'),
                    'invoice_firm'=> $invoiceFirm,
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
$invoiceItems = get_post_meta($invoiceID, 'invoice_items', true);
$invoiceType = get_post_meta($invoiceID, 'invoice_type', true);
$invoiceUser = get_post_meta($invoiceID, 'invoice_user', true);
$invoiceAmount = get_post_meta($invoiceID, 'invoice_amount', true);
$invoiceAddress = get_post_meta($invoiceID, 'invoice_address', true);
$invoiceBank = get_post_meta($invoiceID, 'invoice_bank', true);
$invoiceFirm = get_post_meta($invoiceID, 'invoice_firm', true);
?>

<div class="wp360-invoice-addInvoice">
    <form action="" method="post">
        <input type="hidden" name="invoice_id" value="<?php echo esc_attr($invoiceID);?>">
        <?php wp_nonce_field('add-invoice-nonce-action', '_wpnonce_add_invoice'); ?>
        <div class="invoiceFormInn">
            <input type="text" name="invoice_title" value="<?php echo esc_attr(get_the_title($invoiceID)); ?>" required placeholder="<?php esc_attr_e('Invoice Title', 'wp360-invoice'); ?>" id="" class="textFieldStyle">
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
                             echo '<option value="'.esc_html($customerID).'"
                             '.(($invoiceUser == $customerID)?'selected':'').'
                             >'.esc_html($user_display_name).' ('.esc_html($user_email).')'.'</option>';
                        }
                    }
                ?>
            </select>
            <input type="number" name="invoice_amount" value="<?php echo esc_attr($invoiceAmount);?>" required placeholder="<?php esc_attr_e('Invoice Total Amount', 'wp360-invoice'); ?>" id="totalAmountField" readonly class="textFieldStyle disableField halfWidth">
        </div>
        <div class="invoiceFormInn radioButtonCon">
            <h3><?php esc_html_e('Invoice Type', 'wp360-invoice'); ?></h3>
            <div class="radioButtons">
                <label>
                    <input type="radio" name="invoice_type" value="hourly" 
                    <?php if($invoiceType == 'hourly'){ echo 'checked';}?>
                    required id="">
                    <?php esc_html_e('Hourly', 'wp360-invoice'); ?>
                </label>
                <label>
                    <input type="radio" name="invoice_type" <?php if($invoiceType == 'fixed'){ echo 'checked';}?> value="fixed" id="">
                    <?php esc_html_e('Fixed', 'wp360-invoice'); ?>
                </label>
            </div>
        </div>
        <div class="invoiceFormInn">
            <div class="selectWrapper fullWidth">
                <h3><?php esc_html_e('Firm/ Business', 'wp360-invoice'); ?></h3>
                <?php
                    $saved_invoice_firm = get_option('wp360_firm_details', array());
                    if ($saved_invoice_firm && is_array($saved_invoice_firm) && !empty($saved_invoice_firm)) {
                        echo '<select name="wp360_invoice_firm" id="wp360_invoice_firm" class="selectFieldStyle fullWidth" required>
                        <option value="">' . esc_html__("Select firm details", "wp360-invoice") . '</option>';
                        foreach ($saved_invoice_firm as $index => $firm) {
                            $selected = '';
                            if(!empty($invoiceFirm)){
                                $selected = ($firm['id'] == $invoiceFirm['id'] ? 'selected' : '');
                            }                            
                            echo '<option value="'.wp_kses($firm['firm_name'], $allowed_html).'"'. esc_html($selected) .' data-firm-id="'. esc_attr($firm['id']) .'" data-logo="'. esc_attr($firm['logo_url']) .'" data-tagline="'. esc_attr($firm['tagline']) .'" data-text-logo="'. esc_attr($firm['text_logo']) .'">'.esc_html($firm['firm_name']).'</option></select>';
                        }
                    }
                    else{
                        echo '<select name="wp360_invoice_firm" id="wp360_invoice_firm" class="selectFieldStyle fullWidth"><option value="">' . esc_html__("No firm/business details availble.", "wp360-invoice") . '</option></select>';
                    }
                ?>
                </select>
                <input type="hidden" name="wp360_invoice_firm_id" id="firm_id" value="<?php echo !empty($invoiceFirm['id']) ? $invoiceFirm['id'] : ''; ?>">
                <input type="hidden" name="wp360_invoice_firm_logo" id="firm_logo" value="<?php echo !empty($invoiceFirm['logo_url']) ? $invoiceFirm['logo_url'] : ''; ?>">
                <input type="hidden" name="wp360_invoice_firm_tagline" id="firm_tagline" value="<?php echo !empty($invoiceFirm['tagline']) ? $invoiceFirm['tagline'] : ''; ?>">
                <input type="hidden" name="wp360_invoice_firm_text_logo" id="firm_text_logo" value="<?php echo !empty($invoiceFirm['text_logo']) ? $invoiceFirm['text_logo'] : ''; ?>">

                <div class="wp360_invoice_addInvoiceDetails">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp360-invoice-settings')); ?>" class="wp360_invoice_settings_link"><?php esc_html_e('Add Firm Details', 'wp360-invoice'); ?></a>                
                </div>
            </div>
        </div>
        <div class="invoiceFormInn">
            <div class="selectWrapper halfWidth">
                <h3><?php esc_html_e('Address', 'wp360-invoice'); ?></h3>
                <select name="invoice_address" id="" class="selectFieldStyle fullWidth" required>
                    <?php
                        $saved_invoice_addresses = get_option('wp360_invoice_addresses', array());
                        if ($saved_invoice_addresses && is_array($saved_invoice_addresses) && !empty($saved_invoice_addresses)) {
                            echo '<option value="">' . esc_html__("Select address", "wp360-invoice") . '</option>';
                            foreach ($saved_invoice_addresses as $index => $address) {
                                $selected = ($address == $invoiceAddress ? 'selected' : '');
                                echo '<option value="'.wp_kses($address, $allowed_html).'"'. esc_html($selected).'>'.esc_html($address).'</option>';
                            }                        
                        }
                        else{
                            echo '<option value="">' . esc_html__("No addresses availble.", "wp360-invoice") . '</option>';
                        }
                    ?>
                </select>
                <div class="wp360_invoice_addInvoiceDetails">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp360-invoice-settings')); ?>" class="wp360_invoice_settings_link"><?php esc_html_e('Add Address', 'wp360-invoice'); ?></a>                
                </div>
            </div>
            <div class="selectWrapper halfWidth">
                <h3><?php esc_html_e('Bank Details', 'wp360-invoice'); ?></h3>
                <select name="invoice_bank" id="" class="selectFieldStyle fullWidth">                
                    <?php
                        $saved_invoice_banking = get_option('wp360_invoice_banking', array());
                        if ($saved_invoice_banking && is_array($saved_invoice_banking) && !empty($saved_invoice_banking)) {
                            echo '<option value="">' . esc_html__("Select bank details", "wp360-invoice") . '</option>';
                            foreach ($saved_invoice_banking as $index => $bank) {                            
                                $selected = ($bank == $invoiceBank ? 'selected' : '');
                                echo '<option value="'.wp_kses($bank, $allowed_html).'"'. esc_html($selected) .'>'.esc_html($bank).'</option>';
                            }
                        }
                        else{
                            echo '<option value="">' . esc_html__("No bank details availble.", "wp360-invoice") . '</option>';
                        }
                    ?>
                </select>
                <div class="wp360_invoice_addInvoiceDetails">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp360-invoice-settings')); ?>" class="wp360_invoice_settings_link"><?php esc_html_e('Add Bank Details', 'wp360-invoice'); ?></a>                
                </div>
            </div>
        </div>

        <div class="invoiceFormInn wp360_invoice_itemsCon">
            <?php foreach($invoiceItems as $key => $item) { ?>
                <div class="wp360_invoiceItem">
                    <input type="text" name="items[<?php echo esc_attr($key); ?>][description]" value="<?php echo esc_attr($item['description']); ?>" required class="oneThirdWidth textFieldStyle itemDescField" placeholder="<?php esc_attr_e('Item Description', 'wp360-invoice'); ?>">
                    <input type="number" name="items[<?php echo esc_attr($key); ?>][qty]" value="<?php echo esc_attr($item['qty']); ?>" required class="oneThirdWidth textFieldStyle qtyField" placeholder="<?php esc_attr_e('Item QTY', 'wp360-invoice'); ?>" step="0.1">
                    <input type="number" name="items[<?php echo esc_attr($key); ?>][unit_price]" value="<?php echo esc_attr($item['unit_price']); ?>" required class="oneThirdWidth textFieldStyle unitPriceField" placeholder="<?php esc_attr_e('Item Unit Price', 'wp360-invoice'); ?>" step="0.1">
                </div>
            <?php } ?>

            <div class="wp360_invoice_addInvoiceItemCon">
                <a href="javascript:;" class="wp360_invoice_addItem"><?php esc_html_e('Add Item/Service', 'wp360-invoice'); ?></a>
                <a href="javascript:;" class="wp360_invoice_removeInvoiceItem" style="display:none"><?php esc_html_e('Remove Item/Service', 'wp360-invoice'); ?></a>
            </div>
        </div>

        <div class="invoiceFormInn">
            <input type="submit" value="<?php esc_attr_e('Update', 'wp360-invoice'); ?>" name="update_invoice" class="button is-primary">
        </div>
    </form>
</div>
