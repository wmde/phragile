Feature: Projects
  In order be able to find the sprints I am looking for
  As a visitor
  I want to find sprints grouped by project

  Scenario: Project page
    Given the "Wikidata" project exists
    When I am on the "Wikidata" project page
    Then I should see "Wikidata"

  Scenario: Project List
    Given the "Wikidata" project exists
    And the "Some Other Project" project exists
    When I am on "/"
    Then I should see "Wikidata"
    And I should see "Some Other Project"

  Scenario: Create project
    Given I am on "/"
    And I am logged in
    And I am a Phragile admin
    And the project "Foobar" does not exist
    When I fill in "title" with "Foobar"
    And I press "Create"
    Then I should see "Foobar" in the "#projects" element
    And I should see "Foobar" on "/projects/foobar"

  Scenario: Project titles have to be unique
    Given I am on "/"
    And I am logged in
    And the "Foobar" project exists
    When I fill in "title" with "Foobar"
    And I press "Create"
    Then I should see "A project with this title already exists."

  Scenario: Error for projects that do not exist
    Given the project "Foobar" does not exist
    When I go to "/projects/foobar"
    Then I should see "It looks like this project does not exist."
    And I should see "Select a project"
