<?php
/**
 * This file is part of visual-irc-log.
 * Copyright (c) 2014 Václav Vrbka (http://aurielle.cz)
 */

namespace Aurielle\VisualLog\Log;


class NickLine extends Line
{
	/** @var string */
	private $newNick;


	public function __construct(\DateTime $timestamp, $nick, $newNick)
	{
		parent::__construct($timestamp, $nick);
		$this->newNick = $newNick;
	}

	/**
	 * @return string
	 */
	public function getNewNick()
	{
		return $this->newNick;
	}
}