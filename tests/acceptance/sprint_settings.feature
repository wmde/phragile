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

  Scenario: Edit ignore estimates
    Given I am logged in
    And a sprint "Sprint 42" exists for the "Wikidata" project
    When I go to the "Sprint 42" sprint overview
    And I check "ignore_estimates"
    And I press "save-sprint-settings"
    Then I should see "The sprint settings have been updated"
    And the "ignore_estimates" checkbox should be checked

  Scenario: Invalid date format
    Given I am logged in
    And a sprint "Sprint 42" exists for the "Wikidata" project
    When I go to the "Sprint 42" sprint overview
    And I fill in "sprint_start" with "26.11.2014"
    And I press "save-sprint-settings"
    Then I should see "The sprint start does not match the format Y-m-d."

  Scenario: Invalid end date
    Given I am logged in
    And a sprint "Sprint 42" exists for the "Wikidata" project
    When I go to the "Sprint 42" sprint overview
    And I fill in "sprint_start" with "2014-11-25"
    And I fill in "sprint_end" with "2014-11-20"
    And I press "save-sprint-settings"
    Then I should see "The sprint end must be a date after 2014-11-25."

  Scenario: Missing title
    Given I am logged in
    And a sprint "Sprint 42" exists for the "Wikidata" project
    When I go to the "Sprint 42" sprint overview
    And I fill in "title" with ""
    And I press "save-sprint-settings"
    Then I should see "The title field is required."


  Scenario: Duplicate title
    Given I am logged in
    And a sprint "Sprint 42" exists for the "Wikidata" project
    And a sprint "Sprint Foo Bar" exists for the "Wikidata" project
    When I go to the "Sprint 42" sprint overview
    And I fill in "title" with "Sprint Foo Bar"
    And I press "save-sprint-settings"
    Then I should see "The title has already been taken."
    And the "title" field should contain "Sprint 42"
