<?php
function _post($url, $data)
{
	$opts = array (
		'http' => array (
			'method'  => 'POST',
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'timeout' => 120,
			'content' => http_build_query($data, '', '&')
		)
	);
	//$cnt = 0;
	//while ($cnt < 3 && ($response = file_get_contents($url, false, stream_context_create($opts))) === false) $cnt++;
	return file_get_contents($url, false, stream_context_create($opts));
}

function _get($url)
{
	$opts = array (
		'http' => array (
			'method'  => 'GET',
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'timeout' => 120,
		)
	);
	//$cnt = 0;
	//while ($cnt < 3 && ($response = file_get_contents($url, false, stream_context_create($opts))) === false) $cnt++;
	return file_get_contents($url, false, stream_context_create($opts));
}

function _jsonPost($url, $json)
{
	$opts = array (
		'http' => array (
			'method'  => 'POST',
			'header'  => 'Content-Type: application/json',
			'timeout' => 120,
			'content' => $json
		)
	);
	//$cnt = 0;
	//while ($cnt < 3 && ($response = file_get_contents($url, false, stream_context_create($opts))) === false) $cnt++;
	return file_get_contents($url, false, stream_context_create($opts));
}

function _log($message, $type = 'log')
{
	$logFile = str_replace('\\', '/', dirname(__FILE__)) . '/Log/' . $type . date('Ymd') . '.log';
	$content = "[". date('Y-m-d H:i:s') ."] : {$message}\r\n";
	error_log($content, 3, $logFile);
}
