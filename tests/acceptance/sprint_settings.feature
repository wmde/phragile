Feature: Sprint Settings
  In order to customize sprint overviews to my needs
  As a user
  I want to set custom options for my sprints

  Scenario: Edit sprint dates
    Given I am logged in
    And a sprint "Sprint 42" exists for the "Wikidata" project
    And the sprint "Sprint 42" starts on "2014-11-25" and ends on "2014-12-08"
    When I go to the "Sprint 42" sprint overview
    And I fill in "sprint_start" with "2014-11-26"
    And I fill in "sprint_end" with "2014-12-09"
    And I press "save-sprint-settings"
    Then I should see "The sprint settings have been updated"
    And the "sprint_start" field should contain "2014-11-26"
    And the "sprint_end" field should contain "2014-12-09"

  Scenario: Edit sprint title
    Given I am logged in
    And a sprint "Sprint 42" exists for the "Wikidata" project
    When I go to the "Sprint 42" sprint overview
    And I fill in "title" with "Sprint Foo Bar"
    And I press "save-sprint-settings"
    Then I should see "The sprint settings have been updated"
    And the "title" field should contain "Sprint Foo Bar"
    And I should see "Wikidata Sprint Foo Bar" on "/projects/wikidata"

  Scenario: Edit ignore estimates
    Given I am logged in
    And a sprint "Sprint 42" exists for the "Wikidata" project
    When I go to the "Sprint 42" sprint overview
    And I check "ignore_estimates"
    And I press "save-sprint-settings"
    Then I should see "The sprint settings have been updated"
    And the "ignore_estimates" checkbox should be checked
