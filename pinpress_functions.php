<?php 
function pinpress_load_pins($board, $columns, $paged) {
	$args = array('post_type' => 'pin', 'boards' => $board, 'posts_per_page' => 35, 'paged' => $paged);
	$query = new WP_Query($args);
	$count = 0;
	$columns_text = array();
	$board_text = '';
	while ($query->have_posts()) : $query->the_post();
		$post_custom = get_post_custom();
		$text = pinpress_board_pin($post_custom['pin_source_url'][0], $post_custom['pin_local_url'][0], get_the_title(), get_the_content());
		$columns_text[$count % $columns] = $columns_text[$count % $columns] . $text;
		$count += 1;
	endwhile;
	for ($i=0;$i<$columns;$i++) {
		$board_text = $board_text . '<div style="display:inline-block;align:top;vertical-align:top;">' . $columns_text[$i] . '</div>';
	}
	return $board_text;
}

function pinpress_board_pin($pin_url, $image_url, $title, $text) {
	$base_url = plugin_dir_url(__FILE__);
	$text = <<<EOT
	<div class="pinpress_pin_item" style="width:170px;border:1px solid #cccccc;padding:5px;margin:5px;">
		<a href="$pin_url">
			<div class="pinpress_pin_title"><strong>$title</strong></div>
			<img src="$base_url/timthumb.php?src=$image_url&w=150" width="150" style="padding:none;border:none;background:none;margin:none;"/>
		</a>
		<div>$text</div>
	</div>
EOT;
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