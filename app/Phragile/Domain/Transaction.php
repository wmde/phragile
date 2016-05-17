<?php

namespace Phragile\Domain;

abstract class Transaction {

	const TYPE = 'undefined';

	protected $timestamp;

	public function getTimestamp()
	{
		return $this->timestamp;
	}

	abstract public function getData();

}
