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
 * Admin tool "Redis management" - Main page
 *
 * @package    tool_redis
 * @copyright  2020 Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @copyright  2026 Alexander Bias <bias@alexanderbias.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use tool_redis\local\server;

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Set up the plugin's main page as external admin page.
admin_externalpage_setup('tool_redis');

// Get the parameters.
$serverindex = optional_param('server', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

// If there isn't any Redis server configured (neither as cache store nor as session store), there is nothing to show.
// Thus, redirect to the cache store configuration and ask the admin to configure a Redis store there.
$redisservers = server::get_all();
if (empty($redisservers)) {
    redirect(
        new moodle_url('/cache/admin.php'),
        get_string('noredisstoreconfigured', 'tool_redis'),
        null,
        notification::NOTIFY_INFO,
    );
}

// Build the list of servers to show: One per Redis server or, in cluster mode, one per master node of the server.
$servers = [];
foreach ($redisservers as $redisserver) {
    foreach ($redisserver->get_nodes() as $node) {
        $suffix = ($node['label'] === null) ? '' : ' (' . $node['label'] . ')';
        $servers[] = [
            'server' => $redisserver,
            'node' => $node['node'],
            'name' => $redisserver->name . $suffix,
            'tabtitle' => $redisserver->tabtitle . $suffix,
        ];
    }
}

// If not proper server is given, we have to stop here.
if (!isset($servers[$serverindex])) {
    throw new moodle_exception('invalidparameter', 'debug');
}

// Compose the page URL.
$pageurl = new moodle_url('/' . $CFG->admin . '/tool/redis/index.php', ['server' => $serverindex]);

// Process the flush action.
if ($action === 'flush') {
    // Require the sesskey.
    require_sesskey();

    // The database number must be given and must not be negative. There is no upper limit as the number of
    // databases is a configuration setting of the Redis server which we do not know here, Redis itself rejects
    // non-existent databases when the database is selected.
    $db = required_param('db', PARAM_INT);
    if ($db < 0) {
        throw new moodle_exception('invalidparameter', 'debug');
    }

    // Flush the database on the given server (or, in cluster mode, on the given master node of the server).
    // The flush is only attempted if the server is reachable. Any connection or command error is caught and reported
    // to the admin as failed flush instead of showing an exception page.
    $server = $servers[$serverindex];
    try {
        $flushed = $server['server']->is_ready() && $server['server']->flush_database($db, $server['node']);
    } catch (RedisException | RedisClusterException $e) {
        $flushed = false;
    }

    // Redirect back to the tab of the server with a notification about the result. The redirect also makes sure
    // that the page shows the fresh key counts and that a page reload does not repeat the flush request.
    $a = ['db' => 'db' . $db, 'server' => $server['name']];
    if ($flushed) {
        redirect($pageurl, get_string('flushsuccess', 'tool_redis', $a), null, notification::NOTIFY_SUCCESS);
    }
    redirect($pageurl, get_string('flushfailed', 'tool_redis', $a), null, notification::NOTIFY_ERROR);
}

// Output the page.
$PAGE->set_url($pageurl);
$title = get_string('pluginname', 'tool_redis');
$PAGE->set_title($title);
$PAGE->set_heading($title);

$renderer = $PAGE->get_renderer('tool_redis');
echo $OUTPUT->header();
echo $renderer->render_page($servers, $serverindex);
echo $OUTPUT->footer();
