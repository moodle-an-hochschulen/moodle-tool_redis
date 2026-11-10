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

namespace tool_redis\local;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/cache/stores/redis/lib.php');

/**
 * Admin tool "Redis management" - Redis cache store wrapper.
 *
 * This class extends the Redis cache store from Moodle core so that the Redis management page connects to the Redis servers
 * in exactly the same way as the cache store does (including TLS, Unix sockets, cluster mode, passwords and timeouts) and
 * gets access to the underlying Redis connection.
 *
 * @package    tool_redis
 * @copyright  2026 Alexander Bias <bias@alexanderbias.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class store extends \cachestore_redis {
    /**
     * Returns the connection to the Redis server which the cache store has opened.
     *
     * @return \Redis|\RedisCluster|null
     */
    public function get_redis(): \Redis|\RedisCluster|null {
        return $this->redis;
    }
}
