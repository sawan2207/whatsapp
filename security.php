<?php
if (!isset($_GET['c'])) {
	header('Content-Type: application/json');
	echo json_encode(array('error' => 1, 'message' => 'unknown contact number'));
	die();
} else if (!is_numeric($_GET['c'])) {
	echo json_encode(array('error' => 1, 'message' => 'invalid contact number'));
	die();
}
?>