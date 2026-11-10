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
 * Admin tool "Redis management" - Behat step definitions
 *
 * @package    tool_redis
 * @copyright  2026 Alexander Bias <bias@alexanderbias.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ExpectationException;

/**
 * Step definitions.
 *
 * @package    tool_redis
 * @copyright  2026 Alexander Bias <bias@alexanderbias.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_tool_redis extends behat_base {
    /**
     * The response of the last flush request.
     *
     * @var string|null
     */
    private $flushresponse = null;

    /**
     * The step disables debugging on the Behat site.
     *
     * Behat fails a step if the page shows a debugging() message. The Redis cache store from Moodle core outputs such a
     * message on every page which instantiates the store if the connection to the Redis server fails. This step is needed
     * to test how the Redis management page handles an unreachable Redis server. It should wrap only the steps which would
     * fail otherwise, debugging is enabled again with the corresponding step.
     *
     * @Given /^debugging is disabled on the Behat site$/
     * @return void
     */
    public function debugging_is_disabled_on_the_behat_site(): void {
        set_config('debug', 0);
        set_config('debugdisplay', 0);
    }

    /**
     * The step enables debugging on the Behat site again, with the same settings as the Behat site is initialised with.
     *
     * @Given /^debugging is enabled on the Behat site$/
     * @return void
     */
    public function debugging_is_enabled_on_the_behat_site(): void {
        set_config('debug', DEBUG_DEVELOPER);
        set_config('debugdisplay', 1);
    }

    /**
     * The step sends a flush request with the given POST parameters to the Redis management page.
     *
     * The request is sent from the browser as a synchronous POST request with the session cookie of the current user,
     * in the same way as the flush button of the Redis management page does it. This way, the flush operation can be
     * tested with invalid parameters and with any user without the Behat test failing on the exception page which the
     * Redis management page shows in these cases. The placeholder {sesskey} in the parameters is replaced by the valid
     * sesskey of the current user, so the current page must be a Moodle page.
     *
     * @When /^I send a flush request to the Redis management page with the parameters "(?P<parameters>[^"]*)"$/
     * @param string $parameters The URL-encoded POST parameters, optionally containing the placeholder {sesskey}.
     * @return void
     */
    public function i_send_a_flush_request_to_the_redis_management_page_with_the_parameters(string $parameters): void {
        // Compose the script which sends the request synchronously, so that its response can be returned directly.
        $script = '(function() {' .
                'var request = new XMLHttpRequest();' .
                'request.open("POST", M.cfg.wwwroot + "/admin/tool/redis/index.php", false);' .
                'request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");' .
                'request.send(' . json_encode($parameters) . '.replace("{sesskey}", M.cfg.sesskey));' .
                'return request.responseText;' .
                '})()';

        // Send the request and remember its response.
        $this->flushresponse = $this->evaluate_script($script);
    }

    /**
     * The step checks if the last flush request was successful or rejected.
     *
     * A rejected request is answered with a Moodle exception page (e.g. for an invalid sesskey, a missing parameter or
     * a missing capability), a successful request with a redirect to the Redis management page.
     *
     * @Then /^the Redis flush request should be (?P<result>successful|rejected)$/
     * @param string $result Either 'successful' or 'rejected'.
     * @return void
     * @throws ExpectationException
     */
    public function the_redis_flush_request_should_be(string $result): void {
        // If no flush request was sent before, the check can't be done.
        if ($this->flushresponse === null) {
            throw new ExpectationException('No Redis flush request was sent before', $this->getSession());
        }

        // Check if the flush request had the expected result.
        $rejected = str_contains($this->flushresponse, 'data-rel="fatalerror"');
        if ($rejected !== ($result === 'rejected')) {
            throw new ExpectationException('The Redis flush request was expected to be ' . $result .
                    ', but the response was: ' . strip_tags($this->flushresponse), $this->getSession());
        }
    }
}
