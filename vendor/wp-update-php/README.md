# WPupdatePHP library
Library to be bundled with WordPress plugins to enforce users to upgrade their PHP versions _or_ switch to a decent host.

## Installation

Clone the git repo into your project. Then just require /YOUR_PROJECT_PATH/wp-update-php/src/WPUpdatePhp.php in your php script.

## Usage
Usage of this library depends on how you start your plugin. 

The core `does_it_meet_required_php_version` method does all the checking for you and adds an admin notice in case the version requirement is not met.

For example, when you start your plugin by instantiating a new object, you should wrap a conditional check around it. 

_Example:_
```php
$updatePhp = new WPUpdatePhp( $minimum_version='5.5.0', $plugin_name='My Plugin Name' );

if ( $updatePhp->does_it_meet_required_php_version( PHP_VERSION ) ) {
	// Instantiate new object here
}

// The version check has failed, a admin notice has been thrown
```

## Fork Modifications

This is based on the original script by Coen Jacobs (see below).
This version has been forked & modified by Dan Bright (Zaziork Web Solutions), productions at zaziork dot com.
The modifications include intergrated version detection, and the ability to pass the plugin's name into the upgrade message.

## License
(GPLv3 license or later)

WP Update PHP Library
Copyright (C) 2015  Coen Jacobs

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
