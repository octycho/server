<?php
require_once realpath(__DIR__ . '/../../') . '/lib/KalturaClient.php';
require_once realpath(__DIR__ . '/../') . '/KalturaMonitorResult.php';

$options = getopt('', array('service-url:'));

if(!isset($options['service-url']))
{
	echo "Argument service-url is required";
	exit(-1);
}

$serviceUrl = $options['service-url'];
$clientConfig = new KalturaConfiguration();
$clientConfig->partnerId = null;
$clientConfig->serviceUrl = $serviceUrl;

$client = new KalturaClient($clientConfig);
$start = microtime(true);
$monitorResult = new KalturaMonitorResult();
try
{
	$res = $client->system->ping();
	$end = microtime(true);

	$monitorResult->executionTime = $end - $start;
	$monitorResult->value = $monitorResult->executionTime;
	$monitorResult->description = "Ping time: $monitorResult->value seconds";
}
catch(Exception $ex)
{
	$end = microtime(true);
	$monitorResult->executionTime = $end - $start;
	$monitorResult->value = -1;
	$monitorResult->description = "Exception: " . get_class($ex) . ", Code: " . $ex->getCode() . ", Message: " . $ex->getMessage();
}
echo "$monitorResult";
exit(0);