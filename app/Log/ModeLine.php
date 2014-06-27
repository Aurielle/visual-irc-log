<?php
/**
 * This file is part of visual-irc-log.
 * Copyright (c) 2014 Václav Vrbka (http://aurielle.cz)
 */

namespace Aurielle\VisualLog\Log;


class ModeLine extends Line
{
	/** @var string */
	private $mode;

	/** @var string */
	private $target;


	public function __construct(\DateTime $timestamp, $nick, $mode, $target = NULL)
	{
		parent::__construct($timestamp, $nick);
		$this->mode = $mode;
		$this->target = $target;
	}

	/**
	 * @return string
	 */
	public function getMode()
	{
		return $this->mode;
	}

	/**
	 * @return string
	 */
	public function getTarget()
	{
		return $this->target;
	}
}