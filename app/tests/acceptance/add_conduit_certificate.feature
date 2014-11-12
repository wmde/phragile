Feature: Add Conduit certificate
  In order to add new sprints to Phrabricator through Phragile
  As a scrum master
  I want to be able to add my Conduit certificate

  Scenario: Submit a valid Conduit certificate
    Given I am logged in
    When I click "Conduit certificate"
    And I submit a valid Conduit certificate
    Then I should see "Added your Conduit certificate."

  Scenario: Submit an invalid Conduit certificate
    Given I am logged in
    When I click "Conduit certificate"
    And I submit an invalid Conduit certificate
    Then I should see "The submitted Conduit certificate was invalid."
