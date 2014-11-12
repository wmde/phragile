<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context, SnippetAcceptingContext
{
	private $params;

	public function __construct(array $params)
	{
		$this->params = $params;
	}

	/**
	 * Setup Laravel
	 *
	 * @beforeSuite
	 */
	public static function bootstrapLaravel()
	{
		$app = require_once __DIR__ . '/../../../../bootstrap/start.php';
		$app->boot();
	}

	/**
	 * @Given I am not logged in
	 */
	public function iAmNotLoggedIn()
	{
		$this->visit('/logout');
	}

	/**
	 * @When Phabricator authorizes me
	 */
	public function phabricatorAuthorizesMe()
	{
		$this->phabricatorLogin();
	}

	/**
	 * @Then I should not be logged in
	 */
	public function iShouldNotBeLoggedIn()
	{
		$this->assertPageContainsText('Log in using Phabricator');
	}

	/**
	 * @Given I am logged in
	 */
	public function iAmLoggedIn()
	{
		$this->visit('/');
		$this->clickLink('Log in');
		$this->phabricatorLogin();
	}

	private function phabricatorLogin()
	{
		$this->fillField('username', $this->params['phabricator_username']);
		$this->fillField('password', $this->params['phabricator_password']);
		$this->pressButton('Login');
		$this->clickLink('Continue');
	}

	/**
	 * @Then /^I should (?:|still )be logged in$/
	 */
	public function iShouldBeLoggedIn()
	{
		$this->assertPageContainsText('Logged in as ' . $this->params['phabricator_username']);
	}

	/**
	 * @When I submit a valid Conduit certificate
	 */
	public function iSubmitAValidConduitCertificate()
	{
		$this->submitConduitCertificate($this->params['conduit_certificate']);
	}

	/**
	 * @When I submit an invalid Conduit certificate
	 */
	public function iSubmitAnInvalidConduitCertificate()
	{
		$this->submitConduitCertificate('INVALID_CERTIFICATE');
	}

	private function submitConduitCertificate($certificate)
	{
		$this->fillField('conduit_certificate', $certificate);
		$this->pressButton('Submit');
	}

	/**
	 * @When /^(?:|I )click "(?P<link>(?:[^"]|\\")*)"$/
	 */
	public function iClick($link)
	{
		$this->clickLink($link);
	}
}
