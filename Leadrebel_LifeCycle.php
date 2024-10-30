<?php
/*
    "WordPress Plugin Template" Copyright (C) 2022 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of WordPress Plugin Template for WordPress.

    WordPress Plugin Template is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WordPress Plugin Template is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see http://www.gnu.org/licenses/gpl-3.0.html
*/

include_once('Leadrebel_InstallIndicator.php');

class Leadrebel_LifeCycle extends Leadrebel_InstallIndicator
{

    public function install()
    {

        // Initialize Plugin Options
        $this->initOptions();

        // Initialize DB Tables used by the plugin
        $this->installDatabaseTables();

        // Other Plugin initialization - for the plugin writer to override as needed
        $this->otherInstall();

        // Record the installed version
        $this->saveInstalledVersion();

        // To avoid running install() more then once
        $this->markAsInstalled();
    }

    public function uninstall()
    {
        $this->otherUninstall();
        $this->unInstallDatabaseTables();
        $this->deleteSavedOptions();
        $this->markAsUnInstalled();
    }

    /**
     * Perform any version-upgrade activities prior to activation (e.g. database changes)
     * @return void
     */
    public function upgrade()
    {
    }

    /**
     * @return void
     */
    public function activate()
    {
    }

    /**
     * @return void
     */
    public function deactivate()
    {
    }

    /**
     * @return void
     */
    protected function initOptions()
    {
    }

    public function addActionsAndFilters()
    {
    }

    /**
     * Called by install() to create any database tables if needed.
     * Best Practice:
     * (1) Prefix all table names with $wpdb->prefix
     * (2) make table names lower case only
     * @return void
     */
    protected function installDatabaseTables()
    {
    }

    /**
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    protected function unInstallDatabaseTables()
    {
    }

    /**
     * Override to add any additional actions to be done at install time
     * @return void
     */
    protected function otherInstall()
    {
    }

    /**
     * Override to add any additional actions to be done at uninstall time
     * @return void
     */
    protected function otherUninstall()
    {
    }

    /**
     * Puts the configuration page in the Plugins menu by default.
     * Override to put it elsewhere or create a set of submenus
     * Override with an empty implementation if you don't want a configuration page
     * @return void
     */
    public function addSettingsSubMenuPage()
    {
        $this->addSettingsSubMenuPageToSettingsMenu();
    }


    protected function requireExtraPluginFiles()
    {
        require_once(ABSPATH . 'wp-includes/pluggable.php');
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }

    /**
     * @return string Slug name for the URL to the Setting page
     * (i.e. the page for setting options)
     */
    protected function getSettingsSlug()
    {
        return get_class($this) . 'Settings';
    }

    protected function addSettingsSubMenuPageToPluginsMenu()
    {
        $this->requireExtraPluginFiles();
        $displayName = $this->getPluginDisplayName();
        add_submenu_page('plugins.php',
            $displayName,
            $displayName,
            'manage_options',
            $this->getSettingsSlug(),
            array(&$this, 'settingsPage'));
    }


    protected function addSettingsSubMenuPageToSettingsMenu()
    {
        $this->requireExtraPluginFiles();
        $displayName = $this->getPluginDisplayName();
        add_options_page($displayName,
            $displayName,
            'manage_options',
            $this->getSettingsSlug(),
            array(&$this, 'settingsPage'));
    }

}

