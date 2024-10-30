<?php
/*
   Plugin Name: LeadRebel
   Plugin URI: https://app.leadrebel.io/
   Version: 1.0.2
   Author: LeadRebel
   Description: https://app.leadrebel.io/
   Text Domain: leadrebel
   License: GPLv3
  */

/*
    "WordPress Plugin Template" Copyright (C) 2022

    This following part of this file is part of WordPress Plugin Template for WordPress.

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

$Leadrebel_minimalRequiredPhpVersion = '7.0';

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 * @return boolean true if version check passed. If false, triggers an error which WP will handle, by displaying
 * an error message on the Admin page
 */
function Leadrebel_noticePhpVersionWrong() {
    global $Leadrebel_minimalRequiredPhpVersion;
    echo '<div class="updated fade">' .
      __('Error: plugin "LeadRebel" requires a newer version of PHP to be running.',  'leadrebel').
            '<br/>' . __('Minimal version of PHP required: ', 'leadrebel') . '<strong>' . $Leadrebel_minimalRequiredPhpVersion . '</strong>' .
            '<br/>' . __('Your server\'s PHP version: ', 'leadrebel') . '<strong>' . phpversion() . '</strong>' .
         '</div>';
}


function Leadrebel_PhpVersionCheck() {
    global $Leadrebel_minimalRequiredPhpVersion;
    if (version_compare(phpversion(), $Leadrebel_minimalRequiredPhpVersion) < 0) {
        add_action('admin_notices', 'Leadrebel_noticePhpVersionWrong');
        return false;
    }
    return true;
}


/**
 * Initialize internationalization (i18n) for this plugin.
 * References:
 *      http://codex.wordpress.org/I18n_for_WordPress_Developers
 *      http://www.wdmac.com/how-to-create-a-po-language-translation#more-631
 * @return void
 */
function Leadrebel_i18n_init() {
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('leadrebel', false, $pluginDir . '/languages/');
}


//////////////////////////////////
// Run initialization
/////////////////////////////////

// Initialize i18n
add_action('plugins_loadedi','Leadrebel_i18n_init');

// Run the version check.
// If it is successful, continue with initialization for this plugin
if (Leadrebel_PhpVersionCheck()) {
    // Only load and run the init function if we know PHP version can parse it
    include_once('leadrebel_init.php');
    Leadrebel_init(__FILE__);
}


add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'apd_settings_link');
function apd_settings_link(array $links)
{
    $url = get_admin_url() . "options-general.php?page=Leadrebel_PluginSettings";
    $settings_link = '<a href="' . $url . '">' . __('Settings', 'textdomain') . '</a>';
    $links[] = $settings_link;
    return $links;
}
