<?php 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
add_style();
datepicker_script();
jquery_validator();

$date = date(get_option('date_format')); //the_date('', '', '',false);

$date_next = $date ." +" . get_plugin_options( 'rfq_option_name_general', 'next_date', 10 ) ." day"; 

if ( isset( $_POST['action'] ) && $_POST['action'] == 'save_rfq' ) {
        
    $quote_db = new Db_Quotes;
    unset( $_POST['action'] );

    $data = esc_sql ( $_POST );
    
    $data['name'] = $data['customer_name'];
    unset($data['customer_name']);
    $result = $quote_db->save( $data ); 
    
    $quote_db->send_mail($data['email'], get_plugin_options('rfq_option_name_mail', 'sender_subject'), get_plugin_options('rfq_option_name_mail', 'sender_response'));
    
    if ( $result->success ) {
        echo '<p style="color:green;" >'. get_plugin_options('rfq_option_name_general', 'success_msg') .'</p>';
    } else {
        echo '<p style="color:red;" >'. __('Failed! Unable to process', 'fme-request-for-quote') .'</p>';
    }
}
?>

<div>
    
    <form class="rfq_form_wrap" id="rfq_form" method="post">

        <div class="row">
			<div class="left">
				<label for="name">Name: <abbr title="required" class="required">*</abbr></label>
			</div>
			<div class="right">
				<input type="text" id="name"  name="customer_name" size="30" required/>
			</div>	
        </div>

        <div class="row validate-required ">
			<div class="left">
				<label for="email">Email: <abbr title="required" class="required">*</abbr></label>
			</div>
			<div class="right">
				<input type="text" size="30" id="email" name="email"/>
			</div>
        </div>

        <div class="row">
			<div class="left">
				<label for="company">Company: </label>
			</div>
			<div class="right">
				<input type="text" id="company" name="company" size="30" />
			</div>
        </div>

        <div class="row">
			<div class="left">
				<label for="phone">Phone: </label>
			</div>
			<div class="right">
				<input type="text" id="phone" name="phone" size="30" />
			</div>
        </div>

        <div class="row">
			<div class="left">
				<label for="quote_needed_by">Quote needed by: </label>
			</div>
			<div class="right">
				<input size="10" type="text" id="quote_needed_by" name="quote_needed_by" value="<?php echo get_date($date_next, 'Y-m-d'  )?>"/>
			</div>
        </div>

        <div class="row">
			<div class="left">
				<label for="request_text">Brief Overview: <abbr title="required" class="required">*</abbr></label>
			</div>
			<div class="right">
				<textarea class="request_text" id="request_text" name="request_text" required></textarea>
			</div>
        </div>

        <div class="row">
            <input name="action" type="hidden" value="save_rfq" />
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('form-nonce') ?>" />          
            <input type="submit" value="Submit" />
        </div>
    </form>
</div>
<script type="text/javascript">
            
    function IsEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }        

    jQuery(document).ready(function ($) {

        $('#quote_needed_by').datepicker({
            dateFormat : 'yy-mm-dd',
            setDate: '<?php echo get_date($date_next, 'Y-m-d'  )?>'
        });
        
         $.validator.addMethod("validEmail", function(value, element) {
            // allow any non-whitespace characters as the host part
            return this.optional( element ) || /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@(?:\S{1,63})$/.test( value );
        }, 'Please enter a valid email address.');
        
        $( "#rfq_form" ).validate({
			errorElement: 'div',
            rules: {
                email: {
                    required: true,
                    validEmail: true
                }
            }
        });
        
    });
</script>
