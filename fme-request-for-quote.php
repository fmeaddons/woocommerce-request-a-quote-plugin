<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * Check if WooCommerce is active
 * */
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	echo 'This plugin required woocommerce installed';
    exit;
}

/*
  Plugin Name: Request For Quote
  Plugin URI:  https://www.fmeaddons.com/woocommerce-plugins-extensions/request-for-quote.html
  Description: Woocommerce request a quote plugin allows your customers to quickly ask for a price estimate / quotation for your products.
  Version:     1.0.1
  Author:      FMEAddons
  Author URI:  http://fmeaddons.com
  License:     GPL2
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Domain Path: /languages
 */
require plugin_dir_path(__FILE__) .'functions.php';

if (!class_exists('Db_Quote')) {
    
    require_once( plugin_dir_path(__FILE__) . 'class-db-quotes.php' );
}

class FME_Request_For_Quote {
    
    public function __construct() {
        $this->module_constants();
        load_plugin_textdomain('fme-request-for-quote', false, basename(dirname(__FILE__)) . '/languages');
        // Hook into the 'init' action
        //add_action('init', array($this, 'request_for_quote'), 0);

        //add_action('init', array($this, 'remove_cart_btn'), 0);

        add_action('wp_print_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_save_rfq', array($this, 'save_rfq'));
        add_action('wp_ajax_nopriv_save_rfq', array($this, 'save_rfq'));
        
        // add button below add-to-cart button  for detail and list page for products
        add_action('woocommerce_after_shop_loop_item', array($this, 'rfq_form'), 20);
        //add_action('woocommerce_after_add_to_cart_button', array($this, 'rfq_form'), 20);
        add_action('woocommerce_product_meta_start', array($this, 'rfq_form'), 20);

        //add_filter( 'page_template', array( $this, 'add_page_template' ) );
        add_shortcode( 'fme-rfq-page', array( $this, 'add_page_template' ) );
        
        if (is_admin()) {
            require_once( plugin_dir_path(__FILE__) . 'class-admin-quotes.php' );
        }
        
        register_activation_hook(__FILE__, array( $this, 'rfq_install') );
        register_activation_hook( __FILE__, array($this, 'fme_add_rfq_page' ) ); 
        add_option( "rfq_db_version", "1.0.0" );
        
        add_filter( 'woocommerce_is_purchasable', array( $this, 'wpa_rfq_is_purchasable'), 10, 2 );
        add_action( 'woocommerce_before_single_product_summary', array($this, 'addlink'));
    }
    
