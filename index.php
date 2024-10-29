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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/tool/redis/locallib.php');
global $CFG;

// Set up the plugin's main page as external admin page.
admin_externalpage_setup('tool_redis');

// Page setup.
$title = get_string('pluginname', 'tool_redis');
$PAGE->set_title($title);
$PAGE->set_heading($title);

// Output has to be buffered because we want to modify the Redis Stats HTML code.
ob_start();

// Include Redis Stats.
require_once(__DIR__ . '/lib/valkey-stats/valkey-stats.php.inc');

// Get buffered content and finish buffering.
$output = ob_get_contents();
ob_end_clean();

// Take control over libXML error handling as the Redis Stats HTML code is not perfect.
libxml_use_internal_errors(true);

// Process Redis Stats HTML code into a DOMDocument object.
$redisdoc = new DOMDocument();
$redisdoc->loadHTML($output);

// Process DOM and extract the body tag.
$redisbodytag = $redisdoc->getElementsByTagName('body')->item(0);
$redisguihtml = $redisdoc->saveHTML($redisbodytag);

// Process DOM and extract the style tag.
$redisstyletag = $redisdoc->getElementsByTagName('style')->item(0);
$redisstylescode = $redisstyletag->nodeValue;

// Throw away any libXML errors which have been raised.
libxml_clear_errors();

// Finish control over libXML error handling.
libxml_use_internal_errors(false);

// Prefix all styles to avoid any conflicts with Moodle styles.
$cssprefix = '#page-admin-tool-redis-index #region-main';
$redisstylescode = tool_redis_get_prefixed_css($redisstylescode, $cssprefix);

// Add the Redis Stats styles to the page.
$CFG->additionalhtmlhead .= '<style>'.$redisstylescode.'</style>';

// Page setup.
echo $OUTPUT->header();

// Output Redis Stats.
echo $redisguihtml;

// Page setup.
echo $OUTPUT->footer();
