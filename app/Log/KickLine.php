<?php
/**
 * This file is part of visual-irc-log.
 * Copyright (c) 2014 Václav Vrbka (http://aurielle.cz)
 */

namespace Aurielle\VisualLog\Log;


class KickLine extends Line
{
	/** @var string */
	private $target;

	/** @var string */
	private $reason;


	public function __construct(\DateTime $timestamp, $nick, $target, $reason = NULL)
	{
		parent::__construct($timestamp, $nick);
		$this->target = $target;
		$this->reason = $reason;
	}

	/**
	 * @return string
	 */
	public function getReason()
	{
		return $this->reason;
	}

	/**
	 * @return string
	 */
	public function getTarget()
	{
		return $this->target;
	}
}