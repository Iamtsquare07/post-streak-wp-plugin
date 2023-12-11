<?php
/*
Plugin Name: Post Streak Tracker
Description: Keep track of author's posting streak and display a message in the dashboard.
Version: 1.2
Author: Tsquare07
*/

// Hook into the post publishing process
add_action('publish_post', 'update_post_streak');
// Hook into the post update process
add_action('save_post', 'update_post_streak');

add_action('wp_dashboard_setup', 'post_streak_dashboard_widget');

function update_post_streak($post_id) {
    if (wp_is_post_revision($post_id)) {
        return;
    }

    $author_id = get_post_field('post_author', $post_id);
    $streak_key = 'post_streak_' . $author_id;

    // Get the current streak
    $current_streak = get_user_meta($author_id, $streak_key, true);

    // If the user hasn't posted today, reset the streak
    $last_post_date = strtotime(get_the_time('Y-m-d', $post_id));
    $today_date = strtotime(date('Y-m-d'));
    if ($last_post_date < $today_date) {
        $current_streak = 0;
    }

    $current_streak++;
    update_user_meta($author_id, $streak_key, $current_streak);

    // Add an alert to the post/page editor
    echo "<script>alert('You have posted $current_streak times this week. Keep it up, " . get_the_author_meta('display_name', $author_id) . "!');</script>";
}

// Function to display the dashboard widget
function post_streak_dashboard_widget() {
    wp_add_dashboard_widget(
        'post_streak_dashboard_widget',
        'Posting Streak',
        'display_post_streak'
    );
}

// Function to display the content of the dashboard widget
function display_post_streak() {
    $user_id = get_current_user_id();
    $streak_key = 'post_streak_' . $user_id;
    $current_streak = get_user_meta($user_id, $streak_key, true);

    echo '<p>';
    if ($current_streak > 0) {
        echo 'You have posted ' . $current_streak . ' times this week. Keep it up, ' . get_the_author_meta('display_name', $user_id) . '!';
    } else {
        echo 'Start your posting streak today!';
    }
    echo '</p>';
}
?>
