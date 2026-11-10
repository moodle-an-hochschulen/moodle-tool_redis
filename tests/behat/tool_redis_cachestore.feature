@tool @tool_redis @javascript
Feature: Using the admin tool Redis management with Redis cache stores
  In order to monitor the Redis servers which are used as Moodle cache stores
  As admin
  I need to see each Redis cache store on the Redis management page

  Background:
    Given I log in as "admin"
    And I navigate to "Plugins > Caching > Configuration" in site administration
    And I click on "Add instance" "link" in the "Redis" "table_row"
    And I set the field "Store name" to "Redis-GHA"
    And I set the field "Server" to "localhost"
    And I press "Save changes"
    And "Redis-GHA" "text" should appear after "Configured store instances" "text"

  Scenario: Showing one tab per Redis cache store
    Given I navigate to "Plugins > Caching > Configuration" in site administration
    And I click on "Add instance" "link" in the "Redis" "table_row"
    And I set the field "Store name" to "Redis-GHA-2"
    And I set the field "Server" to "localhost"
    And I press "Save changes"
    And "Redis-GHA-2" "text" should appear after "Configured store instances" "text"
    When I navigate to "Server > Redis management" in site administration
    Then I should see "Cache store: Redis-GHA" in the "#region-main .nav-tabs .nav-link.active" "css_element"
    And I should see "Cache store: Redis-GHA-2" in the "#region-main .nav-tabs" "css_element"
    And "#tool_redis-pane-0" "css_element" should be visible
    And "#tool_redis-pane-1" "css_element" should not be visible
    And I click on "Cache store: Redis-GHA-2" "link" in the "#region-main .nav-tabs" "css_element"
    And I should see "Cache store: Redis-GHA-2" in the "#region-main .nav-tabs .nav-link.active" "css_element"
    And "#tool_redis-pane-1" "css_element" should be visible
    And "#tool_redis-pane-0" "css_element" should not be visible

  Scenario: Showing an error for a Redis cache store which is not reachable
    Given I navigate to "Plugins > Caching > Configuration" in site administration
    And I click on "Add instance" "link" in the "Redis" "table_row"
    And I set the field "Store name" to "Redis-Unreachable"
    And I set the field "Server" to "localhost:6390"
    # As soon as the unreachable store is saved, the Redis cache store from Moodle core outputs a debugging message on every
    # page which instantiates the store (the cache store configuration page as well as the Redis management page), which
    # would fail the following steps. Thus, debugging is disabled for exactly these steps. The steps after the Redis
    # management page has been loaded do not load another page, so debugging is enabled again right after the page load.
    And debugging is disabled on the Behat site
    And I press "Save changes"
    And "Redis-Unreachable" "text" should appear after "Configured store instances" "text"
    When I navigate to "Server > Redis management" in site administration
    And debugging is enabled on the Behat site
    Then I should see "Cache store: Redis-GHA" in the "#region-main .nav-tabs .nav-link.active" "css_element"
    And I should see "Keys in db0" in the "#tool_redis-pane-0" "css_element"
    And I click on "Cache store: Redis-Unreachable" "link" in the "#region-main .nav-tabs" "css_element"
    And I should see "The connection to the Redis server \"Redis-Unreachable\" failed. Please check its configuration." in the "#tool_redis-pane-1 .alert-danger" "css_element"
    And "#tool_redis-pane-1 .tool_redis-card" "css_element" should not exist

  Scenario: Showing the hint which database the Moodle cache store uses only if there is more than one database
    # Make sure that db1 is empty, even if other tests (like the session store tests) have left keys there.
    Given I visit "/admin/tool/redis/tests/behat/fixtures/redis_entry.php?action=flush&db=1"
    And I should see "Redis database flushed"
    When I navigate to "Server > Redis management" in site administration
    Then I should see "Keys in db0" in the "#region-main" "css_element"
    And I should not see "Keys in db1"
    And I should not see "This database is used by the Moodle cache store."
    And I visit "/admin/tool/redis/tests/behat/fixtures/redis_entry.php?action=store&db=1"
    And I should see "Redis entry stored"
    And I navigate to "Server > Redis management" in site administration
    And I should see "Keys in db1" in the "#region-main" "css_element"
    And I should see "This database is used by the Moodle cache store." in the ".tool_redis-card-db0" "css_element"
    And I should see "This database is not used by the Moodle cache store, but by other clients of this Redis server." in the ".tool_redis-card-db1" "css_element"
    # Flush db1 again to clean up and to verify that the flush button of a database other than db0 flushes this database.
    And I click on "Flush" "button" in the ".tool_redis-card-db1" "css_element"
    And I click on "Flush" "button" in the "Flush database" "dialogue"
    Then I should see "The database db1 on the Redis server Redis-GHA was flushed."
    And I should not see "Keys in db1"
    And I should not see "This database is used by the Moodle cache store."
    And I visit "/admin/tool/redis/tests/behat/fixtures/redis_entry.php?action=check&db=1"
    And I should see "Redis entry does not exist"
