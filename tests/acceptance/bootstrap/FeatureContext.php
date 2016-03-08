<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use PHPUnit_Framework_Assert as PHPUnit;
use Phragile\StatusByStatusFieldDispatcher;
use Phragile\TaskDataFetcher;
use Phragile\TaskDataProcessor;
use Symfony\Component\Console\Input\StringInput;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context, SnippetAcceptingContext
{
	private $params;
	private $phabricatorProjectID;
	private $selectedTask;
	private $numberOfSnapshots;
	private $numberOfSprints;
	private $numberOfProjects;
	private $testSnapshot;
	private $testSnapshotTitle;

	public function __construct(array $params)
	{
		$this->params = $params;
	}

	private function setUserAuthToken()
	{
		User::where('username', $this->params['phabricator_username'])
			->update(['conduit_api_token' => $this->params['conduit_api_token']]);
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
		try
		{
			$this->pressButton('Authorize Access');
		} catch(Behat\Mink\Exception\ElementNotFoundException $e)
		{
			// Absence of this button just means that the user has authorized Phragile before.
			return;
		} finally
		{
			$this->clickLink('Continue');
			$this->setUserAuthToken();
		}
		return;
	}

	/**
	 * @Then /^I should (?:|still )be logged in$/
	 */
	public function iShouldBeLoggedIn()
	{
		$this->assertPageContainsText('Logged in as ' . $this->params['phabricator_username']);
	}

	/**
	 * @When I submit a valid Conduit API Token
	 */
	public function iSubmitAValidConduitAPIToken()
	{
		$this->submitConduitAPIToken($this->params['conduit_api_token']);
	}

	/**
	 * @When I submit an invalid Conduit API Token
	 */
	public function iSubmitAnInvalidConduitAPIToken()
	{
		$this->submitConduitAPIToken('INVALID_API_TOKEN');
	}

	private function submitConduitAPIToken($token)
	{
		$this->fillField('conduit_api_token', $token);
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
	 * @Given I have not added my Conduit API Token
	 */
	public function iHaveNotAddedMyConduitAPIToken()
	{
		User::where(['username' => $this->params['phabricator_username']])
			->update(['conduit_api_token' => '']);
	}

	/**
	 * @Then I should see my Conduit API Token in an input field
	 */
	public function iShouldSeeMyConduitAPITokenInAnInputField()
	{
		$this->assertFieldContains('conduit_api_token', $this->params['conduit_api_token']);
	}

	/**
	 * @Then I should see :title in the Phabricator project list
	 */
	public function iShouldSeeInThePhabricatorProjectList($title)
	{
		if (!App::make('phabricator')->queryProjectByTitle($title))
		{
			throw new Exception('Project "' . $title . '" does not exist.');
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
		return Project::firstOrCreate([
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

	private function getOrCreatePhabricatorProjectFromTitle($title)
	{
		$phabricator = App::make('phabricator');
		return $phabricator->queryProjectByTitle($title) ?: $phabricator->createProject($title, []);
	}

	/**
	 * @Given a sprint :sprint exists for the :project project
	 */
	public function aSprintExistsForTheProject($sprintTitle, $projectTitle)
	{
		$project = $this->theProjectExists($projectTitle);
		$phabricatorProject = $this->getOrCreatePhabricatorProjectFromTitle($sprintTitle);
		$existingSprint = Sprint::where('phid', $phabricatorProject['phid'])->first();

		if ($existingSprint !== null && !$existingSprint->delete())
		{
			throw new Exception('Could not delete the existing sprint.');
		}

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
		$this->phabricatorProjectID = $existingSprint ? $existingSprint->phabricator_id : $newSprint->phabricator_id;
	}

	/**
	 * @Given :sprint has a task with :title :priority and :points
	 */
	public function sprintHasATaskWith($sprint, $title, $priority, $points)
	{
		$sprintPHID = Sprint::where('title', $sprint)->first()->phid;
		$phabricator = App::make('phabricator');

		$this->selectedTask = $phabricator->createTask($sprintPHID, [
			'title' => $title,
			'priority' => $priority,
			'points' => $points
		]);
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
	 * @Then :projectTitle should be shown as a project name
	 */
	public function shouldBeShownAsAProjectName($projectTitle)
	{
		$this->assertElementContains('h1.sprint-overview-title', $projectTitle);
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
		if (!User::where('username', $this->params['phabricator_username'])->first()->isInAdminList(env('PHRAGILE_ADMINS')))
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
		$this->visit('/projects/' . Project::where('title', $project)->first()->slug);
	}

	/**
	 * @Given a sprint :sprintTitle exists for the :projectTitle project in Phabricator but not in Phragile
	 */
	public function aSprintExistsForTheProjectInPhabricatorButNotInPhragile($sprintTitle, $projectTitle)
	{
		$this->getOrCreatePhabricatorProjectFromTitle($sprintTitle);
		$project = Project::where('title', $projectTitle)->first();
		try
		{
			$this->aSprintExistsForTheProject($sprintTitle, $projectTitle);
		} catch(Exception $e)
		{
			if (!str_contains($e->getMessage(), 'Project name is already used'))
			{
				throw $e;
			}
		}

		Sprint::where('title', $sprintTitle)->where('project_id', $project->id)->delete();
	}

	/**
	 * @Given I copied the :sprint Phabricator ID from Phabricator
	 */
	public function iCopiedThePhabricatorIdFromPhabricator($sprint)
	{
		$phabricatorProject = $this->getOrCreatePhabricatorProjectFromTitle($sprint);
		$this->phabricatorProjectID = $phabricatorProject['id'];
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
		Artisan::handle(new StringInput($command));
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

	/**
	 * @When I go to the latest snapshot export page of :sprint
	 */
	public function iGoToTheLatestSnapshotExportPage($sprint)
	{
		$this->visit('/snapshots/' . Sprint::where('title', $sprint)->first()->sprintSnapshots->first()->id . '/export.json');
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
		$tasks = (new TaskDataFetcher($phabricator))->fetchProjectTasks($sprintPHID);

		if (!empty($tasks))
		{
			return array_values($tasks)[0];
		}

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

	/**
	 * @Then I should see the task with :title as title
	 */
	public function iShouldSeeTheTaskWithTitle($title)
	{
		$this->assertElementContains('#t' . $this->selectedTask['id'] . ' .title', $title);
	}

	/**
	 * @Then I should see the task with :priority as priority
	 */
	public function iShouldSeeTheTaskWithPriority($priority)
	{
		$this->assertElementContains('#t' . $this->selectedTask['id'] . ' .priority', $priority);
	}

	/**
	 * @Then I should see the task with :points story points
	 */
	public function iShouldSeeTheTaskWithStoryPoints($storyPoints)
	{
		$this->assertElementContains('#t' . $this->selectedTask['id'] . ' .points', $storyPoints);
	}

	/**
	 * @When the selected task is removed from all projects
	 */
	public function theSelectedTaskIsRemovedFromAllProjects()
	{
		App::make('phabricator')->updateTask($this->selectedTask['id'], ['projectPHIDs' => []]);
	}

	/**
	 * @Then I should not see the selected task
	 */
	public function iShouldNotSeeTheSelectedTask()
	{
		$this->assertPageNotContainsText('#' . $this->selectedTask['id'] . ' ');
	}

	/**
	 * @Then I should see the selected in the latest :sprint snapshot
	 */
	public function iShouldSeeTheSelectedInTheLatestSnapshot($sprint)
	{
		$this->iGoToTheLatestSnapshotPageOf($sprint);
		$this->assertPageContainsText('#' . $this->selectedTask['id'] . ' ');
	}

	/**
	 * @When I go to the sprint overview of the missing sprint
	 */
	public function iGoToTheSprintOverviewOfTheMissingSprint()
	{
		$this->visit('/sprints/' . $this->phabricatorProjectID);
	}

	/**
	 * @Then I should see the current number of projects
	 */
	public function iShouldSeeTheCurrentNumberOfProjects()
	{
		$this->assertPageContainsText('Projects ' . Project::count());
	}

	/**
	 * @Then I should see the current number of sprints
	 */
	public function iShouldSeeTheCurrentNumberOfSprints()
	{
		$this->assertPageContainsText('Sprints ' . Sprint::count());
	}

	/**
	 * @When I remember the current number of projects and sprints
	 */
	public function iRememberTheCurrentNumberOfProjectsAndSprints()
	{
		$this->numberOfProjects = Project::count();
		$this->numberOfSprints = Sprint::count();
	}

	/**
	 * @Then I should see the remembered number of projects plus :number
	 */
	public function iShouldSeeTheRememberedNumberOfProjectsPlus($number)
	{
		$this->assertPageContainsText('Projects ' . ($this->numberOfProjects + $number));
	}

	/**
	 * @Then I should see the remembered number of sprints plus :number
	 */
	public function iShouldSeeTheRememberedNumberOfSprintsPlus($number)
	{
		$this->assertPageContainsText('Sprints ' . ($this->numberOfSprints + $number));
	}

	/**
	 * @Given there is a snapshot in the maniphest.query format
	 */
	public function thereIsASnapshotInTheManiphestQueryFormat()
	{
		$this->testSnapshotTitle = '[Phragile] Migration script for old snapshots';
		$this->testSnapshot = new SprintSnapshot();
		$this->testSnapshot->data = $this->getManiphestQuerySnapshotData();
		$this->testSnapshot->save();
	}

	/**
	 * @Then the snapshot should be in the maniphest.search format
	 */
	public function theSnapshotShouldBeInTheManiphestSearchFormat()
	{
		$snapshotTaskTitle = '[Phragile] Migration script for old snapshots';
		$taskProcessor = new TaskDataProcessor(
			new StatusByStatusFieldDispatcher(''), ['ignore_estimates' => false, 'ignored_columns' => []]
		);
		$tasks = $taskProcessor->process(json_decode($this->testSnapshot->fresh()->getData(), true)['tasks']);
		PHPUnit::assertSame($snapshotTaskTitle, $tasks[0]->getTitle());
		PHPUnit::assertSame(12, $tasks[0]->getPoints());
	}

	/**
	 * @Then the snapshot should still be in the maniphest.query format
	 */
	public function theSnapshotShouldStillBeInTheManiphestQueryFormat()
	{
		$snapshotTaskTitle = '[Phragile] Migration script for old snapshots';
		$snapshot = json_decode($this->testSnapshot->fresh()->getData(), true);
		$task = array_shift($snapshot['tasks']);
		PHPUnit::assertSame($snapshotTaskTitle, $task['title']);
		PHPUnit::assertSame(12, $task['auxiliary'][env('MANIPHEST_STORY_POINTS_FIELD')]);
	}

	private function getManiphestQuerySnapshotData()
	{
		return '{
		"transactions":[],
		"tasks":{
			"PHID-123123":{
				"id":"127180",
				"phid":"PHID-TASK-4kvxc4re6xrshgxtfajl",
				"authorPHID":"PHID-USER-t4sxxglz6yyrgxeib43i",
				"ownerPHID":"PHID-USER-5dv7dcltvyvolwzbm2af",
				"ccPHIDs":["PHID-USER-5dv7dcltvyvolwzbm2af","PHID-USER-fn7qnpccfbitivgtw2rt","PHID-USER-lltif2drabccdkwhet7x"],
				"status":"open",
				"statusName":"Open",
				"isClosed":false,
				"priority":"High",
				"priorityColor":"red",
				"title":"' . $this->testSnapshotTitle . '",
				"description":"Snapshot data needs to be migrated to a new format since we are going to '
		. 'abandon maniphest.query in favor of maniphest.search.",
				"projectPHIDs":["PHID-PROJ-ptnfbfyq36kkebaxugcz","PHID-PROJ-tazsyaydzpbd643tderv","PHID-PROJ-knyj2bgnrkrwu72n27bg"],
				"uri":"https:\/\/phabricator.wikimedia.org\/T127180",
				"auxiliary":{
					"std:maniphest:security_topic":"default",
					"' . env('MANIPHEST_STORY_POINTS_FIELD') . '":12
				},
				"objectName":"T127180",
				"dateCreated":"1455716487",
				"dateModified":"1455880296",
				"dependsOnTaskPHIDs":["PHID-TASK-tuaxg2zcafwmpoe2d5ys"]
			}
		}}';
	}
}
