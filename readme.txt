=== ZWS Contacts Database ===

Donate link: https://www.aninstance.com/donate
Contributors: zaziork
Tags: contacts, database, contacts database, google map, distance calculator, contacts location database, postcode calculator
Requires at least: 3.0
Tested up to: 4.7.5
Stable tag: 1.0.2
License: GPLv2 or later
Plugin to create and administer a contacts database and calculate nearest contacts to any given postcode.

== Description ==

This is a plugin to create and administer a contacts database and calculate the nearest contacts to any given postcode.

The plugin is currently configured to support UK and USA addresses. If you are not in the UK or USA and would but would like this configured for your own country of residence, please contact us at: https://www.aninstance.com/contact/

= Example use case =

The plugin is being developed for use initially on a wildlife hospital website, to allow people to register as "wildlife ambulances".

When a wildlife casualty is reported, the administrator enters the address or postcode, and is presented with the 5 closest contacts ("ambulances") to the casualty who are within their specified maximum travel distance. The casualty and the contacts are also plotted on a Google map.

== Beta Information ==

This is a beta testing release. The first release version (1.0) of this plugin is currently still under active development.

If you are installing from the master zip file from GitHub, please re-download the zip and reinstall periodically, to ensure you always have the latest release version.

== Features ==

* Contacts submit details into database. Details include:
    - First and last name
    - Postcode
    - Contact phone number
    - Email address
    - Maximum radius prepared to travel to target
    - Free form extra information
* Administrators submit target address, which is automatically converted to a postcode, after which database is queried and nearest contacts to target are returned (provided target is within contact's maximum radius).
* Contact information for the nearest contacts to the target are displayed, together with a Google Map upon which the target, contacts and home base are marked.
* Uses Memcached to cache requests to the Google Distance Matrix API, to improve speed and limit API requests.
* Administrators can browse, edit and delete records from the full database of submitted contacts.
* Administrators can search for a contact's record by name.
* Configurable map icons (administrators can set URL in admin page).
* Contacts are requested to select the days and times that they would be available when registering.
* Only contacts who have indicated that they are available on the current day of the week are displayed when searching for the 'nearest contact'.
* Option for administrators to receive email notifications when new contacts register.
* Option to configure a confirmation email and have it sent to new registrants upon registration.
* Ability to configure plugin to work with alternative (non UK) country of deployment. Current options: UK, USA.

== Requirements ==

You will need to have Memcached installed on your system to make use of the Cache feature.

The plugin has been tested with PHP versions 5.6.x and above.

== Future development roadmap ==

* Add facility for administrators to access a contact's record by submitting a phone number or email address.
* Add ability for administrators to define the administrator's notification email subject and message from the admin page.
* Add feature to optionally require new registrants to confirm their email address before their account is made 'live' (hook into registration confirmation email that is already an option).
* Add feature to allow contacts to modify their own stored data. This would necessitate creating a password at sign-up.
* Test it works with additional countries and add those to the list of available countries of deployment.
* Add selection of language, to internationalise.
* Add option to display distances in metric units rather than imperial (km rather than miles).
* Add ability for administrator to select not to include the day/time feature. When not included, remove section from registration form, times button from display pages, and submit defaults of 00:00 to database).
* Add presentation polish.
* Refactor code to tidy up the mess!

== Plugin Website ==

The URL of this plugin's website is: https://www.aninstance.com/zws-contacts-database/

The URL of this plugin's Wordpress page is: https://wordpress.org/plugins/zws-contacts-database/

== Installation ==

Note: We do sell installation, configuration and support services. If you'd be interested in hiring us to get this up and running for you, please get in touch at: https://www.aninstance.com/contact/

To install search for "ZWS Contacts Database" in the WordPress Plugins Directory, then click the "Install Now" button.

When it's installed, simply activate, then navigate to the Settings page to update the defaults to your liking.

