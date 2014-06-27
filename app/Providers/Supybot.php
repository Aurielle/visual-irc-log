<?php
/**
 * This file is part of visual-irc-log.
 * Copyright (c) 2014 Václav Vrbka (http://aurielle.cz)
 */

namespace Aurielle\VisualLog\Providers;

use Aurielle\VisualLog;
use Nette;
use Nette\Utils\Strings;


/**
 * Parses Supybot's logs
 */
class Supybot extends Nette\Object implements IProvider
{
	/** @var string */
	private $filename;

	/** @var VisualLog\Log\Log */
	private $log;


	public function __construct($filename)
	{
		if (!file_exists($filename)) {
			throw new Nette\InvalidArgumentException("File '$filename' doesn't exist.");
		}

		$this->filename = $filename;
	}


	/**
	 * Returns parsed log as object
	 * @return VisualLog\Log\Log
	 */
	public function getParsedLog()
	{
		if (!$this->log) {
			$this->parseLog();
		}

		return $this->log;
	}


	/**
	 * Loads and parses log into VisualLog\Log\Log object.
	 */
	private function parseLog()
	{
		if (!($matches = Strings::match($this->filename, '&(#[A-Za-z0-9-_.]+)\.(\d{4}\-\d{2}\-\d{2})\.log&'))) {
			throw new Nette\InvalidArgumentException("File '$this->filename' has invalid filename. Channel and date must be present.");
		}

		$file = Strings::normalizeNewLines(file_get_contents($this->filename));
		$log = new VisualLog\Log\Log($matches[1], new \DateTime($matches[2]), $file);

		foreach (explode("\n", $file) as $line) {
			if (!$line) {
				continue;
			}

			list($time, $data) = explode('  ', $line, 2);
			$time = new \DateTime($time);

			if (Strings::startsWith($data, '***')) {
				$match = Strings::match($data, '&\*\*\* ([^ ]+) (has quit|has joined|sets mode: ((?:\+|\-)(?:[a-z]+)) ?(.+)?|has left|was kicked by (.+) \((.+)\)|is now known as (.+)|changes topic to \"(.+)\")&');

				if (Strings::startsWith($match[2], 'has quit')) {
					$logLine = new VisualLog\Log\QuitLine($time, $match[1]);
				}
				else if (Strings::startsWith($match[2], 'has joined')) {
					$logLine = new VisualLog\Log\JoinLine($time, $match[1]);
				}
				else if (Strings::startsWith($match[2], 'sets mode')) {
					// @ is intentional
					$logLine = new VisualLog\Log\ModeLine($time, $match[1], $match[3], @$match[4]);
				}
				else if (Strings::startsWith($match[2], 'has left')) {
					$logLine = new VisualLog\Log\PartLine($time, $match[1]);
				}
				else if (Strings::startsWith($match[2], 'was kicked')) {
					// @ is intentional
					$logLine = new VisualLog\Log\KickLine($time, $match[5], $match[1], @$match[6]);
				}
				else if (Strings::startsWith($match[2], 'is now known')) {
					$logLine = new VisualLog\Log\NickLine($time, $match[1], $match[7]);
				}
				else if (Strings::startsWith($match[2], 'changes topic')) {
					$logLine = new VisualLog\Log\TopicLine($time, $match[1], $match[8]);
				}
			}
			else {
				list($nick, $message) = explode(' ', $data, 2);

				if (Strings::startsWith($data, '*')) {
					list($nick, $message) = explode(' ', substr($data, 2), 2);
					$logLine = new VisualLog\Log\ActionLine($time, $nick, $message);
				}
				else if (Strings::startsWith($data, '-')) {
					$logLine = new VisualLog\Log\NoticeLine($time, trim($nick, '-'), $message);
				}
				else {
					$logLine = new VisualLog\Log\MessageLine($time, trim($nick, '<>'), $message);
				}
			}

			$log->addLine($logLine);
		}

		$this->log = $log;
	}
} 