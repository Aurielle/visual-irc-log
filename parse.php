<?php
/**
 * This file is part of visual-irc-log.
 * Copyright (c) 2014 Václav Vrbka (http://aurielle.cz)
 */

require __DIR__ . '/vendor/autoload.php';
Nette\Diagnostics\Debugger::enable(FALSE, __DIR__ . '/log');
Nette\Diagnostics\Debugger::$strictMode = TRUE;

$allowedProviders = array(
	'supybot' => 'Aurielle\VisualLog\Providers\Supybot',
);

// Won't run from the browser
if (PHP_SAPI !== 'cli') {
	echo('<h1>Please run this from command line.</h1>');
	exit;
}

// We need 2 arguments
if ($argc !== 3) {
	echo 'Usage: php parse.php <provider> <filename>' . PHP_EOL;
	exit;
}

if (!isset($allowedProviders[$argv[1]])) {
	echo 'Unsupported log file parser: ' . $argv[1] . PHP_EOL;
	echo 'Please choose from these: ' . implode(', ', array_keys($allowedProviders)) . PHP_EOL;
	exit;
}

// Let the selected provider parse the log file
/** @var Aurielle\VisualLog\Providers\IProvider $provider */
$provider = new $allowedProviders[$argv[1]]($argv[2]);
$log = $provider->getParsedLog();

// And render the template
$template = new Nette\Templating\FileTemplate(__DIR__ . '/template.latte');
$template->registerFilter(new Nette\Latte\Engine());
$template->registerHelperLoader('Nette\Templating\Helpers::loader');
$template->registerHelper('timeOfDay', function($hour) {
	$morning = range(5, 11);
	$noon = range(12, 13);
	$afternoon = range(14, 17);
	$evening = range(18, 22);
	$night = array(23) + range(0, 4);

	if (in_array($hour, $morning)) {
		$timeOfDay = 'in the morning';
	} else if (in_array($hour, $noon)) {
		$timeOfDay = 'at noon';
	} else if (in_array($hour, $afternoon)) {
		$timeOfDay = 'in the afternoon';
	} else if (in_array($hour, $evening)) {
		$timeOfDay = 'in the evening';
	} else if (in_array($hour, $night)) {
		$timeOfDay = 'at night';
	} else {
		$timeOfDay = "at $hour hours";
	}

	return $timeOfDay;
});

$template->registerHelper('forPieChart', function(array $data, $useMapping = FALSE) {
	$actionMapping = Aurielle\VisualLog\Log\Log::$mapping;
	$r = array();
	foreach ($data as $k => $v) {
		$r[] = array($useMapping ? $actionMapping[$k] : $k, $v);
	}

	return $r;
});

$template->registerHelper('arsort', function(array $data, $slice = 0) {
	arsort($data);
	if ($slice) {
		$data = array_slice($data, 0, $slice, TRUE);
	}

	return $data;
});


$template->log = $log;
$template->maxHour = array_search(max($log->times), $log->times);
$template->save(__DIR__ . '/out/' . $log->getChannel() . '.' . $log->getDate()->format('Y-m-d') . '.html');
echo 'Finished~!' . PHP_EOL;