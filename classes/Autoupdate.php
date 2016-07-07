<?php

class Autoupdate {

    private static $plugins = array();
    private static $update_dir = ROOT_DIR . '/media/updates';

    /**
     * Check
     *
     * Checks that the environment is setup
     * correctly
     *
     * @event: autoupdate/checks/passed
     */
    public static function check() {

        // Media dir is writeable
        if ( !is_dir(self::$update_dir) && is_writeable(ROOT_DIR . '/media') ) {
            mkdir(self::$update_dir);
        }

        // Media dir not writeable
        if ( !is_dir(self::$update_dir) || !is_writeable(ROOT_DIR . '/media') ) {
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

        // Check if any plugins are registered
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
                if ( trim($current) > $plugin['version'] && $plugin['ignore'] === false ) {

                    // Event: autoupdate/found
                    do_event( 'autoupdate/found', $plugin );

                    // Download update package
                    $package = self::download_update($plugin['name'], $plugin['url'], $current);

                    // Install update
                    if ( self::install_update( $plugin['name'], $package ) ) {
                        self::delete_package( $package );
                    }

                }

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
        if ( $saved = file_put_contents( trim($filename), $package ) ) {

            // Event: autoupdate/update/downloaded
            do_event( 'autoupdate/update/downloaded', $name, $url, $version );

            return $filename;

        }

    }

    /**
     * Install Update
     *
     * @param $plugin
     * @param $package
     */
    public static function install_update( $plugin, $package ) {

        // Check plugin exists
        if ( !plugin_exists($plugin) ) {
            throw new Exception(sprintf('%s is not installed so it cannot be updated.', $plugin));
        }

        // Check package exists
        if ( !is_readable($package) ) {
            throw new Exception(sprintf('%s could not be found so updates were not installed.'));
        }

        // Create Zip object
        $zip = new ZipArchive();

        // Attempt to open
        if ( $zip->open($package) === true ) {

            // Extract ZIP
            $zip->extractTo(sprintf('%s/%s', plugin_dir(), $plugin));

            // Event: autoupdate/update/installed
            do_event( 'autoupdate/update/installed', $plugin, $zip );

            // Close ZIP
            if ( $zip instanceof ZipArchive ) {
                $zip->close();
            }

            return true;
        }

        // Event: autoupdate/update/failed
        do_event( 'autoupdate/update/failed', $plugin );

        // Extract failed
        throw new Exception(sprintf('Could not extract update package [%s]', $package));

    }

    /**
     * Delete Package
     *
     * Remove an update package after installing
     *
     * @param $package (string) Path to package
     */
    public static function delete_package( $package ) {

        if ( is_writeable($package) ) {
            unlink($package);

            // Event: autoupdate/package/delete
            do_event( 'autoupdate/package/delete', $package );

            return true;
        }

        // Event: autoupdate/package/delete/failed
        do_event( 'autoupdate/package/delete/failed', $package );

        return false;

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

    /**
     * Registered
     *
     * Return an array of registered plugins
     */
    public static function registered() {
        return self::$plugins;
    }

}
