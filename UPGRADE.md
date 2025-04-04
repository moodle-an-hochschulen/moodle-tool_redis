Upgrading this plugin
=====================

This is an internal documentation for plugin developers with some notes what has to be considered when updating this plugin to a new Moodle major version.

General
-------

* Generally, this is a quite simple plugin with just one purpose.
* It does not rely on any fluctuating library functions and should remain quite stable between Moodle major versions.
* Thus, the upgrading effort is low.


Upstream changes
----------------

* This plugin relies on the thiry-party Valkey Stats management GUI tool which is located within the plugin directory. Every now and then, it should be checked if there is a new version which could be updated in the plugin.


Automated tests
---------------

* The plugin has a good coverage with Behat tests which test all of the plugin's user stories.


Manual tests
------------

* There aren't any manual tests needed to upgrade this plugin.


Visual checks
-------------

* It might be advisable to have a look at the admin page of the plugin in the Moodle GUI as Moodle themes or the Redis Stats management GUI itself can always change small details in this area.
