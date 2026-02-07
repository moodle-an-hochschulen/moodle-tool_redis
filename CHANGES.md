moodle-tool_redis
=================

Changes
-------

### v4.5-r3

* 2026-02-07 - Improvement: Connect to Redis with TLS / through a Unix socket / to a Redis cluster if configured in the Moodle caching backend configuration.
* 2026-02-07 - Update valkey-stats library to version 1.2.0.

### v4.5-r2

* 2025-10-15 - Make codechecker happy again
* 2025-10-15 - Tests: Switch Github actions workflows to reusable workflows by Moodle an Hochschulen e.V.

### v4.5-r1

* 2024-10-14 - Upgrade: Adopt changes from MDL-81960 and use new \core\url class
* 2024-10-14 - Upgrade: Adopt changes from MDL-81920 and use new \core\lang_string class.
* 2024-10-14 - Upgrade: Adopt changes from MDL-82158 and use new \core_cache\factory class.
* 2024-10-07 - Prepare compatibility for Moodle 4.5.

### v4.4-r2

* 2024-10-29 - Replace Redis Stats GUI with the newer Valkey Stats GUI

### v4.4-r1

* 2024-08-24 - Development: Rename master branch to main, please update your clones.
* 2024-08-20 - Prepare compatibility for Moodle 4.4.

### v4.3-r3

* 2024-08-21 - Replace deprecated print_error() with moodle_exception.

### v4.3-r2

* 2024-08-11 - Add section for scheduled tasks to README
* 2024-08-11 - Updated Moodle Plugin CI to latest upstream recommendations

### v4.3-r1

* 2023-10-20 - Prepare compatibility for Moodle 4.3.

### v4.2-r1

* 2023-09-01 - Prepare compatibility for Moodle 4.2.

### v4.1-r3

* 2023-10-14 - Add automated release to moodle.org/plugins
* 2023-10-14 - Make codechecker happy again
* 2023-10-10 - Updated Moodle Plugin CI to latest upstream recommendations

### v4.1-r2

* 2023-05-03 - Bugfix: Get rid of a debugging message in redis-stats library on PHP 8.1.
* 2023-04-30 - Tests: Updated Moodle Plugin CI to use PHP 8.1 and Postgres 13 from Moodle 4.1 on.

### v4.1-r1

* 2023-04-30 - Update redis-stats library to latest version.
* 2023-01-21 - Prepare compatibility for Moodle 4.1.
* 2022-11-28 - Updated Moodle Plugin CI to latest upstream recommendations

### v4.0-r1

* 2022-08-08 - Improvement: Get rid of second vertical scrollbar around Redis Stats management GUI
* 2022-07-12 - Prepare compatibility for Moodle 4.0.

### v3.11-r3

* 2022-07-10 - Add Visual checks section to UPGRADE.md
* 2022-07-10 - Add Capabilities section to README.md

### v3.11-r2

* 2022-06-26 - Make codechecker happy again
* 2022-06-26 - Updated Moodle Plugin CI to latest upstream recommendations
* 2022-06-26 - Add UPGRADE.md as internal upgrade documentation
* 2022-06-26 - Update maintainers and copyrights in README.md.

### v3.11-r1

* 2021-07-19 - Prepare compatibility for Moodle 3.11.
* 2021-02-05 - Move Moodle Plugin CI from Travis CI to Github actions

### v3.10-r1

* 2021-01-09 - Prepare compatibility for Moodle 3.10.
* 2021-01-06 - Change in Moodle release support:
               For the time being, this plugin is maintained for the most recent LTS release of Moodle as well as the most recent major release of Moodle.
               Bugfixes are backported to the LTS release. However, new features and improvements are not necessarily backported to the LTS release.
* 2021-01-06 - Improvement: Declare which major stable version of Moodle this plugin supports (see MDL-59562 for details).

### v3.9-r1

* 2020-09-28 - Prepare compatibility for Moodle 3.9.

### v3.8-r1

* 2020-09-28 - Prepare compatibility for Moodle 3.8.

### v3.7-r3

* 2020-09-28 - Remove hardcoded admin directory in paths - Thanks to @adamtppaw.

### v3.7-r2

* 2020-09-28 - Enhance Security note in README.md with regards to the risk of flushing the Redis DB

### v3.7-r1

* 2020-05-03 - Initial version
