@tool @tool_redis @javascript
Feature: Using the admin tool Redis management
  In order to show the Redis management
  As admin
  I need to be able open the Redis management page

  Scenario: Calling the Redis management page
    When I log in as "admin"
    And I navigate to "Plugins > Caching > Configuration" in site administration
    And I click on "Add instance" "link" in the "Redis" "table_row"
    And I set the field "Store name" to "Redis-GHA"
    And I set the field "Server" to "localhost"
    And I press "Save changes"
    Then "Redis-GHA" "text" should appear after "Configured store instances" "text"
    And I navigate to "Server > Redis management" in site administration
    Then I should see "Valkey Stats"
    And I should see "Used Memory"
    And I should see "Hits"
    And I should see "Persistence"
