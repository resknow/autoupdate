<?php

class Autoupdate {

    private static $plugins = array();

    /**
     * Check
     *
     * Checks that the environment is setup
     * correctly
     *
     * @event: autoupdate/checks/passed
     */
    public static function check() {

        // Check updates directory
        $update_dir = ROOT_DIR . '/media/updates';

        // Media dir is writeable
        if ( !is_dir($update_dir) && is_writeable(ROOT_DIR . '/media') ) {
            mkdir($update_dir);
        }

        // Media dir not writeable
        if ( !is_dir($update_dir) || !is_writeable(ROOT_DIR . '/media') ) {
            throw new Exception('Autoupdate: Updates cannot be saved. Make sure updates directory exists and is writeable by the server.');
        }

        // Event: autoupdate/checks/passed
        do_event( 'autoupdate/checks/passed' );

    }

    /**
     * Run
     *
     * @event autoupdate/found
     */
    public static function run() {

        // Check if any plugins are register
        if ( empty(self::$plugins) ) {
            return;
        }

        // Run checks
        self::check();

        // Check for updates
        foreach ( self::$plugins as $plugin ) {

            // Build version URL
            $url = sprintf('%s/%s/current-version.txt', $plugin['url'], $plugin['name']);

            if ( $current = file_get_contents($url) ) {

                // If current version is not higher than install
                // move on to the next one
                if ( $current <= $plugin['version'] ) {
                    continue;
                }

                // Event: autoupdate/found
                do_event( 'autoupdate/found', $plugin );

                // Download update package
                self::download_update($plugin['name'], $plugin['url'], $current);

            } else {
                throw new Exception(sprintf('Plugin update failed: Unable to get current version for %s', $plugin['name']));
            }

        }

    }

    /**
     * Download Update
     *
     * @param $name (string) Plugin name
     * @param $url (string) URL to get update from
     * @param $version (string) Version to download
     *
     * @filter autoupdate/download/$name (string) Plugin download URL
     * @event autoupdate/update/downloaded
     */
    public static function download_update( $name, $url, $version ) {

        // Build download URL
        $download = apply_filters( 'autoupdate/download/' . $name, sprintf('%s/%s/%s.zip', $url, $name, trim($version)) );

        // Download the update package
        if ( ! $package = file_get_contents($download) ) {
            throw new Exception(sprintf('Plugin update failed. Unable to download package for %s', $name));
        }

        // Save the package
        $filename = sprintf('%s/media/updates/%s-%s.zip', ROOT_DIR, $name, $version);
        if ( $saved = file_put_contents( $filename, $package ) ) {

            // Event: autoupdate/update/downloaded
            do_event( 'autoupdate/update/downloaded', $name, $url, $version );

        }

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
