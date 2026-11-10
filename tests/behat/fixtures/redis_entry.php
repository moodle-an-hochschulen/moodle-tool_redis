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
 * Admin tool "Redis management" - Behat fixture page which stores and checks a known entry in the Redis server
 *
 * This page is visited by the Behat tests to store an entry in a database of the first configured Redis store (action=store),
 * to check if this entry still exists (action=check) or to flush the database (action=flush), so that the flush operation
 * of the Redis management page can be verified and the database can be cleaned up before a test.
 * The database is db0 by default and can be changed with the db parameter.
 *
 * @package    tool_redis
 * @copyright  2026 Alexander Bias <bias@alexanderbias.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_redis\local\store;

require_once(__DIR__ . '/../../../../../../config.php');

defined('BEHAT_SITE_RUNNING') || die();

global $PAGE, $OUTPUT;

// Require admin permissions.
require_admin();

// Get parameters.
$action = optional_param('action', 'check', PARAM_ALPHA);
$db = optional_param('db', 0, PARAM_INT);

// Prepare page.
$PAGE->set_url('/admin/tool/redis/tests/behat/fixtures/redis_entry.php', ['action' => $action, 'db' => $db]);
$PAGE->set_context(context_system::instance());

// Connect to the Redis server of the first configured Redis store. The connection is opened by the cache store wrapper
// of the plugin, i.e. in exactly the same way as the cache store itself does it.
$redis = null;
foreach (core_cache\config::instance()->get_all_stores() as $details) {
    if ($details['plugin'] === 'redis' && empty($details['default'])) {
        $store = new store($details['name'], $details['configuration']);
        $redis = $store->is_ready() ? $store->get_redis() : null;
        break;
    }
}

// If no Redis server is available, we have to stop here.
if ($redis === null) {
    throw new moodle_exception('noredisstoreconfigured', 'tool_redis');
}

// Select the database. The cache store always uses db0, so this is only necessary for other databases.
if ($db !== 0) {
    $redis->select($db);
}

echo $OUTPUT->header();

// Store the entry, flush the database or check if the entry exists.
if ($action == 'store') {
    $stored = $redis->set('tool_redis_behat_entry', 'Behat test entry');
    echo $OUTPUT->notification($stored ? 'Redis entry stored' : 'Redis entry not stored', $stored ? 'success' : 'error');
} else if ($action == 'flush') {
    $flushed = $redis->flushDB();
    echo $OUTPUT->notification($flushed ? 'Redis database flushed' : 'Redis database not flushed', $flushed ? 'success' : 'error');
} else {
    $exists = $redis->exists('tool_redis_behat_entry') > 0;
    echo $OUTPUT->notification($exists ? 'Redis entry exists' : 'Redis entry does not exist', 'info');
}

echo $OUTPUT->footer();
