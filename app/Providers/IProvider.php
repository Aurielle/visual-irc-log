<?php
/**
 * This file is part of visual-irc-log.
 * Copyright (c) 2014 Václav Vrbka (http://aurielle.cz)
 */

namespace Aurielle\VisualLog\Providers;


interface IProvider
{
	/**
	 * @return \Aurielle\VisualLog\Log\Log
	 */
	function getParsedLog();
} 