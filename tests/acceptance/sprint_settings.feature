Feature: Sprint Settings
  In order to customize sprint overviews to my needs
  As a user
  I want to set custom options for my sprints

  Scenario: Edit sprint settings
    Given I am logged in
    And a sprint "Sprint 42" exists for the "Wikidata" project
    When I go to the "Sprint 42" sprint overview
    And I check "ignore_estimates"
    And I press "save-sprint-settings"
    Then I should see "The sprint settings have been updated"
    And the "ignore_estimates" checkbox should be checked
