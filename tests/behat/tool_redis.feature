@tool @tool_redis @javascript
Feature: Using the admin tool Redis management
  In order to monitor and flush the Redis servers which Moodle uses
  As admin
  I need to be able to open the Redis management page and to flush a Redis database

  # The Redis management page needs at least one Redis server, so a Redis cache store instance is configured here.
  Background:
    Given the following "users" exist:
      | username |
      | student1 |
    And I log in as "admin"
    And I navigate to "Plugins > Caching > Configuration" in site administration
    And I click on "Add instance" "link" in the "Redis" "table_row"
    And I set the field "Store name" to "Redis-GHA"
    And I set the field "Server" to "localhost"
    And I press "Save changes"
    And "Redis-GHA" "text" should appear after "Configured store instances" "text"

  Scenario: Calling the Redis management page
    When I navigate to "Server > Redis management" in site administration
    Then I should see "Cache store: Redis-GHA" in the "#region-main .nav-tabs .nav-link.active" "css_element"
    And I should see "Hits" in the "#region-main" "css_element"
    And I should see "Memory" in the "#region-main" "css_element"
    And I should see "Uptime" in the "#region-main" "css_element"
    And I should see "Connection" in the "#region-main" "css_element"
    And I should see "Persistence" in the "#region-main" "css_element"
    And I should see "Keys in db0" in the "#region-main" "css_element"
    And I should see "Details" in the "#region-main" "css_element"
    And I should not see "redis_version"
    And I click on "Server" "button" in the "#region-main .accordion" "css_element"
    And I should see "redis_version" in the "#region-main .accordion" "css_element"

  Scenario: Flushing a Redis database from the Redis management page
    Given I visit "/admin/tool/redis/tests/behat/fixtures/redis_entry.php?action=store"
    And I should see "Redis entry stored"
    When I navigate to "Server > Redis management" in site administration
    Then I should see "1" in the ".tool_redis-card-db0 .tool_redis-keycount" "css_element"
    And I click on "Flush" "button" in the "#region-main" "css_element"
    And I click on "Flush" "button" in the "Flush database" "dialogue"
    Then I should see "The database db0 on the Redis server Redis-GHA was flushed."
    And I should see "0" in the ".tool_redis-card-db0 .tool_redis-keycount" "css_element"
    And I visit "/admin/tool/redis/tests/behat/fixtures/redis_entry.php?action=check"
    And I should see "Redis entry does not exist"

  Scenario Outline: Rejecting invalid flush requests
    Given I visit "/admin/tool/redis/tests/behat/fixtures/redis_entry.php?action=store"
    And I should see "Redis entry stored"
    When I send a flush request to the Redis management page with the parameters "<parameters>"
    Then the Redis flush request should be rejected
    And I visit "/admin/tool/redis/tests/behat/fixtures/redis_entry.php?action=check"
    And I should see "Redis entry exists"

    Examples:
      | parameters                                    |
      | action=flush&server=0&db=0&sesskey=invalid    |
      | action=flush&server=0&sesskey={sesskey}       |
      | action=flush&server=0&db=-1&sesskey={sesskey} |
      | action=flush&server=1&db=0&sesskey={sesskey}  |

  Scenario: Rejecting flush requests of non-admin users
    Given I visit "/admin/tool/redis/tests/behat/fixtures/redis_entry.php?action=store"
    And I should see "Redis entry stored"
    And I log out
    And I log in as "student1"
    When I send a flush request to the Redis management page with the parameters "action=flush&server=0&db=0&sesskey={sesskey}"
    Then the Redis flush request should be rejected
    And I log out
    And I log in as "admin"
    And I visit "/admin/tool/redis/tests/behat/fixtures/redis_entry.php?action=check"
    And I should see "Redis entry exists"
    # Cross-check that the very same request is successful for an admin.
    And I send a flush request to the Redis management page with the parameters "action=flush&server=0&db=0&sesskey={sesskey}"
    And the Redis flush request should be successful
    And I visit "/admin/tool/redis/tests/behat/fixtures/redis_entry.php?action=check"
    And I should see "Redis entry does not exist"
