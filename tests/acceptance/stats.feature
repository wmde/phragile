Feature: Website
  In order to see how the project performs
  As an admin
  I should see a page with project statistics

  Scenario: Statspage when logged in
    Given I am logged in
    And the "Wikidata" project exists
    When I click "Statistics"
    Then I should see "Overall numbers"
    And I should see the current number of projects
    And I should see the current number of sprints
    And I should see "Wikidata"

  Scenario: Statspage when adding a project and sprint
    Given I am logged in
    And the project "Foobar" does not exist
    And the sprint "FooSprint" does not exist
    And I remember the current number of projects and sprints
    When I fill in "title" with "Foobar"
    And I press "Create"
    And a sprint "FooSprint" exists for the "Foobar" project
    And I click "Statistics"
    Then I should see the remembered number of projects plus 1
    Then I should see the remembered number of sprints plus 1
    And I should see "Foobar"
