<?php

include 'vendor/autoload.php';
include 'inc/init.php';
include 'inc/func.php';

$start_time = microtime(true);
$faker = Faker\Factory::create('fr_FR');
$meta_key_flag = "user_anonymized";
$batch_size = 10;
$excluded_roles =  ['administrator', 'shop_manager'];
$logger = wc_get_logger();
$anonymized_users = 0;

//plog( count_users_to_anonymize($meta_key_flag, $excluded_roles) . " users to anonymized");

plog("Exec time : " . getExecutionTime() . " sec");

$users = get_users_to_anonymize($meta_key_flag, $batch_size, $excluded_roles);

plog("Exec time : " . getExecutionTime() . " sec");

if ( ! empty( $users ) ) {
    
    foreach ( $users as $user_id ) {
        plog('-----');
        plog("Exec time : " . getExecutionTime() . " sec");
        $email = $faker->email();
        $phone = $faker->phoneNumber();
        $firstname = $faker->firstName();
        $lastname = $faker->lastName();
        $address_1 = $faker->streetAddress();
        $address_2 = $faker->secondaryAddress();
        $city = $faker->city();
        $postcode = $faker->postcode();
        $state = $faker->region();
        $company = $faker->optional($weight = 0.1)->company();

        // New billing informations
        $new_address = array(
            'first_name' => $firstname,
            'last_name'  => $lastname,
            'company'    => $company,
            'address_1'  => $address_1,
            'address_2'  => $address_2,
            'city'       => $city,
            'postcode'   => $postcode,
            'country'    => 'FR',
            'state'      => $state,
            'email'      => $email,
            'phone'      => $phone,
        );

        $result = wp_update_user( [
            'ID' => $user_id,
            'user_login' => $email,
            'user_email' => $email,
            'first_name' => $firstname,
            'last_name' => $lastname,
            'display_name' => $firstname . " " . $lastname,
            'nickname' => $firstname . " " . $lastname,
            'user_nicename' => strtolower( $firstname ) . "-" . strtolower( $lastname ),
            'user_pass' => wp_generate_password(),
        ] );

        update_user_meta($user_id, $meta_key_flag, true);
        plog("user $user_id anonymized");
        plog("Exec time : " . getExecutionTime() . " sec");
        
        if ( $result ) {

            $wpdb->update(
                $wpdb->users,
                ['user_login' => $email],
                ['ID' => $user_id],
                ['%s'],
                ['%d']
            );

            plog("user $user_id credentials anonymized");
            plog("Exec time : " . getExecutionTime() . " sec");
            
            if(class_exists('WC_Customer')){

                // Ano customer adresses
                $customer = new WC_Customer( $user_id );    
                if ( $customer ) {               
                    foreach ($new_address as $key => $value) {
                        if ( is_callable( array( $customer, "set_billing_{$key}" ) ) ) {
                            $customer->{"set_billing_{$key}"}($value);
                        } else {
                            $customer->update_meta_data( 'billing_' . $key, $value );
                        }
                    }
                    foreach ($new_address as $key => $value) {
                        if ( is_callable( array( $customer, "set_shipping_{$key}" ) ) ) {
                            $customer->{"set_shipping_{$key}"}($value);
                        } else {
                            $customer->update_meta_data( 'shipping_' . $key, $value );
                        }
                    }
                    $customer->save();
                    
                    plog("user $user_id customer anonymized");
                    plog("Exec time : " . getExecutionTime() . " sec");
                    
                    // Get customer orders
                    $customer_orders = get_order_ids($user_id);

                    if (!empty($customer_orders)) {
                        foreach ($customer_orders as $order_id) {
                            // Update billing informations
                            foreach ($new_address as $key => $value) {
                                update_post_meta($order_id, '_billing_' . $key, $value);
                            }

                            // Update shipping informations
                            foreach ($new_address as $key => $value) {
                                update_post_meta($order_id, '_shipping_' . $key, $value);
                            }

                        }

                        plog("user $user_id : ". count($customer_orders) . " orders anonymized");
                    } else {
                        plog("user $user_id No orders");
                    }
                }
                plog("Exec time : " . getExecutionTime() . " sec");
            }

        }

        $anonymized_users++;

    }
    
    $logger->info( $anonymized_users . ' users anonymized', array( 'source' => 'anonymize-woocommerce' ) );

}

plog($anonymized_users . ' users anonymized');
plog("Exec time : " . getExecutionTime() . " sec");
 