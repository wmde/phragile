Feature: Create Sprint
  In order to be able to assign tasks to timeboxed development cycles with a title, a start date and an end date
  As a scrum master
  I want to be able to create new sprints

  Scenario: Create new sprint
    Given I am logged in
    And I am on "/projects/wikidata"
    And the sprint "Wikidata Sprint 42" does not exist
    When I click "New sprint"
    And I fill in "title" with "Wikidata Sprint 42"
    And I fill in "sprint_start" with "2014-12-01"
    And I fill in "sprint_end" with "2014-12-14"
    And I press "Create new sprint"
    Then I should see "Successfully created \"Wikidata Sprint 42\""
    And I should see "Wikidata Sprint 42" in the Phabricator project list
    And I should see "Wikidata Sprint 42" on "/projects/wikidata"

  Scenario: Sprint with invalid data
    Given I am logged in
    And I am on "/projects/wikidata"
    When I click "New sprint"
    And I fill in "sprint_end" with "01.01.2014"
    And I press "Create new sprint"
    Then I should see "The title field is required."
    Then I should see "The sprint start field is required."
    Then I should see "The sprint end does not match the format Y-m-d."

  Scenario: Sprint with start date after end date
    Given I am logged in
    And I am on "/projects/wikidata"
    When I click "New sprint"
    And I fill in "sprint_end" with "2014-12-01"
    And I fill in "sprint_start" with "2014-12-01"
    And I press "Create new sprint"
    Then I should see "The sprint end must be a date after 2014-12-01"
