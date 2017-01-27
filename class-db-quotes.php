<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Db_Quotes {

    protected $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix.'fme_rfq';
    }

	public function count_items( $status = 1 ) {
		
		global $wpdb;
		$items_count = 0;
		
		$items_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $this->table_name WHERE item_status = %d", (int) $status ) );
		
		
		return $items_count;
	}
	
    public function view_item( $id, $format = OBJECT ) {

        global $wpdb;
        $sql = 'SELECT * FROM ' . $this->table_name . ' WHERE id = ' . $id;
        $result = $wpdb->get_row($sql, $format);

        return $result;
    }

    public function save( $data, $id = 0 ) {

        global $wpdb;
        
        $success = false;
        $msg = '';
        
        $nonce = $data['nonce'];
        
        if (!wp_verify_nonce($nonce, 'form-nonce')) {
            wp_die('Security check!');
        }
        
        unset($data['nonce']);
        
        if ( $id > 0 ) {
            
           $affected_rows = $wpdb->update( $this->table_name, $data, array( 'id' => $id ) );   
           $success = true;
           $msg = 'Item has been successfully updated!';
        } else {
            $affected_rows = $wpdb->insert( $this->table_name, $data );
            $success = true;
            $msg = 'Saved item!';
        }

        if (!$affected_rows) {

            $success = false;
            $msg = 'Query error!';//$wpdb->last_error;            
        } 
        
        return (object) array( 'success' => $success, 'msg' => __( $msg, 'fme-request-for-quote' ) );
    }

	public function trash( $id ) {
		
		global $wpdb;
		$status = 3; // trash
		
		$msg = '';
		
		if (is_array($id)) {
            $id = implode( ',', $id );
        }
		
		$result = $wpdb->query(
            'UPDATE  '. $this->table_name . ' SET item_status = '. $status .' WHERE id IN ('. $id .')'  
        );
        
        if ( false === $result ) {

            $error = new WP_Error( 'broke', __( 'Trash operation failed', 'fme-request-for-quote' ) );
            $msg = $error->get_error_message();
            $status = false;
        } else if ( 0 === $result ) {

            $error = new WP_Error( 'broke', __( 'No record found with the ID(s): '. $id, 'fme-request-for-quote' ) );
            $msg = $error->get_error_message();
            $status = false;
        } else {
			
			$msg = sprintf( '(%d) Item(s) moved to trash', $result );
		}
        
		if ($msg == '') {
			$msg = __( 'Unknown Error', 'fem-request-for-quote' );
		}
		
        return (object) array('success' => $status, 'msg' => $msg);
	}
	
	public function untrash( $id ) {
		
		global $wpdb;
		$status = 1; // all|untrash
		
		$msg = '';
		
		if (is_array($id)) {
            $id = implode( ',', $id );
        }
		
		$result = $wpdb->query(
            'UPDATE  '. $this->table_name . ' SET item_status = '. $status .' WHERE id IN ('. $id .')'  
        );
        
        if ( false === $result ) {

            $error = new WP_Error( 'broke', __( 'Restore item(s) operation failed', 'fme-request-for-quote' ) );
            $msg = $error->get_error_message();
            $status = false;
        } else if ( 0 === $result ) {

            $error = new WP_Error( 'broke', __( 'No record found with the ID(s): '. $id, 'fme-request-for-quote' ) );
            $msg = $error->get_error_message();
            $status = false;
        } else {
			
			$msg = sprintf( '(%d) Item(s) restored', $result );
		}
        
		if ($msg == '') {
			$msg = __( 'Unknown Error', 'fem-request-for-quote' );
		}
		
        return (object) array('success' => $status, 'msg' => $msg);
	}
	
    public function delete( $id ) {
        
        global $wpdb;
        
        $status = true;
        $msg = __( 'Item deleted successfully!', 'fme-request-for-quote' );
        
        if (is_array($id)) {
            $id = implode( ',', $id );
        }
        
        $result = $wpdb->query(
            'DELETE FROM '. $this->table_name . ' WHERE id IN ('. $id .')'  
        );
        
        if ( false === $result ) {

            $error = new WP_Error( 'broke', __( 'Delete operation failed', 'fme-request-for-quote' ) );
            $msg = $error->get_error_message();
            $status = false;
        }

        if ( 0 === $result ) {

            $error = new WP_Error( 'broke', __( 'No record found with the ID(s): '. $id, 'fme-request-for-quote' ) );
            $msg = $error->get_error_message();
            $status = false;
        }
        
        return (object) array('success' => $status, 'msg' => $msg);
    }
    
    public function send_mail($to, $subject, $content, $headers = '') {
        
        send_email($to, $subject, $content);
		return true;
    }

    public function set_html_content_type() {
        return 'text/html';
    }

    public function get_plugin_options( $section, $key, $value = false ) {
        
        return get_plugin_options($section, $key, $value);
    }
}
