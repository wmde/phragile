Feature: OAuth Login
  In order to perform actions on the site that need authentication
  As a scrum master
  I want to be able to log in with my Phabricator account

  Scenario: Log in
    Given I am not logged in
    When I follow "Log in using Phabricator"
    And Phabricator authorizes me
    Then I should see "you are now logged in"
    And I should be logged in

  Scenario: Log in with invalid access token
    Given I am not logged in
    When I go to "/login?code=abc123"
    Then I should see "Login failed"
    And I should not be logged in

  Scenario: Log out
    Given I am logged in
    When I follow "Logout"
    Then I should see "You are now logged out."
    And I should not be logged in

  Scenario: Stay logged in after reload
    Given I am logged in
    When I reload the page
    Then I should still be logged in
