<?php

namespace ZwsContactsDatabase;

/**
 * Installation file for ZWS  Contacts Database
 *
 * @copyright Copyright (c) 2015, Zaziork Web Solutions
 * @author    Zaziork Web Solutions
 * @license This plugin uses the Composer library - see composer-license.txt
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link https://www.zaziork.com/zws-contacts-database-plugin/
 */
Class Database {

    const OPTIONS_LABEL = 'zws_contacts_database_options';

    public static function update_database() {

        // check to ensure user has at least 'editor' privileges
        if (!\ZwsContactsDatabase\AdminView::authenticate()) {
            return false;
        }

        // increment this when database structure changed or name changed
        $db_version = '1.1';

// updated database 
        global $wpdb;
        $stored_table_name = apply_filters('zws_filter_basic_sanitize', get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_table_name']);
        $installed_ver = apply_filters('zws_filter_basic_sanitize', get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_db_version']);
        // create the complete create statement
        if ($installed_ver !== $db_version) {
            $table_name = $wpdb->prefix . $stored_table_name;
            $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        first_name varchar(255) DEFAULT '' NOT NULL,
        last_name varchar(255) DEFAULT '' NOT NULL,
        postcode varchar(8) DEFAULT '' NOT NULL,
        lat varchar(15) DEFAULT '' NOT NULL,
        lng varchar(15) DEFAULT '' NOT NULL,
        phone varchar(20) NOT NULL,
        email varchar(255) DEFAULT '' NOT NULL,
        max_radius mediumint(9) NOT NULL,
        extra_info varchar(1000),
        earliest_time_mondays varchar(5) NULL,
        latest_time_mondays varchar(5) NULL,
        earliest_time_tuesdays varchar(5) NULL,
        latest_time_tuesdays varchar(5) NULL,
        earliest_time_wednesdays varchar(5) NULL,
        latest_time_wednesdays varchar(5) NULL,
        earliest_time_thursdays varchar(5) NULL,
        latest_time_thursdays varchar(5) NULL,
        earliest_time_fridays varchar(5) NULL,
        latest_time_fridays varchar(5) NULL,
        earliest_time_saturdays varchar(5) NULL,
        latest_time_saturdays varchar(5) NULL,
        earliest_time_sundays varchar(5) NULL,
        latest_time_sundays varchar(5) NULL,
        pp_accepted tinyint(1) DEFAULT '0' NOT NULL,
        CONSTRAINT UNIQUE  (phone),
        CONSTRAINT UNIQUE  (email),
        PRIMARY KEY  id (id)
	);";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta($sql);
// update the stored db version
            $new_options = self::get_updated_options_array($db_version);
            update_site_option(self::OPTIONS_LABEL, $new_options);
// return for unit testing
            return true;
        }
    }

    private static function get_updated_options_array($db_version) {
// grab options
        $opts = get_site_option(self::OPTIONS_LABEL);
// update with new
        $opts['zws_contacts_database_plugin_db_version'] = $db_version;
        return $opts;
    }

    public static function insert($safe_values, $user_signup=False) {

        // check to ensure user has at least 'editor' privileges unless being called as a new user signup (to ensure database entries can't be modified by non-admins)
        if (!\ZwsContactsDatabase\AdminView::authenticate() && !$user_signup) {
            return false;
        }

        $saved_table_name = apply_filters('zws_filter_basic_sanitize', get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_table_name']);
        if (is_array($safe_values)) {
            global $wpdb;
            $table_name = $wpdb->prefix . $saved_table_name;
            // insert data
            $safe_values['time'] = current_time('mysql');
            return $wpdb->insert($table_name, $safe_values);
        }
        return false;
    }

    public static function update($safe_values, $where) {

        // check to ensure user has at least 'editor' privileges
        if (!\ZwsContactsDatabase\AdminView::authenticate()) {
            return false;
        }

        $saved_table_name = apply_filters('zws_filter_basic_sanitize', get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_table_name']);
        if (is_array($safe_values)) {
            global $wpdb;
            $table_name = $wpdb->prefix . $saved_table_name;
            // insert data
            $safe_values['time'] = current_time('mysql');
            return $wpdb->update($table_name, $safe_values, $where);
        }
        return false;
    }

    public static function getAllRecords($order_by = 'id') {

        // check to ensure user has at least 'editor' privileges
        if (!\ZwsContactsDatabase\AdminView::authenticate()) {
            return false;
        }

        // method to get all records from the database
        $saved_table_name = apply_filters('zws_filter_basic_sanitize', get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_table_name']);
        global $wpdb;
        $table_name = $wpdb->prefix . $saved_table_name;
        // grab the data
        $sql = "SELECT * FROM " . $table_name . " ORDER BY " . apply_filters('zws_filter_basic_sanitize', $order_by) . "";
        return $wpdb->get_results($sql);
    }

    public static function getAllRecordsWhere($order_by = 'id', $where = null) {

        // check to ensure user has at least 'editor' privileges
        if (!\ZwsContactsDatabase\AdminView::authenticate()) {
            return false;
        }

        // method to get records from the database WHERE IS NOT. $where should be an array (field => value)
        if (!empty($where)) {
            $where_statement = "`" . $where['field'] . "` = '" . $where['value'] . "'";
        } else {
            return false;
        }
        $saved_table_name = apply_filters('zws_filter_basic_sanitize', get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_table_name']);
        global $wpdb;
        $table_name = $wpdb->prefix . $saved_table_name;
        // grab the data
        try {
            $sql = "SELECT * FROM " . $table_name . " WHERE " . $where_statement . " ORDER BY " . apply_filters('zws_filter_basic_sanitize', $order_by) . "";
            return $wpdb->get_results($sql);
        } catch (Exception $e) {
            return false;
        }
    }

    public static function getAllRecordsWhereIsNot($order_by = 'id', $where = null) {

        // check to ensure user has at least 'editor' privileges
        if (!\ZwsContactsDatabase\AdminView::authenticate()) {
            return false;
        }

        // method to get records from the database WHERE IS NOT. $where should be an array (field => value)
        if (!empty($where)) {
            $where_statement = "`" . $where['field'] . "` <> '" . $where['value'] . "'";
        } else {
            return false;
        }
        $saved_table_name = apply_filters('zws_filter_basic_sanitize', get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_table_name']);
        global $wpdb;
        $table_name = $wpdb->prefix . $saved_table_name;
        // grab the data
        //$sql = $my_wpdb->prepare("SELECT * FROM $table_name ORDER BY %s;", 'id');
        try {
            $sql = "SELECT * FROM " . $table_name . " WHERE " . $where_statement . " ORDER BY " . apply_filters('zws_filter_basic_sanitize', $order_by) . "";
            return $wpdb->get_results($sql);
        } catch (Exception $e) {
            return false;
        }
    }

    public static function deleteRecord($record_id) {

        // check to ensure user has at least 'editor' privileges
        if (!\ZwsContactsDatabase\AdminView::authenticate()) {
            return false;
        }

        // method to delete a record from the database
        $saved_table_name = apply_filters('zws_filter_basic_sanitize', get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_table_name']);
        global $wpdb;
        $table_name = $wpdb->prefix . $saved_table_name;
          
        try {
            $return = $wpdb->delete($table_name, array('id' => apply_filters('zws_filter_basic_sanitize', $record_id)), array('%d'));
            // return true if more than 0 rows deleted and the action did not return an error, or false otherwise
            return true ? $return > 0 && $return !== false : false;
        } catch (Exception $e) {
            return false;
        }
    }

}
