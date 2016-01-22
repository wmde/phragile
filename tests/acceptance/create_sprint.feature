Feature: Create Sprint
  In order to be able to assign tasks to timeboxed development cycles with a title, a start date and an end date
  As a scrum master
  I want to be able to create new sprints

  Background:
    Given I am logged in
    And I submit a valid Conduit API Token
    And the "Wikidata" project exists
    And I am on the "Wikidata" project page

  Scenario: Add sprint
    Given the sprint "Wikidata Sprint 42" does not exist
    When I click "Add sprint"
    And I fill in "title" with "Wikidata Sprint 42"
    And I fill in "sprint_start" with "2014-12-01"
    And I fill in "sprint_end" with "2014-12-14"
    And I press "Add sprint"
    Then I should see "Successfully created \"Wikidata Sprint 42\""
    And I should see "Wikidata Sprint 42" in the Phabricator project list
    And I should see "Wikidata Sprint 42" on "/projects/wikidata"

  Scenario: Sprint with invalid data
    When I click "Add sprint"
    And I fill in "sprint_end" with "01.01.2014"
    And I press "Add sprint"
    Then I should see "The title field is required."
    And I should see "The sprint start field is required."
    And I should see "The sprint end does not match the format Y-m-d."

  Scenario: Sprint with start date after end date
    When I click "Add sprint"
    And I fill in "sprint_end" with "2014-12-01"
    And I fill in "sprint_start" with "2014-12-01"
    And I press "Add sprint"
    Then I should see "The sprint end must be a date after 2014-12-01"
