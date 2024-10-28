<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Admin tool "Redis management" - Redis Stats configuration file
 *
 * The Valkey Stats code which we have placed into lib/valkey-stats/valkey-stats.php.inc
 * is looking for a file called config.php and includes this file.
 * In this file, we can set the configuration of Valkey Stats.
 *
 * @package    tool_redis
 * @copyright  2020 Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Get the Redis server(s) configuration from the MUC configuration and provide it in a way as Valkey Stats expects it.
//
// Servers are defined as an array
// [ Name, IP/Socket, Port, Password ]
//
// Name (string):                     name shown in drop-down list (also used for command mapping)
// IP/Socket (string):                IP address or socket of the server
// Port (integer):                    port of server, use -1 for socket
// Password entry (string|array):     credentials for the server (optional)
// $ret = cache_administration_helper::get_store_instance_summaries();
// $ret = cachestore_redis::config_get_configuration_array($data)
//
// First: Initialize empty stores array.
$servers = [];

// Second: Get all MUC stores' configurations.
$factory = \core_cache\factory::instance();
$config = $factory->create_config_instance();
$stores = $config->get_all_stores();

// Third: Pick and remember the Redis stores.
foreach ($stores as $key => $store) {
    // Ignore all default stores.
    if (substr($key, 0, 8) == 'default_') {
        continue;
    }

    // Ignore all non-Redis stores.
    if ($store['plugin'] != 'redis') {
        continue;
    }

    // Remember the Redis store information.
    $servers[] = [$store['name'], $store['configuration']['server'], 6379, $store['configuration']['password']];
}

// Forth: If there isn't any Redis store configured, we should stop here.
if (count($servers) < 1) {
    throw new moodle_exception('noredisstoreconfigured', 'tool_redis');
}

// Show a 'Flush' button for databases.
define("FLUSHDB", true);

// Ask for confirmation before flushing database.
define("CONFIRM_FLUSHDB", true);

// Don't show a 'Flush All' button for the instance.
define("FLUSHALL", false);

// Position of status line: "bottom".
define("STATUS_LINE", "bottom");

// Don't show the 'Check for update' button.
define("CHECK_FOR_UPDATE", false);
