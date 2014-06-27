<?php
/**
 * This file is part of visual-irc-log.
 * Copyright (c) 2014 Václav Vrbka (http://aurielle.cz)
 */

namespace Aurielle\VisualLog\Log;


class MessageLine extends Line
{
	/** @var string */
	private $message;


	public function __construct(\DateTime $timestamp, $nick, $message)
	{
		parent::__construct($timestamp, $nick);
		$this->message = $message;
	}

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}
}