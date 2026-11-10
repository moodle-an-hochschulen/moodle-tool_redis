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
 * Admin tool "Redis management" - Language pack
 *
 * @package    tool_redis
 * @copyright  2020 Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @copyright  2026 Alexander Bias <bias@alexanderbias.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['cachestore'] = 'Cache store: {$a}';
$string['changessincelastsave'] = 'Changes since last save';
$string['commandsprocessed'] = 'Commands processed';
$string['componentcachestore'] = 'Moodle cache store';
$string['componentsessionhandler'] = 'Moodle session handler';
$string['connectedclients'] = 'Connected clients';
$string['connection'] = 'Connection';
$string['connectionfailed'] = 'The connection to the Redis server "{$a}" failed. Please check its configuration.';
$string['connectionsreceived'] = 'Connections received';
$string['connectionsrejected'] = 'Connections rejected';
$string['dbother'] = 'This database is not used by the {$a}, but by other clients of this Redis server.';
$string['dbused'] = 'This database is used by the {$a}.';
$string['details'] = 'Details';
$string['evictedkeys'] = 'Evicted keys';
$string['expiredkeys'] = 'Expired keys';
$string['flush'] = 'Flush';
$string['flushconfirm'] = 'This will delete all keys in the database {$a->db} on the Redis server {$a->server}. Are you sure?';
$string['flushconfirmtitle'] = 'Flush database';
$string['flushfailed'] = 'The database {$a->db} on the Redis server {$a->server} could not be flushed.';
$string['flushsuccess'] = 'The database {$a->db} on the Redis server {$a->server} was flushed.';
$string['freememory'] = 'Free memory';
$string['hitrate'] = 'Hit rate';
$string['hits'] = 'Hits';
$string['keys'] = 'Keys in {$a}';
$string['keyswithexpiry'] = 'Keys with expiry';
$string['lastsave'] = 'Last saved {$a} ago';
$string['memory'] = 'Memory';
$string['memorychart'] = 'Memory (MB)';
$string['memorylimit'] = 'Memory limit';
$string['memorylimitusage'] = 'Usage of memory limit';
$string['misses'] = 'Misses';
$string['noredisstoreconfigured'] = 'The Redis management shows the information of the Redis servers which are used as Moodle cache stores or as Moodle session store. However, there isn\'t any Redis cache store instance configured yet and Redis is not configured as session handler either. Please add a Redis cache store instance in the cache store configuration first.';
$string['peakmemory'] = 'Peak memory';
$string['persistence'] = 'Persistence';
$string['pluginname'] = 'Redis management';
$string['privacy:metadata'] = 'The Redis management plugin provides extended functionality to Moodle admins, but does not store any personal data.';
$string['releasedmemory'] = 'Released memory';
$string['sessionstore'] = 'Session store: {$a}';
$string['uptime'] = 'Uptime';
$string['usedmemory'] = 'Used memory';
