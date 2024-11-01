<?php

namespace ZwsContactsDatabase;

/**
 * Filters file for ZWS Contacts Database
 *
 * @copyright Copyright (c) 2015, Zaziork Web Solutions
 * @license This plugin uses the Composer library - see composer-license.txt
 * @author    Zaziork Web Solutions
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link https://www.zaziork.com/
 */
Class ZwsFilters {
    /* DEFINE FILTERS */

    public static function validate_path_filter($input) {
        // ensure any leading and trailing slashes are removed
        return sanitize_text_field((ltrim(rtrim($input, '/'), '/')));
    }

    public static function validate_base_url_filter($input) {
        $proto_removed = strstr($input, '://');
        if ($proto_removed) {
            $input = $proto_removed;
        }
        return sanitize_text_field(ltrim($input, '://'));
    }

    public static function validate_interger($input) {
        // verify input is an integer - returns 0 if not.
        return absint($input);
    }

    public static function validate_basic_sanitize_filter($input) {
        // user WP built in sanitize_text_field
        return sanitize_text_field($input);
    }

    public static function validate_textfield_with_linebreaks($input) {
        // set up allowed html for wp_kses filter (nothing in this instance - wp_kses DOES preserve linebreaks, however)
        $allowed_html = array();
        return wp_kses($input, $allowed_html);
    }

    public static function validate_textfield_postcode($input) {
        return sanitize_text_field(strtoupper(preg_replace('/\s+/', '', $input)));
    }

    public static function validate_textfield_to_date_obj($input) {
        return sanitize_text_field($input);
    }

    public static function enforce_numeric($input) {
        return sanitize_text_field(trim(preg_replace('/\D/', '', $input)));
    }
    
    public static function limit_chars($input, $limit_length=950, $append='...') {
       // Method to limit characters of an input string, without cutting words, appending "...". Default return is 953 chars including append.
           if (strlen($input > $limit_length)) {
           $wrapped = wordwrap($input, $limit_length);
           $wrapped_array = explode('\n', $wrapped, 2);
           return $wrapped_array[0] . $append;
           }
           return $input; // return unchanged input if less than the max length
   }

}

/* ADD THE FILTERS */
add_filter('zws_filter_validate_path', array('\ZwsContactsDatabase\ZwsFilters', 'validate_path_filter'), 10, 1);
add_filter('zws_filter_validate_url', array('\ZwsContactsDatabase\ZwsFilters', 'validate_base_url_filter'), 10, 1);
add_filter('zws_filter_validate_integer', array('\ZwsContactsDatabase\ZwsFilters', 'validate_interger'), 10, 1);
add_filter('zws_filter_basic_sanitize', array('\ZwsContactsDatabase\ZwsFilters', 'validate_basic_sanitize_filter'), 10, 1);
add_filter('zws_filter_text_with_linebreak', array('\ZwsContactsDatabase\ZwsFilters', 'validate_textfield_with_linebreaks'), 10, 1);
add_filter('zws_filter_to_date_obj', array('\ZwsContactsDatabase\ZwsFilters', 'validate_textfield_to_date_obj'), 10, 1);
add_filter('zws_filter_sanitize_postcode', array('\ZwsContactsDatabase\ZwsFilters', 'validate_textfield_postcode'), 10, 1);
add_filter('zws_filter_enforce_numeric', array('\ZwsContactsDatabase\ZwsFilters', 'enforce_numeric'), 10, 1);
add_filter('zws_filter_limit_chars', array('\ZwsContactsDatabase\ZwsFilters', 'limit_chars'), 10, 1);
