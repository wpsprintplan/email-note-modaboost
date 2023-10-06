<?php
/**
 * Plugin Name: Paypal Rotatational emails
 * Plugin URI: https://profiles.wordpress.org/iqbal1486/
 * Description: Manage The Email Address Note for paypal payments
 * Version: 2.0.0
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * Author: Geekerhub
 * Author URI: https://profiles.wordpress.org/iqbal1486/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 **/

global $jal_db_version, $wpdb;
$jal_db_version = '1.0';

define('EN_TABLE_NAME', $wpdb->prefix . 'email_notes');
function email_note_init(){
    include_once('simple_html_dom.php');
    include_once('zapier.php');
    include_once('admin/admin.php');
    include_once('public/paypal_transfer_payment_instructions.php');
	include_once('admin/processed.php');
}
add_action('plugins_loaded', 'email_note_init');

function jal_install() {
	global $wpdb;
	global $jal_db_version;

	$table_name = EN_TABLE_NAME;
	
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE $table_name (
			  	`id` int(10) NOT NULL AUTO_INCREMENT,
			  	`email` text NOT NULL,
			  	`notes` text NOT NULL,
			  	`status` text NOT NULL,
				`time` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			  PRIMARY KEY (`id`)
			) $charset_collate;";


	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	add_option( 'jal_db_version', $jal_db_version );
}
register_activation_hook( __FILE__, 'jal_install' );

/***************************************************************/
/***************************************************************/
/***************************************************************/

//Custom Paypal 1
function display_custom_paypal_transfer_payment_instructions( $order ) {
    global $wpdb;
    if ( $order->get_payment_method() !== 'custom_paypal_transfer' || ($order->get_status() !== 'pending' && $order->get_status() !== 'abandoned') ) {
        return;
    }
    
    $sql = "SELECT `email` FROM ".EN_TABLE_NAME." WHERE `status` = 'active'";
	$email_notes = $wpdb->get_results($sql, ARRAY_A);
	$paypal_handles = array();
	if(!empty($email_notes)){
		foreach($email_notes as $key => $value){
			$paypal_handles[] = $value['email'];
		}	
	}
		
    $order_total = $order->get_total();

    ///JUST COMMENT THIS ARRAY WHENEVER YOU NEED TO USE ACTIVE EMAIL ADDRESS FROM ADMIN SIDE
    /*
    $paypal_handles = array(
        'honecklanz7@hotmail.com',
        'cofrangummerr@hotmail.com',
        'wittigrongg@hotmail.com',
        'gronerabbottp@hotmail.com',
        'cabezaperaza9@hotmail.com',
        'decouxlaperg@hotmail.com',
        'chiernadingk@hotmail.com',
    );
    */
    $fallback_handle = 'cookeybir1@hotmail.com';

    $paypal_handle = $order->get_meta('paypal_handle');

    if (!$paypal_handle) {
        $handle_index = get_option('paypal_handle_index', 0);

        if ($handle_index >= 0 && $handle_index < count($paypal_handles)) {
            $paypal_handle = $paypal_handles[$handle_index];
            update_option('paypal_handle_index', ($handle_index + 1) % count($paypal_handles));
        } else {
            $paypal_handle = $fallback_handle;
            update_option('paypal_handle_index', 0);
        }

        $order->update_meta_data('paypal_handle', $paypal_handle);
        $order->save_meta_data();
    }
    ?>
    <style>
        .paypal-payment-instructions {
            background-color: #d3fffc;
            padding: 20px;
        }
        .paypal-payment-instructions p strong {
            font-weight: bold;
        }
    </style>
    <div class="paypal-payment-instructions">
        <p>
            To be transferred: <strong><?php echo $order_total; ?> USD</strong><br>
            Transferred on: <a href="https://www.paypal.com" style="text-decoration: underline;">https://www.paypal.com</a><br>
            Our paypal handle: <strong id="paypal-handle"><?php echo $paypal_handle; ?></strong> <button id="copy-paypal-handle" style="cursor: pointer;">Copy to Clipboard</button><br><br>
            ⚠️ Select <strong>"Sending to a family/friend"</strong> when making the payment instead of "Paying for an item or service" option.
        <form id="additional-service-form" method="post">
            <input type="checkbox" id="additional_service" name="additional_service" value="1" <?php checked($order->get_meta('_additional_service'), 'yes'); ?>>
            <label for="additional_service"> (+$20) Send by <strong>"Services & Goods"</strong> instead.</label>
            <input type="hidden" name="order_id" value="<?php echo $order->get_id(); ?>">
            <?php wp_nonce_field('additional_service_action', 'additional_service_nonce'); ?>
        </form><br> 
        <div><p>
            📢 Please refrain from adding any notes to your transfer. Payment will be automatically matched to your order through the order amount and time-frame.<br> <br>
            ✅ After payment, expect a confirmation email from ModaBoost within 1 to 6 hours. This process isn't automated, so we appreciate your patience.
		</p></div>
    </div>

	<script>
    document.getElementById('copy-paypal-handle').addEventListener('click', function() {
        var text = document.getElementById('paypal-handle').textContent;
        navigator.clipboard.writeText(text).then(function() {
            alert('Paypal handle copied to clipboard');
        }).catch(function(err) {
            alert('Failed to copy text: ', err);
        });
    });
   document.getElementById('additional_service').addEventListener('change', function() {
        document.getElementById('additional-service-form').submit();
    });
	</script>
    <?php
}
add_action( 'woocommerce_order_details_before_order_table', 'display_custom_paypal_transfer_payment_instructions', 10, 1 );



function modaboost_ajax_delete_action(){
    global $wpdb;
    $ids = implode(',', $_POST['deleteids_arr']);
    $sql = "DELETE FROM ".EN_TABLE_NAME." WHERE id IN ($ids)";
    $response = $wpdb->query($sql);
    wp_send_json_success( $response );
}
add_action( 'wp_ajax_modaboost_mass_delete', 'modaboost_ajax_delete_action' );

function moodaboost_ajax_action_callback() {
    global $wpdb;
    $response = array();
    if ( isset( $_POST['checkboxvalue'] ) ) {
        $checkboxvalue  = $_POST['checkboxvalue'];
        $update_id      = absint( $_POST['id'] );
        $status = "active";
        if($checkboxvalue == "true"){
            $status = "inactive";
        }

        $sql = "UPDATE ".EN_TABLE_NAME." SET status='$status' WHERE id=$update_id";
        //echo $sql;
        $response = $wpdb->query($sql);

        wp_send_json_success( $response );
    }
}
add_action( 'wp_ajax_moodaboost_ajax_action', 'moodaboost_ajax_action_callback' );
add_action( 'wp_ajax_nopriv_moodaboost_ajax_action', 'moodaboost_ajax_action_callback' );
