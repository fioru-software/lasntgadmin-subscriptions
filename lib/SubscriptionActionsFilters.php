<?php

namespace Lasntg\Admin\Subscriptions;

class SubscriptionActionsFilters {
    public static function init():void
    {
        add_action('post_updated', [self::class, 'wp_insert_post'], 10, 3);
        // add_action('updated_postmeta', [self::class, 'wp_insert_post'], 10, 3);
        
    }
    
    public static function wp_insert_post($post_ID, $post_after, $post_before)
    {   
        if('product' !== $post_after->post_type){
            return;
        }
        $acfs_fields = $_POST['acf'];
        Notifications::check_acf_fields_change($post_ID, $acfs_fields);
        //check if there's change in datetime, cost or venue.
        
        if($old_cost !== $acfs_fields['field_63be5267eab6c']){

        }
        
        echo "<Pre>";
        var_dump($_POST['acf']['field_63be5267eab6c']);
        var_dump($old_cost);
        var_dump($post_ID);
        echo "PostAfter:";
        var_dump($post_after);
        echo "postBefore:";
        var_dump($post_before);
        var_dump($_POST);
        // die();
        
    }
}