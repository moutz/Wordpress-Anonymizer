<?php

function count_users_to_anonymize($meta_key_flag, $excluded_roles) {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $roles_conditions = array();

    foreach ($excluded_roles as $role) {
        $roles_conditions[] = $wpdb->prepare("um_role.meta_value NOT LIKE %s", '%' . $role . '%');
    }
    $roles_conditions_sql = implode(' AND ', $roles_conditions);

    $query = $wpdb->prepare("
        SELECT COUNT(*)
        FROM {$prefix}users u
        LEFT JOIN {$prefix}usermeta um_flag ON u.ID = um_flag.user_id AND um_flag.meta_key = %s
        LEFT JOIN {$prefix}usermeta um_role ON u.ID = um_role.user_id AND um_role.meta_key = '{$prefix}capabilities'
        WHERE um_flag.meta_key IS NULL
        AND ($roles_conditions_sql)
    ", $meta_key_flag);

    $total_users = $wpdb->get_var($query);

    return $total_users;
}



function get_users_to_anonymize($meta_key_flag, $batch_size = 500, $excluded_roles) {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $roles_conditions = array();

    foreach ($excluded_roles as $role) {
        $roles_conditions[] = $wpdb->prepare("um_role.meta_value NOT LIKE %s", '%' . $role . '%');
    }
    $roles_conditions_sql = implode(' AND ', $roles_conditions);

    $query = $wpdb->prepare("
        SELECT u.ID
        FROM {$prefix}users u
        LEFT JOIN {$prefix}usermeta um_flag ON u.ID = um_flag.user_id AND um_flag.meta_key = %s
        LEFT JOIN {$prefix}usermeta um_role ON u.ID = um_role.user_id AND um_role.meta_key = '{$prefix}capabilities'
        WHERE um_flag.meta_key IS NULL
        AND ($roles_conditions_sql)
        ORDER BY u.ID
        LIMIT %d
    ", $meta_key_flag, $batch_size);

    $user_ids = $wpdb->get_col($query);

    return $user_ids;
}


function get_order_ids($user_id) {
    global $wpdb;

    $prefix = $wpdb->prefix;

    $query = $wpdb->prepare("
        SELECT p.ID as order_id
        FROM {$prefix}posts p
        INNER JOIN {$prefix}postmeta pm ON p.ID = pm.post_id
        WHERE p.post_type = 'shop_order'
        AND pm.meta_key = '_customer_user'
        AND pm.meta_value = %d
    ", $user_id);

    $order_ids = $wpdb->get_col($query);

    return $order_ids;
}

/**
 * TOOLS
 */

function plog($message)
{
    $message = date("H:i:s") . " - $message ".PHP_EOL;
    print($message);
    flush();
    ob_flush();
}

function getExecutionTime(){
    $end_time = microtime(true);
    $execution_time = ($end_time - $GLOBALS['start_time']);
    return $execution_time;
}