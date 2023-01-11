<?php

namespace Lasntg\Admin\Subscriptions;

class SubscriptionActionsFilters {
    public static function init():void
    {
        // add_action('wp_insert_post_data', [self::class, 'wp_insert_post'], 10, 4);
        
    }

    public static function wp_insert_post($data, $postarr, $unsanitized_postarr, $update)
    {   
        error_log("========");
        error_log("post_id: ". $data);
        error_log( serialize($postarr));
        error_log( $unsanitized_postarr);
        error_log( $update);
        error_log("========");
        
    }
}