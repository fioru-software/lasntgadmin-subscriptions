<?php

namespace Lasntg\Admin\Subscriptions;


class Notifications{

    public static function init()
    {
        
    }

    public static function notify_managers($post, $postarr)
    {
        
    }

    public static function notify_managers_course_cancellation($post)
    {
        
    }

    public static function check_acf_fields_change($post_ID, $acfs_fields)
    {
        $managers = self::get_managers_of_area($post_ID);
        
        $acfs_fields = $_POST['acf'];
        //check if there's change in datetime, cost or venue.
        $fields_to_check = ['field_63be5267eab6c', 'field_63be5267eab6c'];

        foreach($fields_to_check as $field) {
            $old_value = get_field( $field, $post_ID, true);
            $new_value = $acfs_fields[$field];
            if(!$old_value !== $new_value){

            }
            
        }
    }


/**
    efficiency??
    1. check all user roles that have national.
    2. check each national belongs to the selected group ids.

    1.use sql query
 */
    public static function get_managers_of_area($post_ID)
    {
        global $wpdb;
        $group_ids = \Groups_Post_Access::get_read_group_ids( $post_ID );
        $email_body = Editors::get_options('course_update');
        $email_body = ParseEmail::add_course_info($post_ID, $email_body);
        
        $string_s = implode(', ', array_fill(0, count($group_ids), '%s'));
        $query = "select u.ID, u.display_name, u.user_email From wp_users u
                  INNER JOIN wp_usermeta um
                  on um.user_id = u.ID
                  INNER JOIN wp_groups_user_group g
                  on g.user_id = u.ID
                  where meta_value like '%national_manager%'
                  and meta_key = 'wp_capabilities'
                  and g.group_id in ($string_s)";
        $results = $wpdb->get_results($wpdb->prepare($query, $group_ids));
        var_dump($results);
        foreach($results as $user){
            $to_user = ParseEmail::add_receiver_info($user, $email_body);
            var_dump($to_user);
        }
        
        die();
    }
}