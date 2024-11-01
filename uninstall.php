<?php

namespace ZwsContactsDatabase;

/**
 * Runs on uninstall of Zws Contacts Database
 * 
 * @copyright Copyright (c) 2015, Zaziork Web Solutions
 * @license This plugin uses the Composer library - see composer-license.txt
 * @author    Zaziork Web Solutions
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link https://www.zaziork.com/
 */
global $wpdb;
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}
if (get_site_option('zws_contacts_database_remove_data') === 'TRUE') {
// remove database
    $database = $wpdb->prefix . 'zws_contacts_database_plugin';
    $wpdb->query("DROP TABLE IF EXISTS $database");
// remove options
    delete_site_option('zws_contacts_database_options');
    delete_site_option('zws_contacts_database_remove_data');
}