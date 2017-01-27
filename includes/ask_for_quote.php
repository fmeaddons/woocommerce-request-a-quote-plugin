<?php 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$product_id = get_the_ID();
add_style();

datepicker_script();
jquery_validator();
$date = date(get_option('date_format'));//the_date('', '', '',false);

$date_next = $date .  " +" . get_plugin_options('rfq_option_name_general', 'next_date', 10) ." day";
$success_msg = get_plugin_options('rfq_option_name_general','success_msg');
?>

<a id="btn_<?php echo $product_id ?>" class="button add_to_cart_button" data-quantity="1" data-product_sku="" data-product_id="<?php echo $product_id ?>" rel="nofollow" href="#rfq_form_<?php echo $product_id ?>"><?php echo get_btn_text(); ?></a>

    <!-- The Modal -->
    <div id="model_<?php echo $product_id ?>" class="modal">

      <!-- Modal content -->
      <div class="modal-content">
        <span class="close">×</span>
        

        <form class="rfq_form_wrap" id="rfq_form_<?php echo $product_id ?>">

                
                <p id="rfq_success_text<?php echo $product_id ?>" style="display:none; color: green" ><?php echo esc_html__($success_msg); ?></p>
                
                <div class="row">
                    <div class="left">
                        <label for="name">Name: <abbr title="required" class="required">*</abbr></label>
                    </div>
                    <div class="right">
                        <input type="text" id="name<?php echo $product_id ?>" name="name" required  />
                        <p id="rfq_error_name<?php echo $product_id ?>" class="required" style="display:none" >Enter your name</p>
                    </div>  
                </div>
                <div class="row">
                    <div class="left">
                        <label for="email">Email: <abbr title="required" class="required">*</abbr></label>
                    </div>
                    <div class="right">
                        <input type="email" id="email<?php echo $product_id ?>"  class="required email" required name="email"  />
                        <p id="rfq_error_email<?php echo $product_id ?>" class="required" style="display:none" >Enter your email address</p>
                        <p id="rfq_error_vemail<?php echo $product_id ?>" class="required" style="display:none" >Enter a valid email address</p>
                    </div>
                </div>
                <div class="row">
                    <div class="left">
                        <label for="company">Company: </label>
                    </div>
                    <div class="left">
                        <input type="text" id="company<?php echo $product_id ?>" name="company"  />
                    </div>
                </div>

                <div class="row">
                    <div class="left">
                        <label for="phone">Phone: </label>
                    </div>
                    <div class="right">
                        <input type="text" id="phone<?php echo $product_id ?>" name="phone" />
                    </div>
                </div>

                <div class="row">
                    <div class="left">
                        <label for="quote_needed_by">Quote needed by: </label>
                    </div>
                    <div class="left">
                        <input size="10" type="text" id="quote_needed_by<?php echo $product_id ?>" name="quote_needed_by" value="<?php echo get_date( $date_next, 'Y-m-d'  )?>"/>
                    </div>  
                </div>
                
                <div class="row">
                    <div class="left">
                        <label for="request_text">Brief Overview: <abbr title="required" class="required">*</abbr></label>
                    </div>  
                    <div class="right">
                        <textarea class="request_text" id="request_text<?php echo $product_id ?>" required name="request_text"></textarea>
                        <p id="rfq_error_text<?php echo $product_id ?>" class="required" style="display:none" >Enter breif overview</p>
                    </div>
                </div>

                <div class="row">
                    <input type="hidden" name="product_id" value="<?php echo $product_id ?>"/>
                    <input name="action" type="hidden" value="save_rfq" />
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('form-nonce') ?>" />          
                    <input type="button" value="Submit" onclick="subForm()" />
                </div>
            </form>


      </div>

    </div>

        <script type="text/javascript">
            
            var form_id = "#rfq_form_<?php echo $product_id ?>";
            var ajaxurl = "<?php echo admin_url( 'admin-ajax.php'); ?>";
            
            jQuery(document).ready(function ($) {
                
                $('#quote_needed_by<?php echo $product_id ?>').datepicker({
                    dateFormat : 'yy-mm-dd',
                    setDate: '<?php echo get_date($date_next, 'Y-m-d'  )?>'
                }); 


                
            });

            function subForm() {

                var name  = jQuery('#name<?php echo $product_id ?>').val();
                var email  = jQuery('#email<?php echo $product_id ?>').val();
                var request_text  = jQuery('#request_text<?php echo $product_id ?>').val();

                if(name == '') {
                    jQuery('#rfq_error_name<?php echo $product_id ?>').show();
                    return false;
                    
                } else if(email == '') {
                    if(name!='') {
                        jQuery('#rfq_error_name<?php echo $product_id ?>').hide();
                    }
                    jQuery('#rfq_error_email<?php echo $product_id ?>').show();
                    return false;
                    
                } else if(!validateEmail(email)) {

                    if(email!='') {
                        jQuery('#rfq_error_email<?php echo $product_id ?>').hide();
                    }
                    jQuery('#rfq_error_vemail<?php echo $product_id ?>').show();
                    return false;

                } else if(request_text == '') {
                    if(email!='') {
                        jQuery('#rfq_error_email<?php echo $product_id ?>').hide();
                    }
                    if(validateEmail(email)) {
                        jQuery('#rfq_error_vemail<?php echo $product_id ?>').hide();
                    }
                    jQuery('#rfq_error_text<?php echo $product_id ?>').show();
                    return false;
                    
                } else {

                    jQuery.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: jQuery(form_id).serialize(),
                        success: function (data) {
                            jQuery(form_id)[0].reset();
                            jQuery('#rfq_error_name<?php echo $product_id ?>').hide();
                            jQuery('#rfq_error_email<?php echo $product_id ?>').hide();
                            jQuery('#rfq_error_text<?php echo $product_id ?>').hide();
                            jQuery('#rfq_error_vemail<?php echo $product_id ?>').hide();

                            jQuery('#rfq_success_text<?php echo $product_id ?>').show();
                        }
                    });
                }
            }

            function validateEmail(email) {
                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(email);
            }

        </script>


        <script>
        // Get the modal
        var modal = document.getElementById('model_<?php echo $product_id ?>');

        // Get the button that opens the modal
        var btn = document.getElementById("btn_<?php echo $product_id ?>");

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks the button, open the modal 
        btn.onclick = function() {
            modal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
        </script>
