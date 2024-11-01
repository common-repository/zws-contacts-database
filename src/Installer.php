<?php

namespace ZwsContactsDatabase;

/**
 * Installation file for ZWS Contacts Database
 *
 * @copyright Copyright (c) 2015, Zaziork Web Solutions
 * @license This plugin uses the Composer library - see composer-license.txt
 * @author    Zaziork Web Solutions
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link https://www.zaziork.com/
 */
Class Installer {

    const OPTIONS_LABEL = 'zws_contacts_database_options';
    const TABLE_NAME_NO_PREFIX = 'zws_contacts_database_plugin';
    const DB_VERSION = '0'; // NEVER CHANGE THIS. Increment new DB versions in db.php.
    const USE_MEMCACHED = 'FALSE';
    const MEMCACHED_PERIOD = 86400;
    const DEFAULT_MEMCACHED_IP = '127.0.0.1';
    const DEFAULT_MEMCACHED_PORT = '11211';
    const DEFAULT_FULL_REMOVAL = 'FALSE';
    const DEFAULT_PRIVACY_POLICY_URL = '';
    const DEFAULT_GOOGLE_SERVER_API_KEY = '';
    const DEFAULT_MAP_CONTACT_ICON = '';
    const DEFAULT_MAP_BASE_ICON = '';
    const DEFAULT_MAP_TARGET_ICON = '';
    const MEMCACHED_KEY_BASE = 'ZWS_CONTACTS_DATABASE_KEY';
    const GOOGLE_MAP_ZOOM = 7;
    const ADMIN_EMAIL_ACTIVE = 'FALSE';
    const REG_EMAIL_ACTIVE = 'FALSE';
    const REG_EMAIL_SUBJECT = 'Thank you for your registration!';
    const COUNTRY_CODE = 'GB';

    private static $existing_stored_options = array();

    public static function install() {
        // set up options array
        $new_stored_options = array(
            'zws_contacts_database_plugin_table_name' => self::TABLE_NAME_NO_PREFIX,
            'zws_contacts_database_memcached_active' => self::USE_MEMCACHED,
            'zws_contacts_database_memcached_period' => self::MEMCACHED_PERIOD,
            'zws_contacts_database_memcached_ip' => self::DEFAULT_MEMCACHED_IP,
            'zws_contacts_database_memcached_port' => self::DEFAULT_MEMCACHED_PORT,
            'zws_contacts_database_plugin_db_version' => self::DB_VERSION,
            'zws_contacts_database_plugin_privacy_policy_url' => self::DEFAULT_PRIVACY_POLICY_URL,
            'zws_contacts_database_google_server_api_key' => self::DEFAULT_GOOGLE_SERVER_API_KEY,
            'zws_contacts_database_plugin_map_contact_icon_url' => self::DEFAULT_MAP_CONTACT_ICON,
            'zws_contacts_database_plugin_map_target_icon_url' => self::DEFAULT_MAP_TARGET_ICON,
            'zws_contacts_database_plugin_map_base_icon_url' => self::DEFAULT_MAP_BASE_ICON,
            'zws_contacts_database_plugin_memcached_keybase' => self::MEMCACHED_KEY_BASE . '_' . rand(),
            'zws_contacts_database_plugin_google_map_zoom' => self::GOOGLE_MAP_ZOOM,
            'zws_contacts_database_plugin_base_name' => '',
            'zws_contacts_database_plugin_base_postcode' => '',
            'zws_contacts_database_plugin_base_coordinates' => array(),
            'zws_contacts_database_plugin_admin_email_active' => self::ADMIN_EMAIL_ACTIVE,
            'zws_contacts_database_plugin_admin_email' => array(),
            'zws_contacts_database_plugin_reg_email_active' => self::REG_EMAIL_ACTIVE,
            'zws_contacts_database_plugin_reg_email_from' => array(get_site_option('blogname'), get_site_option('admin_email')),
            'zws_contacts_database_plugin_reg_email_subject' => self::REG_EMAIL_SUBJECT,
            'zws_contacts_database_plugin_country_of_use' => self::COUNTRY_CODE
        );

        // set options array if does not exist
        if (!get_site_option(self::OPTIONS_LABEL)) {
            add_site_option(self::OPTIONS_LABEL, $new_stored_options);
        } else {
            // update array with new key/values if do not exist
            self::$existing_stored_options = get_site_option(self::OPTIONS_LABEL);
            foreach ($new_stored_options as $new_key => $new_value) {
                // if option and/or option value does not exist ...
                if (!self::check_exists($new_key)) {
                    // update existing options with the non-existent new key/value
                    self::$existing_stored_options[$new_key] = $new_value;
                }
            }
            // update the options with the newly updated existing_stored_options array
            update_site_option(self::OPTIONS_LABEL, self::$existing_stored_options);
        }

        // the special option for removal of data on uninstall
        if (!get_site_option('zws_contact_database_remove_data')) {
            add_site_option('zws_contacts_database_remove_data', self::DEFAULT_FULL_REMOVAL);
        }

        // create or update the database
        require_once(__DIR__ . '/Database.php');
        \ZwsContactsDatabase\Database::update_database();

        // return true when successful
        return True;
    }

    private static function check_exists($new_key) {
        // method to check defined options exist in the WP options db table
        return array_key_exists($new_key, self::$existing_stored_options) ? TRUE : FALSE;
    }

}
