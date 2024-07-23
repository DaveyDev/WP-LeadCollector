<?php
/*
Plugin Name: Lead Collector
Description: A simple plugin to collect leads (email addresses) and save them in the database.
Version: 1.2
Author: Davey_Dev
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Create the database table on plugin activation
function lead_collector_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'lead_collector';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        email varchar(100) NOT NULL,
        time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'lead_collector_create_table');


// Shortcode to display the lead collection form
function lead_collector_form() {
    ob_start();

    // Display the form
    ?>
    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
        <label for="lead_collector_email">Email:</label>
        <input type="email" name="lead_collector_email" required>
        <input type="hidden" name="action" value="save_lead">
        <button type="submit">Submit</button>
    </form>
    <?php

    // Output the saved lead function content
    lead_collector_save_lead();

    return ob_get_clean();
}


add_shortcode('lead_collector_form', 'lead_collector_form');

// Handle form submission and save the email to the database
function lead_collector_save_lead() {
    if (isset($_POST['lead_collector_email'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'lead_collector';
        $email = sanitize_email($_POST['lead_collector_email']);

        if (is_email($email)) {
            $wpdb->insert($table_name, ['email' => $email]);

            // Display a thank you message
            wp_redirect(home_url('/thank-you/'));
            exit; // You can style this message as needed

            // Optionally, clear the form or display additional content here
            // Example: echo '<p>Form submitted successfully!</p>';

            //return; // Exit after displaying the message
        }
        else {
            echo '<p>PLEASE ENTER EMAIL</p>';
        }
    }

    
}


add_action('admin_post_nopriv_save_lead', 'lead_collector_save_lead');
add_action('admin_post_save_lead', 'lead_collector_save_lead');

// Add an admin menu for viewing collected leads
function lead_collector_admin_menu() {
    add_menu_page(
        'Lead Collector',
        'Lead Collector',
        'manage_options',
        'lead-collector',
        'lead_collector_admin_page',
        'dashicons-email-alt',
        6
    );
}

add_action('admin_menu', 'lead_collector_admin_menu');

// Display the collected leads on the admin page
function lead_collector_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'lead_collector';
    $leads = $wpdb->get_results("SELECT * FROM $table_name");

    ?>
    <div class="wrap">
        <h1>Collected Leads</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leads as $lead) : ?>
                <tr>
                    <td><?php echo esc_html($lead->id); ?></td>
                    <td><?php echo esc_html($lead->email); ?></td>
                    <td><?php echo esc_html($lead->time); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
