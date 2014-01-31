<?php 

function pinpress_get_load_more_pins($board, $columns, $offset, $category) {
	global $pinpress_pins_per_page;
	$text = <<<EOT3
	<div id="pinpress_more_pins_container">
		<a id="pinpress_load_more_pins" href="#" onclick="javascript:pinpress_load_more_pins('$board', '$columns', '$offset', '$category');return false;">Cargar m&aacute;s pins</a>
	</div>
EOT3;
	return $text;
}

function pinpress_load_pins($board, $columns, $offset, $category) {
	global $pinpress_pins_per_page;
	$isboard = isset($board) && strlen($board) > 0;
	$iscategory = isset($category) && strlen($category) > 0;
	if ($isboard) {
		$args = array('post_type' => 'pin', 'boards' => $board, 'posts_per_page' => $pinpress_pins_per_page, 'offset' => $offset);
	} elseif ($iscategory) {
		$args = array('post_type' => 'post', 'category_name' => $category, 'posts_per_page' => $pinpress_pins_per_page, 'offset' => $offset);
	}
	$query = new WP_Query($args);
	$count = 0;
	$columns_text = array();
	$board_text = "<div>";
	while ($query->have_posts()) : $query->the_post();
		if ($isboard) {
			$post_custom = get_post_custom();
			$text = pinpress_board_pin($post_custom['pin_source_url'][0], $post_custom['pin_local_url'][0], get_the_title(), get_the_content(), false);
		} elseif ($iscategory) {
			$thumbimg = get_the_post_thumbnail(get_the_ID(), array(150, 150), array('style' => 'padding:none;border:none;background:none;margin:none;'));
			$text = pinpress_board_pin(get_permalink(), $thumbimg, get_the_title(), '', true);
		}
		$columns_text[$count % $columns] = $columns_text[$count % $columns] . $text;
		$count += 1;
	endwhile;
	return array($columns_text, $count);
}

function pinpress_board_pin($pin_url, $image_url, $title, $text, $iscategory) {
	$base_url = plugin_dir_url(__FILE__);
	if ($iscategory) {
		$text = <<<EOT2
		<div class="pinpress_pin_item">
			<a href="{$pin_url}">
				<div class="pinpress_pin_title">{$title}</div>
				<div>&nbsp;</div>
				{$image_url}
			</a>
			<div>&nbsp;</div>
		</div>
EOT2;
	} else {
		$text = <<<EOT4
		<div class="pinpress_pin_item">
			<a href="{$pin_url}">
				<div class="pinpress_pin_title"><strong>{$title}</strong></div>
				<img src="{$base_url}timthumb.php?src={$image_url}&w=150" width="150"/>
			</a>
			<div>{$text}</div>
		</div>
EOT4;
	}
	return $text;
}

function pinpress_horizontal($width, $height) {
	return $width > $height;
}

function pinpress_script_url() {
	$url = $_SERVER['REQUEST_URI']; //returns the current URL
	$parts = explode('/',$url);
	$dir = $_SERVER['SERVER_NAME'];
	for ($i = 0; $i < count($parts) - 1; $i++) {
	 $dir .= $parts[$i] . "/";
	}
	return $dir;
}

function pinpress_handle_upload($image_url) {
	$pathinfo = pathinfo($image_url);
	$data = file_get_contents($image_url);
	$local_file_name = uniqid('pinpress_', true) . '_' . uniqid('', true) . '.jpg';
	$res = wp_upload_bits($local_file_name, null, $data);
	$imagesize = getimagesize($res['file']);
	$res['width'] = $imagesize[0];
	$res['height'] = $imagesize[1];
	$res['mime'] = $imagesize['mime'];
  return $res;
}

?>
