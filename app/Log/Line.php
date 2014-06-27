<?php
/**
 * This file is part of visual-irc-log.
 * Copyright (c) 2014 Václav Vrbka (http://aurielle.cz)
 */

namespace Aurielle\VisualLog\Log;

use Nette;


abstract class Line extends Nette\Object
{
	/** @var \DateTime */
	private $timestamp;

	/** @var string */
	private $nick;


	public function __construct(\DateTime $timestamp, $nick)
	{
		$this->timestamp = $timestamp;
		$this->nick = $nick;
	}


	/**
	 * @return \DateTime
	 */
	public function getTimestamp()
	{
		return $this->timestamp;
	}

	/**
	 * @return string
	 */
	public function getNick()
	{
		return $this->nick;
	}
}