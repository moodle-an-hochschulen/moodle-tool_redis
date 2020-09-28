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
 * Admin tool "Redis management" - Redis Stats Flush DB stub.
 *
 * The Redis Stats code which we have placed into lib/redis-stats/redis-stats.php.inc
 * is calling a file called flushdb.php to flush the Redis DB.
 * With this file, we provide a stub at the location where the Redis Stats code expect it to be and include the original file.
 *
 * @package    tool_redis
 * @copyright  2020 Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

global $CFG;

// Make sure that the user is logged in.
require_login();

// Make sure that only admins flush the DB.
require_capability('moodle/site:config', context_system::instance());

// Include Redis Stats Flush DB.
require_once($CFG->dirroot.'/'.$CFG->admin.'/tool/redis/lib/redis-stats/flushdb.php.inc');
