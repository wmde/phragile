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

    /**
     * @Given I have not added my Conduit certificate
     */
    public function iHaveNotAddedMyConduitCertificate()
    {
        User::where(['username' => $this->params['phabricator_username']])
			->update(['conduit_certificate' => '']);
    }

    /**
     * @Then I should see my Conduit certificate in a text area
     */
    public function iShouldSeeMyConduitCertificateInATextArea()
    {
		$this->assertElementContainsText('#conduit-modal textarea', $this->params['conduit_certificate']);
    }

    /**
     * @Then I should see :title in the Phabricator project list
     */
    public function iShouldSeeInThePhabricatorProjectList($title)
    {
		if (!App::make('phabricator')->queryProjectByTitle($title))
		{
			throw new Exception("Project '$title' does not exist.");
		}
    }

    /**
     * @Then I should see :text on :page
     */
    public function iShouldSeeOn($text, $page)
    {
		$this->visit($page);
		$this->assertPageContainsText($text);
    }

    /**
     * @Given the :title project exists
     */
    public function theProjectExists($title)
    {
        Project::create([
			'title' => $title,
			'slug' => Str::slug($title)
		]);
    }

    /**
     * @Given the sprint :title does not exist
     */
    public function theSprintDoesNotExist($title)
    {
        Sprint::where('title', $title)->delete();

		$project = App::make('phabricator')->queryProjectByTitle($title);
		if ($project && isset($project['phid']))
		{
			exec($this->params['phabricator_path']
			     . '/bin/remove destroy '
			     . $project['phid']
				 . ' --force');
		}
    }
}
