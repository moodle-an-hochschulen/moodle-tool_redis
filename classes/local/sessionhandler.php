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

/**
 * Admin tool "Redis management" - Redis session handler wrapper.
 *
 * This class extends the Redis session handler from Moodle core so that the Redis management page connects to the Redis
 * session store in exactly the same way as the session handler does (including TLS, Unix sockets, cluster mode, passwords
 * and timeouts) and gets access to the underlying Redis connection. It does not register itself as session handler.
 *
 * @package    tool_redis
 * @copyright  2026 Alexander Bias <bias@alexanderbias.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sessionhandler extends \core\session\redis {
    /**
     * Returns if Redis is configured as session handler in config.php.
     *
     * @return bool
     */
    public static function is_configured(): bool {
        global $CFG;

        return !empty($CFG->session_handler_class) &&
                ltrim($CFG->session_handler_class, '\\') === 'core\\session\\redis' &&
                !empty($CFG->session_redis_host);
    }

    /**
     * Returns the configured host(s) of the Redis session store.
     *
     * @return string
     */
    public function get_host(): string {
        return implode(', ', $this->host);
    }

    /**
     * Returns the number of the database which the session handler uses.
     *
     * @return int
     */
    public function get_database(): int {
        return (int) $this->database;
    }

    /**
     * Connects to the Redis session store and returns the connection.
     *
     * @return \Redis|\RedisCluster|null The connection, null if the connection failed.
     */
    public function get_redis(): \Redis|\RedisCluster|null {
        try {
            $this->connect_to_redis();
        } catch (\Throwable $e) {
            debugging('Failed to connect to the Redis session store, the error returned was: ' . $e->getMessage());
            return null;
        }
        return $this->connection;
    }
}
