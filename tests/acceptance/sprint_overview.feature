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
    Then I should see "2014-11-25"
    And I should see "2014-11-26"
    And I should see "2014-11-27"
    And I should see "2014-11-28"
    And I should see "2014-11-29"
    And I should see "2014-11-30"
    And I should see "2014-12-01"
    And I should see "2014-12-02"
    And I should see "2014-12-03"
    And I should see "2014-12-04"
    And I should see "2014-12-05"
    And I should see "2014-12-06"
    And I should see "2014-12-07"
    And I should see "2014-12-08"
