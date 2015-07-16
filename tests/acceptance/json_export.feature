Feature: Export Chart Data
  In order to create my own charts from the data that Phragile provides
  As a scrum master
  I want to be able to access the charts' underlying data.

  Scenario: View exported JSON
    Given a sprint "Sprint 42" exists for the "Wikidata" project
    And the sprint "Sprint 42" starts on "2014-11-25" and ends on "2014-12-08"
    When I go to the "Sprint 42" export page
    Then I should get a valid JSON response
    And there should be a "pointsClosedBeforeSprint" property in the response
    And there should be a "sprint" property in the response
    And the "sprint" property should contain 14 elements
