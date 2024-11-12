<?php ob_start(); ?>
<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
        <title>Invoice</title>
        <style>
            body {
                color: #525252;
                font-family: "Work Sans", sans-serif;
                font-optical-sizing: auto;
                font-weight: 400;
                font-style: normal;
            }
            h1, h2, h3, h4, h5, h6, th{
                font-weight: 500;
                font-size: 15px;                
            }
            h1, h2, h3, h4, h5, h6 {
                margin-bottom: 5px;          
            }
            table {
                width: 100%;
                border-collapse: collapse;
                word-wrap: break-word;           
                table-layout: fixed; 
                font-size: 13px;
                text-align: left;
            }
            table th{
                background-color: #e4eaee;
            }
            b{
                font-weight: 600;
            }
            p {
                margin: 0 0 5px;
            }
            table tr{
                vertical-align: text-top;
            }
            table td, table th{
                padding: 12px 20px;
            }
            .invoice-header,
            .invoice-summary,
            .invoice-footer,
            .invoice-footer2 {
                margin: 20px 0;
            }
            .invoice_bottom table{
                margin: 0px;
            }       
            .invoice-header td {
                padding: 5px;
            }
            .invoice-header .logo {
                text-align: left;
            }
            .logo img{
                width: 100px;
                height: auto;
                max-width: 100%;
            }
            .logo small{
                display: block;
            }
            small{
                font-size: 13px;
            }
            .invoice-header .invoice-number {
                text-align: right;
            }
            .invoice-details td, .invoice-details th{
                padding: 0px;
            }  
            .invoice-summary {
                border: 1px solid #ddd;            
            }
            .invoice-summary td {
                border-right: 1px solid #ddd;            
            }
            .invoice-summary th {
                background-color: #e4eaee;            
                text-align: left;
                border-right: none;
                border-left: none;
            }
            .bank-detail,
            .total-summary {
                margin-top: 20px;
            }
            .bank-detail td {
                border: none;
            }
            .total-summary .total-row th{
                padding: 5px 0px;
            }
            .total-summary .total-row td {
                text-align: center;
                padding: 5px 0px;
                font-size: 15px;
            }
            .total-summary .total-row:last-child{
                border-top: 2px solid;
            }
            .total-summary .total-row strong {
                font-size: 1.2em;
            }
            .payment-terms {
                margin-top: 20px;
            }
            .pre_line {
                white-space: pre-line;
            }
        </style>
    </head>
    <body>
        <?php
            $autoload_path = ABSPATH . 'wp-content/plugins/' . WP360_SLUG . '/dompdf/autoload.inc.php';
            require_once($autoload_path);
            use Dompdf\Dompdf;
            use Dompdf\Options;
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isFontSubsettingEnabled', true);
            $options->setIsHtml5ParserEnabled(true);
            $tmp = sys_get_temp_dir();            
            $dompdf = new Dompdf($options, ['fontDir' => $tmp,
            'fontCache' => $tmp,
            'tempDir' => $tmp,
            'chroot' => $tmp,]);                
        ?>   
    <?php
        $invoiceID = isset($_POST['invoice_data']) ? absint($_POST['invoice_data']) : 0;
        $timestamp      = esc_html(get_post_meta($invoiceID, 'invoice_createddate', true));
        $dateSetting    = esc_html(get_option('date_format'));
        $formatted_date = gmdate($dateSetting, strtotime($timestamp));
        $invoiceAmount  = esc_html(get_post_meta($invoiceID, 'invoice_amount', true));
        $invoiceNumber  = esc_html(get_post_meta($invoiceID, 'invoice_number', true));
        $invoiceUserID  = get_post_meta($invoiceID, 'invoice_user', true);
        $invoiceUser = get_userdata($invoiceUserID);
        $currency       = get_woocommerce_currency_symbol();
        $userID         = get_current_user_id();
        $userData       = get_userdata($userID);
        $companyEmail          = $userData->user_email;
        $companyPhone = get_user_meta($userID, 'billing_phone', true);

        #Customer Data
        $custName       = esc_html(get_user_meta($invoiceUserID, 'billing_first_name', true)).' '.esc_html(get_user_meta($userID, 'billing_last_name', true));        
        $custLine1      = esc_html(get_user_meta($invoiceUserID, 'billing_address_1', true));
        $custLine2      = esc_html(get_user_meta($invoiceUserID, 'billing_address_2', true));
        $custCountry    = esc_html(get_user_meta($invoiceUserID, 'billing_country', true));
        $custCity       = esc_html(get_user_meta($invoiceUserID, 'billing_city', true));
        $custState      = esc_html(get_user_meta($invoiceUserID, 'billing_state', true));
        $custPostCode   = esc_html(get_user_meta($invoiceUserID, 'billing_postcode', true));
        $custPhone   = esc_html(get_user_meta($invoiceUserID, 'billing_phone', true));
        $custEmail   = esc_html(get_user_meta($invoiceUserID, 'user_email', true));
        #Customer Data Ends

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
        $custAddress = $custLine1.' <br> '.$custCity.', '.$custPostCode.', '.$custState.', '.$custCountry;
        $invoiceItems   = get_post_meta($invoiceID, 'invoice_items', true);
        $invoicetype    = esc_html(get_post_meta($invoiceID, 'invoice_type', true));
        if($invoicetype == 'fixed'){
            $invoicetype = 'Items';
        }
    ?>    
        <table class="invoice-header">
            <tbody>                        
                <tr>
                    <td class="logo">
                        <?php
                            $firm = get_post_meta($invoiceID, 'invoice_firm', true);
                            if(!empty($firm['text_logo'])){
                                echo '<h4>'.$firm['text_logo'].'</h4>';
                            }
                            elseif(!empty($firm['logo_url'])){
                                echo '<img src="'.esc_url($firm['logo_url']).'">';
                            }
                            ?>
                        <small>
                            <?php 
                                if(!empty($firm['tagline'])){
                                    echo $firm['tagline'];
                                }
                            ?>
                        </small>
                    </td>
                    <td class="invoice-number">
                        <p>
                            <?php esc_html_e('Invoice No.', 'wp360-invoice');?> <span>#<?php echo esc_html($invoiceNumber);?></span> <br>
                            <?php echo esc_html($formatted_date);?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="invoice-details">
            <tr>
                <td>
                    <?php
                        $saved_company_address = get_post_meta($invoiceID, 'invoice_address', true);
                        if($saved_company_address){
                            echo '<h4>'. __( 'Address','wp360-invoice' ) .'</h4>';
                            echo '<p class="pre_line">'.wp_kses_post($saved_company_address).'</p>';
                        }
                    ?>
                </td>
                <td></td>
                <td style="text-align: right;">
                    <div style="display: inline-block; text-align: left;">
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
                                    if(!empty($companyEmail)){
                                        echo '<b>'.esc_html__( 'Email :','wp360-invoice' ).'</b>'.esc_html($companyEmail).'<br>';
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
                </td>
            </tr>
        </table>

        <table class="invoice-summary">
            <thead>
                <tr>
                    <th style="width: 45%;"><?php echo __('Service Description', 'wp360-invoice');?></th>
                    <th><?php echo __('Price', 'wp360-invoice');?></th>
                    <th><?php echo __(ucfirst($invoicetype)); ?></th>
                    <th><?php echo __('Subtotal', 'wp360-invoice');?></th>
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

        <table class="invoice_bottom">
            <tr>
            <?php
                $invoiceBank = get_post_meta($invoiceID, 'invoice_bank', true);
                if(!empty($invoiceBank)) { ?>
                    <td style="padding: 0px; width: 60%; border: 1px solid #ddd;">
                        <table class="bank-detail" style="margin: 0px;">
                            <tr>
                                <th style="background-color: #e4eaee;">
                                    <h3 style="text-align: left;margin: 0px;"><?php echo esc_html__('Bank Details', 'text-domain'); ?></h3>                        
                                </th>                    
                            </tr>
                            <tr>
                                <td>
                                    <?php echo '<div class="pre_line">' . wp_kses_post($invoiceBank) . '</div>'; ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                <?php }
                    else {
                        echo '<td style="padding: 0px;"></td>';
                } ?>
                <td style="padding-top: 0px;padding-bottom: 0px;">
                    <table class="total-summary" style="margin: 0px;">
                        <tr class="total-row">
                            <td><b><?php echo __('Subtotal', 'wp360-invoice');?></b></td>
                            <td><?php echo __($currency.$invoiceAmount);?></td>
                        </tr>
                        <tr class="total-row">
                            <td><b><?php echo __('Tax', 'wp360-invoice');?></b></td>
                            <td><?php echo __($currency);?>0</td>
                        </tr>
                        <tr class="total-row">
                            <td><b><?php echo __('Total', 'wp360-invoice');?></b></td>
                            <td><?php echo __($currency.$invoiceAmount);?></td>
                        </tr>
                        <tr class="total-row">
                            <td><b><?php echo __('Balance Due', 'wp360-invoice');?></b></td>
                            <td><?php echo __($currency.$invoiceAmount);?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>   

        <table class="payment-terms" style="padding: 0px;margin-top: 50px;">
            <tr>
                <td style="padding: 0px;">
                    <h4 style="margin: 0px;">Payment Terms</h4>
                    <p>Full payment is due in 3/5 days of this invoice.</p>
                    <?php
                        $thankyoumsg = get_option('wp360_thankyoumsg', '');
                        if($thankyoumsg){
                            echo '<p style="margin: 0px;">'.esc_html($thankyoumsg).'</p>';
                        }else{
                            echo '<p style="margin: 0px;">'.__('Thank you for giving us chance to serve you.', 'wp360-invoice').'</p>';
                        }
                    ?> 
                </td>
            </tr>
        </table>
    </body>
</html>
<?php
    // Get the content and clean the output buffer
    $html = ob_get_clean();

    // Load HTML to Dompdf
    $dompdf->loadHtml($html);

    // Set paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    // Render the PDF
    $dompdf->render();
    $pdf_output = $dompdf->output();

    // Send PDF as binary response for download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="invoice.pdf"');
    echo $pdf_output;
?>