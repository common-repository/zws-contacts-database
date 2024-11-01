<?php

namespace ZwsContactsDatabase;

/**
 *  API query file for ZWS  Contacts Database
 *
 * @copyright Copyright (c) 2015, Zaziork Web Solutions
 * @author    Zaziork Web Solutions
 * @license This plugin uses the Composer library - see composer-license.txt
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link https://www.zaziork.com/zws-wordpress-contacts-database-plugin/
 */
use GuzzleHttp\Client as Client;

Class QueryAPI {

    const OPTIONS_LABEL = 'zws_contacts_database_options';

    public static $error = 'An error has occurred!';

    public static function makeQuery($base_url, $path) {

        require __DIR__ . '/../vendor/autoload.php';

        $memcached_ip = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_memcached_ip'];
        $memcached_port = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_memcached_port'];
        $cache_period = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_memcached_period'];
        $memcached_active = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_memcached_active'];
        $memcached_error = 'Failed to save to cache! Please check the Memcache server IP and port on the Settings page, '
                . 'and that Memcached is installed and running on your system. If all else fails, disable the Memcached feature.';
        $guzzle_error = 'Check the URL, path and protocol ...';
        $connection_timeout = 'The connection to the API timed out ...';
        $memcached_key_base = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_memcached_keybase'];
        $memcached_request_identifier = md5($base_url) . ':' . md5($path);

// Set up cache if activated in options
        if ($memcached_active === 'TRUE' && class_exists('\Memcached')) {
            // error_log('Memcached identified as active ...'); // debug
            $cache = new \Memcached();
            $cache->addServer($memcached_ip, $memcached_port);
            $cache_key = $memcached_key_base;

// Try to get records
            $memcached_cache_result = $cache->get($cache_key);
            if (isset($memcached_cache_result[$memcached_request_identifier])) {
                // error_log('Returning cached ...'); // debug
                return array('cached' => true, 'returned_data' => $memcached_cache_result[$memcached_request_identifier]);
            }
        }
        try {
            $client = new Client(['base_uri' => $base_url, 'timeout' => 3.0, 'connect_timeout' => 2.0]);
            $request = $client->get($path);
            if ($request->getStatusCode() === 200) {
                // assign returned json_decoded records to a variable
                if (!$response = json_decode($request->getBody(), true)) {
                    error_log('Error retrieving Json from API');
                    return false;
                }
                // add the returned records to the cache if it is active
                if ($memcached_active === 'TRUE' && class_exists('\Memcached')) {
                    error_log('ADDING TO CACHED ...');
                    $memcached_cache_result[$memcached_request_identifier] = $response;
                    $cache->set($cache_key, $memcached_cache_result, $cache_period) or die($memcached_error);
                }
                return array('cached' => false, 'returned_data' => $response);
            } else {
                return false;
            }
        } catch (GuzzleHttp\Exception\ConnectException $e) {
            error_log($connection_timeout);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            error_log($guzzle_error . ' | ' . $e);
            return false;
        } catch (InvalidArgumentException $e) {
            error_log($guzzle_error . ' | ' . $e);
            return false;
        }

        // return false if no records to return
        return false;
    }

}
