<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';
}
if (!class_exists('WP360INVOICE_Invoices_List_Table')) {
    class WP360INVOICE_Invoices_List_Table extends WP_List_Table {
        function prepare_items() {
            $columns = $this->get_columns();
            $hidden = array();
            $sortable = $this->get_sortable_columns();

            $this->_column_headers = array($columns, $hidden, $sortable);
            $this->items = $this->wp360invoice_get_invoices_data();
        }
        function wp360invoice_get_invoices_data() {
            $data = array();
            $invoicesArg = array(
                'post_type'      => 'wp360_invoice',
                'posts_per_page' => -1, // Retrieve all posts
            );
            if(isset($_GET['orderby']) && !empty(sanitize_text_field($_GET['orderby']))){
                $invoicesArg['orderby'] = sanitize_text_field($_GET['orderby']);
            }
            $invoices = new WP_Query($invoicesArg);

            //edit url
            $scheme = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ) ? 'https' : 'http';
            $host = sanitize_text_field( $_SERVER['HTTP_HOST'] );
            $request_uri = esc_url_raw( $_SERVER['REQUEST_URI'] );

            $current_url = "{$scheme}://{$host}{$request_uri}";

            // Parse the URL into components
            $url_components = wp_parse_url( $current_url );

            // Get the query part and parse it into an array
            $query = isset( $url_components['query'] ) ? $url_components['query'] : '';
            parse_str( $query, $query_array );
            //edit url

            foreach ($invoices->posts as $invoice) {
                $query_array['invoice_id'] = $invoice->ID; // Add or modify a query parameter
                $new_query_string = http_build_query($query_array);
                $new_url = $url_components['scheme'] . '://' . $url_components['host'] . $url_components['path'] . '?' . $new_query_string;     


                $user_id = get_post_meta($invoice->ID, 'invoice_user', true);
                $invoice_address = get_post_meta($invoice->ID, 'invoice_address', true);
                $invoice_bank = get_post_meta($invoice->ID, 'invoice_bank', true);
                $invoice_firm = get_post_meta($invoice->ID, 'invoice_firm', true);
                $user_info = get_userdata($user_id);
                $invoice_amount = floatval(get_post_meta($invoice->ID, 'invoice_amount', true));
                $invoiceStatus = get_post_meta($invoice->ID, 'invoice_status', true);
                $invoiceReceipt = get_post_meta($invoice->ID, 'payment_receipt', true);
                $data[] = array(
                    'ID'               => $invoice->ID,
                    'invoice_number'   => sanitize_text_field(get_post_meta($invoice->ID, 'invoice_number', true)),
                    'user'             => $user_info ? $user_info->display_name . ' (' . $user_info->user_email . ')' : 'N/A',
                    'invoice_title'    => $invoice->post_title,
                    'invoice_firm'    => !empty($invoice_firm) ? $invoice_firm['name'] : 'N/A',
                    'invoice_amount'   => $invoice_amount,
                    'invoice_address'   => $invoice_address,
                    'invoice_bank'   => !empty($invoice_bank) ? $invoice_bank : 'N/A' ,
                    'invoice_type'     => sanitize_text_field(ucfirst(get_post_meta($invoice->ID, 'invoice_type', true))),
                    'invoice_status'     => !empty($invoiceStatus) ? ucwords($invoiceStatus) : 'N/A',
                    'invoice_receipt'     => !empty($invoiceReceipt) ? '<a href="#" target="_blank" data-image="'.$invoiceReceipt.'">'.__('View', 'text-domain').'</a>' : 'N/A',
                    'invoice_download'     => '<a href="#" class="admin-wp360invoice_download button wp-element-button bordered-button" data-invoice-id="'.sanitize_text_field(get_post_meta($invoice->ID, 'invoice_number', true)).'">'.__('Download', 'text-domain').'</a>',
                    'action'     => '<a href="'.$new_url.'">Edit</a>',
                );
            }
            return $data;
        }
        function get_columns() {
            return array(
                'cb'                => '<input type="checkbox" />',
                'invoice_number'    => esc_html__('Invoice Number', 'wp360-invoice'),
                'user'              => esc_html__('User', 'wp360-invoice'),
                'invoice_title'     => esc_html__('Invoice Title', 'wp360-invoice'),
                'invoice_firm'     => esc_html__('Firm/Business', 'wp360-invoice'),
                'invoice_amount'    => esc_html__('Amount', 'wp360-invoice'),
                'invoice_address'    => esc_html__('Address', 'wp360-invoice'),
                'invoice_bank'    => esc_html__('Bank Details', 'wp360-invoice'),
                'invoice_type'      => esc_html__('Invoice Type', 'wp360-invoice'),
                'invoice_status'      => esc_html__('Invoice Status', 'wp360-invoice'),
                'invoice_receipt'      => esc_html__('Receipt', 'wp360-invoice'),
                'invoice_download'      => esc_html__('Download PDF', 'wp360-invoice'),
                'action'      => esc_html__('Action', 'wp360-invoice'),
            );
        }
        function get_sortable_columns() {
            return array(
                'invoice_number'   => array('invoice_number', false),
                'user'             => array('user', false),
                'invoice_title'    => array('invoice_title', true),
            );
        }
        function column_default($item, $column_name) {
            return $item[$column_name];
        }
        function column_cb($item) {
            return '<input type="checkbox" name="invoice[]" value="' . esc_html($item['ID']) . '" />';
        }
        function get_bulk_actions() {
            $actions = array(
                'delete' => 'Delete',
            );
            return $actions;
        }
        function process_bulk_action() {
            if (isset($_POST['_wpnonce_bulk_invoice']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce_bulk_invoice'])), 'bulk-invoice-nonce-action')) {
                if ('delete' === $this->current_action()) {
                    $invoices_to_delete = isset($_REQUEST['invoice']) ? array_map( 'absint',$_REQUEST['invoice']) : array();
                    foreach ($invoices_to_delete as $invoiceID) {
                        $post_data = array(
                            'ID'          => $invoiceID,
                            'post_status' => 'deleted_invoice',
                        );
                        wp_update_post($post_data);
                    }
                    echo '<div class="updated"><p>' . esc_html__('Invoices deleted successfully!', 'wp360-invoice') . '</p></div>';
                }
            } elseif (isset($_POST['_wpnonce_bulk_invoice'])) {
                echo '<div class="error"><p>' . esc_html__('Security check failed.', 'wp360-invoice') . '</p></div>';
            }
        }

    }
}
// Usage: Create an instance of your custom list table and display it
function wp360invoice_display_invoices_list_table() {
    $wp360_invoices_list_table = new WP360INVOICE_Invoices_List_Table();
    $wp360_invoices_list_table->process_bulk_action();
    $wp360_invoices_list_table->prepare_items();
    $wp360_invoices_list_table->display();
}

?>
<div class="wrap">
    <?php echo wp_kses_post(wp360invoice_admin_tabs()); ?>
    <h1 class="wp-heading-inline"><?php esc_html_e('wp360 invoices', 'wp360-invoice');?></h1>
    <?php 
        if(isset($_GET['invoice_id']) && !empty($_GET['invoice_id'])):
            require_once'edit_invoice.php';
        else:        
    ?>
    <a href="javascript:;" onclick="wp360toggleCustomFun('.wp360_invoice_toggleNewInvoice')" class="page-title-action"><?php esc_html_e('Add New Invoice', 'wp360-invoice');?></a>
    <div class="wp360_invoice_toggleNewInvoice" style="display:none;">
        <?php require_once'add_invoice.php'; ?>
    </div>
    <?php endif;?>

    <form method="post">
        <?php      
            wp_nonce_field('bulk-invoice-nonce-action', '_wpnonce_bulk_invoice');
            wp360invoice_display_invoices_list_table();
        ?>
    </form>
    <div id="wp360_invoice_receipt_modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <img id="modalImage" src="" alt="Image" />
        </div>
    </div>
</div>

