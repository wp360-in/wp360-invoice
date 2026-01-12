<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function wp360invoice_showInvoice($invoiceID){
    $invoicePost = get_post($invoiceID);
    if ( !$invoicePost || get_post_type($invoiceID) !== 'wp360_invoice' || $invoicePost->post_status !== 'publish' ) {
        esc_html_e('Invoice not found!', 'wp360-invoice');
    }
    if ($invoicePost && get_post_type($invoiceID) === 'wp360_invoice' && $invoicePost->post_status === 'publish'):
        $timestamp      = esc_html(get_post_meta($invoiceID, 'invoice_createddate', true));
        $dateSetting    = esc_html(get_option('date_format'));
        $formatted_date = gmdate($dateSetting, strtotime($timestamp));
        $invoiceAmount  = esc_html(get_post_meta($invoiceID, 'invoice_amount', true));
        $invoiceNumber  = esc_html(get_post_meta($invoiceID, 'invoice_number', true));
        $invoiceUserID  = get_post_meta($invoiceID, 'invoice_user', true);
        $invoiceUser = get_userdata($invoiceUserID);
        $currency = '';
        if (class_exists('WooCommerce')) {
            $currency = get_woocommerce_currency_symbol();
        }
        $userID         = get_current_user_id();
        $userData       = get_userdata($userID);
        $companyEmail          = $userData->user_email;
        $companyPhone = get_user_meta($userID, 'billing_phone', true);

        #Customer Data
        $custName       = esc_html(get_user_meta($invoiceUserID, 'billing_first_name', true)).' '.esc_html(get_user_meta($invoiceUserID, 'billing_last_name', true));        
        $custLine1      = esc_html(get_user_meta($invoiceUserID, 'billing_address_1', true));
        $custLine2      = esc_html(get_user_meta($invoiceUserID, 'billing_address_2', true));
        $custCountry    = esc_html(get_user_meta($invoiceUserID, 'billing_country', true));
        $custCity       = esc_html(get_user_meta($invoiceUserID, 'billing_city', true));
        $custState      = esc_html(get_user_meta($invoiceUserID, 'billing_state', true));
        $custPostCode   = esc_html(get_user_meta($invoiceUserID, 'billing_postcode', true));
        $custPhone   = esc_html(get_user_meta($invoiceUserID, 'billing_phone', true));
        $custEmail   = esc_html(get_userdata($invoiceUserID)->user_email);
        #Customer Data Ends
        $addressParts = [];
        if (!empty($custLine1)) {
            $addressParts[] = $custLine1 . ',';
        }
        if (!empty($custLine2)) {
            $addressParts[] = $custLine2 . ',';
        }

        // Combine city, postcode, state, and country into a single line.
        $locationParts = [];
        if (!empty($custCity)) {
            $locationParts[] = $custCity;
        }
        if (!empty($custPostCode)) {
            $locationParts[] = $custPostCode;
        }
        if (!empty($custState)) {
            $locationParts[] = $custState;
        }
        if (!empty($custCountry)) {
            $locationParts[] = $custCountry;
        }

        // Add location line to address parts, if present.
        if (!empty($locationParts)) {
            $addressParts[] = implode(', ', $locationParts);
        }

        // Format the final address with line breaks.
        $custAddress = wp_kses_post(implode('<br>', $addressParts));

        $invoiceItems   = get_post_meta($invoiceID, 'invoice_items', true);
        $invoicetype    = esc_html(get_post_meta($invoiceID, 'invoice_type', true));
        if($invoicetype == 'fixed'){
            $invoicetype = 'Items';
        }
    ?>         
        <div class="wp360Invoice_sp_wrapper">
            <div class="wp360invpdf_loader"><span class="wp360inv_loader"></span></div>
            <div class="wp360Invoice_action_buttons hidden-print <?php echo !class_exists('WooCommerce') ? ' _justify' : '';?>">
                <?php
                    if (class_exists('WooCommerce')) {
                        echo '<a href="'.get_permalink(get_option('woocommerce_myaccount_page_id')).'" class="wp360invoice_btma"><span class="dashicons dashicons-admin-users"></span> My account</a>';
                    }
                ?>
                <div class="wp360Invoice_action_buttons_wrap">                
                    <input type="hidden" name="wp360invoice_id" value="<?php echo sanitize_text_field(get_post_meta($invoiceID, 'invoice_number', true));?>">
                    <div id="wp360-invoice_printinvoice" data-id="<?php echo esc_html($invoiceID);?>"><?php esc_html_e('&#128462; Download PDF', 'wp360-invoice')?></div>
                    <?php
                        $status = get_post_meta($invoiceID, 'invoice_status', true);
                        $receipt = get_post_meta($invoiceID, 'payment_receipt', true);
                        if(empty($status) || $status === 'unpaid'){
                            echo '<button type="button" class="wp360_invoice_status_update">'.__('Mark as paid', 'text-domain').'</button>';                        
                        }
                        else{
                            if(!empty($receipt)) {
                                echo '<a href="#" target="_blank" class="view_receipt" data-image="'.$receipt.'">'.__('View Receipt', 'text-domain').'</a>';
                            }
                        }
                    ?>
                </div>                
                <div class="wp360Invoice_status">
                    <?php                    
                        $status_text = !empty($status) ? __(ucwords($status), 'text-domain') : __('Unpaid', 'text-domain');
                        echo '<div class="wp360_invoice_status ' . esc_attr($status) . '"><i></i>' . esc_html($status_text) . '</div>';
                    ?>                
                </div>
            </div>

            <div id="receiptPopup" class="modal">
                <div class="modal-content">
                    <form id="receiptForm" method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="wp360invoice_mark_invoice_as_paid">
                        <?php wp_nonce_field('wp360invoice_mark_invoice_paid', 'wp360invoice_mark_invoice_paid_nonce'); ?>
                        <label for="paymentReceipt"><?php _e('Upload Receipt (PDF or JPG):', 'text-domain'); ?></label>
                        <input type="file" id="paymentReceipt" name="paymentReceipt" accept=".pdf, .jpg, .jpeg" required>
                        <input type="hidden" id="invoiceID" name="invoiceID" value="<?php echo $invoiceID; ?>">
                        <button type="submit" class="siteBtn"><?php _e('Submit', 'text-domain'); ?></button>
                        <button type="button" class="closeReceiptModal""><?php _e('Cancel', 'text-domain'); ?></button>
                    </form>
                </div>
            </div>        
            <div class="wp360InvoiceCon">
                <h2><?php esc_html_e('Invoice', 'wp360-invoice');?></h2>
                <div class="invoiceHead">
                    <div class="invoceCompanyInfo">
                        <?php
                            $saved_invoice_firm = get_option('wp360_firm_details', array());
                            $invFirm = get_post_meta($invoiceID, 'invoice_firm', true);
                            if ($saved_invoice_firm && is_array($saved_invoice_firm) && !empty($saved_invoice_firm)) {
                                foreach ($saved_invoice_firm as $index => $firm) {
                                    if (!empty($invFirm) && ($firm['id'] == $invFirm['id'])) {
                                        $invFirm = $firm;
                                    }
                                }
                            }
                            if(!empty($invFirm['text_logo'])){
                                echo '<h4>'.$invFirm['text_logo'].'</h4>';
                            }
                            elseif(!empty($invFirm['logo_url'])){
                                echo '<img src="'.esc_url($invFirm['logo_url']).'">';
                            }
                            ?>
                        <small>
                            <?php 
                                if(!empty($invFirm['tagline'])){
                                    echo $invFirm['tagline'];
                                }
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
                            $saved_company_address = get_post_meta($invoiceID, 'invoice_address', true);
                            if($saved_company_address){
                                echo '<h4>'. esc_html__( 'Address','wp360-invoice' ) .'</h4>';
                                echo '<p class="pre_line">'.wp_kses_post($saved_company_address).'</p>';
                            }
                        ?>                    
                        <!-- <p>
                            <?php
                                if(!empty($companyPhone)){
                                    echo '<b>'.esc_html__( 'Phone :','wp360-invoice' ).'</b>'.esc_html($companyPhone).'<br>';
                                }
                                if(!empty($companyEmail)){
                                    echo '<b>'.esc_html__( 'Email :','wp360-invoice' ).'</b>'.esc_html($companyEmail);
                                }
                            ?>
                        </p> -->
                    </div>
                    <div class="receiptInfo">
                        <h4><?php esc_html_e('Bill To', 'wp360-invoice');?></h4>
                        <p>
                            <?php echo esc_html($custName);?> <br>
                            <?php echo wp_kses_post($custAddress);?>
                        </p>
                        <p>
                            <?php
                                if(!empty($custPhone)){
                                    echo '<b>'.esc_html__( 'Phone :','wp360-invoice' ).'</b>'.esc_html($custPhone).'<br>';
                                }
                                if(!empty($custEmail)){
                                    echo '<b>'.esc_html__( 'Email :','wp360-invoice' ).'</b>'.esc_html($custEmail).'<br>';
                                }
                            ?>
                            <?php
                                $extraFields = get_user_meta($invoiceUserID, 'wp360_invoice_user_extra_fields', true);
                                if(!empty($extraFields)){
                                    foreach ($extraFields as $field) {
                                        if(!empty($field['name'])) echo '<b>' . esc_html($field['name']).' :' . '</b> ';
                                        if(!empty($field['value'])) echo esc_html($field['value']) . '<br>';
                                    }
                                }
                            ?>
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
                    <?php 
                        $invoiceBank = get_post_meta($invoiceID, 'invoice_bank', true);
                        if(!empty($invoiceBank)){ ?>
                            <div class="bankDetail">
                                <h3><?php echo esc_html__('Bank Details', 'text-domain'); ?></h3>
                                <?php echo '<div class="pre_line">' . wp_kses_post($invoiceBank) . '</div>'; ?>
                            </div>
                        <?php }                
                    ?>                 
                    <div class="infoTotalCon">
                        <div class="totalInn1">
                            <strong><?php esc_html_e('Subtotal', 'wp360-invoice');?></strong>
                            <span><?php echo esc_html($currency.$invoiceAmount);?></span>
                        </div>
                        <div class="totalInn1">
                            <strong><?php esc_html_e('Tax', 'wp360-invoice');?></strong>
                            <span><?php echo esc_html($currency);?>0</span>
                        </div>
                        <div class="totalInn1">
                            <strong><?php esc_html_e('Total', 'wp360-invoice');?></strong>
                            <span><?php echo esc_html($currency.$invoiceAmount);?></span>
                        </div>
                        <div class="totalInn2">
                            <strong><?php esc_html_e('Balance due', 'wp360-invoice');?></strong>
                            <span><?php echo esc_html($currency.$invoiceAmount);?></span>
                        </div>
                    </div>
                </div>
                <div class="invoiceFooter2">
                    <h4>Payment Terms</h4>
                    <p>Full payment is due in 3/5 days of this invoice.</p>
                    <?php
                        $thankyoumsg = get_option('wp360_thankyoumsg', '');
                        if($thankyoumsg){
                            echo esc_html($thankyoumsg);
                        }else{
                            esc_html_e('Thank you for giving us chance to serve you.', 'wp360-invoice');
                        }
                    ?> 
                </div>
            </div>
        </div>
        <div id="wp360_invoice_receipt_modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <img id="modalImage" src="" alt="Image" />
            </div>
        </div>
    <?php       
    endif;
}