    public function module_constants() {
            
        if ( !defined( 'FMERFQ_URL' ) )
            define( 'FMERFQ_URL', plugin_dir_url( __FILE__ ) );

        if ( !defined( 'FMERFQ_BASENAME' ) )
            define( 'FMERFQ_BASENAME', plugin_basename( __FILE__ ) );

        if ( ! defined( 'FMERFQ_PLUGIN_DIR' ) )
            define( 'FMERFQ_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
    }
    
    

    public function fme_add_rfq_page() {
        global $wpdb;

        $option_value = get_option( 'fme_rfq_page_id' );

        if ( $option_value > 0 && get_post( $option_value ) )
          return;

        $page_found = $wpdb->get_var( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_name` = 'fme_rfq' LIMIT 1;" );
        if ( $page_found ) :
            if ( ! $option_value )
                update_option( 'fme_rfq_page_id', $page_found );
            return;
        endif;

        $page_data = array(
            'post_status'     => 'publish',
            'post_type'     => 'page',
            'post_author'     => 1,
            'post_name'     => esc_sql( _x( 'request-for-quote', 'page_slug', 'fme-request-for-quote' ) ),
            'post_title'    => __( 'Request For Quote', 'fme-request-for-quote' ),
            'post_content'    => '[fme-rfq-page]',
            'post_parent'     => 0,
            'comment_status'  => 'closed'
        );

        $page_id = wp_insert_post( $page_data );

        update_option( 'fme_rfq_page_id', $page_id );
    }


    function addlink() { ?>
                <div ><p style="
            color: #9b9b9b;
            cursor: auto;
            font-family: Roboto,helvetica,arial,sans-serif;
            font-size: 2px;
            font-weight: 400;
            margin-top: 116px;
            padding-left: 150px;
            position: absolute;
            z-index: -1;
        ">by <a style="color: #9b9b9b;" rel="nofollow" target="_Blank" href="https://www.fmeaddons.com/woocommerce-plugins-extensions/request-for-quote.html">Fmeaddons</a></p>  </div>
            <?php }
        

    public function get_price( $type = 'regular' ) {

        $price = get_post_meta( get_the_ID(), '_regular_price', true);

        if ( $type == 'sale' ) {
            $price = get_post_meta( get_the_ID(), '_price', true);
        }

        return $price;
    }   

    public function wpa_rfq_is_purchasable( $purchasable, $product ){

        if( $this->get_price() == '0' ) {

            // if ( ! property_exists($product, 'sale_price' ) ) {
            //     return;
            // }

            add_filter( 'woocommerce_variable_free_price_html',  function() { return ''; } );
 
            add_filter( 'woocommerce_free_price_html',           function() { return ''; } );
             
            add_filter( 'woocommerce_variation_free_price_html', function() { return ''; } );
 
            $purchasable = false;
            
        }
        return $purchasable;
    }
//
//
    public function add_page_template( $page_template ) {
        
        //if ( is_page( 'request-for-quote' ) ) {
            
            require dirname( __FILE__ ) . '/includes/rfq-page-template.php';
        //}
        
        //return $page_template;
    }
//    
//    public function remove_cart_btn() {
//        
//        if (get_plugin_options('rfq_option_name_general', 'show_cart_btn') == 2) {
//            
//            remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
//            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
//        }
//    }

    //allow redirection, even if my theme starts to send output to the browser
    function do_output_buffer() {
        ob_start();
    }

    
    public function enqueue_scripts() {
        // Your actual AJAX script
        wp_enqueue_script('jquery');
        // This will localize the link for the ajax url to your 'my-script' js file (above). You can retreive it in 'script.js' with 'myAjax.ajaxurl'
        wp_localize_script('rfq-script', 'rfqAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
    }

    public function save_rfq() {

        global $wpdb;

        $nonce = $_POST['nonce'];
        if (!wp_verify_nonce($nonce, 'form-nonce')) {
            wp_die('Security check');
        }

        $table_name = $wpdb->prefix . 'fme_rfq';

        $data = array(
            'name' =>  sanitize_text_field($_POST['name']),
            'replied' => false,
            'email' => sanitize_text_field($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'company' => sanitize_text_field($_POST['company']),
            'product_id' => intval($_POST['product_id']),
            'request_text' => esc_textarea($_POST['request_text']),
            'quote_needed_by' => sanitize_text_field($_POST['quote_needed_by']),
            'created' => current_time('mysql'),
        );

        $affected_rows = $wpdb->insert($table_name, $data);

        if (!$affected_rows) {

            echo __('Could not insert record!');

            if (WP_DEBUG) {

                echo '<br/>';
                echo __($wpdb->last_error);
            }
        } else {

            echo $this->get_plugin_options( 'success_msg', __( 'Your request has been submitted.' ) );
            $this->send_mail($data['email'], $this->get_plugin_options('sender_subject'), $this->get_plugin_options('sender_response'));
            // send email after submit
        }

        wp_die();
    }

    public function send_mail($to, $subject, $content, $headers = '' ) {
        
        send_email($to, $subject, $content, $headers);
        
        return true;
    }

    public function rfq_form() {

        //$product = new WC_Product( get_the_ID() ); 
        if ($this->get_price() == '0') {
            
            require plugin_dir_path(__FILE__) . '/includes/ask_for_quote.php';
        }
    }

    public function plugin_options_validate($input) {
        
        $options = get_option('plugin_options');
        $options['text_string'] = trim($input['text_string']);
        if(!preg_match('/^[a-z0-9]{32}$/i', $options['text_string'])) {
            $options['text_string'] = '';
        }
        return $options;
    }

    public function get_plugin_options( $key, $value = false ) {
        
        $options = get_option( 'rfq_option_name_general' );
        
        if ( (!isset($options[$key]) || $options[$key] == '') && $value ) {
            return $value;
        }
        
        return $options[$key];
    }
    
    public function rfq_install() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . "fme_rfq";

        $sql = "CREATE TABLE $table_name (
            `id` mediumint(9) NOT NULL AUTO_INCREMENT,
            `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `replied` smallint(6) DEFAULT NULL,
            `name` tinytext NOT NULL,
            `email` tinytext NOT NULL,
            `company` tinytext NOT NULL,
            `phone` tinytext NOT NULL,
            `request_text` text NOT NULL,
            `url` varchar(55) NOT NULL DEFAULT '',
            `product_id` mediumint(9) DEFAULT NULL,
            `updated` datetime DEFAULT NULL,
            `quote_needed_by` date DEFAULT NULL,
            `item_status` smallint(6) DEFAULT '1',
            PRIMARY KEY (`id`),
            UNIQUE KEY `id` (`id`)
          ) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }

}

new FME_Request_For_Quote();
