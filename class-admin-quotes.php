<?php
session_start();
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if (!class_exists('Db_Quote')) {

    require_once( FMERFQ_PLUGIN_DIR . 'class-db-quotes.php' );
}
if (!class_exists('RFQ_Options')) { 
	require_once( FMERFQ_PLUGIN_DIR . 'class-rfq-options.php' );
}
	
class Admin_Quotes {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options, $quotes, $notice, $db;
    private $form_title = 'Manage Quote(s)';

    public function __construct() {

        $this->db = new Db_Quotes();
		
		$this->options = new RFQ_Options;
		add_action('admin_init', array( $this, 'rfq_admin_init' ) );
		add_action('admin_menu', array($this, 'fme_menu'));
		
        //add_action( 'admin_notices', array( $this, 'rfq_messages' ) );
		//add_action( 'wp_loaded', array($this, 'manage_plugin') );
		
        add_filter('set-screen-option', array($this, 'quote_table_set_option', 10, 3));
        
    }

	public function rfq_admin_init() {
        
		//checking for form submission when new hardware is added
		add_action( 'admin_post_untrash', array( $this, 'manage_plugin' ) );
        add_action( 'admin_post_trash', array( $this, 'manage_plugin' ) );
        add_action( 'admin_post_reply', array( $this, 'manage_plugin' ) );
	}
	
	public function rfq_messages() {
		
		$this->message_block();
	}

    public function fme_menu() {
		
		$hook = add_menu_page(
				'Request For Quote',
                'RFQ', 
                'manage_options', 
                'manage-quotes', 
                array($this, 'manage_plugin_list'), 
                FMERFQ_URL.'assets/imgs/fma.jpg',
				60
        );

        add_action("load-$hook", array($this, 'add_list_options'));
	}

    public function manage_plugin($action = '') {
     

        if ($action == '') {
            echo $action = $_REQUEST['action'];
        }

        switch ($action) {

            case 'list':
					$this->manage_plugin_list();
                break;
            case 'delete':
					
					$result = $this->delete_item( $_REQUEST['name'] );
					$_SESSION['rfq'] = $result;
					
                    $this->header_redirect( array( 'rfq' => urlencode( implode( ',', (array)$result ) ) ) );
					
				break;
			case 'trash':
				
					$result = $this->trash( $_REQUEST['name'] );
					
                    $_SESSION['rfq'] = $result;
                    
					$this->header_redirect( array( 'rfq' => urlencode( implode( ',', (array)$result ) ) ) );
					
				break;
			case 'untrash':
				
					$result = $this->db->untrash( $_REQUEST['name'] ); 
                    $_SESSION['rfq'] = $result;
                    $this->header_redirect( array( 'rfq' => urlencode( implode( ',', (array)$result ) ) ) );
				break;
            case 'view':
                    $this->get_view_quote();
                break;
            case 'reply':
                //global $wp;
                //$current_url = home_url(add_query_arg(array(),$wp->request));

					
					if ($_REQUEST['postcontent'] == '') {
						
						$obj = array( 'success' => 0, 'msg' => 'Text must not be empty!');
						
						
						//$_SESSION['rfq'] = $obj;
						$this->header_redirect( array( 'rfq' => urlencode( implode( ',', $obj ) ) ) , $_SERVER["HTTP_REFERER"]);
					}
					
					$result = $this->send_email($_REQUEST);
					if (!$result->success) {
						
						echo $this->generate_status('error', $result->msg);
						$this->get_view_quote();
					} else {
						
						$_SESSION['rfq'] = $result;

                        $_REQUEST['rfq']->msg .= " Email sent!";
                        
                        $this->header_redirect();
					}
                
                break;
            default:
                $this->manage_plugin_list();
        }
    }

    public function get_view_quote() {
        require plugin_dir_path(__FILE__) . '/includes/view_quote.php';
    }

    public function get_quote($id) {
        return $this->db->view_item($id);
    }

