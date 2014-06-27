<?php
/**
 * This file is part of visual-irc-log.
 * Copyright (c) 2014 Václav Vrbka (http://aurielle.cz)
 */

namespace Aurielle\VisualLog\Log;

use Nette;
use Nette\Utils\Strings;


class Log extends Nette\Object implements \Countable, \IteratorAggregate, \ArrayAccess
{
	/** @var string */
	private $channel;

	/** @var \DateTime */
	private $date;

	/** @var string */
	private $rawLog;

	/** @var array */
	private $lines = array();

	/** @var array */
	private $stats = array(
		'action' => 0,
		'join' => 0,
		'kick' => 0,
		'message' => 0,
		'mode' => 0,
		'nick' => 0,
		'notice' => 0,
		'part' => 0,
		'quit' => 0,
		'topic' => 0,
	);

	/** @var array */
	private $words = array();

	/** @var array */
	private $times = array();

	/** @var array */
	private $users = array();

	/** @var array */
	private $urls = array();

	/** @var array */
	private $wordAverage = array();

	/** @var int */
	private $totalLength = 0;

	/** @var array */
	public static $mapping = array(
		'action' => 'Actions',
		'join' => 'Joins',
		'kick' => 'Kicks',
		'message' => 'Messages',
		'mode' => 'Mode changes',
		'nick' => 'Nick changes',
		'notice' => 'Notices',
		'part' => 'Parts',
		'quit' => 'Quits',
		'topic' => 'Topic changes',
	);


	public function __construct($channel, \DateTime $date, $rawLog = NULL)
	{
		$this->channel = $channel;
		$this->date = $date;
		$this->rawLog = $rawLog;

		// Initialize times array, indexes 0-23
		// Now Python syntax would be useful :'(
		// times = {i:0 for i in range(24)}
		$this->times = array_fill(0, 24, 0);
	}


	/**
	 * Adds a line to the log
	 * @param Line $line
	 * @return Log Provides a fluent interface.
	 */
	public function addLine(Line $line)
	{
		$this->lines[] = $line;
		$this->addToStats($line);

		return $this;
	}


	/**
	 * Performs word frequency analysis for statistical purposes
	 * @param MessageLine $line
	 */
	private function freqAnalysis(MessageLine $line)
	{
		foreach (explode(' ', $line->message) as $word) {
			$word = Strings::lower(Strings::trim(Strings::toAscii($word), " \t\n\r\0\x0B\xC2\xA0\"'?!.,@#$%^&*()[]{}-"));
			$word = rtrim($word, ':');  // for smilies
			if (!$word) {
				continue;
			}

			if (!isset($this->words[$word])) {
				$this->words[$word] = 0;
			}

			$this->words[$word] += 1;
		}
	}


	/**
	 * Grabs URLs from messages
	 * @param MessageLine $line
	 */
	private function urlGrab(MessageLine $line)
	{
		foreach ($this->matchLinks($line->message) as $link) {
			$url = $link[1];
			if (!isset($this->urls[$url])) {
				$this->urls[$url] = 0;
			}

			$this->urls[$url] += 1;
		}
	}


	/**
	 * Matches all links in given string, borrowed from Aki
	 * @param string $data
	 * @return array
	 */
	private function matchLinks($data)
	{
		// From: http://daringfireball.net/2010/07/improved_regex_for_matching_urls
		$regex = '~
			(?xi)
			\b
			(                       # Capture 1: entire matched URL
			  (?:
			    https?://               # http or https protocol
			    |                       #   or
			    www\d{0,3}[.]           # "www.", "www1.", "www2." … "www999."
			    |                           #   or
			    [a-z0-9.\-]+[.][a-z]{2,4}/  # looks like domain name followed by a slash
			  )
			  (                       # One or more: (?: removed)
			    [^\s()<>]+                  # Run of non-space, non-()<>
			    |                           #   or
			    \(([^\s()<>]+|(\([^\s()<>]+\)))*\)  # balanced parens, up to 2 levels
			  )+
			  (?:                       # End with:
			    \(([^\s()<>]+|(\([^\s()<>]+\)))*\)  # balanced parens, up to 2 levels
			    |                               #   or
			    [^\s`!()\[\]{};:\'".,<>?«»“”‘’]        # not a space or one of these punct chars
			  )
			)~ix';
		return Strings::matchAll($data, $regex);
	}


	/**
	 * Adds a line to general statistics
	 * Should be called only once for each line
	 * @param Line $line
	 */
	private function addToStats(Line $line)
	{
		// Line type
		$type = strtolower(substr(strrchr(get_class($line), '\\'), 1, -4));
		$this->stats[$type] += 1;

		// Time
		$this->times[$line->timestamp->format('G')] += 1;

		// Users
		if (!isset($this->users[$line->nick])) {
			$this->users[$line->nick] = 0;
		}

		$this->users[$line->nick] += 1;

		// Word frequency
		if ($line instanceof MessageLine) {
			$this->freqAnalysis($line);
			$this->urlGrab($line);

			$this->wordAverage[] = count(explode(' ', $line->message));
			$this->totalLength += Strings::length($line->message);
		}
	}


	/**
	 * Returns all lines in the log ordered by time they occurred (dependant on adding in the right order)
	 * @return array
	 */
	public function getLines()
	{
		return $this->lines;
	}


	/**
	 * @return array
	 */
	public function getStats()
	{
		return $this->stats;
	}


	/**
	 * @return array
	 */
	public function getWords()
	{
		$words = $this->words;
		arsort($words);
		return $words;
	}


	/**
	 * @return array
	 */
	public function getTimes()
	{
		return $this->times;
	}


	/**
	 * @return array
	 */
	public function getUsers()
	{
		return $this->users;
	}


	/**
	 * @return array
	 */
	public function getUrls()
	{
		return $this->urls;
	}


	/**
	 * @return array
	 */
	public function getAverageWords()
	{
		return array_sum($this->wordAverage) / count($this->wordAverage);
	}


	/**
	 * @return int
	 */
	public function getTotalMessages()
	{
		return $this->stats['message'] + $this->stats['action'] + $this->stats['notice'];
	}

	/**
	 * @return int
	 */
	public function getTotalLength()
	{
		return $this->totalLength;
	}


	/**
	 * @return string
	 */
	public function getChannel()
	{
		return $this->channel;
	}


	/**
	 * @return \DateTime
	 */
	public function getDate()
	{
		return $this->date;
	}


	/**
	 * @param string $rawLog
	 * @return Log provides a fluent interface
	 */
	public function setRawLog($rawLog)
	{
		$this->rawLog = $rawLog;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getRawLog()
	{
		return $this->rawLog;
	}


	/**
	 * Line count for \Countable
	 */
	public function count()
	{
		return count($this->lines);
	}

	/**
	 * Iterator for \IteratorAggregate
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->lines);
	}

	/**
	 * \ArrayAccess
	 */
	public function offsetExists($offset)
	{
		return isset($this->lines[$offset]);
	}

	/**
	 * \ArrayAccess
	 */
	public function offsetGet($offset)
	{
		return $this->lines[$offset];
	}

	/**
	 * \ArrayAccess
	 */
	public function offsetSet($offset, $value)
	{
		if ($offset !== NULL) {
			throw new Nette\NotSupportedException('Direct line modification not allowed.');
		}

		$this->addLine($value);
	}

	/**
	 * \ArrayAccess
	 */
	public function offsetUnset($offset)
	{
		throw new Nette\NotSupportedException('Deleting log lines not allowed.');
	}
}