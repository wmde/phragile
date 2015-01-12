Feature: Sprint Snapshots
  In order to review my team's performance in a past sprint
  As a scrum master
  I want to be able to view snapshots that are not affected by actions on Phabricator

  Scenario: Create sprint snapshot
    Given a sprint "Sprint 42" exists for the "Wikidata" project
    When I create a sprint snapshot for "Sprint 42"
    Then I should see a snapshot for "Sprint 42" that was created today
