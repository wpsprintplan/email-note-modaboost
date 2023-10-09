<?php 
function paypal_transfer_payment_instruction_post_submission(){
	if(isset($_POST['save_instruction'])){
		update_option('modaboost_payment_link', $_POST['payment_link']);
	}
}
add_action('init', 'paypal_transfer_payment_instruction_post_submission');

/**
 * Adds a submenu page under a custom post type parent.
 */
function email_plugin_register_submenu_for_paypal_instructions() {
    add_submenu_page(
        'email-note-plugin',
        __( 'Paypal 1', 'textdomain' ),
        __( 'Paypal 1', 'textdomain' ),
        'manage_options',
        'paypal-instructions',
        'paypal_instructions_callback'
    );
}

/**
 * Display callback for the submenu page.
 */
function paypal_instructions_callback() {
	$payment_link = get_option('modaboost_payment_link');
    ?>
    <div class="row">
	    <div class="column width-30">
	        <h2>Payment Instructions</h2>
	        <form method="POST">
	            <table class="form-table email-form-table">
	                <tbody>
	                    <tr valign="top">
	                        <th scope="row" class="titledesc">
	                            <label for="woocommerce_store_address">Payment Link</label>
	                        </th>
	                        <td class="forminp forminp-text">
	                            <input name="payment_link" 
	                                id="email" 
	                                type="url"  
	                                value="<?php echo $payment_link; ?>" 
	                                required 
	                                style="width: 100%;"
	                                placeholder="https://www.paypal.com/paypalme/majutv">
	                       </td>
	                    </tr>
	                </tbody>
	            </table>

	            <p class="submit">
	                <button name="save_instruction" class="button-primary woocommerce-save-button" type="submit" value="save">Save</button>
	            </p>
	        </form>
	    </div>
	</div>
    <?php
}
add_action('admin_menu', 'email_plugin_register_submenu_for_paypal_instructions');


// MB Paypal Transfer payment instruction on the order review page
function email_plugin_display_paypal_payment_instructions( $order ) {
    if ( $order->get_payment_method() === 'modaboost_paypal_transfer' && ($order->get_status() === 'pending' || $order->get_status() === 'abandoned') ) {
        // Get the order total
        $order_total = $order->get_total();
        $payment_link = get_option('modaboost_payment_link');
        echo '<style>
            .MB_paypal-transfer-instructions {
                background-color: #d3fffc; 
                padding: 20px;
            }
            .MB_paypal-transfer-instructions p strong {
                font-weight: bold;
            }
        </style>
        <div class="MB_paypal-transfer-instructions">
            <p>
                <strong>Total: </strong>' . $order_total . ' USD<br>
                <strong>Paypal Link: </strong><a href="'.$payment_link.'" style="text-decoration: underline;">'.$payment_link.'</a><br>
                <br> 
                📢 Please refrain from adding any notes to your transfer. Payment will be automatically matched to your order through the order amount and time-frame.<br> <br>
                ✅ After payment, expect a confirmation email from ModaBoost within 1 to 6 hours. This process isn\'t automated, so we appreciate your patience. 
            </p>
        </div>';
    }
}
add_action( 'woocommerce_order_details_before_order_table', 'email_plugin_display_paypal_payment_instructions', 10, 1 );
?>