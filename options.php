<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
    add_action('wp_enqueue_scripts', 'se_wp_enqueue_scripts');
    
    function se_wp_enqueue_scripts() {
        
        wp_enqueue_script('suggest');
    }
    
    add_action('wp_head', 'se_wp_head');
    
    function se_wp_head() {
    
        echo '<script type="text/javascript">
            var se_ajax_url = "'. admin_url('admin-ajax.php') .'";
        
            jQuery(document).ready(function($) {
            
                $("#se_search_element_id").suggest(se_ajax_url + "?action=se_lookup");
            });
        </script>';
    
    }
    
    add_action('wp_ajax_se_lookup', 'se_lookup');
    add_action('wp_ajax_nopriv_se_lookup', 'se_lookup');
    
    function se_lookup() {
        
        global $wpdb;
    
        $search = like_escape($_REQUEST['q']);
    
        $query = 'SELECT ID,post_title FROM ' . $wpdb->posts . '
            WHERE post_title LIKE \'' . $search . '%\'
            AND post_type = \'product\'
            AND post_status = \'publish\'
            ORDER BY post_title ASC';
        foreach ($wpdb->get_results($query) as $row) {
            
            $post_title = $row->post_title;
            $id = $row->ID;
    
            //$meta = get_post_meta($id, 'YOUR_METANAME', TRUE);
    
            echo $post_title . ' (' . $id . ')' . "\n";
        }
        die();
    }
?>
<?php
    if( isset( $_GET[ 'tab' ] ) ) {
        $active_tab = $_GET[ 'tab' ];
    } // end if
    
    $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
    
    
?>

<div class="wrap">
    <h2><?php echo _e( 'RFQ Settings' ); ?></h2>
    <?php settings_errors(); ?>
    <h2 class="nav-tab-wrapper">
        <a href="?page=rfq-setting-admin&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php echo _e( 'General' ); ?></a>
        <a href="?page=rfq-setting-admin&tab=mail" class="nav-tab <?php echo $active_tab == 'mail' ? 'nav-tab-active' : ''; ?>"><?php echo _e( 'Mail' ); ?></a>
        
    </h2>
    <form method="post" action="options.php">
        
        <?php if( $active_tab == 'general' ): ?>
        
        <?php  
            settings_fields( 'rfq_option_group_general' );   
            do_settings_sections( 'rfq-setting-admin' );     
        ?>
            
        <?php endif; ?>
        
        <?php if( $active_tab == 'mail' ): ?>
            <div id="mail">
                <?php 
                
                    settings_fields( 'rfq_option_group_mail' );
                    do_settings_sections( 'rfq-setting-admin-mail' );
                ?>
            </div>
        <?php endif; ?>
        
        <?php if( $active_tab == 'products' ): ?>
            <div id="products">
                <?php 
                
                    settings_fields( 'rfq_option_group_products' );
                    do_settings_sections( 'rfq-setting-admin-products' );
                ?>
            </div>
        <?php endif; ?>
        
        <?php submit_button(); ?>
    </form>
    
    <script type="text/javascript">
        
        //jQuery(document).ready(function($){
        //    
        //    var search_str = '#search_str';
        //    
        //    $(search_str).keypress(function() {
        //        
        //        var str = '';
        //        if ($(this).val() != '') {
        //            str = $(this).val();
        //        }
        //        console.log(str);
        //    });
        //});
        
    </script>
</div>
