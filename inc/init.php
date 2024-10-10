<?php

set_time_limit(0);
ini_set('memory_limit', '-1');

/** Loads the WordPress Environment and Template */
define('WP_USE_THEMES', false);
require dirname(__FILE__) . '/../../wp-load.php';
wp();


// DONT SEND EMAIL & PASSWORD CHANGE USER EMAILS
add_filter( 'send_email_change_email', '__return_false' );
add_filter( 'send_password_change_email', '__return_false' );

if (!function_exists('wp_mail')) {
    function wp_mail($to, $subject, $message, $headers = '', $attachments = array()) {
        return true;
    }
}

add_action('phpmailer_init', 'disable_phpmailer');
function disable_phpmailer($phpmailer) {
    $phpmailer->ClearAllRecipients();
}

add_filter('wp_mail', 'disable_wpmail');
function disable_wpmail($args) {
    return array(
        'to' => 'no-reply@beauxarts.com',
        'subject' => $args['subject'],
        'message' => $args['message'],
        'headers' => $args['headers'],
        'attachments' => $args['attachments']
    );
}

// https://docs.woocommerce.com/document/unhookremove-woocommerce-emails/
add_action('woocommerce_email', 'unhook_those_pesky_emails');

function unhook_those_pesky_emails($email_class) {
    /**
     * Hooks for sending emails during store events
     **/
    remove_action('woocommerce_low_stock_notification', array($email_class, 'low_stock'));
    remove_action('woocommerce_no_stock_notification', array($email_class, 'no_stock'));
    remove_action('woocommerce_product_on_backorder_notification', array($email_class, 'backorder'));

    // New order emails
    remove_action('woocommerce_order_status_pending_to_processing_notification', array($email_class->emails['WC_Email_New_Order'], 'trigger'));
    remove_action('woocommerce_order_status_pending_to_completed_notification', array($email_class->emails['WC_Email_New_Order'], 'trigger'));
    remove_action('woocommerce_order_status_pending_to_on-hold_notification', array($email_class->emails['WC_Email_New_Order'], 'trigger'));
    remove_action('woocommerce_order_status_failed_to_processing_notification', array($email_class->emails['WC_Email_New_Order'], 'trigger'));
    remove_action('woocommerce_order_status_failed_to_completed_notification', array($email_class->emails['WC_Email_New_Order'], 'trigger'));
    remove_action('woocommerce_order_status_failed_to_on-hold_notification', array($email_class->emails['WC_Email_New_Order'], 'trigger'));

    // Processing order emails
    remove_action('woocommerce_order_status_pending_to_processing_notification', array($email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger'));
    remove_action('woocommerce_order_status_pending_to_on-hold_notification', array($email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger'));

    // Completed order emails
    remove_action('woocommerce_order_status_completed_notification', array($email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger'));

    // Note emails
    remove_action('woocommerce_new_customer_note_notification', array($email_class->emails['WC_Email_Customer_Note'], 'trigger'));
}
