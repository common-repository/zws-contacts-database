<?php

namespace ZwsContactsDatabase;

/**
 * Administration file for ZWS Contacts Database
 *
 * @copyright Copyright (c) 2015, Zaziork Web Solutions
 * @license This plugin uses the Composer library - see composer-license.txt
 * @author    Zaziork Web Solutions
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link https://www.zaziork.com/
 */
Class Admin {

    const OPTIONS_LABEL = 'zws_contacts_database_options';

    public static $notifications = array();

    public static function setup_menu() {
        add_menu_page('ZWS Contacts Database', 'ZWS Contacts Database', 'manage_options', 'zws-database-creator', array('\ZwsContactsDatabase\Admin',
            'settings')
        );
        add_action('admin_init', array('\ZwsContactsDatabase\Admin', 'settings_panel_fields'));
    }

    public static function settings() {

        // hande postbacks from form
        if (isset($_GET['postback'])) {
            $updated = false;
            // check to determine if this is form postback
            switch (apply_filters('zws_filter_basic_sanitize', $_GET['postback'])) {
                case 'set_options':
                    require_once(__DIR__ . '/SetOptions.php');
                    if (isset($_POST) && isset($_POST['my_nonce_field']) && wp_verify_nonce($_POST['my_nonce_field'], 'update_options_action')) {
                        // make the update. Returns true if successful, or false if not.
                        $updated = \ZwsContactsDatabase\SetOptions::update_options($_POST);
                    }
                    $updated ? self::$notifications['update_options'] = 'You have successfully changed some options!' : self::$notifications['update_options'] = 'No options changed!';
                    break;
                case 'clear_cache':
                    if (self::clear_cache()) {
                        self::$notifications['cache_cleared'] = 'Cache flushed!';
                    } elseif (self::clear_cache() === false) {
                        self::$notifications['cache_cleared'] = 'Cache not flushed!';
                    } else {
                        self::$notifications['cache_cleared'] = 'Memcached does not appear to be installed on your system!';
                    }
                    break;
                default:
                // carry on ...
            }
        }
        ?>

        <h1>ZWS Contacts Database Administration Page</h1>

        <!-- notifications section -->
        <div class="zws_contacts_database_notifications" style="margin:2em 0 0 2em;color:red;"><?php
            foreach (self::$notifications as $key => $value) {
                if (isset($value)) {
                    echo '<span class="zws_contacts_database_notfication_item" style="font-size:2em;">' . $value . '</span><br>';
                }
            }
            ?>
        </div>

        <!-- forms section -->
        <div class="forms"  style="position:relative;margin:1em;float:left;clear:left;">
            <div class="wrap" style="margin:1em;">
                <form method="post" action="<?php
                if ((isset($_GET['postback']) && apply_filters('zws_filter_basic_sanitize', $_GET['postback']) != 'set_options' ) || (!isset($_GET['postback']))) {
                    echo self::get_url() . '&postback=set_options';
                } else {
                    echo self::get_url();
                }
                ?>">
                          <?php
                          settings_fields("basic_options_section_group");
                          do_settings_sections("basic_options_section");
                          do_settings_sections("memcached_options_section");
                          submit_button();
                          ?>
                </form>
            </div>

            <!-- Clear cache button form  -->
            <div class="wrap" style="margin:1em;">
                <form method="post" action="<?php
                if (isset($_GET['postback']) && apply_filters('zws_filter_basic_sanitize', $_GET['postback']) != 'clear_cache' || (!isset($_GET['postback']))) {
                    echo self::get_url() . '&postback=clear_cache';
                } else {
                    echo self::get_url();
                }
                ?>">
                          <?php
                          do_settings_sections("clear_section_group");
                          submit_button('Manually Clear Cache Now');
                          ?>
                </form>
            </div>

            <div class="footer" style="position:relative;float:left;clear:left;">
                <p>Thank you for using ZWS Contacts Database.  <a href="https://www.aninstance.com/donate/">Donations are much appreciated!</a></p>
            </div>
            <?php
            // return true if ran without errors, for unit testing purposes.
            return true;
        }

        /* // define form elements // */

        /* general options */

        public static function google_api_key_form_field_element() {
            ?>
            <small class="zws-database-creator-form-helper" style="display:block;margin-bottom:1em;">Your Google API Key (server)</small>
            <input type="text" name="zws_contacts_database_google_server_api_key" size="55" id="zws_contacts_database_google_server_api_key"
                   value="<?php echo get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_google_server_api_key']; ?>" />
                   <?php
               }

               public static function privacy_page_url_form_field_element() {
                   ?>
            <small class="zws-database-creator-form-helper" style="display:block;margin-bottom:1em;">URL of privacy policy page (relative link - e.g. /privacy-policy)</small>
            <input type="text" name="zws_contacts_database_plugin_privacy_policy_url" size="55" id="zws_contacts_database_plugin_privacy_policy_url"
                   value="<?php echo get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_privacy_policy_url']; ?>" />
                   <?php
               }

               public static function map_contact_icon_url_form_field_element() {
                   ?>
            <small class="zws-database-creator-form-helper" style="display:block;margin-bottom:1em;">URL of the map icon to use for contacts</small>
            <input type="text" name="zws_contacts_database_plugin_map_contact_icon_url" size="55" id="zws_contacts_database_plugin_map_contact_icon_url"
                   value="<?php echo get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_map_contact_icon_url']; ?>" />
                   <?php
               }

               public static function map_target_icon_url_form_field_element() {
                   ?>
            <small class="zws-database-creator-form-helper" style="display:block;margin-bottom:1em;">URL of the map icon to use for target</small>
            <input type="text" name="zws_contacts_database_plugin_map_target_icon_url" size="55" id="zws_contacts_database_plugin_map_target_icon_url"
                   value="<?php echo get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_map_target_icon_url']; ?>" />
                   <?php
               }

               public static function map_base_icon_url_form_field_element() {
                   ?>
            <small class="zws-database-creator-form-helper" style="display:block;margin-bottom:1em;">URL of the map icon to use for 'home base'</small>
            <input type="text" name="zws_contacts_database_plugin_map_base_icon_url" size="55" id="zws_contacts_database_plugin_map_base_icon_url"
                   value="<?php echo get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_map_base_icon_url']; ?>" />
            <small style="display:block;margin-top:1em;">If you're looking for map icons, you may like to try <a href="https://mapicons.mapsmarker.com/" target="_blank">this resource</a>.

                <?php
            }

            public static function map_zoom_form_field_element() {
                ?>
                <small class="zws-database-creator-form-helper" style="display:block;margin-bottom:1em;">Initial zoom factor of the map (suggest between 7 and 10)</small>
                <input type="text" name="zws_contacts_database_plugin_google_map_zoom" size="55" id="zws_contacts_database_plugin_google_map_zoom"
                       value="<?php echo get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_google_map_zoom']; ?>" />

                <?php
            }

            public static function base_postcode_form_field_element() {
                ?>
                <small class="zws-database-creator-form-helper" style="display:block;margin-bottom:1em;">Postcode of 'home base' (No spaces - e.g. AB467TR)</small>
                <input type="text" name="zws_contacts_database_plugin_base_postcode" size="55" id="zws_contacts_database_plugin_base_postcode"
                       value="<?php echo get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_base_postcode']; ?>" />
                       <?php
                   }

                   public static function base_name_form_field_element() {
                       ?>
                <small class="zws-database-creator-form-helper" style="display:block;margin-bottom:1em;">Display name of 'home base'</small>
                <input type="text" name="zws_contacts_database_plugin_base_name" size="55" id="zws_contacts_database_plugin_base_name"
                       value="<?php echo get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_base_name']; ?>" />
                       <?php
                   }

                   public static function country_form_field_element() {
                       $current_country = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_country_of_use'];
                       echo '<small class="zws-database-creator-form-helper" style="display:block;margin-bottom:1em;">Country of deployment</small>';
                       echo '<select name="zws_contacts_database_plugin_country_of_use" id="zws_contacts_database_plugin_country_of_use">';
                       foreach (unserialize(ZWS_CDB_COUNTRY) as $country_name => $country_code) {
                           if ($country_code == apply_filters(
                                           'zws_filter_basic_sanitize', get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_country_of_use'])) {
                               $selected = 'selected';
                           } else {
                               $selected = '';
                           }
                           echo "<option {$selected} value=\"{$country_code}\">{$country_name}</option>";
                       }
                       echo '</select>';
                   }

                   public static function admin_email_active_form_field_element() {
                       ?>
                <small class="zws-rest_api-consumer-form-helper" style="display:block;margin-bottom:1em;">Whether you want notification email(s) sent to administrator(s) upon new registrations</small>
                <?php
                // check to see if option is set as true or false, then pre-populate the radio buttons accordingly
                $true_checked = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_admin_email_active'] === 'TRUE' ? 'checked' : '';
                $false_checked = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_admin_email_active'] === 'FALSE' ? 'checked' : '';
                echo '<input type="radio" name="zws_contacts_database_plugin_admin_email_active" value="TRUE" ' . $true_checked . '>Yes
                      <br>
                      <input type="radio" name="zws_contacts_database_plugin_admin_email_active" value="FALSE" ' . $false_checked . '>No';
            }

            public static function admin_email_form_field_element() {
                $emails = '';
                if (is_array(get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_admin_email']) &&
                        !empty(get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_admin_email'])) {
                    $emails = implode(' ', get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_admin_email']);
                }
                echo "<small class=\"zws-database-creator-form-helper\" style=\"display:block;margin-bottom:1em;\">"
                . "Notification email address(es). Separate multiple with a space (e.g. first@email1.com second@email2.com)</small>";
                echo "<textarea name=\"zws_contacts_database_plugin_admin_email\" id=\"zws_contacts_database_plugin_admin_email\" cols=\"55\" rows=\"3\">"
                . "{$emails}</textarea>";
            }

            public static function reg_email_active_form_field_element() {
                ?>
                <small class="zws-rest_api-consumer-form-helper" style="display:block;margin-bottom:1em;">Whether you want notification email(s) sent to new registrants</small>
                <?php
                // check to see if option is set as true or false, then pre-populate the radio buttons accordingly
                $true_checked = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_reg_email_active'] === 'TRUE' ? 'checked' : '';
                $false_checked = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_reg_email_active'] === 'FALSE' ? 'checked' : '';
                echo '<input type="radio" name="zws_contacts_database_plugin_reg_email_active" value="TRUE" ' . $true_checked . '>Yes
                      <br>
                      <input type="radio" name="zws_contacts_database_plugin_reg_email_active" value="FALSE" ' . $false_checked . '>No';
            }

            public static function reg_email_from_address_form_field_element() {
                $reg_email = '';
                if (is_array(get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_reg_email_from']) && !empty(get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_reg_email_from'])) {
                    $reg_email = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_reg_email_from'][0] .
                            ', ' . get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_reg_email_from'][1];
                }
                echo "<small class=\"zws-database-creator-form-helper\" style=\"display:block;margin-bottom:1em;\">The \"From\" email address of the notification email sent to registrants in form: THE NAME, name@domain.com</small>";
                echo "<input type=\"text\" name=\"zws_contacts_database_plugin_reg_email_from\" size=\"55\" id=\"zws_contacts_database_plugin_reg_email_from\" placeholder=\"My name, me@domain.com\" value=\"{$reg_email}\"/>";
            }

            public static function reg_email_subject_form_field_element() {
                ?>
                <small class="zws-database-creator-form-helper" style="display:block;margin-bottom:1em;">The text of the "Subject" field of the notification email sent to registrants (max 45 characters)</small>
                <input type="text" name="zws_contacts_database_plugin_reg_email_subject" size="55" maxlength="45" id="zws_contacts_database_plugin_reg_email_subject"
                       value="<?php echo get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_reg_email_subject']; ?>" />
                       <?php
                   }

                   public static function reg_email_form_field_element() {
                       $message = self::get_notification_email();
                       echo "<small class=\"zws-database-creator-form-helper\" style=\"display:block;margin-bottom:1em;\">"
                       . "Text of confirmation email to be sent to new registrants. May include the following tags: {{first-name}}, {{last-name}}, {{site-name}}.</small>"
                       . "<small>N.B. This email WILL be overwritten with a default if you reinstall the plugin, regardless of whether you retain other options upon uninstall.</small>";
                       echo "<textarea name=\"zws_contacts_database_plugin_reg_email\" id=\"zws_contacts_database_plugin_reg_email\" cols=\"55\" rows=\"11\">"
                       . $message . "</textarea>";
                   }

                   public static function full_removal_on_uninstall_element() {
                       ?>
                <small class="zws-rest_api-consumer-form-helper" style="display:block;margin-bottom:1em;">Whether you want to entirely remove ALL DATABASES AND OPTIONS when this plugin is uninstalled</small>
                <?php
                // check to see if option is set as true or false, then pre-populate the radio buttons accordingly
                $true_checked = get_site_option('zws_contacts_database_remove_data') === 'TRUE' ? 'checked' : '';
                $false_checked = get_site_option('zws_contacts_database_remove_data') === 'FALSE' ? 'checked' : '';
                echo '<input type="radio" name="zws_contacts_database_remove_data" value="TRUE" ' . $true_checked . '>Yes
            <br>
            <input type="radio" name="zws_contacts_database_remove_data" value="FALSE" ' . $false_checked . '>No';
            }

            /* memcachced options */

            public static function memcached_server_active_element() {
                ?>
                <small class="zws-rest_api-consumer-form-helper" style="display:block;
                       margin-bottom:1em;
                       ">Use Memcached to cache API requests</small>
                       <?php
                       // check to see if option is set as true or false, then pre-populate the radio buttons accordingly
                       $true_checked = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_memcached_active'] === 'TRUE' ? 'checked' : '';
                       $false_checked = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_memcached_active'] === 'FALSE' ? 'checked' : '';
                       echo '<input type="radio" name="zws_contacts_database_memcached_active" value="TRUE" ' . $true_checked . '>Yes
            <br>
            <input type="radio" name="zws_contacts_database_memcached_active" value="FALSE" ' . $false_checked . '>No';
                   }

                   public static function memcached_server_period_element() {
                       ?>
                <small class="zws-rest_api-consumer-form-helper" style="display:block;
                       margin-bottom:1em;
                       ">Length of time to cache content (in seconds)</small>
                <input type="text" name="zws_contacts_database_memcached_period" size="55" id="zws_contacts_database_memcached_period"
                       value="<?php echo get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_memcached_period'];
                       ?>" />
                       <?php
                   }

                   public static function memcached_server_IP_element() {
                       ?>
                <small class="zws-rest_api-consumer-form-helper" style="display:block;margin-bottom:1em;">Memcached server IP (default 127.0.0.1)</small>
                <input type="text" name="zws_contacts_database_memcached_ip" size="55" id="zws_contacts_database_memcached_ip"
                       value="<?php echo get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_memcached_ip']; ?>" />
                       <?php
                   }

                   public static function memcached_server_port_element() {
                       ?>
                <small class="zws-rest_api-consumer-form-helper" style="display:block;margin-bottom:1em;">Memcached server port (default 11211)</small>
                <input type="text" name="zws_contacts_database_memcached_port" size="55" id="zws_contacts_database_memcached_port"
                       value="<?php echo get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_memcached_port']; ?>" />
                <?php
            }

            public static function nonce_element() {
                wp_nonce_field('update_options_action', 'my_nonce_field');
            }

            public static function settings_panel_fields() {
                // add the sections to the section groups
                add_settings_section('basic_options_section_group', 'Basic Configuration Options', null, 'basic_options_section');
                add_settings_section('basic_options_section_group', 'Memcached Configuration Options', null, 'memcached_options_section');
                // add the fields to the sections
                add_settings_field('zws_contacts_database_google_server_api_key', 'Google Server API Key', array('\ZwsContactsDatabase\Admin',
                    'google_api_key_form_field_element'), 'basic_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_plugin_map_contact_icon_url', 'Map contacts icon URL', array('\ZwsContactsDatabase\Admin',
                    'map_contact_icon_url_form_field_element'), 'basic_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_plugin_map_target_icon_url', 'Map target icon URL', array('\ZwsContactsDatabase\Admin',
                    'map_target_icon_url_form_field_element'), 'basic_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_plugin_map_base_icon_url', 'Map home base icon URL', array('\ZwsContactsDatabase\Admin',
                    'map_base_icon_url_form_field_element'), 'basic_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_plugin_google_map_zoom', 'Initial map zoom', array('\ZwsContactsDatabase\Admin',
                    'map_zoom_form_field_element'), 'basic_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_plugin_base_postcode', 'Home base postcode', array('\ZwsContactsDatabase\Admin',
                    'base_postcode_form_field_element'), 'basic_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_plugin_base_name', 'Home base display name', array('\ZwsContactsDatabase\Admin',
                    'base_name_form_field_element'), 'basic_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_plugin_country_of_use', 'Country of deployment', array('\ZwsContactsDatabase\Admin',
                    'country_form_field_element'), 'basic_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_plugin_admin_email_active', 'Notifications', array('\ZwsContactsDatabase\Admin',
                    'admin_email_active_form_field_element'), 'basic_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_plugin_admin_email', 'Notification email address(es)', array('\ZwsContactsDatabase\Admin',
                    'admin_email_form_field_element'), 'basic_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_plugin_reg_email_active', 'Send notifiation to registrants?', array('\ZwsContactsDatabase\Admin',
                    'reg_email_active_form_field_element'), 'basic_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_plugin_reg_email_from', 'Notification email "From" address', array('\ZwsContactsDatabase\Admin',
                    'reg_email_from_address_form_field_element'), 'basic_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_plugin_reg_email_subject', 'Notification subject text', array('\ZwsContactsDatabase\Admin',
                    'reg_email_subject_form_field_element'), 'basic_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_plugin_reg_email', 'Notification email text', array('\ZwsContactsDatabase\Admin',
                    'reg_email_form_field_element'), 'basic_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_plugin_privacy_policy_url', 'Privacy policy URL', array('\ZwsContactsDatabase\Admin',
                    'privacy_page_url_form_field_element'), 'basic_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_remove_data', 'Fully remove all plugin\'s databases & options on uninstall?', array('\ZwsContactsDatabase\Admin',
                    'full_removal_on_uninstall_element'), 'basic_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_memcached_active', 'Use cache?', array('\ZwsContactsDatabase\Admin',
                    'memcached_server_active_element'), 'memcached_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_memcached_period', 'Memcached period', array('\ZwsContactsDatabase\Admin',
                    'memcached_server_period_element'), 'memcached_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_memcached_ip', 'Memached IP', array('\ZwsContactsDatabase\Admin',
                    'memcached_server_IP_element'), 'memcached_options_section', 'basic_options_section_group');
                add_settings_field('zws_contacts_database_memcached_port', 'Memcached port', array('\ZwsContactsDatabase\Admin',
                    'memcached_server_port_element'), 'memcached_options_section', 'basic_options_section_group');
                // add the nonce field
                add_settings_field('my_nonce_field', '', array('\ZwsContactsDatabase\Admin',
                    'nonce_element'), 'basic_options_section', 'basic_options_section_group');
            }

            public static function clear_cache() {
                // method to clear the memcached cache
                $memcached_ip = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_memcached_ip'];
                $memcached_port = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_memcached_port'];
                $memcached_key_base = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_memcached_keybase'];
                if (class_exists('\Memcached')) {
                    $cache = new \Memcached();
                    if ($cache->addServer($memcached_ip, $memcached_port)) {
                        if ($cache->delete($memcached_key_base)) {
                            return true;
                        }
                    }
                } else {
                    return null;
                }
                return false;
            }

            public static function get_url() {
                if (isset($_SERVER['HTTPS'])) {
                    $protocol = 'https';
                } else {
                    $protocol = 'http';
                }
                return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            }

            private static function get_notification_email() {
                /// method that returns a string containing the notification email file
                $email_file_url = __DIR__ . '/../inc/registration_confirmation.tpl';
                if (file_get_contents($email_file_url) !== false) {
                    return stripslashes(apply_filters('zws_filter_text_with_linebreak', file_get_contents($email_file_url)));
                } else {
                    return 'There is currently no email saved';
                }
            }

        }
