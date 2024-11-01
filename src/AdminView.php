<?php

namespace ZwsContactsDatabase;

use ZwsContactsDatabase\Helpers as Zelp;

/**
 * Administration view file for ZWS Contacts Database
 *
 * @copyright Copyright (c) 2017, Aninstance
 * @license This plugin uses the Composer library - see composer-license.txt
 * @author    Dan Bright (Aninstance)
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link https://www.aninstance.com
 */
Class AdminView {

    const OPTIONS_LABEL = 'zws_contacts_database_options';

    public static function dashboard() {

// check to ensure user has at least 'editor' privileges
        if (!self::authenticate()) {
            self::display_error('not_authorised');
            return false;
        }

        // set success variable for return - will be changed on successful completion of action
        $success = null;

        // sanitize all $_GET
        if (!empty($_GET)) {
            $safe_attr = array();
            foreach ($_GET as $key => $value) {
                $safe_attr[apply_filters('zws_filter_basic_sanitize', $key)] = apply_filters('zws_filter_basic_sanitize', $value);
            }
        }

        /* navigation stuff */

        // IF URL BEING ACCESSED AS CALLBACK TO DELETE A RECORD FROM THE DATABASE
        if (!empty($safe_attr['delete'])) {
            if (self::delete_record($safe_attr['delete'])) {
                // if successful deletion, echo value for ajax returned data to confirm
                echo 'DELETION_SUCCESSFUL';
                return true;
            } else {
                echo 'DELETION_ERROR';
                return false;
            }
        }

        // PROCESS THE POSTCODE / ADDRESS SEARCH FORM AND SHOW NEAREST CONTACTS
        if (!$success && !empty($safe_attr['postback']) && $safe_attr['postback'] == 'true') {
            // if postcode_search is true
            if (!empty($safe_attr['postcode_address_search']) && $safe_attr['postcode_address_search'] == 'true') {
                $posted_postcode = self::process_form();
                // if form processing successful, display nearest contacts. Returns true or false.
                $success = true ? self::display_nearest($posted_postcode) : false;
            }
        }
        // PROCESS THE NAME SEARCH FORM
        if (!$success && (empty($safe_attr['postback']) || $safe_attr['postback'] !== 'true')) {
            // if get_name is true
            if (!empty($safe_attr['getname']) && $safe_attr['getname'] == 'true') {
                if (empty($safe_attr['lastname'])) {
                    $name_to_query = self::process_form();
                } else {
                    $name_to_query = $safe_attr['lastname'];
                }
                // if form processing successful, display records. Returns true or false.
                $success = true ? self::get_records_for_name($name_to_query) : false;
            }
        }
        // PROCESS THE RECORDS UPDATE FORM (incoming from ZwsPaginator)
        if (!$success && !empty($safe_attr['postback']) && $safe_attr['postback'] == 'true') {
            // if update is true
            if (!empty($safe_attr['update']) && $safe_attr['update'] == 'true') {
                require_once(__DIR__ . '/ZwsPaginator.php');
                echo \ZwsContactsDatabase\ZwsPaginator::process_form($_POST);
                // if coming from name search
                if (!empty($safe_attr['getname']) && $safe_attr['getname'] == 'true') {
                    $success = true ? self::get_records_for_name($safe_attr['lastname']) : false;
                } else {
                    $success = true ? self::display_all_records() : false;
                }
            }
        }
        // SHOW ALL RECORDS
        if (!$success) {
            // if show_all is true
            if (!empty($safe_attr['show_all']) && $safe_attr['show_all'] == 'true') {
                $success = true ? self::display_all_records() : false;
            }
        }
        // DEFAULT - SHOW DASHBOARD IF NO OTHER ACTION SUCCESSFULLY COMPLETED AND NOT A POSTBACK
        if (!$success && (empty($safe_attr['postback']) || $safe_attr['postback'] != 'true')) {
            $success = true ? self::display_form() : false;
        }

// return null if successful, or false if not
        if ($success) {
            return null;
        } else {
            self::display_error('no_data');
            return false;
        }
    }

    public static function display_form() {
        require_once(__DIR__ . '/Helpers.php');
        $country_code = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_country_of_use'];
// method to display the target postcode / address entry form.
        echo '<h3 style="' . Zelp::getCss('header_style_tag') . '">Search for nearest drivers</h3>';
        echo '<form action="' . Zelp::set_url_query_cleared(array('postback' => 'true', 'postcode_address_search' => 'true')) . '" method="post">';
        echo '<p style="' . Zelp::getCss('label_style_tag') . '">Target postcode (type address or postcode)</p>';
        echo "<p><input type=\"text\" id=\"target_postcode\" data-geo=\"postal_code\" data-country={$country_code} placeholder=\"Postcode / address\" name=\"target_postcode\" pattern=\"[a-zA-Z0-9|\s]+\" maxlength=\"255\" value=\"\" style=\"width:85%\" /></p>";
        wp_nonce_field('submit_details_action', 'my_nonce_field');
        echo '<p><input type="submit" name="submitted" value="Submit"/></p>';
        echo '</form>';
        echo '<h3 style="' . Zelp::getCss('header_style_tag') . '">Get records for name</h3>';
        echo '<form action="' . Zelp::set_url_query_cleared(array('getname' => 'true')) . '" method="post">';
        echo '<p style="' . Zelp::getCss('label_style_tag') . '">Please enter the LAST NAME of the contact you require</p>';
        echo '<p><input type="text" placeholder="Last name" name="last_name" pattern="[a-zA-Z0-9]+" maxlength="21" value="" style="width:85%" /></p>';
        wp_nonce_field('submit_details_action', 'my_nonce_field');
        echo '<p><input type="submit" name="submitted" value="Submit"/></p>';
        echo '</form>';
        echo '<h3 style="' . Zelp::getCss('header_style_tag') . '">View entire database</h3>';
        $view_db_url = Zelp::set_url_query_cleared(array('show_all' => 'true', 'postback' => 'false'));
        echo '<div><button onclick="viewDatabase()">View the database</button><script>function viewDatabase() { window.location.href="' . html_entity_decode($view_db_url) . '";}</script></div>';
        return true;
    }

    public static function process_form() {
// checks if incoming POST, and that nonce was set, and that nonce details match
        if (isset($_POST['submitted']) && isset($_POST['my_nonce_field']) && wp_verify_nonce($_POST['my_nonce_field'], 'submit_details_action')) {
// set the target postcode from the form post
            if (!empty($_POST['target_postcode'])) {
                $target_postcode = apply_filters('zws_filter_sanitize_postcode', $_POST['target_postcode']);
            }
            if (!empty($_POST['last_name'])) {
                $last_name = apply_filters('zws_filter_basic_sanitize', $_POST['last_name']);
            }
            if (!empty($target_postcode)) {
                return $target_postcode;
            } elseif (!empty($last_name)) {
                return $last_name;
            }
            return false;
        }
    }

    public static function display_error($reason) {
        require_once(__DIR__ . '/Helpers.php');
        switch ($reason) {
            case 'no_data':
                $error_string = '<h2>Nothing to see here ...</h2><p>Oh dear, it looks like there is nothing to display.</p>
            <p>This may be because there are currently no contacts available. Or, the more likely reason is that the postcode you entered is invalid.</p>
            <p>Please <a href="' . \ZwsContactsDatabase\Helpers::set_url_query_cleared(array('postback' => 'false')) . '">re-enter the postcode to try again</a>!</p>';
                break;
            case 'not_authorised':
                $error_string = '<h2>Access denied ...</h2><p>It seems you are not logged in as an administrative user. Please log in and try again.</p>';
                break;
            default:
                $error_string = '<h2>Unspecified error ... </h2><p>Oooops, an unspecified error has occurred. Please report this to the website administrator.<p>';
                break;
        }
        echo $error_string;
    }

    public static function display_nearest($target_postcode) {
// check params have been passed
        if (!isset($target_postcode)) {
            return false;
        }
        require_once(__DIR__ . '/DistanceCalculator.php');
        require_once(__DIR__ . '/Helpers.php');
        $options = get_site_option(self::OPTIONS_LABEL);
        $success = false;
        $how_many_contacts = 5;
        $contacts_array = \ZwsContactsDatabase\DistanceCalculator::nearestContacts($how_many_contacts, $target_postcode);
        if ($contacts_array !== false) {
            $contacts_array_safe = [];
// display the  elements
            echo '<div class="contact-list"><h2>' . $how_many_contacts . ' Closest Contacts</h2>';
            echo '<small><a href="' . \ZwsContactsDatabase\Helpers::set_url_query_cleared(array('postback' => 'false')) . '">Back to administration dashboard</a></small>';
            // set variabe for earliest_time field corresponding to 'today'
            $days_of_week = array(1 => 'mondays', 2 => 'tuesdays', 3 => 'wednesdays', 4 => 'thursdays', 5 => 'fridays', 6 => 'saturdays', 7 => 'sundays');
            $earliest_time_today = 'earliest_time_' . $days_of_week[current_time(date('N', time()))];
            $latest_time_today = 'latest_time_' . $days_of_week[current_time(date('N', time()))];
            foreach ($contacts_array as $key => $value) {
// ensure variables from database are safe to output and add them to the contacts array. Only include contacts within their specified radius from target ...
                // ... only those who are available TODAY are returned from the DistanceCalculator::nearestContacts method, above.
                // ... ALL of TODAY's results (up to max results) are displayed, with the available times also printed.
                    $contacts_array_safe[$key]['id'] = sanitize_text_field($value['id']);
                    $contacts_array_safe[$key]['distance'] = apply_filters('zws_filter_enforce_numeric', $value['distance']);
                    $contacts_array_safe[$key]['postcode'] = apply_filters('zws_filter_basic_sanitize', $value['postcode']);
                    $contacts_array_safe[$key]['lat'] = apply_filters('zws_filter_basic_sanitize', $value['lat']);
                    $contacts_array_safe[$key]['lng'] = apply_filters('zws_filter_basic_sanitize', $value['lng']);
                    $contacts_array_safe[$key]['first_name'] = apply_filters('zws_filter_basic_sanitize', $value['first_name']);
                    $contacts_array_safe[$key]['last_name'] = apply_filters('zws_filter_basic_sanitize', $value['last_name']);
                    $contacts_array_safe[$key]['phone'] = apply_filters('zws_filter_enforce_numeric', $value['phone']);
                    $contacts_array_safe[$key]['email'] = sanitize_email($value['email']);
                    $contacts_array_safe[$key][$earliest_time_today] = apply_filters('zws_filter_basic_sanitize', $value[$earliest_time_today]);
                    $contacts_array_safe[$key][$latest_time_today] = apply_filters('zws_filter_basic_sanitize', $value[$latest_time_today]);
                    $contacts_array_safe[$key]['max_radius'] = apply_filters('zws_filter_enforce_numeric', $value['max_radius']);
                    $contacts_array_safe[$key]['extra_info'] = nl2br(
                            stripslashes(
                                    apply_filters('zws_filter_text_with_linebreak', $value['extra_info'])));
            }

            // add contacts array to map config
            $map_config['contacts_array_safe'] = $contacts_array_safe;
            // set up additional map config
            $map_config['target_postcode'] = $target_postcode;
            $map_config['contact_icon_url'] = apply_filters('zws_filter_basic_sanitize', $options['zws_contacts_database_plugin_map_contact_icon_url']);
            $map_config['target_icon_url'] = apply_filters('zws_filter_basic_sanitize', $options['zws_contacts_database_plugin_map_target_icon_url']);
            $map_config['base_icon_url'] = apply_filters('zws_filter_basic_sanitize', $options['zws_contacts_database_plugin_map_base_icon_url']);
            $map_config['base_postcode'] = apply_filters('zws_filter_sanitize_postcode', $options['zws_contacts_database_plugin_base_postcode']);
            $map_config['base_name'] = apply_filters('zws_filter_basic_sanitize', $options['zws_contacts_database_plugin_base_name']);
            $map_config['base_coordinates'] = $options['zws_contacts_database_plugin_base_coordinates'];
            $map_config['users_id'] = get_current_user_id();
            $map_config['zoom'] = apply_filters('zws_filter_validate_integer', $options['zws_contacts_database_plugin_google_map_zoom']);

            // display the map
            if (self::display_map($map_config)) {
                $success = true;
            }

            echo '<ol class="contact-info-list">';

            $c = 0; // counter to give each entry's available times fields a unique class name for jQuery
            foreach ($contacts_array_safe as $key => $value) {
// display textual elements
                echo '<li style="margin-bottom:1em;">';
                echo '<ul class="contact-info-list-inner">';
                echo '<li>Distance from target: ' . $value['distance'] . ' miles</li>';
                echo '<li>Name of contact: ' . stripslashes($value['first_name']) . ' ' . stripslashes($value['last_name']) . '</li>';
                echo '<li>Postcode of contact: ' . $value['postcode'] . '</li>';
                echo '<li>Phone of contact: <a href="tel:' . $value['phone'] . '">' . $value['phone'] . '</a></li>';
                echo '<li>Email of contact: <a href="mailto:' . $value['email'] . '">' . $value['email'] . '</a></li>';
                echo '<li>Extra notes: ' . $value['extra_info'] . '</li>';
                echo '<li><button class="modal_opener_' . $c . '">View time that ' . $value['first_name'] . ' is available</button><div class="zws-contacts-db-times-available">'
                . '<ul class="contact-info-list-inner_' . $c . '">';
                echo '<li>Earliest: ' . $value[$earliest_time_today] . '</li>';
                echo '<li style="border-bottom:1px solid silver;">Latest: ' . $value[$latest_time_today] . '</li>';
                echo '</ul></div></li>';
                echo '</ul>';
                echo '</li>';
                $c++;
            }
            echo '</ol><div>';
        }
// return true if map has successfully displayed, otherwise false.
        return true ? $success : false;
    }

    public static function display_map($map_config) {
// check params have been passed and that there is something to display
        if (!isset($map_config['contacts_array_safe'][0]['id'])) {
            error_log('An error with the passing of $map_config to display_map has occurred.');
            return false;
        }
// method to display the Google map
//
// create the javascript filename (add random id to script to ensure a cached version is not returned to client)
        $rand = rand();
        $user_id = $map_config['users_id'];
        $new_filename = plugins_url('/../inc/googlemaps_' . $user_id . '_' . $rand . '_.js', __FILE__);
        $map_config['new_script_uri'] = 'googlemaps_' . $user_id . '_' . $rand . '_.js';
// remove any existing script for this user (globbing any script with this user ID, followed by "anything else" - which includes the random no-cache string)
        $existing_file = glob(__DIR__ . '/../inc/' . 'googlemaps_' . $user_id . '_' . '*');
        if ($existing_file && !empty($existing_file)) {
            foreach ($existing_file as $key => $file) {
                unlink($file);
            }
        }
// generate the javascript file
        require_once(__DIR__ . '/JavascriptBuilder.php');
        if (\ZwsContactsDatabase\JavascriptBuilder::generate_js($map_config)) {
// load up the scripts
            wp_enqueue_script('my_implementation', $new_filename, array('jquery'));

// define the display structure
            echo '<div id="map-canvas" style="width:500px;height:400px;background-color:#CCC;margin:1em;"></div>';
            return true;
        }
        return false;
    }

// display all records
    private static function display_all_records() {
// hard code resuts per page and index length for now. Add to user-defined options later?
        $page_size = 5;
        $order_by = 'last_name'; // allow to be configured by users in options later?
// grab all registered users from db
        require_once(__DIR__ . '/Database.php');
        $result_set = \ZwsContactsDatabase\Database::getAllRecords($order_by);
// paginate and display the results
        require_once(__DIR__ . '/ZwsPaginator.php');
        return true ? \ZwsContactsDatabase\ZwsPaginator::paginate($result_set, $page_size) : false;
    }

// delete record
    private static function delete_record($record_id) {
        require_once(__DIR__ . '/Database.php');
        return true ? \ZwsContactsDatabase\Database::deleteRecord($record_id) : false;
    }

// display individual contact record
    private static function get_records_for_name($last_name) {
// grab all registered users from db
        require_once(__DIR__ . '/Database.php');
        $result_set = \ZwsContactsDatabase\Database::getAllRecordsWhere('id', array('field' => 'last_name', 'value' => $last_name));
// display the results
        require_once(__DIR__ . '/ZwsPaginator.php');
        $page_size = 5;
        return true ? \ZwsContactsDatabase\ZwsPaginator::paginate($result_set, $page_size, $last_name) : false;
    }

    public static function authenticate() {
        // method to check that users are authenticated as 'editor' or above
        $user = wp_get_current_user();
        $allowed_roles = array('editor', 'administrator');
        return true ? array_intersect($allowed_roles, $user->roles) : false;
    }

}
