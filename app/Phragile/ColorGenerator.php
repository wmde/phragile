<?php
namespace Phragile;

/**
 * Generates different shades of a color for the pie chart
 */
class ColorGenerator {
	private $MAX_COLOR_INTERVAL = 5;
	private $MAX_COLOR_DIFFERENCE = 30;

	/**
	 * @param int $n - Number of colors
	 * @param array $base - Base color in [r, g, b] notation
	 * @return string
	 */
	public function generate($n, array $base)
	{
		$colors = [];
		$steps = $this->getColorSteps($n);

		for ($i = 0; $i < $n; $i++)
		{
			$colors[] = $this->colorToString(
				$this->adjustBrightness($base, $steps[$i])
			);
		}

		return $colors;
	}

	private function adjustBrightness($base, $factor)
	{
		return array_map(function($colorValue) use($factor)
		{
			return round(max(0, min($colorValue * $factor / 100, 255)));
		}, $base);
	}

	private function getColorSteps($number)
	{
		if ($number <= 0)
		{
			return [];
		}

		$interval = min($this->MAX_COLOR_INTERVAL, round($this->MAX_COLOR_DIFFERENCE / $number));
		$steps = [];
		for ($i = 0; $i < $number; $i++)
		{
			$steps[] = 100 + ($i * $interval) - ($interval * (($number - 1) / 2));
		}

		return $steps;
	}

	private function colorToString(array $color)
	{
		return 'rgb(' . implode(',', $color) . ')';
	}
}
