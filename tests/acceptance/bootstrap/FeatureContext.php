<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use PHPUnit_Framework_Assert as PHPUnit;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context, SnippetAcceptingContext
{
	private $params;
	private $phabricatorProjectID;
	private $selectedTask;

	public function __construct(array $params)
	{
		$this->params = $params;
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
		Project::firstOrCreate([
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

	private function getPhabricatorProjectFromTitle($title)
	{
		return App::make('phabricator')->queryProjectByTitle($title);
	}

	/**
	 * @Given a sprint :sprint exists for the :project project
	 */
	public function aSprintExistsForTheProject($sprintTitle, $projectTitle)
	{
		Auth::login(User::where('username', $this->params['phabricator_username'])->first()); // this is a bit ugly.

		$project = Project::firstOrCreate(['title' => $projectTitle]);
		$existingSprint = Sprint::where('title', $sprintTitle)->first();

		if (!$existingSprint)
		{
			$phabricatorProject = $this->getPhabricatorProjectFromTitle($sprintTitle);
			$newSprint = new Sprint([
				'title' => $sprintTitle,
				'project_id' => $project->id,
				'sprint_start' => '2014-12-01',
				'sprint_end' => '2014-12-14',
				'phabricator_id' => $phabricatorProject['id'],
				'phid' => $phabricatorProject['phid'],
			]);

			if (!$phabricatorProject || !$newSprint->save())
			{
				throw new Exception('There was a problem creating the sprint.' . $newSprint->getPhabricatorError());
			}
		}
	}

	/**
	 * @Given it has the following tasks for the :sprint Sprint:
	 */
	public function itHasTheFollowingTasks($sprint, \Behat\Gherkin\Node\TableNode $table)
	{
		$sprintPHID = Sprint::where('title', $sprint)->first()->phid;
		$phabricator = App::make('phabricator');

		foreach ($table->getHash() as $task)
		{
			$phabricator->createTask($sprintPHID, $task);
		}
	}

	/**
	 * @When I go to the :sprint sprint overview
	 */
	public function iGoToTheSprintOverview($sprint)
	{
		$this->visit('/sprints/' . Sprint::where('title', $sprint)->first()->phabricator_id);
	}

	/**
	 * @Given the sprint :sprint starts on :start and ends on :end
	 */
	public function itStartsOnAndEndsOn($sprint, $start, $end)
	{
		Sprint::where('title', $sprint)->update([
				'sprint_start' => $start,
				'sprint_end' => $end
			]
		);
	}

	/**
	 * @When I create a sprint snapshot for :sprint
	 */
	public function iCreateASprintSnapshotFor($sprint)
	{
		Sprint::where(['title' => $sprint])->first()
			->createSnapshot();
	}

	/**
	 * @Then I should see a snapshot that was created today
	 */
	public function iShouldSeeASnapshotForThatWasCreatedToday()
	{
		$this->assertElementContains('#snapshots', date('Y-m-d'));
	}

	/**
	 * @When I remove task :taskID from all projects
	 */
	public function iRemoveTaskFrom($taskID)
	{
		App::make('phabricator')->updateTask($taskID, ['projectPHIDs' => []]);
	}

	/**
	 * @Then I should see :text in the latest :sprint snapshot
	 */
	public function iShouldSeeInTheLatestSnapshot($text, $sprint)
	{
		$this->iGoToTheLatestSnapshotPageOf($sprint);
		$this->assertResponseContains($text);
	}

	/**
	 * @When I go to the :sprint live page
	 */
	public function iGoToTheSprintLivePage($sprint)
	{
		$this->visit('/live/' . Sprint::where('title', $sprint)->first()->phabricator_id);
	}

	/**
	 * @Given :sprint has one snapshot
	 */
	public function hasOneSnapshot($sprint)
	{
		$sprint = Sprint::where('title', $sprint)->first();

		$sprint->sprintSnapshots()->delete();
		$sprint->createSnapshot();
	}

	/**
	 * @When I go to the latest snapshot page of :sprint
	 */
	public function iGoToTheLatestSnapshotPageOf($sprint)
	{
		$this->visit('/snapshots/' . Sprint::where(['title' => $sprint])->first()->sprintSnapshots->first()->id);
	}

	/**
	 * @Then :sprint should not have any snapshots
	 */
	public function shouldNotHaveAnySnapshots($sprint)
	{
		$this->iGoToTheSprintLivePage($sprint);
		$this->assertElementNotOnPage('#snapshots ul');
	}

	/**
	 * @Given I am a Phragile admin
	 */
	public function iAmAPhragileAdmin()
	{
		if (!User::where('username', $this->params['phabricator_username'])->first()->isInAdminList($_ENV['PHRAGILE_ADMINS']))
		{
			throw new Exception($this->params['phabricator_username'] . ' is not a Phragile admin.');
		}
	}

	/**
	 * @Given the project :project does not exist
	 */
	public function theProjectDoesNotExist($project)
	{
		Project::where('title', $project)->delete();
	}

	/**
	 * @Given I am on the :project project page
	 */
	public function iAmOnTheProjectPage($project)
	{
		$this->theProjectExists($project);
		$this->visit("/projects/" . Project::where('title', $project)->first()->slug);
	}

	/**
	 * @Given a sprint :sprintTitle exists for the :projectTitle project in Phabricator but not in Phragile
	 */
	public function aSprintExistsForTheProjectInPhabricatorButNotInPhragile($sprintTitle, $projectTitle)
	{
		$project = Project::where('title', $projectTitle)->first();
		try
		{
			$this->aSprintExistsForTheProject($sprintTitle, $projectTitle);
		} catch(Exception $e)
		{
			if (!str_contains($e->getMessage(), 'Project name is already used')) throw $e;
		}

		Sprint::where('title', $sprintTitle)->where('project_id', $project->id)->delete();
	}

	/**
	 * @Given I copied the :project :sprint Phabricator ID
	 */
	public function iCopiedThePhabricatorId($project, $sprint)
	{
		$this->phabricatorProjectID = Sprint::where(
			'title', $sprint
		)->where(
			'project_id', Project::where('title', $project)->first()->id
		)->first()->phabricator_id;
	}

	/**
	 * @When I paste the copied Phabricator ID
	 */
	public function iPasteTheCopiedPhabricatorId()
	{
		$this->fillField('title', $this->phabricatorProjectID);
	}

	/**
	 * @Given I know the number of snapshots
	 */
	public function iKnowTheNumberOfSnapshots()
	{
		$this->numberOfSnapshots = SprintSnapshot::count();
	}

	/**
	 * @When I execute artisan :command
	 */
	public function iExecuteArtisan($command)
	{
		Artisan::call($command);
	}

	/**
	 * @Then I should have created one snapshot for each sprint
	 */
	public function iShouldHaveCreatedOneSnapshotForEachSprint()
	{
		$numberOfActiveSprints = count(array_filter(Sprint::all()->all(), function($sprint)
			{
				return $sprint->isActive();
			}
		));

		PHPUnit::assertSame($this->numberOfSnapshots + $numberOfActiveSprints, SprintSnapshot::count());

		SprintSnapshot::take($numberOfActiveSprints)->delete(); // cleaning up
	}

	/**
	 * @When I go to the :sprint export page
	 */
	public function iGoToTheExportPage($sprint)
	{
		$this->visit('/sprints/' . Sprint::where('title', $sprint)->first()->phabricator_id . '/export.json');
	}

	private function responseJSON()
	{
		return json_decode($this->getSession()->getPage()->getContent());
	}

	/**
	 * @Then I should get a valid JSON response
	 */
	public function iShouldGetAValidJsonResponse()
	{
		PHPUnit::assertNotNull($this->responseJSON());
	}

	/**
	 * @Then there should be a :key property in the response
	 */
	public function thereShouldBeAPropertyInTheResponse($key)
	{
		PHPUnit::assertObjectHasAttribute($key, $this->responseJSON());
	}

	/**
	 * @Then the :key property should contain :number elements
	 */
	public function thePropertyShouldContainElements($key, $number)
	{
		PHPUnit::assertCount(intval($number), $this->responseJSON()->$key);
	}

	/**
	 * @Then the sprint :sprint should not exist
	 */
	public function theSprintShouldNotExist($sprint)
	{
		PHPUnit::assertNull(Sprint::where('title', $sprint)->first());
	}

	/**
	 * @Given :sprint contains a task
	 */
	public function containsATask($sprintTitle)
	{
		$phid = Sprint::where('title', $sprintTitle)->first()->phid;
		$this->selectedTask = $this->getOrCreateTaskForSprint($phid);
	}

	private function getOrCreateTaskForSprint($sprintPHID)
	{
		$phabricator = App::make('phabricator');
		$tasks = $phabricator->queryTasksByProject($sprintPHID);

		if ($tasks) return array_values($tasks)[0];

		return $phabricator->createTask($sprintPHID, [
			'title' => 'automated test task',
			'priority' => 'high',
			'points' => 1,
		]);
	}

	/**
	 * @When I am assigned to this task
	 */
	public function iAmAssignedToThisTask()
	{
		App::make('phabricator')->updateTask(
			$this->selectedTask['id'],
			['ownerPHID' => User::where('username', $this->params['phabricator_username'])->first()->phid]
		);
	}

	/**
	 * @Then I should see my name in the task's row of the sprint backlog
	 */
	public function iShouldSeeMyNameInTheTaskSRowOfTheSprintBacklog()
	{
		$this->assertElementContains('#t' . $this->selectedTask['id'], $this->params['phabricator_username']);
	}
}
