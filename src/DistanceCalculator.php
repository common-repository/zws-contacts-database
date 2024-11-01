<?php

namespace ZwsContactsDatabase;

/**
 * Distance calculator file for ZWS  Contacts Database
 *
 * @copyright Copyright (c) 2015, Zaziork Web Solutions
 * @author    Zaziork Web Solutions
 * @license This plugin uses the Composer library - see composer-license.txt
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link https://www.zaziork.com/zws-wordpress-contacts-database-plugin/
 */
Class DistanceCalculator {

    // uses Google Distance Matrix API
    const OPTIONS_LABEL = 'zws_contacts_database_options';
    const DISTANCE_MATRIX_BASE_URL = 'https://maps.googleapis.com/maps/api/distancematrix/json';

    public static function nearestContacts($how_many = 5, $target = NULL) {
        
        /* returns a multidimensional array of x contacts, sorted by distance from target, containing an innter array of details */

        if (isset($target)) {
            // set up variables
            $distance_array = array();

            // grab all registered users from db
            require_once(__DIR__ . '/Database.php');
            $days_of_week = array(1 => 'mondays', 2 => 'tuesdays', 3 => 'wednesdays', 4 => 'thursdays', 5 => 'fridays', 6 => 'saturdays', 7 => 'sundays');
            // set variabe for earliest_time field corresponding to 'today'
            $earliest_time_today = 'earliest_time_' . $days_of_week[current_time(date('N', time()))];
            $latest_time_today = 'latest_time_' . $days_of_week[current_time(date('N', time()))];
            $result_set = \ZwsContactsDatabase\Database::getAllRecordsWhereIsNot('id', array('field' => $earliest_time_today, 'value' => 'UNAVL'));

            // loop postcodes and get distances
            if (!empty($result_set) && $result_set !== false && is_array($result_set)) {
                foreach ($result_set as $key => $row) {
                    // get distance from contact to each target. Returns false if error or target not found.
                    $distance = \ZwsContactsDatabase\DistanceCalculator::getDistance(sanitize_text_field($target), sanitize_text_field($row->postcode));
                    // if distance successfully returned add to the distance array
                    if ($distance !== false) {
                        $distance_array[sanitize_text_field($row->id)] = array('distance' => $distance,
                            'postcode' => sanitize_text_field($row->postcode),
                            'lng' => sanitize_text_field($row->lng),
                            'lat' => sanitize_text_field($row->lat),
                            'first_name' => sanitize_text_field($row->first_name),
                            'last_name' => sanitize_text_field($row->last_name),
                            'phone' => sanitize_text_field($row->phone),
                            'email' => sanitize_email($row->email),
                            $earliest_time_today => sanitize_text_field($row->$earliest_time_today),
                            $latest_time_today => sanitize_text_field($row->$latest_time_today),
                            'max_radius' => sanitize_text_field($row->max_radius),
                            'extra_info' => esc_textarea($row->extra_info));
                    }
                }
                if (!empty($distance_array)) {

                    $top_n = [];
                    $c = 0;
                    foreach ($distance_array as $key => $value) {
                        // if within max radius, and available, and within requested size of resultset
                        if (apply_filters('zws_filter_enforce_numeric', $value['distance']) <=
                                apply_filters('zws_filter_enforce_numeric', $value['max_radius']) && $c < $how_many) {
                            // add the id to the actual dataset, at it will no longer be available as the key
                            $value['id'] = sanitize_text_field($key);
                            $top_n[$c] = $value;
                            $c++;
                        }
                    }
                    return $top_n;
                }
            }
            return false;
        }
    }

    public static function getDistance($target_postcode, $contact_postcode) {
        require_once(__DIR__ . '/QueryAPI.php');
        
        $country_list = unserialize(ZWS_CDB_COUNTRY);

        $google_api_key = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_google_server_api_key'];
        $country_code = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_country_of_use'];
        $country_name = array_search($country_code, $country_list);
        $path = "?origins={$target_postcode}, {$country_name}
            &destinations={$contact_postcode}, {$country_name}
            &mode=driving
            &language=en-EN
            &components=country:{$country_code}
            &units=imperial
            &sensor=false
            &key={$google_api_key}";
        // $data = file_get_contents($url);
        $data = \ZwsContactsDatabase\QueryAPI::makeQuery(self::DISTANCE_MATRIX_BASE_URL, $path);

        if ($data['returned_data'] && $data['returned_data']['status'] === 'OK' && $data['returned_data']['rows'][0]['elements'][0]['status'] === "OK") {
            if ($data['cached']) {
                // error_log('THE DATA WAS CACHED ...'); // debug
            }
            return round((sanitize_text_field($data['returned_data']['rows'][0]['elements'][0]['distance']['value']) * 0.000621371), 0, PHP_ROUND_HALF_UP);
        } else {
            error_log('An error occurred whilst attempting to get a distance, at \ZwsContactsDatabase\DistanceCalculator::getDistance');
            return false;
        }
    }

    private static function my_multidimensional_array_sorter($value1
    , $value2) {
        $sort_order = 'asc'; // default
        $sort_key = 'distance';
        if ($sort_order == 'asc') {
            return $value1[$sort_key] - $value2[$sort_key];
        } else {
            return $value2[$sort_key] - $value1[$sort_key];
        }
    }

}
