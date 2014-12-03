Feature: Projects
  In order be able to find the sprints I am looking for
  As a visitor
  I want to find sprints grouped by project

  Scenario: Project page
    Given the "Wikidata" project exists
    When I am on "/projects/wikidata"
    Then I should see "Wikidata"
