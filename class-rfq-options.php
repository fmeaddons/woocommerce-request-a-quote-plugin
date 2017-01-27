<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class RFQ_Options {
    
    public $options;
    
    public function __construct( $options = array() ) {
        
        $this->options = $options;
        
        add_action('admin_init', array($this, 'register_rfq_settings'));
        add_action('admin_menu', array($this, 'rfq_create_settings_submenu'), 20);
        add_action('admin_menu', array($this, 'fme_rfq_settings_submenu'), 20);
        
        
    }
    
    public function rfq_create_settings_submenu() {
        //create new top-level menu
        // This page will be under "Settings"
        add_options_page(
                'Request For Quote Settings', 
                'RFQ Settings', 
                'manage_options', 
                'rfq-setting-admin', 
                array($this, 'create_options_page')
        );
    }

    public function fme_rfq_settings_submenu() {
		
		add_submenu_page(
			'manage-quotes',
			'Settings Request for Quote Plugin Menu', 
			'Settings', 
			'manage_options', 
			'rfq-setting-admin', 
			array($this, 'create_options_page')
        );
	}
    
    public function create_options_page() {
		
		$tab_general = (array) get_option('rfq_option_name_general');
        $tab_mail = (array) get_option('rfq_option_name_mail');

        $this->options = array_merge($tab_general, $tab_mail);
        include( plugin_dir_path(__FILE__) . 'options.php' );
    }
    
    
    
    public function register_rfq_settings() { // whitelist options
        
        //register our settings
        register_setting(
            'rfq_option_group_general', // Option group
            'rfq_option_name_general' // Option name
            //array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            'general', // ID
            'General', // Title
            array($this, 'print_section_info'), // Callback
            'rfq-setting-admin' // Page
        );

        

        add_settings_field(
            'btn_title', // ID
            __('Button Text', 'fme-request-for-quote'), // Title 
            function () { 
                
                printf(
                    '<input type="text" style="width: 400px;" id="btn_title" name="rfq_option_name_general[btn_title]" value="%s" />', 
                        isset($this->options['btn_title']) ? esc_attr($this->options['btn_title']) : ''
                );
            }, // Callback
            'rfq-setting-admin', // Page
            'general' // Section           
        );
                
        add_settings_field(
            'next_date', // ID
            sprintf(
                'Number of days <p class="description">(%1$s)</p>', 
                'This will be used to calculate "Quote needed by" for users. Default is 10'
            ), // Title 
            function () { 
                
                printf(
                    '<input type="text" style="width: 400px;" id="next_date" name="rfq_option_name_general[next_date]" value="%s" />', 
                        isset($this->options['next_date']) ? esc_attr($this->options['next_date']) : ''
                );
            }, // Callback
            'rfq-setting-admin', // Page
            'general' // Section           
        );

        add_settings_field(
            'success_msg', 
            sprintf(
                'Success Message <p class="description">(%1$s)</p>', 
                'Will be visible after successfull quote submission on front-end'
            ), 
            array($this, 'success_msg_callback'), //array($this, 'title_callback'), 
            'rfq-setting-admin', 'general'
        );

        register_setting(
            'rfq_option_group_mail', // Option group
            'rfq_option_name_mail' // Option name
            //array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            'mail', // ID
            'Mail', // Title
            array($this, 'print_section_info_mail'), // Callback
            'rfq-setting-admin-mail' // Page
        );

        add_settings_field(
            'sender_name', // ID
            sprintf(
                'Sender Name <p class="description">(%1$s)</p>', 
                'Sender Name and Email, will be used when replying to individual customer query'
            ), // Title 
            array($this, 'sender_name_callback'), // Callback
            'rfq-setting-admin-mail', // Page
            'mail' // Section           
        );

        add_settings_field(
            'sender_email', // ID
            sprintf(
                'Sender Email <p class="description">(%1$s)</p>', 
                'Sender Name and Email, will be used when replying to individual customer query'
            ), // Title 
            array($this, 'sender_email_callback'), // Callback
            'rfq-setting-admin-mail', // Page
            'mail' // Section           
        );


        add_settings_field(
            'sender_subject', // ID
            'Subject', // Title 
            array($this, 'sender_subject_callback'), // Callback
            'rfq-setting-admin-mail', // Page
            'mail' // Section           
        );

        add_settings_field(
            'sender_response', // ID
            'Response Text', // Title 
            array($this, 'sender_response_callback'), // Callback
            'rfq-setting-admin-mail', // Page
            'mail' // Section           
        );
        
        // products
        
        register_setting(
            'rfq_option_group_products', // Option group
            'rfq_option_name_products' // Option name
            //array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            'products', // ID
            'Products', // Title
            array($this, 'print_section_info_products'), // Callback
            'rfq-setting-admin-products' // Page
        );

        add_settings_field(
            'products_search_str', // ID
            sprintf(
                'Products <p class="description">(%1$s)</p>', 
                'Choose products you want to enable "Add to Quote" on'
            ), // Title 
            array($this, 'search_str_callback'), // Callback
            'rfq-setting-admin-products', // Page
            'products' // Section           
        );
        
        add_settings_field(
            'products', // ID
            sprintf(
                'Products <p class="description">(%1$s)</p>', 
                'Selected produts only'
            ), // Title 
            array($this, 'product_ids_callback'), // Callback
            'rfq-setting-admin-products', // Page
            'products' // Section           
        );
        
    }

    public function sender_name_callback() {
        printf(
            '<input type="text" style="width: 400px;" id="sender_name" name="rfq_option_name_mail[sender_name]" value="%s" />', isset($this->options['sender_name']) ? esc_attr($this->options['sender_name']) : ''
        );
    }

    public function sender_email_callback() {
        printf(
            '<input type="text" style="width: 400px;" id="sender_email" name="rfq_option_name_mail[sender_email]" value="%s" />', isset($this->options['sender_email']) ? esc_attr($this->options['sender_email']) : ''
        );
    }

    public function sender_subject_callback() {
        printf(
            '<input type="text" style="width: 400px;" id="sender_subject" name="rfq_option_name_mail[sender_subject]" value="%s" />', isset($this->options['sender_subject']) ? esc_attr($this->options['sender_subject']) : ''
        );
    }

    public function sender_response_callback() {
        printf(
            '<textarea name="rfq_option_name_mail[sender_response]" style="resize:vertical; width: 400px; height:200px;">%s</textarea>', isset($this->options['sender_response']) ? esc_attr($this->options['sender_response']) : ''
        );
    }

    public function search_str_callback() {
        printf(
            '<input type="text" style="width: 400px;" id="search_str" name="rfq_option_name_products[search_str]" value="%s" />
            <div id="se_search_element_id"></div>',
            isset($this->options['search_str']) ? esc_attr($this->options['search_str']) : ''
        );
    }
    
    public function product_ids_callback() {
        printf(
            '<textarea style="width: 400px;" id="product_ids" name="rfq_option_name_products[product_ids]">%s</textarea>', isset($this->options['product_ids']) ? esc_attr($this->options['product_ids']) : ''
        );
    }
    
    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input) {
        $new_input = array();
        if (isset($input['id_number']))
            $new_input['id_number'] = absint($input['id_number']);

        if (isset($input['modal_title']))
            $new_input['modal_title'] = sanitize_text_field($input['modal_title']);

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info() {
        print 'Enter your settings below:';
    }

    /**
     * Print the Section text
     */
    public function print_section_info_mail() {
        print 'Enter your settings below:';
    }

    /**
     * Print the Section text
     */
    public function print_section_info_products() {
        print 'Choose products below:';
    }
    /**
     * Get the settings option array and print one of its values
     */
    public function modal_title_callback() {
        printf(
            '<input type="text" style="width: 400px;" id="modal_title" name="rfq_option_name_general[modal_title]" value="%s" />', isset($this->options['modal_title']) ? esc_attr($this->options['modal_title']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function success_msg_callback() {
        printf(
            '<textarea name="rfq_option_name_general[success_msg]" style="resize:vertical; width: 400px;">%s</textarea>', isset($this->options['success_msg']) ? esc_attr($this->options['success_msg']) : ''
        );
    }
}
