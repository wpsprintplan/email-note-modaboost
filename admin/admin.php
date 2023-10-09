<?php

function Email_plugin_register_menu() {
    add_menu_page(
        'Paypal Payment',
        'Paypal Payment',
        'manage_options',
        'email-note-plugin',
        'email_note_plugin_menu_page',
        'dashicons-calendar-alt',
        25
    );
}
add_action('admin_menu', 'Email_plugin_register_menu');

function email_note_plugin_menu_page(){
    wp_enqueue_style('dataTables-min-css', plugins_url('../assets/dataTables.min.css',__FILE__ ));
    wp_enqueue_style('email-css', plugins_url('../assets/style.css',__FILE__ ));
    if(isset($_GET['check_id'])){
        include_once('edit_form.php'); 
    } else {
        include_once('form.php'); 
    }
    wp_enqueue_script( 'dataTables-min-js', plugins_url('../assets/jquery.dataTables.min.js', __FILE__ ), array('jquery') );
    wp_enqueue_script( 'email-js', plugins_url('../assets/admin.js', __FILE__ ), array('jquery') );    
}