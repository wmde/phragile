<?php

use Phragile\AssigneeRepository;
use Phragile\PhabricatorAPI;
use Phragile\Task;

/**
 * @covers Phragile\AssigneeRepository
 */
class AssigneeRepositoryTest extends PHPUnit_Framework_TestCase {

	private $users = [
		'PHID-USER-abc666' => 'Dummy User',
	];

	private function newPhabricatorAPI()
	{
		$phabricatorAPI = $this->getMockBuilder(PhabricatorAPI::class)
			->disableOriginalConstructor()
			->getMock();
		$phabricatorAPI->method('getUserData')->will($this->returnCallback(function()
		{
			return array_map(function($phid)
			{
				return ['phid' => $phid, 'userName' => $this->users[$phid]];
			}, array_keys($this->users));
		}));
		return $phabricatorAPI;
	}

	private function newAssigneeRepository()
	{
		$tasks = [
			new Task([
				'id' => '123',
				'title' => 'Simple Task',
				'priority' => 'Normal',
				'points' => 1,
				'status' => 'Doing',
				'closed' => false,
				'assigneePHID' => 'PHID-USER-abc666'
			]),
		];
		return new AssigneeRepository($this->newPhabricatorAPI(), $tasks);
	}

	public function testGivenPhidOfNotExistingUser_getNameReturnsNull()
	{
		$repository = $this->newAssigneeRepository();
		$this->assertNull($repository->getName('PHID-USER-no-such-user'));
	}

	public function testGivenPhidOfExistingUser_getNameReturnsName()
	{
		$repository = $this->newAssigneeRepository();
		$this->assertEquals('Dummy User', $repository->getName('PHID-USER-abc666'));
	}

}
