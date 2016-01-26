Feature: Website
  In order to see how the project performs
  As an admin
  I should see a page with project statistics

  Scenario: Statspage when logged in
    Given I am logged in
    When I click "Statistics"
    Then I should see "Overall numbers"
    And I should see "Projects 0"

  Scenario: Statspage when adding a project with a sprint
    Given a sprint "Sprint 42" exists for the "Wikidata" project
    And I am logged in
    When I click "Statistics"
    Then I should see "Projects 1"
    And I should see "Sprints 1"
    And I should see "Wikidata 1"