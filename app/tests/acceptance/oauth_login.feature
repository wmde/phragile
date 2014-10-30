Feature: OAuth Login
  In order to perform actions on the site that need authentication
  As a scrum master
  I want to be able to log in with my Phabricator account

  Scenario: Log in
    Given I am not logged in
    When I follow "Sign in using Phabricator"
    And Phabricator authorizes me
    Then I should see "Logged in as"
