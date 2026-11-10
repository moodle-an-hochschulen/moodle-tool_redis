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

namespace tool_redis\output;

use core\chart_pie;
use core\chart_series;
use core\output\plugin_renderer_base;
use core\output\single_button;
use core\url;
use tool_redis\local\server;

/**
 * Admin tool "Redis management" - Renderer.
 *
 * The renderer turns the INFO responses of the Redis servers into the template context of the Redis management page.
 *
 * @package    tool_redis
 * @copyright  2026 Alexander Bias <bias@alexanderbias.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    /** @var string The Bootstrap success colour of Boost, used for the hits in the hits chart. */
    const COLOR_HITS = '#357a32';

    /** @var string The Bootstrap warning colour of Boost, used for the misses in the hits chart. */
    const COLOR_MISSES = '#f0ad4e';

    /** @var string The brand colour of Boost, used for the used memory in the memory chart. */
    const COLOR_USEDMEMORY = '#0f6cbf';

    /** @var string A lighter tint of the brand colour of Boost, used for the released memory in the memory chart. */
    const COLOR_RELEASEDMEMORY = '#7baedc';

    /** @var string A mid grey, used for the free memory in the memory chart. */
    const COLOR_FREEMEMORY = '#8f959e';

    /** @var string|null The localized decimal separator, cached by format_number(). */
    private ?string $decsep = null;

    /** @var string|null The localized thousands separator, cached by format_number(). */
    private ?string $thousandssep = null;

    /**
     * Renders the Redis management page.
     *
     * @param array $servers The servers to show, as built by index.php: Arrays with the keys 'server' (the server
     *                       object), 'node' (the cluster node or null), 'name' and 'tabtitle'.
     * @param int $serverindex The index of the server whose tab is active.
     * @return string The HTML of the page.
     */
    public function render_page(array $servers, int $serverindex): string {
        // Gather the data of each server, one tab per server. The tab of the given server is the active one.
        $templatecontext = ['servers' => []];
        foreach ($servers as $index => $server) {
            $templatecontext['servers'][] = $this->get_server_data($index, $server, $index === $serverindex);
        }

        // Render the page template.
        return $this->render_from_template('tool_redis/page', $templatecontext);
    }

    /**
     * Gathers the template data of one server (or, in cluster mode, of one master node of a server).
     *
     * @param int $index The index of the server in the list of servers.
     * @param array $server The server as described in render_page().
     * @param bool $active If the tab of the server is the active tab.
     * @return array The template data.
     */
    protected function get_server_data(int $index, array $server, bool $active): array {
        // Start with the data of the tab itself.
        $data = ['index' => $index, 'tabtitle' => $server['tabtitle'], 'active' => $active];

        // Get the INFO response of the server. If the server is not reachable, only the error is shown on its tab.
        $info = $this->get_info($server);
        if ($info === null) {
            $data['error'] = get_string('connectionfailed', 'tool_redis', $server['name']);
            return $data;
        }

        // Flatten the sections of the INFO response into one list of fields, as the cards do not care about the sections.
        $flat = array_merge(...array_values($info));

        // Add the data of the cards, one after another. The databases and the details also need the server itself and
        // the sections of the INFO response.
        return $data +
                $this->get_hits_data($flat) +
                $this->get_memory_data($flat) +
                $this->get_uptime_data($flat) +
                $this->get_connection_data($flat) +
                $this->get_persistence_data($flat) +
                $this->get_databases_data($index, $server, $info) +
                $this->get_details_data($index, $info);
    }

    /**
     * Gets the INFO response of a server.
     *
     * @param array $server The server as described in render_page().
     * @return array|null The INFO response as returned by server::get_info(), null if the server is not reachable.
     */
    protected function get_info(array $server): ?array {
        // If the connection to the server failed already when it was opened, there is nothing to ask.
        if (!$server['server']->is_ready()) {
            return null;
        }

        // Send the INFO command. A failing command (e.g. because the server went away in the meantime) is treated
        // in the same way as a failed connection.
        try {
            return $server['server']->get_info($server['node']);
        } catch (\RedisException | \RedisClusterException $e) {
            return null;
        }
    }

    /**
     * Gathers the template data of the hits card.
     *
     * @param array $flat The flattened INFO response.
     * @return array The template data.
     */
    protected function get_hits_data(array $flat): array {
        $data = [];

        // Get the hits and misses of the key lookups.
        $hits = (int) ($flat['keyspace_hits'] ?? 0);
        $misses = (int) ($flat['keyspace_misses'] ?? 0);

        // Calculate the hit rate and render its chart. Both can only be shown if there was any lookup at all.
        if ($hits + $misses > 0) {
            $data['hitrate'] = format_float($hits / ($hits + $misses) * 100, 1) . '%';
            $data['chart'] = $this->render_doughnut(
                get_string('hitrate', 'tool_redis'),
                [$hits, $misses],
                [get_string('hits', 'tool_redis'), get_string('misses', 'tool_redis')],
                [self::COLOR_HITS, self::COLOR_MISSES],
            );
        }

        // Format the numbers which are listed next to the chart.
        $data['hits'] = $this->format_number($hits);
        $data['misses'] = $this->format_number($misses);
        $data['expiredkeys'] = $this->format_number($flat['expired_keys'] ?? 0);
        $data['evictedkeys'] = $this->format_number($flat['evicted_keys'] ?? 0);

        return $data;
    }

    /**
     * Gathers the template data of the memory card.
     *
     * @param array $flat The flattened INFO response.
     * @return array The template data.
     */
    protected function get_memory_data(array $flat): array {
        $data = [];

        // Get the used memory, the peak memory and the memory limit (which is 0 if no limit is set).
        $used = (int) ($flat['used_memory'] ?? 0);
        $peak = (int) ($flat['used_memory_peak'] ?? 0);
        $max = (int) ($flat['maxmemory'] ?? 0);

        // Format the sizes which are listed next to the chart. The limit and its usage are only shown if a limit is set.
        $data['usedmemory'] = display_size($used);
        $data['peakmemory'] = display_size($peak);
        if ($max > 0) {
            $data['maxmemory'] = display_size($max);
            $data['memorypercent'] = round($used / $max * 100);
        }

        // Render the memory chart. It shows the used memory and the released memory (the peak memory above the used
        // memory). If a memory limit is set, the limit is the whole chart and the free memory up to the limit is the
        // remaining slice. Otherwise, the peak memory is the whole chart. The chart is skipped if the whole chart would
        // be zero.
        if (($max > 0 ? $max : $peak) > 0) {
            $values = [$this->format_megabytes($used), $this->format_megabytes(max($peak - $used, 0))];
            $labels = [get_string('usedmemory', 'tool_redis'), get_string('releasedmemory', 'tool_redis')];
            $colors = [self::COLOR_USEDMEMORY, self::COLOR_RELEASEDMEMORY];
            if ($max > 0) {
                $values[] = $this->format_megabytes(max($max - $peak, 0));
                $labels[] = get_string('freememory', 'tool_redis');
                $colors[] = self::COLOR_FREEMEMORY;
            }
            $data['memorychart'] = $this->render_doughnut(get_string('memorychart', 'tool_redis'), $values, $labels, $colors);
        }

        return $data;
    }

    /**
     * Gathers the template data of the uptime card.
     *
     * @param array $flat The flattened INFO response.
     * @return array The template data.
     */
    protected function get_uptime_data(array $flat): array {
        // Format the uptime as human readable duration.
        return ['uptime' => format_time((int) ($flat['uptime_in_seconds'] ?? 0))];
    }

    /**
     * Gathers the template data of the connection card.
     *
     * @param array $flat The flattened INFO response.
     * @return array The template data.
     */
    protected function get_connection_data(array $flat): array {
        // Format the connection and command counters.
        return [
            'connectionsreceived' => $this->format_number($flat['total_connections_received'] ?? 0),
            'connectedclients' => $this->format_number($flat['connected_clients'] ?? 0),
            'connectionsrejected' => $this->format_number($flat['rejected_connections'] ?? 0),
            'commandsprocessed' => $this->format_number($flat['total_commands_processed'] ?? 0),
        ];
    }

    /**
     * Gathers the template data of the persistence card.
     *
     * @param array $flat The flattened INFO response.
     * @return array The template data.
     */
    protected function get_persistence_data(array $flat): array {
        // Format the number of changes since the last save and the time which has passed since the last save.
        return [
            'changessincelastsave' => $this->format_number($flat['rdb_changes_since_last_save'] ?? 0),
            'lastsave' => format_time(time() - (int) ($flat['rdb_last_save_time'] ?? time())),
        ];
    }

    /**
     * Gathers the template data of the keys cards, one per database.
     *
     * The database which the Moodle component (cache store or session handler) uses is always shown, even if it is
     * empty and thus missing in the INFO response. The hint which database the component uses is only shown if there
     * is more than one database.
     *
     * @param int $index The index of the server in the list of servers.
     * @param array $server The server as described in render_page().
     * @param array $info The INFO response as returned by server::get_info().
     * @return array The template data.
     */
    protected function get_databases_data(int $index, array $server, array $info): array {
        // Compose the list of databases: The database of the Moodle component as empty placeholder first, then the
        // databases from the Keyspace section of the INFO response which override the placeholder if the database has
        // any keys. The list is sorted by the database number.
        $componentdb = $server['server']->database;
        $databases = array_merge(['db' . $componentdb => 'keys=0,expires=0'], $info['Keyspace'] ?? []);
        ksort($databases, SORT_NATURAL);

        // Get the name of the Moodle component for the database hints.
        $component = get_string('component' . $server['server']->usedby, 'tool_redis');

        // Build the data of one card per database.
        $data = ['databases' => []];
        foreach ($databases as $dbname => $dbinfo) {
            // Skip anything in the Keyspace section which is not a database.
            if (!preg_match('/^db(\d+)$/', $dbname, $matches)) {
                continue;
            }
            $db = (int) $matches[1];

            // Parse the database line of the INFO response (e.g. "keys=12,expires=3,avg_ttl=0") into its fields.
            parse_str(str_replace(',', '&', $dbinfo), $dbvalues);

            // Render the flush button. In cluster mode, only db0 can be flushed (see server::flush_database()), so the
            // flush button is only shown for db0 there.
            $flushbutton = '';
            if ($server['node'] === null || $db === 0) {
                $flushbutton = $this->render_flush_button($index, $db, $server['name']);
            }

            // Compose the hint if the database is the one which the Moodle component uses or one of other clients.
            $dbhint = '';
            if (count($databases) > 1) {
                $dbhint = ($db === $componentdb) ? get_string('dbused', 'tool_redis', $component) :
                        get_string('dbother', 'tool_redis', $component);
            }

            // Add the card.
            $data['databases'][] = [
                'name' => $dbname,
                'keys' => $this->format_number($dbvalues['keys'] ?? 0),
                'keyswithexpiry' => $this->format_number($dbvalues['expires'] ?? 0),
                'dbhint' => $dbhint,
                'flushbutton' => $flushbutton,
            ];
        }

        return $data;
    }

    /**
     * Gathers the template data of the details accordion, one item per section of the INFO response.
     *
     * @param int $index The index of the server in the list of servers.
     * @param array $info The INFO response as returned by server::get_info().
     * @return array The template data.
     */
    protected function get_details_data(int $index, array $info): array {
        // Build one accordion item per section with the section's fields as table rows. The id of the item must be
        // unique on the page, so it contains the server index as well as the section name.
        $data = ['sections' => []];
        foreach ($info as $section => $rows) {
            $data['sections'][] = [
                'id' => $index . '-' . clean_param($section, PARAM_ALPHANUM),
                'name' => $section,
                'rows' => array_map(fn($key, $value) => ['key' => $key, 'value' => $value], array_keys($rows), $rows),
            ];
        }

        return $data;
    }

    /**
     * Renders the flush button of a database, including the confirmation modal.
     *
     * @param int $index The index of the server in the list of servers.
     * @param int $db The number of the database.
     * @param string $servername The name of the server, shown in the confirmation modal.
     * @return string The HTML of the button.
     */
    protected function render_flush_button(int $index, int $db, string $servername): string {
        global $CFG;

        // Compose the URL of the flush action. It is a POST request to the Redis management page which contains the
        // server, the database and the sesskey.
        $flushurl = new url('/' . $CFG->admin . '/tool/redis/index.php', [
            'server' => $index,
            'action' => 'flush',
            'db' => $db,
            'sesskey' => sesskey(),
        ]);

        // Build the button as danger button which asks for confirmation in a modal before the request is sent.
        $a = ['db' => 'db' . $db, 'server' => $servername];
        $button = new single_button($flushurl, get_string('flush', 'tool_redis'), 'post', single_button::BUTTON_DANGER, [
            'data-modal' => 'confirmation',
            'data-modal-type' => 'delete',
            'data-modal-title-str' => json_encode(['flushconfirmtitle', 'tool_redis']),
            'data-modal-content-str' => json_encode(['flushconfirm', 'tool_redis', $a]),
            'data-modal-yes-button-str' => json_encode(['flush', 'tool_redis']),
        ]);

        // Render the button.
        return $this->render($button);
    }

    /**
     * Renders a doughnut chart with a single series.
     *
     * The "Show chart data" link below the chart is not rendered as the numbers are shown next to the chart anyway.
     *
     * @param string $title The title of the series.
     * @param array $values The values of the series.
     * @param array $labels The labels of the values.
     * @param array $colors The colours of the values. The number of colours must match the number of values,
     *                      otherwise the chart is not rendered. Chart.js can't resolve CSS variables, so the Boost
     *                      colours are hardcoded in the COLOR_* constants.
     * @return string The HTML of the chart.
     */
    protected function render_doughnut(string $title, array $values, array $labels, array $colors): string {
        // Build the chart as doughnut with one series and the given labels and colours.
        $chart = new chart_pie();
        $chart->set_doughnut(true);
        $series = new chart_series($title, $values);
        $series->set_colors($colors);
        $chart->add_series($series);
        $chart->set_labels($labels);

        // Render the chart without the "Show chart data" link.
        return $this->render_chart($chart, false);
    }

    /**
     * Formats an integer from the INFO response in a localized way.
     *
     * @param int|string $value The value, as returned in the INFO response.
     * @return string The formatted number.
     */
    protected function format_number(int|string $value): string {
        // Get the localized separators once and keep them for the following calls.
        if ($this->decsep === null) {
            $this->decsep = get_string('decsep', 'langconfig');
            $this->thousandssep = get_string('thousandssep', 'langconfig');
        }

        // Format the number without decimals.
        return number_format((int) $value, 0, $this->decsep, $this->thousandssep);
    }

    /**
     * Converts a number of bytes from the INFO response to megabytes for the memory chart.
     *
     * @param int $bytes The number of bytes.
     * @return float The number of megabytes, rounded to one decimal.
     */
    protected function format_megabytes(int $bytes): float {
        // Convert the bytes to megabytes with one decimal.
        return round($bytes / 1048576, 1);
    }
}
