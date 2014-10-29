Feature: Website
  In order to see what site I'm on
  As a visitor
  I should see the title of the current page

Scenario: Homepage
  Given I am on the homepage
  Then I should see "Phragile" in the "title" element
