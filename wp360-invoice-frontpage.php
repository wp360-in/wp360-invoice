<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
$invoiceID = '';
if(isset($_GET['view-invoice']) && !empty(sanitize_text_field($_GET['view-invoice']))){
    $invoiceID = sanitize_text_field($_GET['view-invoice']);
}

if (empty($invoiceID)) {
    // Get the current page number
    $paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;

    // WP_Query arguments for pagination
    $invoiceArgs = [
        'post_type'      => 'wp360_invoice',
        'post_status'    => 'publish',
        'posts_per_page' => 5,
        'paged'          => $paged,
    ];
    
    // Check if the current user is not an administrator
    if (!current_user_can('administrator')) {
        $invoiceArgs['meta_query'] = [
            [
                'key'     => 'invoice_user',
                'value'   => get_current_user_id(),
                'compare' => '=',
            ],
        ];
    }

    $userInvoices = new WP_Query($invoiceArgs);
    $res = '';
    if (class_exists('WooCommerce')) {
        echo '<div class="wp360invoice_btma_wrap"><a href="'.get_permalink(get_option('woocommerce_myaccount_page_id')).'" class="wp360invoice_btma"><span class="dashicons dashicons-admin-users"></span> My account</a></div>';
    }
    if ($userInvoices->have_posts()) {
        $res .= '<table class="wp360invoiceSummaryTable">
            <thead>
                <tr>
                    <th>' . esc_html__('Invoice number', 'wp360-invoice') . '</th>
                    <th width="20%">' . esc_html__('Title', 'wp360-invoice') . '</th>
                    <th width="25%">' . esc_html__('Summary', 'wp360-invoice') . '</th>
                    <th width="15%>' . esc_html__('Price', 'wp360-invoice') . '</th>
                    <th>' . esc_html__('Total', 'wp360-invoice') . '</th>
                    <th width="15%>' . esc_html__('Action', 'wp360-invoice') . '</th>
                </tr>
            </thead>
            <tbody>
        ';

        while ($userInvoices->have_posts()) {
            $currency = '';
            if (class_exists('WooCommerce')) {
                $currency = get_woocommerce_currency_symbol();
            }
            $userInvoices->the_post();
            $postID        = get_the_ID();
            $invoiceTitle  = esc_html(get_the_title());
            $invoiceNumber = esc_html(get_post_meta($postID, 'invoice_number', true));
            $invoiceAmount = esc_html(get_post_meta($postID, 'invoice_amount', true));
            $invoiceItems  = get_post_meta($postID, 'invoice_items', true);
            $summary = '';

            if (isset($invoiceItems) && !empty($invoiceItems) && is_array($invoiceItems)) {
                $summary .= '<ul class="wp360InvoiceSummary">';
                foreach ($invoiceItems as $key => $item) {
                    if (isset($item['description']) && isset($item['unit_price']))
                        $summary .= '
                            <li>
                                <div class="itemDesc">#' . ($key + 1) . ' ' . esc_html($item['description']) . '</div>
                                <div class="itemQty"> x ' .esc_html($item['qty']) . '</div>
                            </li>';
                }
                $summary .= '</ul>';
            }

            $res .= '<tr>
                        <td>' . $invoiceNumber . '</td>
                        <td>' . $invoiceTitle . '</td>
                        <td>' . $summary . '</td>
                        <td>' . $item['unit_price'] . '</td>
                        <td>' . $currency . $invoiceAmount . '</td>
                        <td class="wp360View_inv"><a href="'.get_the_permalink(get_option( 'wp360_invoices_page_id' )).'?view-invoice='.$postID.'" class="btnStyle">'.esc_html__('View Invoice','wp360-invoice').'</a></td>
                    </tr>';
        }

        $res .= '</tbody></table>';

        // Pagination
        $res .= '<div class="wp360invoiceSummaryTable--pagination">';
        $res .= paginate_links([
            'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
            'format'    => '?paged=%#%',
            'current'   => max(1, $paged),
            'total'     => $userInvoices->max_num_pages,
            'prev_text' => __('&laquo; Previous', 'wp360-invoice'),
            'next_text' => __('Next &raquo;', 'wp360-invoice'),
        ]);
        $res .= '</div>';

        wp_reset_postdata();
    } else {
        $res .= '<p>' . esc_html__('No invoice found', 'wp360-invoice') . '</p>';
    }

    echo wp_kses_post($res);
    }

    else {
        $inv = get_post($invoiceID);
    
        if ($inv && $inv->post_type === 'wp360_invoice') {
            $invoice_user = get_post_meta($invoiceID, 'invoice_user', true);
            $current_user_id = get_current_user_id();
    
            if ($invoice_user == $current_user_id || current_user_can('administrator')) {
                require_once 'front/view_invoice.php';
                wp360invoice_showInvoice($invoiceID);
            } else {
                echo wp_kses_post('<p>' . esc_html__('You do not have permission to view this invoice.', 'wp360-invoice') . '</p>');
            }
        } else {
            echo wp_kses_post('<p>' . esc_html__('No invoice found.', 'wp360-invoice') . '</p>');
        }
    }
?> 