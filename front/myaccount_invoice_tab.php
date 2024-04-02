<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
// Add Invoice Tab to My Account Page
function wp360invoice_add_invoice_tab_to_my_account( $menu_links ) {
    $menu_links = array_slice( $menu_links, 0, 3, true ) 
	+ array( 'wp360_invoice' => 'WP360 Invoice' )
	+ array_slice( $menu_links, 3, NULL, true );
    return $menu_links;
}
add_filter( 'woocommerce_account_menu_items', 'wp360invoice_add_invoice_tab_to_my_account', 20 );
function wp360invoice_invoice_endpoint() {
    add_rewrite_endpoint( 'wp360_invoice', EP_ROOT | EP_PAGES );
}  
add_action( 'init', 'wp360invoice_invoice_endpoint' );
function wp360invoice_invoice_query_vars( $vars ) {
    $vars[] = 'wp360_invoice';
    return $vars;
}
add_filter( 'query_vars', 'wp360invoice_invoice_query_vars', 0 );


function wp360invoice_invoice_tab_content() {
    $invoiceArgs = [
        'post_type' => 'wp360_invoice',
        'post_status'=> 'publish',
        'posts_per_page' => -1,
        'meta_query'     => [
            [
                'key'     => 'invoice_user',
                'value'   => get_current_user_id(),
                'compare' => '=',
            ],
        ],
    ];
    $userInvoices = new WP_Query($invoiceArgs);
    $res = '<h3>'.esc_html__('Your Invoices','wp360-invoice').'</h3>';
    if($userInvoices->have_posts()){
        $res .= '<table class="invoiceSummaryTable woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
            <thead>
                <tr>
                    <th width="100">' . esc_html__('Invoice number', 'wp360-invoice') . '</th>
                    <th width="150">' . esc_html__('Title', 'wp360-invoice') . '</th>
                    <th width="250">' . esc_html__('Summary', 'wp360-invoice') . '</th>
                    <th width="100">' . esc_html__('Amount', 'wp360-invoice') . '</th>
                    <th width="100">' . esc_html__('Action', 'wp360-invoice') .'</th>
                </tr>
            </thead>
            <tbody>
        ';
        while($userInvoices->have_posts()){
            $currency = get_woocommerce_currency_symbol();
            $userInvoices->the_post();
            $postID        = get_the_ID();
            $invoiceTitle  = esc_html(get_the_title());
            $invoiceNumber = esc_html(get_post_meta($postID, 'invoice_number', true));
            $invoiceAmount = esc_html(get_post_meta($postID, 'invoice_amount', true));
            $invoiceItems  = get_post_meta($postID, 'invoice_items', true);
            $summary = '';
            if(isset($invoiceItems) && !empty($invoiceItems) && is_array($invoiceItems)){
                $summary .= '<ul class="summaryTable">';
                foreach($invoiceItems as $key => $item){
                    if(isset($item['description']) && isset($item['unit_price']))
                    $summary .= '
                        <li>
                            <div class="itemDesc">#'. ($key + 1) .' '.esc_html($item['description']).'</div>
                            <div class="itemPrice">'.$currency.$item['unit_price'] * esc_html($item['qty']).'</div>
                        </li>
                    ';
                }
                $summary .= '</ul>';
            }
            $res .= '<tr>
                        <td>'.$invoiceNumber.'</td>
                        <td>'.$invoiceTitle.'</td>
                        <td>'.$summary.'</td>
                        <td>'.$currency.$invoiceAmount.'</td>
                        <td><a href="'.wc_get_account_endpoint_url('view-invoice').$postID.'" class="btnStyle">'.esc_html__('View Invoice','wp360-invoice').'</a></td>
            </tr>';
        }
        $res .= '</tbody></table>';
        wp_reset_postdata();
    }else{
        $res .= '<p>' . esc_html__('No invoice found', 'wp360-invoice') . '</p>';
    }
    echo wp_kses_post($res);
 }
   
 add_action( 'woocommerce_account_wp360_invoice_endpoint', 'wp360invoice_invoice_tab_content' );


