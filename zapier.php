<?php

add_action( 'rest_api_init', 'modaboost_zapier_endpoint' );
 
function modaboost_zapier_endpoint(){
    register_rest_route(
        'wp/v2',
        'main/zapier',
        array(
            'methods' => 'POST',
            'callback' => 'zapier_webhook_callback',
        )
    );
}
 
function zapier_webhook_callback() {
 		global $wpdb;
        $logFile = 'zapier_webhook_2.log';

        // Get the raw JSON data from the POST request
        $rawData = file_get_contents("php://input");
        $jsonData = json_decode($rawData, true);
        if ($jsonData === null) {
            http_response_code(400); // Bad Request
            exit("Invalid JSON data");
        }
        //file_put_contents($logFile, print_r($jsonData, true), FILE_APPEND);
        //file_put_contents($logFile, print_r($jsonData, true));
        
        $email          = $jsonData['to_email_address'];
        $total_amount   = trim(modaboost_get_amount($jsonData['body_content']));
        $total_fee      = trim(modaboost_get_fee($jsonData['body_content']));

        file_put_contents($logFile, print_r("Total Amount ".$total_amount."<--->", true), FILE_APPEND);
        file_put_contents($logFile, print_r("Total Fee ".$total_fee."<--->", true), FILE_APPEND);
        file_put_contents($logFile, print_r("To Email ".$email."<---->\n", true), FILE_APPEND);
        
        modaboost_change_status_of_email_from_active_to_inactive($email);
        modaboost_change_status_of_order($email, $total_amount);

        // Send a response if required
        http_response_code(200); // OK
        echo "Webhook data received successfully";
}

function modaboost_change_status_of_order( $email = "", $total_amount = 0 ){
    $logFile = 'zapier_webhook_2.log';
    $args = array(
        'numberposts' => -1,
        'post_type'   => 'shop_order',
        'post_status' => 'any', 
        'meta_query'  => array(
            'relation' => 'AND',
            array(
                'key'     => 'paypal_handle', 
                'value'   => $email,
                'compare' => '='
            ),
            array(
                'key'     => '_order_total', 
                'value'   => $total_amount,
                'compare' => '='
            ),
        ),
    );

    $orders = get_posts($args);

    if (!empty($orders)) {
        $new_status = 'zapier-payment';

        foreach ($orders as $order) {
            $order_id = $order->ID;
            
            $order = wc_get_order($order_id);
            $current_status = $order->get_status();

            if ($current_status === $new_status) {
                // Order status is already "zapier-payment," no need to update
                //echo "Order already has the status $new_status for Order ID: $order_id<br>";
                file_put_contents($logFile, print_r("Order already has the status $new_status for Order ID: $order_id<br>\n", true), FILE_APPEND);
            } else {
                $result = $order->update_status($new_status);
                if (is_wp_error($result)) {
                    echo 'Error: ' . $result->get_error_message() . "<br>";
                } else {
                    //echo "Order status has been successfully updated to $new_status for Order ID: $order_id<br>";
                    file_put_contents($logFile, print_r("Order status has been successfully updated to $new_status for Order ID: $order_id<br>\n", true), FILE_APPEND);
                }
            }
        }
    } else {
        //echo "No orders found matching the criteria.";
        file_put_contents($logFile, print_r("No orders found matching the criteria.\n", true), FILE_APPEND);

    }
}


function modaboost_change_status_of_email_from_active_to_inactive($email = ""){
        global $wpdb;
        $sql = "SELECT * FROM ".EN_TABLE_NAME." WHERE email = '$email'";
        $result = $wpdb->get_row($sql, ARRAY_A);
   
        $id = $result['id'];
        if ( $id > 0 && !empty($id) ) {
            $new_status = 'inactive';
            $update_sql = $wpdb->prepare("UPDATE ".EN_TABLE_NAME." SET status = %s WHERE id = %d", $new_status, $id);
            $wpdb->query($update_sql);
        }
       else{
            echo " not true";
        }
}

function modaboost_get_amount($rawData = ""){
    // Load the HTML content
    $html = str_get_html($rawData);

    // Define the regex pattern to search for "Amount received"
    $pattern = '/Amount received/';
    // Find all text nodes in the HTML content
    foreach ($html->find('text') as $textNode) {
        $text = trim($textNode->plaintext);

        // Check if the text matches the pattern
        if (preg_match($pattern, $text)) {
            // Get the parent <td> element
            $parentTd = $textNode->parent()->parent()->parent();

            // Output the HTML of the parent <td> element
            //echo $parentTd->outertext;
            $parsed = modaboost_get_string_between($parentTd->outertext, '$', '&nbsp;USD');
            return trim($parsed);
            break; // Exit the loop after the first match
        }
    }
}

function modaboost_get_fee($rawData = ""){
    // Load the HTML content
    $html = str_get_html($rawData);

    // Define the regex pattern to search for "Amount received"
    $pattern = '/Fee/';
    // Find all text nodes in the HTML content
    foreach ($html->find('text') as $textNode) {
        $text = trim($textNode->plaintext);

        // Check if the text matches the pattern
        if (preg_match($pattern, $text)) {
            // Get the parent <td> element
            $parentTd = $textNode->parent()->parent()->parent();

            // Output the HTML of the parent <td> element
            //echo $parentTd->outertext;
            $parsed = modaboost_get_string_between($parentTd->outertext, '$', '&nbsp;USD');
            return trim($parsed);
            break; // Exit the loop after the first match
        }
    }
}

function modaboost_get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}