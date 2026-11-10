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
 * Admin tool "Redis management" - Redis server.
 *
 * A Redis server which Moodle uses either as cache store or as session store. The server wraps the Redis connection
 * which the corresponding Moodle component has opened and provides the INFO and FLUSHDB operations for the Redis
 * management page.
 *
 * @package    tool_redis
 * @copyright  2026 Alexander Bias <bias@alexanderbias.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class server {
    /** @var string The Moodle component which uses the server as cache store. */
    const USEDBY_CACHESTORE = 'cachestore';

    /** @var string The Moodle component which uses the server as session store. */
    const USEDBY_SESSIONHANDLER = 'sessionhandler';

    /**
     * Constructor.
     *
     * @param string $name The name of the server (the cache store name or the session store host).
     * @param string $tabtitle The title of the server's tab on the Redis management page.
     * @param string $usedby The Moodle component which uses the server, one of the USEDBY_* constants.
     * @param int $database The number of the database which the Moodle component uses.
     * @param \Redis|\RedisCluster|null $redis The connection to the server, null if the connection failed.
     */
    public function __construct(
        /** @var string The name of the server. */
        public readonly string $name,
        /** @var string The title of the server's tab. */
        public readonly string $tabtitle,
        /** @var string The Moodle component which uses the server. */
        public readonly string $usedby,
        /** @var int The number of the database which the Moodle component uses. */
        public readonly int $database,
        /** @var \Redis|\RedisCluster|null The connection to the server. */
        protected readonly \Redis|\RedisCluster|null $redis,
    ) {
    }

    /**
     * Returns all Redis servers which Moodle uses: One per Redis cache store instance which is configured in the MUC
     * configuration and, if Redis is configured as session handler in config.php, one for the session store.
     *
     * @return server[]
     */
    public static function get_all(): array {
        $servers = [];

        // Cache stores.
        foreach (\core_cache\config::instance()->get_all_stores() as $details) {
            if ($details['plugin'] !== 'redis' || !empty($details['default'])) {
                continue;
            }
            $store = new store($details['name'], $details['configuration']);
            $servers[] = new server(
                $store->my_name(),
                get_string('cachestore', 'tool_redis', $store->my_name()),
                self::USEDBY_CACHESTORE,
                0,
                $store->is_ready() ? $store->get_redis() : null,
            );
        }

        // Session store.
        if (sessionhandler::is_configured()) {
            $handler = new sessionhandler();
            $servers[] = new server(
                $handler->get_host(),
                get_string('sessionstore', 'tool_redis', $handler->get_host()),
                self::USEDBY_SESSIONHANDLER,
                $handler->get_database(),
                $handler->get_redis(),
            );
        }

        return $servers;
    }

    /**
     * Returns if the connection to the server was established.
     *
     * @return bool
     */
    public function is_ready(): bool {
        return $this->redis !== null;
    }

    /**
     * Returns the nodes of this server which the Redis management page shows.
     *
     * For a single Redis server, this is one node without a label. For a Redis cluster, this is one node per master node,
     * labelled with host and port, as each master node has its own statistics.
     *
     * @return array[] Array of arrays with the keys 'label' (string|null) and 'node' (array|null).
     */
    public function get_nodes(): array {
        if ($this->redis instanceof \RedisCluster) {
            $nodes = [];
            foreach ($this->redis->_masters() as $master) {
                $nodes[] = ['label' => $master[0] . ':' . $master[1], 'node' => $master];
            }
            return $nodes;
        }
        return [['label' => null, 'node' => null]];
    }

    /**
     * Returns the INFO response of the Redis server, grouped by its sections.
     *
     * @param array|null $node The cluster node as returned by get_nodes(), null for a single Redis server.
     * @return array[] Array of sections, each an array of key => value pairs.
     * @throws \RedisException|\RedisClusterException If the command fails.
     */
    public function get_info(?array $node = null): array {
        $raw = ($node === null) ? $this->redis->rawCommand('INFO') : $this->redis->rawCommand($node, 'INFO');

        $info = [];
        $section = '';
        foreach (preg_split('/\r?\n/', (string) $raw) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if ($line[0] === '#') {
                $section = trim(substr($line, 1));
                $info[$section] = [];
                continue;
            }
            $pair = explode(':', $line, 2);
            $info[$section][$pair[0]] = $pair[1] ?? '';
        }
        return $info;
    }

    /**
     * Flushes the given database of the Redis server.
     *
     * @param int $db The number of the database.
     * @param array|null $node The cluster node as returned by get_nodes(), null for a single Redis server.
     * @return bool True if the database was flushed.
     * @throws \RedisException|\RedisClusterException If the command fails.
     */
    public function flush_database(int $db, ?array $node = null): bool {
        // In cluster mode, phpredis does not offer a way to select a database, so Moodle can only use db0 there.
        // Redis clusters only have db0 anyway, but Valkey clusters (since Valkey 9.0) may have more databases which
        // other clients use. These can't be flushed from here, so refuse the flush instead of flushing db0 silently.
        if ($node !== null) {
            if ($db !== 0) {
                return false;
            }
            return $this->redis->flushDB($node) === true;
        }

        // Select the database, flush it and re-select the database which the Moodle component uses.
        if ($this->redis->select($db) !== true) {
            return false;
        }
        try {
            return $this->redis->flushDB() === true;
        } finally {
            $this->redis->select($this->database);
        }
    }
}
