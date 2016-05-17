<?php

namespace Phragile\Tests;

use Phragile\StatusByStatusFieldDispatcher;

class StatusByStatusFieldDispatcherTest extends TestCase {
	public function taskWithCorrectStatusProvider()
	{
		return [
			[
				[
					'fields' => [
						'status' => ['value' => 'open'],
						'ownerPHID' => null,
					],
					'attachments' => ['projects' => ['projectPHIDs' => []]]
				],
				'open',
				false
			],
			[
				[
					'fields' => [
						'status' => ['value' => 'resolved'],
						'ownerPHID' => null,
					],
					'attachments' => ['projects' => ['projectPHIDs' => []]]
				],
				'resolved',
				true
			],
			[
				[
					'fields' => [
						'status' => ['value' => 'resolved'],
						'ownerPHID' => null,
					],
					'attachments' => ['projects' => ['projectPHIDs' => [$this->reviewTagPHID]]]
				],
				'resolved',
				true
			],
			[
				[
					'fields' => [
						'status' => ['value' => 'open'],
						'ownerPHID' => null,
					],
					'attachments' => ['projects' => ['projectPHIDs' => [$this->reviewTagPHID]]]
				],
				'patch to review',
				false
			],
		];
	}

	private $reviewTagPHID = 'PHID-PROJ-1337';

	/**
	 * @dataProvider taskWithCorrectStatusProvider
	 */
	public function testReturnsCorrectStatus($rawTask, $status, $isClosed)
	{
		$statusDispatcher = new StatusByStatusFieldDispatcher($this->reviewTagPHID);

		$this->assertSame($status, $statusDispatcher->getStatus($rawTask));
		$this->assertSame($isClosed, $statusDispatcher->isClosed($rawTask));
	}
}
