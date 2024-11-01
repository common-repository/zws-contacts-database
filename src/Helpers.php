<?php

namespace ZwsContactsDatabase;

/**
 * Helpers class file for ZWS Contacts Database
 *
 * @copyright Copyright (c) 2015, Zaziork Web Solutions
 * @license This plugin uses the Composer library - see composer-license.txt
 * @author    Zaziork Web Solutions
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link https://www.zaziork.com/
 */
Class Helpers {

    public static function set_url_query($new_query = array()) {
        // takes array (query_name => query_value) and returns the complete CURRENT URI with the incoming parameters changed or added    
        if (empty($new_query)) {
            return false;
        }

        $request_uri = esc_url($_SERVER['REQUEST_URI']);

        foreach ($new_query as $param_name => $param_value) {       
            if ($request_uri !== false) {   
                $request_uri = self::create_query_string($param_name, $param_value, $request_uri);
            }
        }
        return $request_uri;
    }

    public static function set_url_query_cleared($new_query = array()) {
        // takes array (query_name => query_value) and returns the complete CURRENT URI with the incoming parameters changed or added    
        if (empty($new_query)) {
            return false;
        }

        // get the request url WITHOUT any query parameters
        $request_uri = esc_url($url = strtok($_SERVER["REQUEST_URI"], '?'));

        foreach ($new_query as $param_name => $param_value) {     
            if ($request_uri !== false) {
                $request_uri = self::create_query_string($param_name, $param_value, $request_uri);
            }
        }
        return $request_uri;
    }

    private static function create_query_string($param_name, $param_value, $requested_uri) {
        // returns a query string created from the input parameters.
        // make safe
        $request_uri = esc_url($requested_uri);
        // generate uri
        try {
            // generates and returns a new URI with the incoming parameters changed or added
            $pattern = '/(.*?[&|\?])(' . $param_name . '=[^&]*)(.*$)/i';
            $replacement = '$1' . $param_name . '=' . $param_value . '$3';
            // if no query string in current url
            if (!strpos($request_uri, '?')) {
                $return = $request_uri . '?' . $param_name . '=' . $param_value;
            } else {
                // if new query name already exists in string
                if (preg_match('/\b' . $param_name . '\b/i', $request_uri)) {
                    $return = preg_replace($pattern, $replacement, $request_uri);
                } else {
                    $return = $request_uri .= '&' . $param_name . '=' . $param_value;
                }
            }
        } catch (Exception $e) {
            return false;
        }
        // return url encoded string     
        return $return;
    }

    public static function getCss($id) {
        /* method to return pre-defined css styling. 
         * Returns a string of css statements (without the element locators or brackets, obviously!).
         */
        $css = '';
        switch ($id) {
            case 'label_style_tag':
                return
                        'background-color:#1E73BE;'
                        . 'text-align:centre;'
                        . 'color:yellow;padding:0.2em;'
                        . '-webkit-box-shadow: -5px 5px 5px 0px rgba(0,0,0,0.75);'
                        . '-moz-box-shadow: -5px 5px 5px 0px rgba(0,0,0,0.75);'
                        . 'box-shadow: -5px 5px 5px 0px rgba(0,0,0,0.75);';
            case 'header_style_tag':
                return
                        'background-color:#1E73BE;'
                        . 'display:inline-block;'
                        . 'text-align:centre;'
                        . 'color:yellow;padding:0.2em;'
                        . '-webkit-box-shadow: -5px 5px 5px 0px rgba(0,0,0,0.75);'
                        . '-moz-box-shadow: -5px 5px 5px 0px rgba(0,0,0,0.75);'
                        . 'box-shadow: -5px 5px 5px 0px rgba(0,0,0,0.75);'
                        . 'margin:1em 0;'
                        . 'text-transform:capitalize';
            case 'entry_style_tag':
                return
                        'background-color:#345114;'
                        . 'color:yellow;padding:0.2em;'
                        . '-webkit-box-shadow: -5px 5px 5px 0px rgba(0,0,0,0.75);'
                        . '-moz-box-shadow: -5px 5px 5px 0px rgba(0,0,0,0.75);'
                        . 'box-shadow: -5px 5px 5px 0px rgba(0,0,0,0.75);'
                        . 'margin-bottom:1em;';
            case 'page_index':
                return
                        'padding:0.3em;'
                        . 'color:#345114;'
                        . 'background-color:yellow;'
                        . '-webkit-box-shadow: -5px 5px 5px 0px rgba(0,0,0,0.75);'
                        . '-moz-box-shadow: -5px 5px 5px 0px rgba(0,0,0,0.75);'
                        . 'box-shadow: -5px 5px 5px 0px rgba(0,0,0,0.75);'
                        . 'text-decoration:none;';
            case 'data_style_tag':
                return
                        'margin-left:0.5em;'
                        . 'color:yellow;';
            case 'link_style_tag':
                return
                        'padding:0.3em;'
                        . 'color:yellow;'
                        . 'background-color:#1E73BE;';
            case 'list_style_tag':
                return
                        'margin:0.5em;';
            case 'list_style_tag_button_li;':
                return
                        'margin-top:1em;'
                        . 'color:yellow;';
            case 'zws-contacts-db-success-message':
                return
                        'background-color:green;'
                        . 'padding:0.5em;'
                        . 'margin-bottom:0.5em;'
                        . 'text-align:center;'
                        . 'color:yellow;';
            case 'zws-contacts-db-failure-message':
                return
                        'background-color:yellow;'
                        . 'padding:0.5em;'
                        . 'margin-bottom:0.5em;'
                        . 'color:red;';
            default:
                return null;
        }
    }

}