    public function delete_item( $id ) {
        echo $id;
        return $this->db->delete($id);
    }
    
	public function trash( $id ) {

        return $this->db->trash( $id );
    }
	
    public function manage_plugin_list( $message = '' ) {
        
        if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'view' ) {

            return $this->manage_plugin( $_REQUEST['action'] );
        }
        
        echo '<div class="wrap"><h2>' . __($this->form_title) . '</h2>';
		
		$this->message_block();
		
        $this->quotes->views();
		$this->quotes->prepare_items();
        echo '<div id="icon-users" class="icon32"><br/></div>';
        echo '<form method="post" action="admin-post.php">
                <input type="hidden" name="page" value="' . $_REQUEST['page'] . '" />';
        $this->quotes->search_box('search', 'search_id');
        $this->quotes->display();
        echo '</form></div>';
        unset($_SESSION['rfq']);
    }

    

    public function get_plugin_options($section, $key, $value = false) {

        return get_plugin_options($section, $key, $value);
    }

    public function add_list_options() {

        $option = 'per_page';
        $args = array(
            'label' => 'Names',
            'default' => 10,
            'option' => 'names_per_page'
        );

        add_screen_option($option, $args);

        $file = plugin_dir_path(__FILE__) . 'class-manage-quotes.php';
        if (!file_exists($file)) {

            $error = new WP_Error('broke', __('The file is not found!', 'fme-request-for-quote'));
            echo $error->get_error_message();
            return;
        }

        include $file;
        $this->quotes = new FME_List_Quotes();
    }

    public function quote_table_set_option($status, $option, $value) {

        return $value;
    }

    public function generate_status( $class, $msg ) {
        
        return generate_status( $class, $msg );
    }

    public function send_email($data) {

        $id = isset($data['id']) ? $data['id'] : 0;
        $quote = $this->get_quote($id);

        // settings section
        $section = 'rfq_option_name_mail';
        // prepare header info
        //$headers = 'Content-type: text/html;charset=utf-8' . "\r\n";
        $headers = 'From: ' . $this->get_plugin_options($section, 'sender_name') . ' <' . $this->get_plugin_options($section, 'sender_email') . '>' . "\r\n";

        /*$this->db->send_mail(
            $quote->email, 
            $this->get_plugin_options($section, 'sender_subject'), 
            esc_textarea($data['postcontent']), 
            $headers
        );*/
        
        send_email(
            $quote->email, 
            $this->get_plugin_options($section, 'sender_subject') . "\r\n",
            esc_textarea( stripslashes( $data['postcontent'] ) ),
            $headers
        );

        $result = $this->db->save(
            array(
                'replied'   => $quote->replied + 1,
                'nonce'     => $data['nonce'],
                'updated'   => current_time('mysql')
        ), $id);

        
        return $result;
    }

    public function message_block() { 
		
		if (!isset( $_SESSION['rfq'] )) {
			
			if ( isset( $_REQUEST['rfq'] ) && $_REQUEST['rfq']['msg'] != '' ) { 
				
				list($success, $msg) = explode( ',', $_REQUEST['rfq'] );
				
				$class = ($success)? 'updated': 'error';
				
				echo $this->generate_status( $class, $msg );
			}
		}
		
        if ( isset( $_SESSION['rfq'] ) && $_SESSION['rfq']->msg != '' ) { 
            $class = ($_SESSION['rfq']->success)? 'updated': 'error';
            echo $this->generate_status( $class, $_SESSION['rfq']->msg );
        }
    }

    public function header_redirect( $arg = null, $url = '' ) {
		
		if ($url == '') {
			$url = admin_url() . 'admin.php?page=manage-quotes';//add_query_arg( (array) $result, admin_url() . '/admin.php?page=manage-quotes' );
		}
		
		if ($arg != null) {
			$url = add_query_arg( $arg, $url );
		}
        
        
        wp_redirect( $url );
        exit();
    }
}

new Admin_Quotes();
