Feature: Sprint Snapshots
  In order to review my team's performance in a past sprint
  As a scrum master
  I want to be able to view snapshots that are not affected by actions on Phabricator

  Scenario: View sprint snapshot
    Given a sprint "Sprint 42" exists for the "Wikidata" project
    When I create a sprint snapshot for "Sprint 42"
    And I go to the "Sprint 42" sprint overview
    Then I should see a snapshot that was created today

  Scenario: Snapshots are not affected by Phabricator actions
    Given a sprint "Sprint 42" exists for the "Wikidata" project
    And "Sprint 42" contains a task
    When I create a sprint snapshot for "Sprint 42"
    And the selected task is removed from all projects
    When I go to the "Sprint 42" live page
    Then I should not see the selected task
    But I should see the selected in the latest "Sprint 42" snapshot

  Scenario: Create new snapshot
    Given a sprint "Sprint 42" exists for the "Wikidata" project
    And I am logged in
    When I go to the "Sprint 42" live page
    And I press "Create snapshot"
    Then I should see "Successfully created a snapshot for \"Sprint 42\""
    And I should see a snapshot that was created today

  Scenario: Delete snapshot
    Given a sprint "Sprint 42" exists for the "Wikidata" project
    And I am logged in
    And "Sprint 42" has one snapshot
    When I go to the latest snapshot page of "Sprint 42"
    And I press "Delete snapshot"
    Then "Sprint 42" should not have any snapshots

  Scenario: Automated snapshots
    Given I know the number of snapshots
    When I execute artisan "snapshots:create"
    Then I should have created one snapshot for each sprint