add_action('wp_ajax_leadrebel_signup', 'leadrebel_signup');
add_action('wp_ajax_nopriv_leadrebel_signup', 'leadrebel_signup');
function leadrebel_signup()
{
    $return = false;
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password']; // This is password, it is passed to remote server via API call and can't/haven't be sanitized. Remote server encrypts and saves it MongoDB, so there is no SQL injection risk
    $domain_url = sanitize_url($_POST['domain_url']);
    $user_ip = sanitize_text_field($_POST['user_ip']);

    if ($first_name && $last_name && $email && $password) {
        $token = '';
        // If no user, create user on API side
        if (!$token) {
            $new_user_url = 'https://app.leadrebel.io/api/users';
            $body = [
                "email" => $email,
                "password" => $password,
                "avoidRecaptchaToken" => "sh13nw5yOY5Ya7TsZyPOBBhbuqVwd3l5rWRPwU7QAWdG5L8DoSEDrgnL2KX55VMA",
                "ip" => $user_ip,
                "firstName" => $first_name,
                "lastName" => $last_name,
                "website" => $domain_url,
            ];
            $body = wp_json_encode($body);

            $data = array(
                'body' => $body,
                "headers" => array(
                    "Content-Type" => "application/json; charset=utf-8'",
                ),
                'data_format' => 'body',
                'sslverify' => false,
            );


            $ch_new_user = wp_remote_post($new_user_url, $data);
            $result_new_user = json_decode($ch_new_user['body'], true);

            if ($result_new_user['code'] == 200) {
                $token = $result_new_user['data']['token'];
            }
            if ($result_new_user['code'] == 409) {
                $return = array(
                    'email_taken' => true,
                );
            }
        }
        if ($token) {
            update_option('lead_rebel_token', $token);
            $code_snippet_url = 'https://app.leadrebel.io/api/users/tracking-code-snippet';
            $args = array(
                'headers' => array(
                    'Auth' => ' ' . $token
                ),
            );

            $curl_get_token = wp_remote_get($code_snippet_url, $args);

            $decoded_response = json_decode($curl_get_token['body'], true);
            if ($decoded_response['code'] == 200) {
                $head_script = $decoded_response['data']['html'];
                update_option('lead_rebel_code', $head_script);
                $return = array(
                    'lead_rebel_code' => true,
                    'lead_rebel_code_value' => $head_script,
                );
            }
        }


    }
    wp_send_json_success($return);

}

add_action('wp_ajax_leadrebel_login', 'leadrebel_login');
add_action('wp_ajax_nopriv_leadrebel_login', 'leadrebel_login');
function leadrebel_login()
{
    $return = false;
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password']; // This is password, it is passed to remote server via API call and can't/haven't be sanitized. Remote server encrypts and saves it MongoDB, so there is no SQL injection risk
    $domain_url = sanitize_url($_POST['domain_url']);

    if ($email && $password) {
        //Check if user exist
        $token = '';
        $url_check_if_user_exist = 'https://app.leadrebel.io/api/auth';

        $body = [
            "email" => $email,
            "password" => $password,
            "avoidRecaptchaToken" => "sh13nw5yOY5Ya7TsZyPOBBhbuqVwd3l5rWRPwU7QAWdG5L8DoSEDrgnL2KX55VMA",
            "website" => $domain_url,
        ];
        $body = wp_json_encode($body);

        $data_check_user = array(
            'body' => $body,
            "headers" => array(
                "Content-Type" => "application/json; charset=utf-8'",
            ),
            'data_format' => 'body',
            'sslverify' => false,
        );


        $result_if_exist = wp_remote_post($url_check_if_user_exist, $data_check_user);
        $result_if_exist = json_decode($result_if_exist['body'], true);

        if ($result_if_exist['code'] == 200) {
            $token = $result_if_exist['data']['token'];
        }
        if ($result_if_exist['code'] == 401) {
            $return = array(
                'not_authorized' => true,
            );
        }


        if ($token) {
            update_option('lead_rebel_token', $token);
            $code_snippet_url = 'https://app.leadrebel.io/api/users/tracking-code-snippet';
            $args = array(
                'headers' => array(
                    'Auth' => ' ' . $token
                ),
            );

            $curl_get_token = wp_remote_get($code_snippet_url, $args);

            $decoded_response = json_decode($curl_get_token['body'], true);
            if ($decoded_response['code'] == 200) {
                $head_script = $decoded_response['data']['html'];
                update_option('lead_rebel_code', $head_script);
                $return = array(
                    'lead_rebel_code' => true,
                    'lead_rebel_code_value' => $head_script,
                );

            }
        }

    }
    wp_send_json_success($return);

}

add_action('wp_head', 'add_lead_rebel_code');
function add_lead_rebel_code()
{
    $lead_rebel_code = get_option('lead_rebel_code');

    if ($lead_rebel_code) {
        _e($lead_rebel_code);
    }
}

add_action('wp_ajax_leadrebel_signout', 'leadrebel_signout');
add_action('wp_ajax_nopriv_leadrebel_signout', 'leadrebel_signout');
function leadrebel_signout()
{
    delete_option("lead_rebel_token");
    delete_option("lead_rebel_code");

    wp_send_json_success();
}