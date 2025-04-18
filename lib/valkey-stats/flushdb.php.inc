<?php

/* MAH MODIFICATION START
   REASON: Valkey Stats must not use any custom session handling. */
require(__DIR__ . '/../../../../../config.php');
if (!isset($_GET['id']) || isset($_GET['id']) && $_GET['id'] != sesskey())
{
    die("Invalid request.");
}
/* MAH MODIFICATION END */
/* ORIGINAL START
session_start();

if (!isset($_GET['id']) || isset($_GET['id']) && $_GET['id'] != $_SESSION['id'])
{
	$_SESSION['id'] = '';
	die("Invalid request.");
}
   ORIGINAL END */

if (file_exists(dirname(__FILE__)."/config.php"))
{
    require_once dirname(__FILE__).'/config.php';
}
if (!$servers)
{
	die("No servers in config found.");
}

// Default debug setting
if (!defined('DEBUG'))
{
	define("DEBUG", false);
}

// Process GET request
$server = 0;
if (isset($_GET['s']) && intval($_GET['s']) < count($servers))
{
	$server = intval($_GET['s']);
}
$serverName = $servers[$server][0];
if (isset($_GET['db']))
{
	$db = intval($_GET['db']);
}
if (isset($_GET['async']))
{
	$async = intval($_GET['async']);
}

// Command mapping
$FLUSHDB = 'FLUSHDB';
if (isset($command[$serverName]['FLUSHDB']) && !is_null($command[$serverName]['FLUSHDB']) && !empty($command[$serverName]['FLUSHDB']))
{
	$FLUSHDB = $command[$serverName]['FLUSHDB'];
}
$FLUSHALL = 'FLUSHALL';
if (isset($command[$serverName]['FLUSHALL']) && !is_null($command[$serverName]['FLUSHALL']) && !empty($command[$serverName]['FLUSHALL']))
{
	$FLUSHALL = $command[$serverName]['FLUSHALL'];
}
$AUTH = 'AUTH';
if (isset($command[$serverName]['AUTH']) && !is_null($command[$serverName]['AUTH']) && !empty($command[$serverName]['AUTH']))
{
	$AUTH = $command[$serverName]['AUTH'];
}

// Functions
function debug($var, $pre = true)
{
	if (DEBUG)
	{
		if ($pre) echo "<pre>".PHP_EOL;
		var_dump($var);
		if ($pre) echo "</pre>".PHP_EOL;
	}
}

// Talk to server
$error = null;

$fp = @fsockopen($servers[$server][1], $servers[$server][2], $errno, $errstr, 30);

$info = array();

if (!$fp)
{
	die($errstr);
}
else
{
	$vkCommand = '';
	$ASYNC     = '';
	$numCmds   = 0;

	isset($servers[$server][3]) ? $pwdEntry = $servers[$server][3] : $pwdEntry = null;
	if (!is_null($pwdEntry) && !empty($pwdEntry))
	{
		if (is_array($pwdEntry))
		{
			if (!isset($pwdEntry[1]) || is_null($pwdEntry[1]) || empty($pwdEntry[1]))
			{
				$pwdEntry[1] = '0';
			}
			$credentials = "$pwdEntry[0] $pwdEntry[1]";
		}
		else
		{
			$credentials = $pwdEntry;
		}
		$vkCommand = "$AUTH $credentials\r\n";
		$numCmds++;
	}
	if ($async) // we want async flush
	{
		$ASYNC = ' ASYNC';
	}
	if ($db != -1) // one specific database
	{
		$vkCommand .= "SELECT $db\r\n$FLUSHDB$ASYNC\r\nQUIT\r\n";
		$numCmds += 3;
	}
	else // entire instance
	{
		$vkCommand .= "$FLUSHALL$ASYNC\r\nQUIT\r\n";
		$numCmds += 2;
	}

	fwrite($fp, $vkCommand);
	while (!feof($fp))
	{
		$info[] = trim(fgets($fp));
	}
	fclose($fp);
}

for ($i = 0; $i < $numCmds; $i++)
{
	if ($info[$i] != '+OK')
	{
		debug($vkCommand);
		debug($info);
		die($info[$i]);
	}
}

/* MAH MODIFICATION START
   REASON: Valkey Stats must not use any custom session handling. */
/* ORIGINAL START
$_SESSION['id'] = '';
   ORIGINAL END */
   echo "Success";
