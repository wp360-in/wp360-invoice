<?php
function wp360invoice_admin_tabs(){
    $items = [
        [
            'label' => 'All Invoices',
            'page'  => 'wp360_invoice',
            'tab'   => ''
        ],
        [
            'label' => 'Settings',
            'page'  => 'wp360_invoice',
            'tab'   => 'settings'
        ],
    ];

    $res = '<div class="wp360_invoice_admin_tabs"><ul>';

    foreach($items as $item){
        $active = '';

        // Check if the 'page' parameter matches
        if (isset($_GET['page']) && $_GET['page'] === $item['page']) {
            // Check if the 'tab' parameter matches or if it's absent when it should be
            if ((!isset($_GET['tab']) && $item['tab'] === '') || 
                (isset($_GET['tab']) && $_GET['tab'] === $item['tab'])) {
                $active = 'class="active"';
            }
        }

        // Escape dynamic data
        $escaped_label = esc_html($item['label']);
        $escaped_page  = esc_attr($item['page']);
        $escaped_tab   = esc_attr($item['tab']);

        // Construct the URL
        $url = admin_url('admin.php?page=' . $escaped_page);
        if ($escaped_tab !== '') {
            $url .= '&tab=' . $escaped_tab;
        }

        $res .= '
        <li>
            <a href="'. esc_url($url) . '" ' . $active . '>' . $escaped_label . '</a>
        </li>';
    }

    $res .= '</ul></div>';

    return $res;
}
?>
