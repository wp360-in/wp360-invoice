<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
function wp360invoice_view_invoice_endpoint() {
    add_rewrite_endpoint( 'view-invoice', EP_ROOT | EP_PAGES );
}  
add_action( 'init', 'wp360invoice_view_invoice_endpoint' );
function view_invoice_query_vars( $vars ) {
    $vars[] = 'view-invoice';
    return $vars;
}
add_filter( 'query_vars', 'view_invoice_query_vars', 0 );

add_action( 'woocommerce_account_view-invoice_endpoint', 'wp360invoice_view_invoice_content' );
function wp360invoice_view_invoice_content(){
    $invoiceID     = get_query_var('view-invoice');
    wp360invoice_showInvoice($invoiceID);  
}
function wp360invoice_showInvoice($invoiceID){
    $invoicePost = get_post($invoiceID);
    if ( !$invoicePost || get_post_type($invoiceID) !== 'wp360_invoice' || $invoicePost->post_status !== 'publish' ) {
        esc_html_e('Invice not found!', 'wp360-invoice');
    }
    if ($invoicePost && get_post_type($invoiceID) === 'wp360_invoice' && $invoicePost->post_status === 'publish'):
        $timestamp      = esc_html(get_post_meta($invoiceID, 'invoice_createddate', true));
        $dateSetting    = esc_html(get_option('date_format'));
        $formatted_date = gmdate($dateSetting, strtotime($timestamp));
        $invoiceAmount  = esc_html(get_post_meta($invoiceID, 'invoice_amount', true));
        $invoiceNumber  = esc_html(get_post_meta($invoiceID, 'invoice_number', true));
        $currency       = get_woocommerce_currency_symbol();
        $userID         = get_current_user_id();
        $userData       = get_userdata($userID);
        $custName       = esc_html(get_user_meta($userID, 'billing_first_name', true)).' '.esc_html(get_user_meta($userID, 'billing_last_name', true));
        $email          = $userData->user_email;
        $custLine1      = esc_html(get_user_meta($userID, 'billing_address_1', true));
        $custLine2      = esc_html(get_user_meta($userID, 'billing_address_2', true));
        $custCountry    = esc_html(get_user_meta($userID, 'billing_country', true));
        $custCity       = esc_html(get_user_meta($userID, 'billing_city', true));
        $custState      = esc_html(get_user_meta($userID, 'billing_state', true));
        $custPostCode   = esc_html(get_user_meta($userID, 'billing_postcode', true));
        $phone          = esc_html(get_user_meta($userID, 'billing_phone', true));
        $addressParts = [];
        if (!empty($custLine1)) {
            $addressParts[] = $custLine1;
        }
        if (!empty($custLine2)) {
            $addressParts[] = (!empty($custLine1) ? "<br>" : "") . $custLine2;
        }
        if (!empty($custCity)) {
            $addressParts[] = $custCity;
        }
        if (!empty($custPostCode)) {
            $addressParts[] = $custPostCode;
        }
        if (!empty($custState)) {
            $addressParts[] = $custState;
        }
        if (!empty($custCountry)) {
            $addressParts[] = $custCountry;
        }
        $custAddress    = esc_html(implode(', ', $addressParts));

        $invoiceItems   = get_post_meta($invoiceID, 'invoice_items', true);
        $invoicetype    = esc_html(get_post_meta($invoiceID, 'invoice_type', true));
        if($invoicetype == 'fixed'){
            $invoicetype = 'Items';
        }
    ?>
        <div id="wp360-invoice_printinvoice" class="hidden-print" data-id="<?php echo esc_html($invoiceID);?>"><?php esc_html_e('Print Invoice', 'wp360-invoice')?></div>
        <div class="wp360InvoiceCon">
            <h2><?php esc_html_e('Invoice', 'wp360-invoice');?></h2>
            <div class="invoiceHead">
                <div class="invoceCompanyInfo">
                        <?php
                            if ( has_custom_logo() ) :
                                $custom_logo_id = get_theme_mod( 'custom_logo' );
                                $image          = wp_get_attachment_image_src( $custom_logo_id , 'full' );
                                echo '<img src="'.esc_url($image[0]).'" alt="logo">';
                            endif
                        ?>
                    <small>
                        <?php 
                            $tagLine    = get_bloginfo('description');
                            echo esc_html( sanitize_text_field ($tagLine ));
                        ?>
                    </small>
                </div>
                <div class="invoceNumberCon">
                    <p>
                        <?php esc_html_e('Invoice No.', 'wp360-invoice');?> <span>#<?php echo esc_html($invoiceNumber);?></span> <br>
                        <?php echo esc_html($formatted_date);?>
                    </p>
                </div>
            </div>
            <div class="invoceHead2">
                <div class="companyInfo">
                     <?php
                        $saved_company_address = get_option('wp360_company_address', '');
                        if($saved_company_address){
                            echo '<h4>'. esc_html__( 'Address','wp360-invoice' ) .'</h4>';
                        }
                     ?>
                    <p> 
                        <?php 
                            if($saved_company_address){
                                echo wp_kses_post($saved_company_address); //Allow br tag
                            }
                        ?> 
                    </p>
                </div>
                <div class="receiptInfo">
                    <h4><?php esc_html_e('Bill To', 'wp360-invoice');?></h4>
                    <p>
                        <?php echo esc_html($custName);?> <br>
                        <?php echo esc_html($custAddress);?>
                    </p>
                </div>
            </div>
            <div class="invoSummCon">
                <table class="invoiceSummary">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Service Description', 'wp360-invoice');?></th>
                            <th><?php esc_html_e('Price', 'wp360-invoice');?></th>
                            <th><?php echo esc_html(ucfirst($invoicetype)); ?></th>
                            <th><?php esc_html_e('Subtotal', 'wp360-invoice');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if(isset($invoiceItems) && !empty($invoiceItems) && is_array($invoiceItems)){
                                foreach($invoiceItems as $item){
                                    if(isset($item['description']) && isset($item['unit_price']) &&  isset($item['qty'])){
                                        ?>
                                            <tr>
                                                <td><?php echo esc_html($item['description']);?></td>
                                                <td><?php echo esc_html($currency.$item['unit_price']);?></td>
                                                <td><?php echo esc_html($item['qty']);?> </td>
                                                <td><?php echo esc_html($currency.$item['qty'] * $item['unit_price']);?></td>
                                            </tr>
                                        <?php
                                    }
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="invoiceFooter">
                <div class="textCon">
                    <?php
                        $thankyoumsg = get_option('wp360_thankyoumsg', '');
                        if($thankyoumsg){
                            echo esc_html($thankyoumsg);
                        }else{
                            esc_html_e('Thank you for giving us chance to serve you.', 'wp360-invoice');
                        }
                     ?>
                </div>
                <div class="infoTotalCon">
                    <div class="totalInn1">
                        <strong><?php esc_html_e('Subtotal', 'wp360-invoice');?></strong>
                        <span><?php echo esc_html($currency.$invoiceAmount);?></span>
                    </div>
                    <div class="totalInn1">
                        <strong><?php esc_html_e('Tax', 'wp360-invoice');?></strong>
                        <span><?php echo esc_html($currency);?>0</span>
                    </div>
                    <div class="totalInn2">
                        <strong><?php esc_html_e('Total', 'wp360-invoice');?></strong>
                        <span><?php echo esc_html($currency.$invoiceAmount);?></span>
                    </div>
                </div>
            </div>
        </div>
    <?php
    endif;
}


