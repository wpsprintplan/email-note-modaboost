<?php
// AJAX handler to process the order.
add_action('wp_ajax_process_order', 'process_order');
add_action('wp_ajax_nopriv_process_order', 'process_order');

function process_order() {
    if (isset($_POST['order_id'])) {
        $order_id = intval($_POST['order_id']);

        // Update the post meta with the value 'Yes'.
        update_post_meta($order_id, '_process', 'Yes');

        // You can return a response if needed.
        echo 'success';
    }
    wp_die();
}


// Add the "Process" column to the WooCommerce orders list.
function add_process_column($columns) {
    $columns['process'] = 'Process';
    return $columns;
}
add_filter('manage_edit-shop_order_columns', 'add_process_column');

// Display the switch button in the "Process" column.
function display_process_column($column, $post_id) {
    if ($column === 'process') {
    	$order = wc_get_order($post_id);
   	    $order_status = $order->get_status();
	    if($order_status == "on-hold"){
	    	// Get the current value of the "Process" meta field.
	        $process = get_post_meta($post_id, '_process', true);
	        $class = ($process == "Yes") ? "processed" : "";
	        $label = ($process == "Yes") ? "PROCESSED" : "PROCESS";
	        // Output the switch button.
	        echo '<a href="#" class="process-order '.$class.'" data-order-id="' . $post_id . '">'.$label.'</a>';
	    }

        
    }
}
add_action('manage_shop_order_posts_custom_column', 'display_process_column', 10, 2);


function admin_footer_script(){
	?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
		    // When the "Process" button is clicked.
		    $('.process-order').on('click', function(e) {
		        e.preventDefault();
		        var orderId = $(this).data('order-id');
		        var button = $(this);
		        // Make an AJAX request to update the post meta.
		        $.ajax({
		            type: 'POST',
		            url: ajaxurl, // WordPress AJAX URL
		            data: {
		                action: 'process_order',
		                order_id: orderId,
		            },
		            success: function(response) {
		                // Update the button text and color.
		                if (response === 'success') {
		                    button.text('Processed');
		                    button.addClass('processed'); // Change to your desired color
		                }
		            },
		        });
		    });
		});

	</script>
	<?php
}
add_action('admin_footer', 'admin_footer_script');

function admin_header_css(){
	?>
	<style type="text/css">
		/* Style for the "Process" button */
		.process-order {
		    color: #FFFFFF;
		    margin: -45px 0px -20px 0px;
		    padding: 6px 11px 6px 11px;
		    background-color: #06B3C8;
		    border-style: solid;
		    border-width: 3px 3px 3px 3px;
		    border-color: #FFFFFF00;
		    border-radius: 26px 26px 26px 26px;
		}

		.process-order:hover {
		    color: #000000;
		    background-color: #67FBFF91;
		}

		/* Style for the "Processed" button */
		.process-order.processed {
		    color: #000000;
		    background-color: #67FBFF91;
		}

	</style>
	<?php
}
add_action('admin_head', 'admin_header_css');