Feature: Add Conduit Conduit API Token
  In order to add new sprints to Phrabricator through Phragile
  As a scrum master
  I want to be able to add my Conduit API Token

  Scenario: Submit a valid Conduit API Token
    Given I am logged in
    When I click "Conduit API Token"
    And I submit a valid Conduit API Token
    Then I should see "Added your Conduit API Token."

  Scenario: Submit an invalid Conduit API Token
    Given I am logged in
    When I click "Conduit API Token"
    And I submit an invalid Conduit API Token
    Then I should see "The submitted Conduit API Token was invalid."

  Scenario: Save Conduit API Token
    Given I am logged in
    And I have not added my Conduit API Token
    When I submit a valid Conduit API Token
    Then I should see my Conduit API Token in a text area
