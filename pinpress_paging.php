<?php

function pinpress_get_wp_config_path()
{
    $base = dirname(__FILE__);
    $path = false;

    if (@file_exists(dirname(dirname($base))."/wp-config.php"))
    {
        $path = dirname(dirname($base))."/wp-config.php";
    }
    else
    if (@file_exists(dirname(dirname(dirname($base)))."/wp-config.php"))
    {
        $path = dirname(dirname(dirname($base)))."/wp-config.php";
    }
    else
    $path = false;

    if ($path != false)
    {
        $path = str_replace("\\", "/", $path);
    }
    return $path;
}

include pinpress_get_wp_config_path();

global $pinpress_pins_per_page;

header('Content-Type: application/json');

if (($_GET['board'] || $_GET['category']) && $_GET['columns'] && $_GET['offset']) {
	list($columns, $count) = pinpress_load_pins($_GET['board'], $_GET['columns'], $_GET['offset'], $_GET['category']);
	echo json_encode(array('columns' => $columns, 'more_pins' => pinpress_get_load_more_pins($_GET['board'], $_GET['columns'], $_GET['offset'] + $pinpress_pins_per_page, $_GET['category']), 'count' => $count));
} else {
	echo json_encode(array('message' => 'Missing parameters'));
}

?>
