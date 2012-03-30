<?php
 include 'pinpress_functions.php'

	header('Content-Type: application/json');

if ($_GET['board'] && $_GET['columns'] && $_GET['paged']) {
	$text = pinpress_load_pins($_GET['board'], $_GET['columns'], $_GET['paged']);
	echo json_encode(array('content' => $text));
} else {
	echo json_encode(array('message' => 'Missing parameters'));
}

?>