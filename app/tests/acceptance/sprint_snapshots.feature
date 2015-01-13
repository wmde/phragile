Feature: Sprint Snapshots
  In order to review my team's performance in a past sprint
  As a scrum master
  I want to be able to view snapshots that are not affected by actions on Phabricator

  Scenario: Create sprint snapshot
    Given a sprint "Sprint 42" exists for the "Wikidata" project
    When I create a sprint snapshot for "Sprint 42"
    And I go to the "Sprint 42" sprint overview
    Then I should see a snapshot that was created today
