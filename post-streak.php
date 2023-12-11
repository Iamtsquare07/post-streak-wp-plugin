<?php
/*
Plugin Name: Post Streak Tracker
Description: Keep track of author's posting streak and display a message in the dashboard.
Version: 1.2
Author: Tsquare07
*/

// Hook into the post publishing process
add_action('transition_post_status', 'update_post_streak', 10, 3);

// Hook into the dashboard setup to add the widget
add_action('wp_dashboard_setup', 'post_streak_dashboard_widget');

// Hook into the script and style setup
add_action('wp_enqueue_scripts', 'enqueue_post_streak_script');

function enqueue_post_streak_script() {
    // Enqueue script for the alert
    wp_enqueue_script('post-streak-alert', plugin_dir_url(__FILE__) . 'post-streak-alert.js', array(), '1.0', true);
}

function update_post_streak($new_status, $old_status, $post) {
    if ($new_status === 'publish' && $old_status !== 'publish') {
        $author_id = $post->post_author;
        $streak_key = 'post_streak_' . $author_id;

        // Get the current streak
        $current_streak = get_user_meta($author_id, $streak_key, true);

        // If the user hasn't posted today, reset the streak
        $last_post_date = strtotime(date('Y-m-d', strtotime($post->post_date)));
        $today_date = strtotime(date('Y-m-d'));

        if ($last_post_date < $today_date) {
            $current_streak = 0;
        }

        $current_streak++;
        update_user_meta($author_id, $streak_key, $current_streak);

        // Set a message
        $message = 'Congratulations! You have posted again. Keep up the good work!';

        // Pass data to the script
        wp_localize_script('post-streak-alert', 'postStreakData', array('message' => $message));
    }
}

function post_streak_dashboard_widget() {
    wp_add_dashboard_widget(
        'post_streak_dashboard_widget',
        'Posting Streak',
        'display_post_streak'
    );
}

// Display the content of the dashboard widget
function display_post_streak() {
    $user_id = get_current_user_id();
    $streak_key = 'post_streak_' . $user_id;
    $current_streak = get_user_meta($user_id, $streak_key, true);

    echo '<div style="padding: 20px; background-color: #f4f4f4; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">';
    if ($current_streak > 0) {
        echo '<p style="font-size: 16px; font-weight: bold; color: #333;">You have posted ' . $current_streak . ' times this week. Keep it up, ' . get_the_author_meta('display_name', $user_id) . '!</p>';
    } else {
        echo '<p style="font-size: 16px; font-weight: bold; color: #333;">Start your posting streak today!</p>';
    }
    echo '</div>';
}

?>
