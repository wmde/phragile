Feature: Workboard Support
  In order to work with Phragile and Phabricator's workboards
  As a user
  I want to be able to switch to workboard mode and see each task's workboard column

  Background:
    Given I am logged in
    And I am on the "Wikidata" project page

  Scenario: Edit project settings
    When I check "workboard_mode"
    And I fill in "closed_statuses" with "Done, Deployed"
    And I press "Save"
    Then the "workboard_mode" checkbox should be checked
    And the "closed_statuses" field should contain "Done, Deployed"

  Scenario: Ignore workboard columns
    When I fill in "ignored_columns" with "Proposed, Ignore"
    And I press "Save"
    Then the "ignored_columns" field should contain "Proposed, Ignore"

  Scenario: Set default workboard column
    When I fill in "default_column" with "Incoming"
    And I press "Save"
    Then the "default_column" field should contain "Incoming"
