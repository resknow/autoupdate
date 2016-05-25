<?php

class Autoupdate {

    private static $plugins = array();

    /**
     * Setup
     */
    public static function run() {

        print_r( self::$plugins );

    }

    /**
     * Register Autoupdate
     *
     * @param $name (string) Plugin name
     * @param $current (string) Current version
     * @param $url (string) URL to search for updates
     * @param $ignore (array) Files to ignore while updating
     */
    public static function register( $name, $current, $url, $ignore = false ) {
        self::$plugins[$name] = array(
            'name' => $name,
            'version' => $current,
            'url' => $url,
            'ignore' => $ignore
        );
    }

}
