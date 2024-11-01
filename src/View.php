<?php

namespace ZwsContactsDatabase;

/**
 * View file for ZWS  Contacts Database
 *
 * @copyright Copyright (c) 2015, Zaziork Web Solutions
 * @author    Zaziork Web Solutions
 * @license This plugin uses the Composer library - see composer-license.txt
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link https://www.zaziork.com/zws-contacts-database-plugin/
 */
Class View {

    const MAPS_API_BASE_URL = 'https://maps.googleapis.com/maps/api/geocode/json';
    const OPTIONS_LABEL = 'zws_contacts_database_options';

    public static function submission_form($atts = NULL) {

// turn on output buffer
        \ob_start();
//  display form or submit if a postback
        self::display_or_action();
        return \ob_get_clean();
    }

    private static function create_form() {
        $privacy_policy_url = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_privacy_policy_url'];
        $privacy_blurb = '<small class="form-privacy-checkbox">Please check the box to indicate that you have read and agree to our <a href="' . $privacy_policy_url .
                '" target="_blank">data protection policy</a>&nbsp;</small>';

// create the input form
        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
        echo '<h3>Your details</h3><p>';
        echo 'Your first name (required) <br />';
        echo '<input type="text" name="first_name" required="required" placeholder="First name" pattern="[a-zA-Z0-9]+" value="' . ( isset($_POST["first_name"]) ? esc_attr($_POST["first_name"]) : '' ) . '" size="40" />';
        echo '</p>';
        echo '<p>';
        echo 'Your last name (required) <br />';
        echo '<input type="text" name="last_name" required="required" placeholder="Last name" pattern="[a-zA-Z0-9]+" value="' . ( isset($_POST["last_name"]) ? esc_attr($_POST["last_name"]) : '' ) . '" size="40" />';
        echo '</p>';
        echo '<p>';
        echo 'Your postcode / zipcode (required - no spaces - e.g. AB329BR) <br />';
        echo '<input type="text" name="postcode" required="required" placeholder="Postcode" pattern="[a-zA-Z0-9]+" maxlength="7" value="' . ( isset($_POST["postcode"]) ? esc_attr($_POST["postcode"]) : '' ) . '" size="8" />';
        echo '</p>';
        echo '<p>';
        echo 'Your phone number (required) <br />';
        echo '<input type="text" name="phone" required="required" placeholder="Phone" pattern="[0-9]+" value="' . ( isset($_POST["phone"]) ? esc_attr($_POST["phone"]) : '' ) . '" size="40" />';
        echo '</p>';
        echo '<p>';
        echo 'Your Email (required) <br />';
        echo '<input type="email" name="email" required="required" placeholder="Email" value="' . ( isset($_POST["email"]) ? esc_attr($_POST["email"]) : '' ) . '" size="40" />';
        echo '</p>';
        echo '<h3>How far can you cover?</h3><p>';
        echo 'Distance from your location you\'d cover (full miles, required)<br />';
        echo '<input type="text" name="max_radius" required="required" placeholder="Distance" pattern="[0-9]+" value="' . ( isset($_POST["max_radius"]) ? esc_attr($_POST["max_radius"]) : '' ) . '" size="9" />';
        echo '</p>';
        echo '<h3>When are you available?</h3><p style="display:inline-block;margin-bottom:1em;font-size:0.7em;">'
        . 'Times are in 24 hour clock format (e.g. 00:00 = midnight; 02:30 = 2.30am, 14:30 = 2.30pm).<br>'
        . '"Unavailable" indicates you are unavailable for the <strong>entire day</strong>.<br>'
        . 'By default, the options below are set to <strong>"Unavailable"</strong> every day. Please adjust as required!<br>'
        . 'Feel free to provide more detail in the "Extra information" section if necessary.</p>';
        foreach (unserialize(ZWS_CDB_DAYS) as $value => $day) {
            // note: put the time selectors inside a div with class of zws-contacts-db-modal, as sharing jquery.timepicker.init.js file
            // with update datebase form (displayed in a modal in ZwsPaginator) - where that class is necessary
            // as there are multiple divs using same class, but only the active modal has the zws-contacts-db-modal class added when the modal is opened,
            // which allows only the active class (open modal) to be targeted in jquery.timepicker.init.js.
            echo '<div class="zws-contacts-db-modal"><p>';
            echo 'Times available on ' . ucfirst($day) . '<br>';
            echo '<span class="zws-contacts-database-split-input-class" style="display:inline-block;width:35%;margin-right:1em;">';
            echo 'Earliest available<br>';
            echo '<input id="zws-contacts-database-earlist-time-' . $day . '" required="required" type="text" name="earliest_time_' . $day . '" value="' . ( isset($_POST["earliest_time_' . $day . '"]) ? esc_attr($_POST["earlist_time_' . $day . '"]) : '' ) . '" size="8" />';
            echo '</span><span class="zws-contacts-database-split-input-class" style="display:inline-block;width:35%;margin-right:1em;">';
            echo 'Latest available<br>';
            echo '<input id="zws-contacts-database-latest-time-' . $day . '" required="required" type="text" name="latest_time_' . $day . '" value="' . ( isset($_POST["latest_time_' . $day . '"]) ? esc_attr($_POST["latest_time_' . $day . '"]) : '' ) . '"/>';
            echo '</span></p></div>';
        }
        echo '<h3>Anything else you\'d like to mention?</h3><p>';
        echo 'Any extra information (max. 1000 characters)<br />';
        echo '<textarea rows="10" cols="35" name="extra_info" placeholder="Extra information" maxlength="950">' . ( isset($_POST["extra_info"]) ? esc_attr($_POST["extra_info"]) : '' ) . '</textarea>';
        echo '</p>';
        echo '<h3>Complete registration</h3><p>';
        echo $privacy_blurb . '<input type="checkbox" name="privacy_accept" value="accept">';
        echo '</p>';
        wp_nonce_field('submit_details_action', 'my_nonce_field');
        echo '<p><input type="submit" name="submitted" value="Submit"/></p>';
        echo '</form>';
    }

    private static function display_or_action() {
        $safe_values = array();
// checks if incoming POST, and that nonce was set, and that nonce details match
        if (isset($_POST['submitted']) &&
                isset($_POST['my_nonce_field']) &&
                wp_verify_nonce(apply_filters('zws_filter_basic_sanitize', $_POST['my_nonce_field']), 'submit_details_action')) {
// sanitise values
            $safe_values['first_name'] = apply_filters('zws_filter_basic_sanitize', $_POST['first_name']);
            $safe_values['last_name'] = apply_filters('zws_filter_basic_sanitize', $_POST['last_name']);
            $safe_values['postcode'] = apply_filters('zws_filter_sanitize_postcode', $_POST['postcode']);
            $safe_values['phone'] = apply_filters('zws_filter_enforce_numeric', ($_POST['phone']));
            $safe_values['email'] = apply_filters('zws_filter_basic_sanitize', $_POST['email']);
            $safe_values['max_radius'] = apply_filters('zws_filter_enforce_numeric', $_POST['max_radius']);
            $safe_values['extra_info'] = apply_filters(
                    'zws_filter_limit_chars', apply_filters('zws_filter_text_with_linebreak', $_POST['extra_info']));
            $safe_values['pp_accepted'] = true ? isset($_POST['privacy_accept']) : false;
            foreach (unserialize(ZWS_CDB_DAYS)as $key => $day) {
                if (sanitize_text_field($_POST['earliest_time_' . $day]) !== 'Unavailable') {
                    $safe_values['earliest_time_' . $day] = apply_filters('zws_filter_basic_sanitize', $_POST['earliest_time_' . $day]);
                } else {
                    $safe_values['earliest_time_' . $day] = 'UNAVL';
                }
                $safe_values['latest_time_' . $day] = apply_filters('zws_filter_basic_sanitize', $_POST['latest_time_' . $day]);
            }

// verify privacy policy has been accepted
            if (!$safe_values['pp_accepted']) {
                return self::failure_view('privacy');
            }
// query google maps api to get longitute and latitude for the postcode, to pull back from db when displayed on map
            require_once(__DIR__ . '/QueryAPI.php');
            $google_api_key = apply_filters('zws_filter_basic_sanitize', get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_google_server_api_key']);
        $country_code = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_country_of_use'];
        $country_list = unserialize(ZWS_CDB_COUNTRY);
        $country_name = array_search($country_code, $country_list);
            $path = '?address=' . $safe_values['postcode'] . ',' . $country_name . '&language=en-EN&sensor=false&key=' . $google_api_key;
            $data = \ZwsContactsDatabase\QueryAPI::makeQuery(self::MAPS_API_BASE_URL, $path);
            if ($data['returned_data'] && $data['returned_data']['status'] === 'OK') {
                if ($data['cached']) {
// error_log('THE DATA WAS CACHED ...'); // debug
                }
                $safe_values['lat'] = sanitize_text_field($data['returned_data']['results'][0]['geometry']['location']['lat']);
                $safe_values['lng'] = sanitize_text_field($data['returned_data']['results'][0]['geometry']['location']['lng']);
            } else {
                return self::failure_view();
            }


// send to database
            require_once(__DIR__ . '/Database.php');
            if (\ZwsContactsDatabase\Database::insert($safe_values, $user_signup=True)) {
                // email admins
                if (!self::email_notifications($safe_values)) {
                    error_log('Error sending email to administrator ...');
                }
                // return success
                return self::success_view();
            } else {
                return self::failure_view();
            }
        } else {
            // if it wasn't a form submission, create / present the form
            return self::create_form();
        }
    }

    private static function success_view() {
        $success_message = '<div class="zws-contacts-db-success-message"><p>Thank you for submitting your details!</p>'
                . '<p>Your name, postcode, contact details, and any addtional information you submitted, have been successfully stored in our database.</p>'
                . '<p>If you would like us to remove your details at any point, just let as know.</p></div>';
        echo $success_message;
    }

    private static function failure_view($reason = null) {
        switch ($reason) {
            case 'privacy':
                $message = '<div class="zws-contacts-db-failure-message"><p>You did not accept our privacy policy, therefore your details have not been submitted to our database.</p></div>'
                        . ' <button onclick="goBack()">Try Again?</button><script>function goBack() { window.history.back();}</script>';
                break;
            default:
                $message = '<div class="zws-contacts-db-failure-message"><p>Unfortunately, an error occurred and your details have not been submitted.</p>'
                        . '<p>Did you already register using the same phone or email? Or, maybe you mistyped your postcode? Or, perhaps you submitted more than 950 charcters as your additional information?</p>'
                        . '<p>Please try again, but if you receive this message once more just contact us and we\'ll add your details manually.</p></div>';
                break;
        }
        echo $message;
    }

    private static function email_notifications($safe_values) {
        //// method to email admins with details of new registrant
        // initial setup and checks
        $admin_email = get_site_option('admin_email');
        if (!is_email($admin_email)) {
            return false;
        }
        $registrant_email = $safe_values['email'];
        if (!is_email($registrant_email)) {
            return false;
        }
        // email admins
        try {
            if (get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_admin_email_active']) {
                // grab admin emails array from options (note, unfiltered)
                $emails = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_admin_email'];
                // loop emails, validate it is an email, construct headers and message, send email
                if (!empty($emails)) {
                    $subject = 'A new contact has registered!'; // allow admin config as an option in future ...
                    $message = "A new contact has registered using ZWS Contacts Database!\r\n"
                            . "The name of the contact is: {$safe_values['first_name']} {$safe_values['last_name']}\r\n"
                            . "Full details are availabe via your adminstration dashboard."; // allow admin config as option in future ...
                    // wrap lines if more than 70 characters ...
                    $message = wordwrap($message, 70, "\r\n");
                    // reply to
                    $reply_to = $admin_email;
                    // to
                    $primary_email = apply_filters(
                            'zws_filter_basic_sanitize', $emails[0]);
                    $to = "{$primary_email}";
                    // from
                    $from = "ZWS Contacts Database <{$admin_email}>";
                    // count number of admin email addresses to sent to ...
                    $emails_count = count($emails);
                    $cc = '';
                    // if more than one email, construct CC header
                    if ($emails_count > 1) {
                        foreach ($emails as $key => $email) {
                            if (is_email(apply_filters(
                                                    'zws_filter_basic_sanitize', $email)) && $key > 0) {
                                $cc .= apply_filters('zws_basic_sanitize', $email) . ', ';
                            }
                        }
                        // trim trailing comma and space
                        $cc = rtrim($cc);
                    }
                    // construct headers
                    $headers = array();
                    array_push($headers, "From: {$from}");
                    array_push($headers, "Cc: {$cc}");
                    array_push($headers, "Reply-To: {$reply_to}");
                    array_push($headers, "X-Mailer: PHP/" . phpversion());
                    $extras = "-f{$admin_email} -r{$admin_email}";
                    // send email
                    if (!wp_mail($to, $subject, $message, implode("\r\n", $headers), $extras)) {
                        error_log("An error occurred whilst sending email to the administrators ...");
                    }
                }
            }
            // email registrants
            if (get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_reg_email_active']) {
                // subject
                $reg_subject = apply_filters(
                        'zws_filter_basic_sanitize', get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_reg_email_subject']);
                // email
                $reg_email = 'Thank you for registering with ' . get_site_option('blogname') . '!'; // default in case all else fails!
                $email_file_url = __DIR__ . '/../inc/registration_confirmation.tpl';
                if (file_get_contents($email_file_url) !== false) {
                    $formatted_reg_email = self::format_email(file_get_contents($email_file_url), $safe_values);
                    if ($formatted_reg_email !== false) {
                        $reg_email = wordwrap($formatted_reg_email, 70, "\r\n");
                    }
                }
                // from
                $reg_from = get_site_option('blogname') . ' <' . get_site_option('admin_email') . '>';
                // overwrite with user saved value if exists
                if (is_array(get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_reg_email_from']) &&
                        !empty(get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_reg_email_from'])) {
                    if (is_email(get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_reg_email_from'][1])) {
                        $reg_from = apply_filters(
                                        'zws_filter_basic_sanitize', get_site_option(
                                                self::OPTIONS_LABEL)['zws_contacts_database_plugin_reg_email_from'][0]) .
                                ' <' . get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_reg_email_from'][1] . '>';
                    }
                }
                // reply to
                $reg_reply_to = $admin_email;
                // to
                $reg_to = $safe_values['email'];
                // construct headers
                $reg_headers = array();
                array_push($reg_headers, "From: {$reg_from}");
                array_push($reg_headers, "Reply-To: {$reg_reply_to}");
                array_push($reg_headers, "X-Mailer: PHP/" . phpversion());
                $reg_extras = "-f{$admin_email} -r{$admin_email}";
                // send email
                if (!wp_mail($reg_to, $reg_subject, $reg_email, implode("\r\n", $reg_headers), $reg_extras)) {
                    error_log('An error occurred whilst sending the confirmation email to the registrant ...');
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private static function format_email($email, $safe_values) {
        //// accepts email template and returns formatted string
        // define replacement tags
        $first_name_tag = '{{first-name}}';
        $last_name_tag = '{{last-name}}';
        $site_name_tag = '{{site-name}}';
        try {
            // replace first-name tag
            $email = str_replace($first_name_tag, $safe_values['first_name'], $email);
            // replace last-name tag
            $email = str_replace($last_name_tag, $safe_values['last_name'], $email);
            // replace admin-name tag
            $email = str_replace($site_name_tag, get_site_option('blogname'), $email);
            return stripslashes(htmlspecialchars_decode(apply_filters('zws_filter_text_with_linebreak', $email)));
        } catch (Exception $e) {
            return false;
        }
    }
}
