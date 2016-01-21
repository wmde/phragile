Feature: Unknown Sprint Page
  In order to easily connect an existing Phabricator sprint with Phragile
  As a user
  I want to see a page that allows me to instantly create that sprint on Phragile when I go to a sprint page that Phragile does not know about yet.

  Scenario: Sprint exists in Phabricator but not in Phragile
    Given I am logged in
    And the "Wikidata" project exists
    And a sprint "Test Sprint" exists for the "Wikidata" project in Phabricator but not in Phragile
    When I go to the sprint overview of the missing sprint
    And I select "Wikidata" from "project"
    And I fill in "sprint_start" with "2015-08-08"
    And I fill in "sprint_end" with "2015-08-22"
    And I press "Connect this sprint with Phragile"
    Then I should see "Connected \"Test Sprint\" with an existing Phabricator project"

  Scenario: Not logged in
    Given I am not logged in
    And the "Wikidata" project exists
    And a sprint "Test Sprint" exists for the "Wikidata" project in Phabricator but not in Phragile
    When I go to the sprint overview of the missing sprint
    Then I should see "Please log in to connect the sprint with Phragile."

  Scenario: Sprint exists neither in Phabricator nor Phragile
    Given I am on "/sprints/99999999"
    Then I should see "Sprint not found"
