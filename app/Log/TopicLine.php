<?php
/**
 * This file is part of visual-irc-log.
 * Copyright (c) 2014 Václav Vrbka (http://aurielle.cz)
 */

namespace Aurielle\VisualLog\Log;


class TopicLine extends Line
{
	/** @var string */
	private $topic;


	public function __construct(\DateTime $timestamp, $nick, $topic)
	{
		parent::__construct($timestamp, $nick);
		$this->topic = $topic;
	}

	/**
	 * @return string
	 */
	public function getTopic()
	{
		return $this->topic;
	}
}