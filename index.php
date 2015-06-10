<?php

include_once 'security.php';
include_once 'vendor/autoload.php';
include_once 'functions.php';
include_once 'pools.php';

$contact = $_GET['c'];
$output = array('error' => 0);
$start = time();
$condition = true;

doSniff($contact);

try 
{
	while ($condition) {
		$diff = time() - $start;
		if ($diff > 10) {
			throw new Exception('Time sublimit reached');
		} 
		$condition = shouldContinue();
	}
} catch (Exception $e) {

}


header('Content-Type: application/json');
defineOnlineStatus();
echo json_encode($output);
die();

?>