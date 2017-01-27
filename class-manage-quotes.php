<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if ( !class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if (!class_exists('Db_Quote')) {
    
    require_once( plugin_dir_path(__FILE__) . 'class-db-quotes.php' );
}

class FME_List_Quotes extends WP_List_Table {

    public $list_data;
    public $found_data;
    protected $_per_page = 10;
    protected $_limit = 10;
    protected $table_name;
    protected $notice; 

    protected $db;
    
    public function __construct() {

        global $status, $page, $wpdb;
        $this->table_name = $wpdb->prefix.'fme_rfq';
        $this->db = new Db_Quotes();
        
        parent::__construct( array(
            'singular'  => __( 'name', 'fme-request-for-quote' ),     //singular name of the listed records
            'plural'    => __( 'names', 'fme-request-for-quote' ),   //plural name of the listed records
            'ajax'      => false        //does this table support ajax?

        ) );

        $this->list_data = $this->get_quote($this->_per_page);
        add_action( 'admin_head', array( &$this, 'admin_header' ) );
    }

    public function admin_header() {

        $page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
        if( 'manage-quotes' != $page )
            return; 

        echo '<style type="text/css">';
        echo '.wp-list-table .column-id { width: 5%; }';
        echo '.wp-list-table .column-name { width: 30%; }';
        echo '.wp-list-table .column-company { width: 15%; }';
        echo '.wp-list-table .column-quote_need_by { width: 5%; }';
        echo '</style>';
    }

    public function get_quote(  ) {

        global $wpdb;

        $table_name = $wpdb->prefix.'fme_rfq';

		$item_status = 1;
		
		if ( isset($_REQUEST['item_status']) && $_REQUEST['item_status'] == 'trash' ) {
			$item_status = 3;
		}
		
        if (!isset($wpdb->fme_rfq)) {

            $wpdb->fme_rfq = $wpdb->prefix . 'fme_rfq';
        }

        $sql = "SELECT * FROM {$table_name}";
		
        // where clause
        if ( isset( $_GET['s'] ) && $_GET['s'] != '' ) {

            $search_str = $_GET['s']; 

            $sql .= " WHERE name LIKE '%" . $search_str . "%'";
            $sql .= " OR company LIKE '%" . $search_str . "%'";
            $sql .= " OR request_text LIKE '%" . $search_str . "%'";
			
			$sql .= " AND item_status = $item_status";
			
        } else {
			
			$sql .= " WHERE item_status = $item_status";
		}
		
		

        if(isset($_GET['orderby']) && $_GET['orderby']!='') {
            $orderby = $_GET['orderby'];
        } else {
            $orderby = 'id';
        }

        if(isset($_GET['order']) && $_GET['order']!='') {
            $order = $_GET['order'];
        } else {
            $order = 'DESC';
        }
		
        $sql .= " ORDER BY $orderby $order";

        $results = $wpdb->get_results( $sql, ARRAY_A );
        
        return $results;
    }

    public function get_columns() {

        $columns = array( 
            'cb'            => '<input type="checkbox" />',
            'name'          => __( 'Name', 'fme-request-for-quote' ), 
            //'email'         => __( 'Email', 'fme-request-for-quote' ), 
            'company'       => __( 'Company', 'fme-request-for-quote' ), 
            'product_id'    => __( 'Product', 'fme-request-for-quote' ), 
            'quote_needed_by'    => __( 'Quote Needed By', 'fme-request-for-quote' ), 
            'replied'       => __( sprintf('%1$s <span style="color:silver">(%2$s)</span>', 
                'Replied', 
                'times'
            ), 'fme-request-for-quote' ), 
        );

        return $columns;
    }    

    public function prepare_items() {

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable ); //$this->get_column_info();
        //usort( $this->list_data, array( &$this, 'usort_reorder' ) );
        //$this->items = $this->list_data;

        $per_page = $this->get_items_per_page('names_per_page', 5);
        $current_page = $this->get_pagenum();

        $total_items = count($this->list_data);
        $this->found_data = array_slice( $this->list_data, ( ($current_page-1) * $this->_per_page ), $this->_per_page  );

        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $this->_per_page                     //WE have to determine how many items to show on a page
        ) );

        $this->items = $this->found_data;
    }

	/** Get an associative array ( id => link ) with the list
	 * of views available on this table.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_views() {
		
		$actions = array('all', 'trash');
		$status_links = array();
		
		$all_inner_html = sprintf(
			'All <span class="count">(%d)</span>',
			$this->db->count_items()
		);
		
		$trash_inner_html = sprintf(
			'Trash <span class="count">(%d)</span>',
			$this->db->count_items( 3 )
		);
		
		$class = '';
		
		if (!isset($_REQUEST['item_status'])) {
			$class = ' class="current"';
		}
		
		$status_links['all'] = '<a '. $class .' href="?page=manage-quotes">'. $all_inner_html . '</a>';
		
		$class = '';
		
		if ( isset($_REQUEST['item_status']) && $_REQUEST['item_status'] == 'trash' ) {
			$class = ' class="current"';
		}
		
		$status_links['trash'] = '<a'. $class .' href="?page=manage-quotes&item_status=trash">'. $trash_inner_html . '</a>';
		
		
		
		return $status_links; 
	}
	
	
    public function column_default( $item, $column_name ) {

        switch( $column_name ) { 
            case 'name':
            //case 'email':
            case 'company':
            case 'product_id':
            case 'quote_needed_by':
            case 'replied':
              return $item[ $column_name ];
            default:
              return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
        }
    }

    public function get_sortable_columns() {
		
        $sortable_columns = array(
            'name'          => array('name', false),
            'company'       => array('company', false),
            'quote_needed_by'    => array('quote_needed_by', false),
        );

        return $sortable_columns;
    }

    public function column_product_id( $item ) {
        
        $_pf = new WC_Product_Factory();
        $product = $_pf->get_product($item['product_id'])->post; 
        return sprintf('<a href="%1$s">%2$s<a>', get_permalink($product->ID), $product->post_title);
    }
    
    public function column_replied( $item ) {
        
        $updated = ($item['updated'])? date_i18n( get_option( 'date_format' ), strtotime( $item['updated'] ) ): __('Pending', 'fme-request-for-quote');
        return sprintf( '%1$s <span style="color:silver"> (Last: %2$s) </span>', $item['replied'], $updated );
    }
    
    public function column_quote_needed_by( $item ) {
        
        $quote_needed_by = ($item['quote_needed_by'])? date_i18n( get_option( 'date_format' ), strtotime( $item['quote_needed_by'] ) ): __('Left Empty', 'fme-request-for-quote');
        return $quote_needed_by;
    }
    
    public function column_name($item) {

        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&name=%s">View</a>',$_REQUEST['page'],'view',$item['id']),
            'trash'    	=> sprintf('<a class="submitdelete" href="admin-post.php?action=%s&name=%s">Trash</a>','trash',$item['id']),
        );

		if ( isset($_REQUEST['item_status']) && $_REQUEST['item_status'] == 'trash' ) {
			$actions = array(
				'untrash'	=> sprintf('<a href="admin-post.php?action=%s&name=%s">Restore</a>','untrash',$item['id']),
				'delete'	=> sprintf('<a class="submitdelete" href="?page=%s&action=%s&name=%s&item_status=%s">Delete Permanently</a>',$_REQUEST['page'],'delete',$item['id'],'trash'),
			);
		}
		
        return sprintf(
            '%1$s <span style="color:silver; font-size:11px;">id: %2$s</span><br/><span style="color:silver; font-size:11px;">email: %3$s</span>%4$s', 
            $item['name'], 
            $item['id'], 
            $item['email'], 
            $this->row_actions($actions) 
        );
    }

    public function get_bulk_actions() {

        $actions = array(
            'trash'    => 'Trash'
        );
		
		if ( isset($_REQUEST['item_status']) && $_REQUEST['item_status'] == 'trash' ) {
			
			$actions  = array (
				'untrash' => 'Restore',
				'delete' => 'Delete'
			);
		}
		
        return $actions;
    }

    public function column_cb( $item ) {

        return sprintf(
            '<input type="checkbox" name="name[]" value="%s" />', $item['id']
        );    
    }

    public function no_items() {
        _e( 'No quote(s) found.' );
    }
    
}
