@tool @tool_redis @javascript
Feature: Using the admin tool Redis management with a Redis session store
  In order to monitor the Redis server which is used as Moodle session store
  As admin
  I need to see the Redis session store on the Redis management page

  # Redis is configured as session handler via the config table here, which Moodle core picks up like a setting
  # in config.php. The Behat site then stores its sessions in db1 of the Redis server. The scenarios log out at the
  # end, which removes the admin session from db1, but the following login page creates a new session there.
  # Tests which rely on an empty db1 (see tool_redis_cachestore.feature) flush db1 themselves before they start.
  Background:
    Given the following config values are set as admin:
      | session_handler_class  | \core\session\redis |
      | session_redis_host     | localhost           |
      | session_redis_database | 1                   |
    And I log in as "admin"

  Scenario: Showing the Redis session store without any Redis cache store
    When I navigate to "Server > Redis management" in site administration
    Then I should see "Session store: localhost" in the "#region-main .nav-tabs .nav-link.active" "css_element"
    And I should see "Hits" in the "#region-main" "css_element"
    And I should see "Keys in db1" in the "#region-main" "css_element"
    And I should not see "Cache store:"
    And I log out

  Scenario: Showing the Redis session store next to a Redis cache store
    Given I navigate to "Plugins > Caching > Configuration" in site administration
    And I click on "Add instance" "link" in the "Redis" "table_row"
    And I set the field "Store name" to "Redis-GHA"
    And I set the field "Server" to "localhost"
    And I press "Save changes"
    And "Redis-GHA" "text" should appear after "Configured store instances" "text"
    And I visit "/admin/tool/redis/tests/behat/fixtures/redis_entry.php?action=store"
    And I should see "Redis entry stored"
    When I navigate to "Server > Redis management" in site administration
    Then I should see "Cache store: Redis-GHA" in the "#region-main .nav-tabs .nav-link.active" "css_element"
    And I should see "Session store: localhost" in the "#region-main .nav-tabs" "css_element"
    And I should see "This database is used by the Moodle cache store." in the ".tab-pane.active .tool_redis-card-db0" "css_element"
    And I should see "This database is not used by the Moodle cache store, but by other clients of this Redis server." in the ".tab-pane.active .tool_redis-card-db1" "css_element"
    And I click on "Session store: localhost" "link" in the "#region-main .nav-tabs" "css_element"
    And I should see "Session store: localhost" in the "#region-main .nav-tabs .nav-link.active" "css_element"
    And I should see "This database is used by the Moodle session handler." in the ".tab-pane.active .tool_redis-card-db1" "css_element"
    And I should see "This database is not used by the Moodle session handler, but by other clients of this Redis server." in the ".tab-pane.active .tool_redis-card-db0" "css_element"
    # Flush db0 from the session store tab to verify that flushing works on this tab as well and to clean up the entry.
    And I click on "Flush" "button" in the ".tab-pane.active .tool_redis-card-db0" "css_element"
    And I click on "Flush" "button" in the "Flush database" "dialogue"
    And I should see "The database db0 on the Redis server localhost was flushed."
    And I visit "/admin/tool/redis/tests/behat/fixtures/redis_entry.php?action=check"
    And I should see "Redis entry does not exist"
    And I log out
