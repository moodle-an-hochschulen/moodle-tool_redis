@tool @tool_redis
Feature: Using the admin tool Redis management without any Redis store
  In order to configure a Redis store before using the Redis management
  As admin
  I need to be redirected to the cache store configuration if there isn't any Redis store configured

  Scenario: Calling the Redis management page without any Redis store configured
    Given I log in as "admin"
    When I navigate to "Server > Redis management" in site administration
    Then I should see "there isn't any Redis cache store instance configured yet" in the ".alert-info" "css_element"
    And I should see "Configured store instances"
