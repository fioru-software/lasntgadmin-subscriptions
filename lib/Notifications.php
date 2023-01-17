<?php

namespace Lasntg\Admin\Subscriptions;

use Lasntg\Admin\Subscriptions\Traits\ManagersNotifications;

class Notifications{
    use ManagersNotifications;

    
    /**
     * Should be placed in groups plugin.
     */
    private static function get_post_group_ids($post_ID)
    {
        return \Groups_Post_Access::get_read_group_ids( $post_ID );
    }
    protected static function get_users_in_group($post_ID, $user_role = 'national_manager')
    {
        global $wpdb;
        $user_role = "%$user_role%";
        $group_ids = self::get_post_group_ids($post_ID);
        $params = array_merge([$user_role], $group_ids);
        $string_s = implode(', ', array_fill(0, count($group_ids), '%s'));
        $query = "select u.ID, u.display_name, u.user_email From wp_users u
                  INNER JOIN wp_usermeta um
                  on um.user_id = u.ID
                  INNER JOIN wp_groups_user_group g
                  on g.user_id = u.ID
                  where meta_value like %s
                  and meta_key = 'wp_capabilities'
                  and g.group_id in ($string_s)";
        return $wpdb->get_results($wpdb->prepare($query, $params));
    }

    protected static function get_email_subject_and_body($post_ID, $subject, $body)
    {
        $email_subject = Editors::get_options($subject);
        $email_body = Editors::get_options($body);

        $email_subject = ParseEmail::add_course_info($post_ID, $email_subject);
        $email_body = ParseEmail::add_course_info($post_ID, $email_body);
        $email_body = apply_filters( 'the_content',$email_body);
        return [
            'subject' => $email_subject,
            'body' => $email_body,
        ];
    }
    

    protected static function get_content($post_ID, $subject, $body)
    {
        $email = self::get_email_subject_and_body($post_ID, $subject, $body);
        $email_subject = $email['subject'];
        $email_body = $email['body'];

        $users = self::get_users_in_group($post_ID);
        self::parse_emails_for_users($users, $email_subject, $email_body);
    }
    protected static function parse_emails_for_users($users, $subject, $body)
    {
        foreach($users as $user){
            $unique_body = ParseEmail::add_receiver_info($user, $body);
            $unique_subject = ParseEmail::add_receiver_info($user, $subject);
            self::send_mail($user->user_email, $unique_subject, $unique_body);  
        }
    }

    protected static function send_mail($email, $subject,$body)
    {
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($email, $subject, $body, $headers);
    }
}