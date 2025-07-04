moodle-tool_redis
=================

[![Moodle Plugin CI](https://github.com/moodle-an-hochschulen/moodle-tool_redis/actions/workflows/moodle-plugin-ci.yml/badge.svg?branch=main)](https://github.com/moodle-an-hochschulen/moodle-tool_redis/actions?query=workflow%3A%22Moodle+Plugin+CI%22+branch%3Amain)

Moodle plugin which adds a Redis management GUI to Moodle site administration.


Requirements
------------

This plugin requires Moodle 5.0+


Motivation for this plugin
--------------------------

For performance reasons, Moodle should always be run with a Caching backend. Redis is such a caching backend which Moodle can connect to. Unfortunately, Redis is kind of a black box and doesn't provide a management interface by default.

Luckily, there are some free Redis management GUIs out there with Valkey Stats by Helmut K. C. Tessarek (https://github.com/tessus/valkey-stats) being a really nice and compact one. As a Moodle server administrator, you can just throw Valkey Stats somewhere onto your Moodle server and get a Redis management GUI instantly. However, this approach requires that you protect Valkey Stats from unauthorized access manually in your webserver and comes with the downside that Valkey Stats is located outside Moodle.

For these reasons, we have packaged Valkey Stats as a very simple Moodle admin tool providing it within Moodle site adminstration for Moodle administrators only.


Installation
------------

Install the plugin like any other plugin to folder
/admin/tool/redis

See http://docs.moodle.org/en/Installing_plugins for details on installing Moodle plugins


Usage
-----

After installing the plugin, it is active and fully working.

To use the plugin, please visit:
Site administration -> Server -> Redis management.

The Redis management GUI should be self-explanatory for experienced PHP administrators.


Capabilities
------------

This plugin does not add any additional capabilities.


Scheduled Tasks
---------------

This plugin does not add any additional scheduled tasks.


How this plugin works
---------------------

This plugin works in a really simple way. It adds an admin tool page to Moodle's site administration tree and restricts access to this admin tool page to Moodle administrators (and other users having the moodle/site:config capability). The Redis management GUI is shipped as a library file with this plugin and is just included on the admin tool page.


Security note
-------------

The Redis management GUI was added as a library to this Moodle plugin and the library files were renamed to /lib/redis-gui/redis.php.inc and /lib/redis-gui/flushdb.php.inc.

There is a potential for sensitive data leak, not personal data but data about the webserver's PHP configuration, if your webserver is configured to interpret *.inc files as PHP. An anonymous user could then visit the library's index page directly via https://yourmoodle.com/admin/tool/redis/lib/redis-gui/redis.php.inc and would see the Redis management GUI circumventing Moodle's access control.

Similarly, there is also a potential for a performance degradation attack if your webserver is configured to interpret *.inc files as PHP. An anonymous user could then visit the library's flush page directly via https://yourmoodle.com/admin/tool/redis/lib/redis-gui/flushdb.php.inc with appropriate parameters and would flush the Redis DB circumventing Moodle's access control.

Please make sure that your webserver does not interpret *.inc files as PHP (which should be the default) or take any other measure that this file can not be accessed directly by a browser.


Theme support
-------------

This plugin is developed and tested on Moodle Core's Boost theme.
It should also work with Boost child themes, including Moodle Core's Classic theme. However, we can't support any other theme than Boost.


Plugin repositories
-------------------

This plugin is published and regularly updated in the Moodle plugins repository:
http://moodle.org/plugins/view/tool_redis

The latest development version can be found on Github:
https://github.com/moodle-an-hochschulen/moodle-tool_redis


Bug and problem reports / Support requests
------------------------------------------

This plugin is carefully developed and thoroughly tested, but bugs and problems can always appear.

Please report bugs and problems on Github:
https://github.com/moodle-an-hochschulen/moodle-tool_redis/issues

We will do our best to solve your problems, but please note that due to limited resources we can't always provide per-case support.


Feature proposals
-----------------

Due to limited resources, the functionality of this plugin is primarily implemented for our own local needs and published as-is to the community. We are aware that members of the community will have other needs and would love to see them solved by this plugin.

Please issue feature proposals on Github:
https://github.com/moodle-an-hochschulen/moodle-tool_redis/issues

Please create pull requests on Github:
https://github.com/moodle-an-hochschulen/moodle-tool_redis/pulls

We are always interested to read about your feature proposals or even get a pull request from you, but please accept that we can handle your issues only as feature _proposals_ and not as feature _requests_.


Moodle release support
----------------------

Due to limited resources, this plugin is only maintained for the most recent major release of Moodle as well as the most recent LTS release of Moodle. Bugfixes are backported to the LTS release. However, new features and improvements are not necessarily backported to the LTS release.

Apart from these maintained releases, previous versions of this plugin which work in legacy major releases of Moodle are still available as-is without any further updates in the Moodle Plugins repository.

There may be several weeks after a new major release of Moodle has been published until we can do a compatibility check and fix problems if necessary. If you encounter problems with a new major release of Moodle - or can confirm that this plugin still works with a new major release - please let us know on Github.

If you are running a legacy version of Moodle, but want or need to run the latest version of this plugin, you can get the latest version of the plugin, remove the line starting with $plugin->requires from version.php and use this latest plugin version then on your legacy Moodle. However, please note that you will run this setup completely at your own risk. We can't support this approach in any way and there is an undeniable risk for erratic behavior.


Translating this plugin
-----------------------

This Moodle plugin is shipped with an english language pack only. All translations into other languages must be managed through AMOS (https://lang.moodle.org) by what they will become part of Moodle's official language pack.

As the plugin creator, we manage the translation into german for our own local needs on AMOS. Please contribute your translation into all other languages in AMOS where they will be reviewed by the official language pack maintainers for Moodle.


Right-to-left support
---------------------

This plugin has not been tested with Moodle's support for right-to-left (RTL) languages.
If you want to use this plugin with a RTL language and it doesn't work as-is, you are free to send us a pull request on Github with modifications.


Maintainers
-----------

The plugin is maintained by\
Moodle an Hochschulen e.V.


Copyright
---------

The copyright of this plugin is held by\
Moodle an Hochschulen e.V.

Individual copyrights of individual developers are tracked in PHPDoc comments and Git commits.


Initial copyright
-----------------

This plugin was initially built, maintained and published by\
Ulm University\
Communication and Information Centre (kiz)\
Alexander Bias

It was contributed to the Moodle an Hochschulen e.V. plugin catalogue in 2022.


Credits
-------

This Moodle plugin is only a simple wrapper for the Valkey Stats management GUI by Helmut K. C. Tessarek.
Helmut owns all copyrights for Valkey Stats and maintains this tool on https://github.com/tessus/valkey-stats.
