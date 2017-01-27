<?php
    if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$name = '';
$data = array();
require_once FMERFQ_PLUGIN_DIR .'class-db-quotes.php';


if (isset($_REQUEST['name'])) {
    
    $id = (int) $_REQUEST['name'];
    
    $db = new Db_Quotes();
    
    $data = $db->view_item($id);  
    
}

?>


<div class="wrap">
	
    <?php if ( !empty($data)): ?>
    
        <?php _e(sprintf('<h2>View Quote: %1$s <span style="color:silver">(company:%2$s)</span></h2>', $data->name, $data->company), 'fme-request-for-quote' );  ?>
		
		<?php $this->message_block(); ?>
		
		<table class="form-table">
			
			<tr>
				<th scope="row">
					<label for="name">Name: </label>
				</th>
				<td>
					<?php echo $data->name; ?>
				</td>
			</tr>
			
			<tr>
				<th scope="row">
					<label for="email">Email: </label>
				</th>
				<td>
					<?php echo $data->email; ?>
				</td>
			</tr>
			
			<tr>
				<th scope="row">
					<label for="company">Company: </label>
				</th>
				<td>
					<?php echo $data->company; ?>
				</td>
			</tr>
			
			<tr>
				<th scope="row">
					<label for="phone">Phone: </label>
				</th>
				<td>
					<?php echo $data->phone; ?>
				</td>
			</tr>
			
			<?php if ($data -> product_id > 0): ?>
			<tr>
				<?php 
					$_pf = new WC_Product_Factory();
					$product = $_pf->get_product($data->product_id)->post; 
				?>
				<th scope="row">
					<label for="product">Product: </label>
				</th>
				<td>
					<a href="<?php echo get_permalink($product->ID)?>" target="_blank"><?php echo $product->post_title; ?></a>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<th scope="row">
					<label for="rquest_text">Request Text: </label>
				</th>
				<td>
					<?php echo stripslashes_deep($data->request_text); ?>
				</td>
			</tr>
			
        </table>
        <form id="rfq_view" method="post" action="admin-post.php" enctype='multipart/form-data'>  
				
					<p><?php wp_editor( isset($_POST['postcontent'])? $_POST['postcontent']: '' , 'postcontent' ); ?></p>

					<p>
						<input type="hidden" name="action" value="reply" />
						<input type="hidden" name="nonce" value="<?php echo wp_create_nonce('form-nonce'); ?>" />   
						<input type="hidden" name="id" value="<?php echo $data->id; ?>" />
						<input class="button button-primary" type="submit" value="Reply" />
					</p>
				</form>
</div>  
<script type="text/javascript">


    var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";


    
    

</script>
<?php endif; ?>