Alternatively, the latest version of the plugin may be installed via a zip file, available here: https://github.com/ZWS2014/zws-wordpress-contacts-database/archive/master.zip

After downloading the zip, change the name of the unzipped directory to "ZwsContactsDatabase", upload the plugin to the '/wp-content/plugins/' directory, then activate through the 'Plugins' menu in WordPress.

Once installed, the contacts submission form can be added to a post or page using the shortcode:

[zwscontactsdatabase_public_form]

The administration page where contacts may be viewed and displayed on a map in relation to a "target" postcode can be inserted to a post or page using the shortcode:

[zwscontactsdatabase_results_page]

Be sure to visit the plugin's settings page (available via the Wordpress admin section settings sidebar), to configure the plugin for your system.

You will also need to create a page (or post) for your website's privacy policy, and enter the URL on the aforementioned settings page.

== Frequently Asked Questions ==

There are no frequently asked questions as yet.

== Current version ==

The current version is: 1.0.2

== Changelog ==

= 1.0.2 =

* Switched to send email using wp_mail function rather than native php mail(), in order
to allow confirmation emails to be sent using plugins for services such as PostMark.

= 1.0.1 =

* Updated Guzzle
* Update query-geocomplete and jquery-timepicker
* Tested up to Wordpress 4.7.5

= 0.8.7 =

* Updated Guzzle
* Updated query-geocomplete and jquery-timepicker
* Tested up to Wordpress 4.5.3

= 0.8.6 =

* Updated the Guzzle library
* Updated jquery-geocomplete and jquery-timepicker
* Added PHP version check

= 0.8.5 =

* Fixed error where new registrants were unable to sign up due to bug in authorisation procedure.
* Increased the maximum number of characters for the 'extra info' field, and enforced limit.

= 0.8.4 =

* Added system support (beta) for locations in India
* Fixed bug where incorrect lat/lng may have been set/used under certain circumstances.

= 0.8.3 =

* Fix for bug where correct number of valid results were not being returned for available drivers.

= 0.8.2 =

* No change. Version number bumped to correct versioning issue.

= 0.8.1 =

* Fixed error where Google API key was hard-coded to an invalid value.

= 0.8 =

* Added option to select country of deployment (from list of tested available locations).
* Added USA to list of available locations.

= 0.7 =

* Added option for email to be sent to administrator upon new registrants.
* Added option for email to be sent to new registrants to confirm their registration.

= 0.6 =

* Fixed bug where map failed to display if target shared same postcode as contact where only one contact to display.
* Added timeout for Google distance  matrix api calls.
* Added ability for administrators to obtain target postcode by simply typing the address of the target (uses jQuery and Google GeoCoding).

= 0.5 =

* Added ability to set an option for the initial Google Map zoom factor from the settings menu.
* Updated Guzzle to latest release version.
* Added ability for administrator to update the contact's details, via a button on contact's details pane in the 'view entire database' view.
* Added ability for administrators to search for a contact's record by name.
* Added ability for administrators to delete a record from the database.
* [SECURITY FIX] Added extra layer of security to ensure all database related methods cannot be accessed from the UI by users with privileges below that of editor.

= 0.4 =

* Added ability for administrators to define "home base" postcode and name from the settings page.
* Made memcached key unique in order to allow multiple instances of the plugin to use the same memcached server.
* [SECURITY FIX] Fixed a bug where some input may not have been correctly filtered.

= 0.3 =

* Ability for contacts to select the days and times that they're available added.
* Ability for administrators to view the days/times that contacts are available when accessing the full database via the administration dashboard.
* When using the nearest contacts search, only contacts available "today" are shown.

= 0.2 =

* Settings page added, with ability to change configuration options.

= 0.1 =

First beta version of the plugin.

== Support ==

The plugin is to be used entirely at the user's own risk.

Support and/or implementation of feature requests are not guaranteed, however comments and/or requests for free support are welcome.

For premium support, please contact the author at productions@aninstance.com
