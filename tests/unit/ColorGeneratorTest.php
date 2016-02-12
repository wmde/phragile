<?php

use Phragile\ColorGenerator;

class ColorGeneratorTest extends TestCase {
	/**
	 * @dataProvider countAndColorProvider
	 */
	public function testReturnsNDifferentShades_GivenCountN($count, $baseColor, $expectedColors)
	{
		$generator = new ColorGenerator();
		$this->assertSame($generator->generate($count, $baseColor), $expectedColors);
	}

	public function testRetunsOnlyValidColors()
	{
		$generator = new ColorGenerator();
		foreach ($generator->generate(100, [254, 0, -1]) as $color)
		{
			foreach (explode(',', $color) as $colorCode)
			{
				$code = intval($colorCode);
				$this->assertTrue($code <= 255 && $code >= 0);
			}
		}
	}

	public function countAndColorProvider()
	{
		return [
			[
				1,
				[100, 100, 100],
				['rgb(100,100,100)']
			],
			[
				3,
				[100, 100, 100],
				['rgb(90,90,90)','rgb(100,100,100)','rgb(110,110,110)']
			]
		];
	}
}
