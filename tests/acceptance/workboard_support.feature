Feature: Workboard Support
  In order to work with Phragile and Phabricator's workboards
  As a user
  I want to be able to switch to workboard mode and see each task's workboard column

  Scenario: Edit project settings
    Given I am logged in
    And I am on the "Wikidata" project page
    When I check "workboard_mode"
    And I fill in "closed_statuses" with "Done, Deployed"
    And I press "Save"
    Then the "workboard_mode" checkbox should be checked
    And the "closed_statuses" field should contain "Done, Deployed"
