<?php

/**
 * @subpackage Autoupdate
 * @version 1.0.0-alpha
 * @package Boilerplate II
 *
 * @NOTE FOR TESTING ONLY RIGHT NOW
 *
 * This package has not been testing in a production
 * environment yet. Use it cautiously.
 *
 */

plugin_requires_version( 'autoupdate', '2.0.0' );

// Load class
require_once __DIR__ . '/classes/Autoupdate.php';

// Register update action
add_action( 'plugins/loaded', array( 'Autoupdate', 'run' ) );
