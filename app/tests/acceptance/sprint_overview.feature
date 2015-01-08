Feature: Sprint Overview
  In order to track my team's progress in the current sprint
  As a scrum master
  I want to see a sprint overview including a burndown chart, task information diagrams and a sortable sprint backlog

  Scenario: Sprint Backlog
    Given a sprint "Sprint 42" exists for the "Wikidata" project
    And it has the following tasks for the "Sprint 42" sprint:
      | title         | priority |  points |
      | Fix things    | high     |  13     |
      | Implement XYZ | low      |  8      |
    When I go to the "Sprint 42" sprint overview
    Then I should see "Fix things"
    And I should see "Implement XYZ"
    And I should see "High"
    And I should see "Low"
    And I should see "13"
    And I should see "8"

  Scenario: Sprint duration
    Given a sprint "Sprint 42" exists for the "Wikidata" project
    And the sprint "Sprint 42" starts on "2014-11-25" and ends on "2014-12-08"
    When I go to the "Sprint 42" sprint overview
    Then I should see "Nov 25"
    And I should see "Nov 26"
    And I should see "Nov 27"
    And I should see "Nov 28"
    And I should see "Nov 29"
    And I should see "Nov 30"
    And I should see "Dec 1"
    And I should see "Dec 2"
    And I should see "Dec 3"
    And I should see "Dec 4"
    And I should see "Dec 5"
    And I should see "Dec 6"
    And I should see "Dec 7"
    And I should see "Dec 8"
