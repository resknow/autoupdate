<?php

/**
 * @subpackage Autoupdate
 * @version 1.0.0
 * @package Boilerplate II
 */

plugin_requires_version( 'autoupdate', '2.0.0' );

// Load class
require_once __DIR__ . '/classes/Autoupdate.php';

// Register update action
add_action( 'plugins/loaded', array( 'Autoupdate', 'run' ) );
