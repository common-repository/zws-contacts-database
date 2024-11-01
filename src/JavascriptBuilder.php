<?php

namespace ZwsContactsDatabase;

/**
 * Javascript builder file for ZWS Contacts Database
 *
 * @copyright Copyright (c) 2015, Zaziork Web Solutions
 * @license This plugin uses the Composer library - see composer-license.txt
 * @author    Zaziork Web Solutions
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link https://www.zaziork.com/
 */
Class JavascriptBuilder {

    const OPTIONS_LABEL = 'zws_contacts_database_options';

    public static function generate_js($map_config) {

        // check params have been passed
        if (!isset($map_config['target_postcode']) || !isset($map_config['contacts_array_safe']) || !isset($map_config['base_coordinates'])) {
            return false;
        }

        // start off strings to be concatonated
        $js_lat_str = $js_lng_str = $js_name_str = $js_postcode_str = $js_email_str = $js_phone_str = $js_notes_str = $js_earliest_str = $js_latest_str = '';

        // create string of longitute and latitudes for postcodes of contacts, to display on map
        $dupeCheck = array();
        $c = 0; // counter
        foreach ($map_config['contacts_array_safe'] as $key => $value) {
            if (!empty($value['lat']) && !empty($value['lng']) && !in_array($value['postcode'], $dupeCheck)) {
                $js_lat_str .= $c . ':"' . $value['lat'] . '",';
                $js_lng_str .= $c . ':"' . $value['lng'] . '",';
                $js_name_str .= $c . ':"' . $value['first_name'] . ' ' . $value['last_name'] . '",';
                $js_postcode_str .= $c . ':"' . $value['postcode'] . '",';
                $js_email_str .= $c . ':"' . $value['email'] . '",';
                $js_phone_str .= $c . ':"' . $value['phone'] . '",';
                $js_notes_str .= $c . ':"' . sanitize_text_field($value['extra_info']) . '",';
                array_push($dupeCheck, $value['postcode']);
                $c++;
            }
        }
        // &key=
        // define filename ensuring it includes the unique user id, in case of simultatious sessions by different users.
        $filename = __DIR__ . '/../inc/' . $map_config['new_script_uri'];
        // build the file
        $country_code = get_site_option(self::OPTIONS_LABEL)['zws_contacts_database_plugin_country_of_use'];
        $country_list = unserialize(ZWS_CDB_COUNTRY);
        $country_name = array_search($country_code, $country_list);
        $js = 'jQuery(document).ready(function($) {
        $.getJSON(\'https://maps.googleapis.com/maps/api/geocode/json?address=' . $map_config['target_postcode'] . ',' . $country_name . '&language=en-EN&components=country:' . $country_code . '&sensor=false\', null, function(data){
        var p = data.results[0].geometry.location;
        var LatLng = new google.maps.LatLng(p.lat, p.lng);
        var mapCanvas = document.getElementById(\'map-canvas\');
        var mapOptions = {
        center: LatLng,
        zoom: ' . $map_config['zoom'] . ',
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        }
        var map = new google.maps.Map(mapCanvas, mapOptions)  
        // add the markers - create the objects
        var latArr = {' . trim($js_lat_str, ',') . '};
        var lngArr = {' . trim($js_lng_str, ',') . '};
        var nameArr = {' . trim($js_name_str, ',') . '};
        var postcodeArr = {' . trim($js_postcode_str, ',') . '};
        var emailArr = {' . trim($js_email_str, ',') . '};
        var phoneArr = {' . trim($js_phone_str, ',') . '};
        var notesArr = {' . trim($js_notes_str, ',') . '};
        // loops coordinates array and creates markers
        var myLatLng; 
        var marker = [];
        var contentString = [];
        var infowindow = [];
        for (x = 0; x < Object.keys(latArr).length; x++) {
        myLatLng = new google.maps.LatLng(latArr[x] , lngArr[x]);
        contentString[x] = "<h2>Contact</h2><ul class=\"map-label\">\
        <li>Name: " + nameArr[x] + "</li>\
        <li>Postcode: " + postcodeArr[x] + "</li>\
        <li>Email: " + emailArr[x] + "</li>\
        <li>Phone: " + phoneArr[x] + "</li>\
        <li>Notes: " + notesArr[x] + "</li>\
        </ul>";
        infowindow[x] = new google.maps.InfoWindow({
        content: contentString[x]
        });
        marker[x] = new google.maps.Marker({
        position: myLatLng,
        title: nameArr[x],
        icon: "' . $map_config['contact_icon_url'] . '",
        map: map
        });
        google.maps.event.addListener(marker[x], "click", make_callback(map, marker[x], infowindow[x]));
        }        
        var contentStringTarget = "<h2>Target</h2>\
            <ul class=\"map-label-target\">\
            <li>Postcode: ' . $map_config['target_postcode'] . '</li>\
                </ul>";
        var infowindowTarget = new google.maps.InfoWindow({
        content: contentStringTarget
        });
        var markerTarget = new google.maps.Marker({
        position: LatLng,
        title: "Target",
        icon: "' . $map_config['target_icon_url'] . '",  
        map: map,
        });  
        google.maps.event.addListener(markerTarget, "click", function() {
        infowindowTarget.open(map, markerTarget);
        }); 
        
        var contentStringBase = "<h2>' . $map_config['base_name'] . '</h2>\
            <ul class=\"map-label-target\">\
            <li>Coordinates: ' . $map_config['base_coordinates'][0] . ', ' . $map_config['base_coordinates'][1] . '</li>\
                </ul>";
        var infowindowBase = new google.maps.InfoWindow({
        content: contentStringBase
        });
        var baseLatLng = new google.maps.LatLng(' . $map_config['base_coordinates'][0] . ', ' . $map_config['base_coordinates'][1] . ');
        var markerBase = new google.maps.Marker({
        position: baseLatLng,
        Title: "Home Base",
        icon: "' . $map_config['base_icon_url'] . '",  
        map: map,
        });  
        google.maps.event.addListener(markerBase, "click", function() {
        infowindowBase.open(map, markerBase);
        });

        });
        });
        // function to convert html entities
        function decodeHtml(html) {
        var txt = document.createElement("textarea");
        txt.innerHTML = html;
        return txt.value;
        }
        // helper callback function
        function make_callback(map, marker, infowindow) {
        return function() {
        infowindow.open(map, marker);
        };
        }
        ';
        // write the file
        if (file_put_contents($filename, $js) !== false) {
            return true;
        }
        return false;
    }

}
