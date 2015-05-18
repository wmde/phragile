Feature: Connect Existing Sprint Project
  In order to see Phragile sprint overviews for existing Phabricator sprints
  As a scrum master
  I want to connect an existing project with a sprint

  Scenario: Connect existing project
    Given I am logged in
    And I am on the "Wikidata" project page
    And a sprint "Test Sprint" exists for the "Wikidata" project in Phabricator but not in Phragile
    When I click "New sprint"
    And I fill in "title" with "Test Sprint"
    And I fill in "sprint_start" with "2015-04-01"
    And I fill in "sprint_end" with "2015-04-14"
    And I press "Create new sprint"
    Then I should see "Connected \"Test Sprint\" with an existing Phabricator project"

  Scenario: Sprint already exists in Phragile
    Given I am logged in
    And I am on the "Wikidata" project page
    And a sprint "Test Sprint" exists for the "Wikidata" project
    When I click "New sprint"
    And I fill in "title" with "Test Sprint"
    And I fill in "sprint_start" with "2015-04-01"
    And I fill in "sprint_end" with "2015-04-14"
    And I press "Create new sprint"
    Then I should see "The title has already been taken"

  Scenario: Connect using a Phabricator ID
    Given I am logged in
    And I am on the "Wikidata" project page
    And I copied the "Wikidata" "Test Sprint" Phabricator ID
    And a sprint "Test Sprint" exists for the "Wikidata" project in Phabricator but not in Phragile
    When I click "New sprint"
    And I paste the copied Phabricator ID
    And I fill in "sprint_start" with "2015-04-01"
    And I fill in "sprint_end" with "2015-04-14"
    And I press "Create new sprint"
    Then I should see "Connected \"Test Sprint\" with an existing Phabricator project"
    And I should see "Test Sprint" in the "title" element
