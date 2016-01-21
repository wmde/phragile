Feature: Export Chart Data
  In order to create my own charts from the data that Phragile provides
  As a scrum master
  I want to be able to access the charts' underlying data.

  Background:
    Given I am logged in
    And I submit a valid Conduit API Token
    And a sprint "Sprint 42" exists for the "Wikidata" project
    And the sprint "Sprint 42" starts on "2014-11-25" and ends on "2014-12-08"

  Scenario: View exported sprint
    When I go to the "Sprint 42" export page
    Then I should get a valid JSON response
    And there should be a "pointsClosedBeforeSprint" property in the response
    And there should be a "sprint" property in the response
    And the "sprint" property should contain 14 elements

  Scenario: View exported snapshot
    When I create a sprint snapshot for "Sprint 42"
    And I go to the latest snapshot export page of "Sprint 42"
    Then I should get a valid JSON response
    And there should be a "pointsClosedBeforeSprint" property in the response
    And there should be a "sprint" property in the response
    And the "sprint" property should contain 14 elements
