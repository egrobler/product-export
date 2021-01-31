<?php
/*
Plugin Name: Products Export
Plugin URI: http://www.yourwebsitename.com/visit_plugin_website
Description: Products Export plugin
Author: John Doe
Author URI: http://www.yourwebsitename.com/plugin_by
Version: 1.0.0
*/

function products_export_create_menu_entry()
{

    add_menu_page(
        'Products Export',
        'Products Export',
        'edit_posts',
        'main-page-products-export',
        'products_export_show_main_page',
        plugins_url('/images/empy-plugin-icon-16.png', __FILE__)
    );
}

add_action('admin_menu', 'products_export_create_menu_entry');

function products_export_show_main_page()
{
    $csvArray = array(
        array(
            'date',
            'treatments/bookings',
            'client',
            'therapist',
            'invoice/ordernr',
            'travel fees',
            'order total',
            'therapist amount (60%)',
            'agent amount (30%)',
            'HEAL amount (10%)',
            'total therapist amount',
        )
    );

    $args = array(
        'type' => 'shop_order',
        'date_created' => '2020-03-25...2020-06-24',
    );
    $orders = wc_get_orders( $args );

    foreach ($orders as $order) {
    
        // populate array
        $products = "";
        $i = 1;
        foreach ( $order->get_items() as $item_id => $item ) {
            $name = $item->get_name();
            $total = $item->get_total();
            
            $products .= $name . " | " . $total;
            
            if (($order->get_item_count() > 1) && ($i < $order->get_item_count())) {
                $products .= " || ";
            }

            $i++;
        }

        // callout fee
        $calloutfee = 0;

        foreach( $order->get_items('fee') as $item_id => $item_fee ){
            // The fee name
            $fee_name = $item_fee->get_name();
            if ($fee_name == 'Call out fee') {
                $calloutfee = $item_fee->get_amount();
            }
        }
        
        $csvArray[] = array(
            $order->get_date_created()->format('Y-m-d'),
            $products,
            $order->get_billing_first_name() . " " . $order->get_billing_last_name(),
            '--therapist--',
            $order->get_id(),
            $calloutfee,
            $order->get_total(),
            ($order->get_subtotal() * 0.6),
            ($order->get_subtotal() * 0.3),
            ($order->get_subtotal() * 0.1),
            (($order->get_subtotal() * 0.6) + $calloutfee)
        );

    }
    
    $fp = fopen('text.csv', 'w');
    // echo print_r($orders);
    foreach($csvArray as $fields) {
        fputcsv($fp, $fields, ";");
    }
}
