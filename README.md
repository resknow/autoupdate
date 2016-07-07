# Autoupdate for Boilerplate

Autoupdate is a plugin for Boilerplate that handles updating of plugins in the background without the need for any user interaction. The aim was to keep Boilerplate sites current and also lay the foundations for a UI-based updating plugin.

There are events and filters throughout each process, allowing you to trigger events at certain points in the update process.

### Get Started

Getting your plugin ready for autoupdating is easy, you'll need the following things:

- Include `Autoupdate::register()` method in your `plugin.php` file.
- `current-version.txt` file in your plugin directory.
- A place to host your update files.

### Register Your Plugin

To register your plugin for updating, include the following:

```php
<?php

if ( plugin_is_active('autoupdate') )
    Autoupdate::register( 'myplugin', '1.1.0', 'http://update.resknow.net' );
```
The `register()` method requires 3 arguments:

- Plugin name (directory name)
- Current version
- URL to find updates (update ZIP files should be in a sub-directory here named after your plugin and ZIP files themselves must simply be the version number)
