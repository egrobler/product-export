<?php
/*
Plugin Name: Products Export
Plugin URI: http://www.yourwebsitename.com/visit_plugin_website
Description: Products Export plugin
Author: John Doe
Author URI: http://www.yourwebsitename.com/plugin_by
Version: 1.0.0
*/

/**
* Ensure class doesn't already exist
*/
if (!class_exists('ProductExport')) {
    class ProductExport
    {
        /**
         * Start up
         */
        public function __construct()
        {
            $this->options = get_option('eg_product_export_settings');

            add_action('admin_menu', [$this, 'add_plugin_page']);
            add_action('admin_init', [$this, 'page_init']);
            add_action('admin_enqueue_scripts', [$this, 'admin_plugin_styles']);
            add_action('wp_ajax_generate_report', [$this, 'ajaxReport']);
        }

        /**
         * Add options page
         */
        public function add_plugin_page()
        {
            add_menu_page(
                'Products Export',
                'Products Export',
                'edit_posts',
                'main-page-products-export',
                [$this, 'create_admin_page']
            );
        }

        /**
         * Options page callback
         */
        public function create_admin_page()
        {
            // Set class property
            $this->options = get_option('eg_product_export_settings'); ?>
            <div class="wrap eg-product-export">
            <h2>Products Export</h2>
            <form method="post" action="options.php">
            <?php
                settings_fields('eg_product_export_settings_group');
            do_settings_sections('main-page-products-export');
            submit_button('Generate Report', 'delete', 'generate-report', false); ?>
            </form>
            </div>
        <?php
        }

        /**
         * Register and add settings
         */
        public function page_init()
        {
            register_setting(
                'eg_product_export_settings_group', // Option group
                'eg_product_export_settings', // Option name
                [$this, 'sanitize'] // Sanitize
            );

            add_settings_section(
                'eg_product_export_section', // ID
                '', // Title
                [$this, 'print_section_info'], // Callback
                'main-page-products-export' // Page
            );

            add_settings_field(
                'eg_product_export_start_date',
                'Start Date',
                [$this, 'display_date_picker'],
                'main-page-products-export',
                'eg_product_export_section',
                ['id' => 'eg_product_export_start_date']
            );

            add_settings_field(
                'eg_product_export_end_date',
                'End Date',
                [$this, 'display_date_picker'],
                'main-page-products-export',
                'eg_product_export_section',
                ['id' => 'eg_product_export_end_date'] // Page
            );
        }

        /**
         * Sanitize each setting field as needed
         *
         * @param array $input Contains all settings fields as array keys
         */
        public function sanitize($input)
        {
            return $input;
        }

        /**
         * Print the Section text
         */
        public function print_section_info()
        {
            echo '<p>Enter your settings below:';
        }

        public function display_date_picker($args)
        {
            extract($args);
            echo '<input type="date" id="' . $args['id'] . '" name="' . $args['id'] . '" value="" class="eg-product-export-datepicker" />';
        }

        public function admin_plugin_styles()
        {
            wp_enqueue_script('eg-product-export-admin', $this->getBaseUrl() . '/resources/js/product-export-admin-script.js', ['jquery'], '1.0.0', true);
        }

        public function ajaxReport()
        {
            if (isset($_POST['action']) && isset($_POST['endDate']) && isset($_POST['startDate']) && $_POST['action'] === 'generate_report') {
                $url = $this->reportQuery($_POST['startDate'], $_POST['endDate']);

                $output = ['download' => true, 'message' => __('Download Success', '_tk'), 'url' => $url, 'filename' => ''];
            } else {
                $output = ['download' => false, 'message' => __('Download Failed', '_tk')];
            }

            echo json_encode($output);
            exit;
        }

        public function reportQuery($startDate, $endDate)
        {
            $csvArray = [
                [
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
                ]
            ];

            // the date variables go in here below somehow

            $args = [
                'type' => 'shop_order',
                'date_created' => '2020-03-25...2020-06-24',
            ];
            $orders = wc_get_orders($args);

            foreach ($orders as $order) {
                // populate array
                $products = '';
                $i = 1;
                foreach ($order->get_items() as $item_id => $item) {
                    $name = $item->get_name();
                    $total = $item->get_total();

                    $products .= $name . ' | ' . $total;

                    if (($order->get_item_count() > 1) && ($i < $order->get_item_count())) {
                        $products .= ' || ';
                    }

                    $i++;
                }

                // callout fee
                $calloutfee = 0;

                foreach ($order->get_items('fee') as $item_id => $item_fee) {
                    // The fee name
                    $fee_name = $item_fee->get_name();
                    if ($fee_name == 'Call out fee') {
                        $calloutfee = $item_fee->get_amount();
                    }
                }

                $csvArray[] = [
                    $order->get_date_created()->format('Y-m-d'),
                    $products,
                    $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                    '--therapist--',
                    $order->get_id(),
                    $calloutfee,
                    $order->get_total(),
                    ($order->get_subtotal() * 0.6),
                    ($order->get_subtotal() * 0.3),
                    ($order->get_subtotal() * 0.1),
                    (($order->get_subtotal() * 0.6) + $calloutfee)
                ];
            }

            $fp = fopen('text.csv', 'w');
            // echo print_r($orders);
            foreach ($csvArray as $fields) {
                fputcsv($fp, $fields, ';');
            }

            // We need to return something here

            return '';
        }

        //Returns the url of the plugin's root folder
        protected function getBaseUrl()
        {
            return plugins_url(null, __FILE__);
        }

        //Returns the physical path of the plugin's root folder
        protected function getBasePath()
        {
            $folder = basename(dirname(__FILE__));
            return WP_PLUGIN_DIR . '/' . $folder;
        }
    } //End Class

    /**
     * Instantiate this class to ensure the action and shortcode hooks are hooked.
     * This instantiation can only be done once (see it's __construct() to understand why.)
     */
    new ProductExport();
}
