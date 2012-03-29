<?php
/*
Plugin Name: PinPress Boards
Plugin URI: http://dsthode.info/pinpress-boards/
Description: Pins and boards for your Wordpress blog just like Pinterest
Version: 0.42
Author: Damian Serrano Thode
Author URI: http://dsthode.info/pinpress-boards/
License: GNU GPLv2
*/

/*
Copyright (C) 2012  Damian Serrano Thode

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/* Custom post types (pinboard and pins like) */

add_action('init', 'pin_register');
 
function pin_register() {
 
	$labels = array(
		'name' => _x('PinPress', 'post type general name'),
		'singular_name' => _x('Pin Item', 'post type singular name'),
		'add_new' => _x('Add New', 'pin item'),
		'add_new_item' => __('Add New Pin Item'),
		'edit_item' => __('Edit Pin Item'),
		'new_item' => __('New Pin Item'),
		'view_item' => __('View Pin Item'),
		'search_items' => __('Search Pins'),
		'not_found' =>  __('Nothing found'),
		'not_found_in_trash' => __('Nothing found in Trash'),
		'parent_item_colon' => ''
	);
 
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
//		'menu_icon' => get_stylesheet_directory_uri() . '/article16.png',
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array('title','editor')
	  ); 
 
	register_post_type( 'pin' , $args );
	register_taxonomy("Boards", array("pin"), array("hierarchical" => true, "label" => "Boards", "singular_label" => "Board", "rewrite" => true));
	flush_rewrite_rules();
}

add_action("admin_init", "admin_init");
 
function admin_init(){
  add_meta_box("pin_image_meta", "Pin image", "pin_image_meta", "pin", "normal", "low");
}
 
function pin_image_meta() {
  global $post;
  $custom = get_post_custom($post->ID);
  $pin_image = $custom["pin_source_image_url"][0];
  $pin_source = $custom["pin_source_url"][0];
  wp_enqueue_script('jquery');
  ?>
  <p><label>Pin image:</label><br />
  <input id="pin_image" name="pin_image" value="<?php echo $pin_image; ?>"></input></p>
  <p><label>Pin source:</label><br />
  <input id="pin_source" name="pin_source" value="<?php echo $pin_source; ?>"></input></p>
  <p><button type="button" id="clear_pin_data">Clear pin data</button><button type="button" id="open_pin_data">Enter pin data</button></p>

<div id="pin_data_container" style="display:none;">
<div id="pin_data" style="position:fixed;width:400px;height:300px;top:50%;left:50%;margin-top:-150px;margin-left:-200px;background-color:white;border:1px solid black;z-index:20001;padding:10px;">
	<div>Enter pin source URL</div>
	<input type="text" id="pin_source_url"></input>
	<button id="pin_fetch_images" type="button">Fetch images</button>
	<div>Choose image:</div>
	<div id="pin_image_chooser" style="width:100%;">
		<div>Image <span id="pin_image_index">0</span> of <span id="pin_images_count">0</span></div>
		<div style="width:150px;height:150px;">
		<img src="" id="pin_current_image" style="max-width:150px;max-height:150px;"><br/>
		</div>
		<a id="pin_previous_image" style="left:0;cursor:pointer;">&larr;Previous</a>
		<a id="pin_next_image" style="right:0;cursor:pointer;">Next&rarr;</a>
	</div>
	<div><button type="button" id="cancel_pin_data">Cancel</button><button type="button" id="submit_pin_data">Submit</button></div>
</div>
<div id="pin_data_bg" style="position:fixed;top:0;left:0;width:100%;height:100%;background-color:black;z-index:20000;opacity:0.7;filter:alpha(opacity=70);"></div>
</div>
<script type="text/javascript">

	var currentImages = [];
	var currentIndex = 0;
	
	jQuery(document).ready(function() {
		jQuery('#open_pin_data').on('click', open_pin_data);
		jQuery('#clear_pin_data').on('click', clear_pin_data);
		jQuery('#pin_fetch_images').on('click', pin_fetch_images);
		jQuery('#submit_pin_data').on('click', submit_pin_data);
		jQuery('#cancel_pin_data').on('click', cancel_pin_data);
		jQuery('#pin_previous_image').on('click', pin_previous_image);
		jQuery('#pin_next_image').on('click', pin_next_image);
	});
	
	function open_pin_data() {
		jQuery("#pin_data_container").show();
	}
	
	function clear_pin_data() {
		jQuery('#pin_source').val('');
		jQuery('#pin_image').val('');
	}
	function pin_fetch_images() {
		jQuery.get('<?php echo plugin_dir_url(__FILE__); ?>/fetch_images.php', {source_url: jQuery('#pin_source_url').val()}, pin_process_images);
	}
	
	function pin_process_images(data, textStatus, jqXHR) {
		currentImages = data;
		currentIndex = 0;
		if (currentImages.length > 0) {
			jQuery('#pin_images_count').html(currentImages.length);
			pin_set_image();
		}
	}
	
	function submit_pin_data() {
		jQuery('#pin_data_container').hide();
		jQuery('#pin_source').val(jQuery('#pin_source_url').val());
		jQuery('#pin_image').val(currentImages[currentIndex]);
	}

	function cancel_pin_data() {
		jQuery('#pin_data_container').hide();
	}
	
	function pin_set_image() {
		jQuery('#pin_current_image').attr('src', currentImages[currentIndex]);
		jQuery('#pin_image_index').html(currentIndex + 1);		
	}
	
	function pin_previous_image() {
		if (currentIndex > 0) {
			currentIndex -= 1;
			pin_set_image();
		}
	}
	
	function pin_next_image() {
		if (currentIndex < currentImages.length) {
			currentIndex += 1;
			pin_set_image();
		}
	}

</script>

<?php
}

add_action('save_post', 'save_details');

function save_details(){
  global $post;
  if (strlen($_POST['pin_image']) > 0) {
	$post_meta = get_post_custom($post->ID);
	if ((strcmp($post_meta['pin_source_image_url'][0], $_POST['pin_image']) != 0) || !file_exists($post_meta['pin_local_file'][0])) {
		$pin_local_data = pinpress_handle_upload($_POST['pin_image']);
		if (!$pin_local_data['error']) {
			update_post_meta($post->ID, 'pin_source_image_url', $_POST['pin_image']);
			update_post_meta($post->ID, 'pin_source_url', $_POST['pin_source']);
			update_post_meta($post->ID, 'pin_local_file', $pin_local_data['file']);
			update_post_meta($post->ID, 'pin_local_url', $pin_local_data['url']);
			update_post_meta($post->ID, 'pin_image_width', $pin_local_data['width']);
			update_post_meta($post->ID, 'pin_image_height', $pin_local_data['height']);
		}
	}
  }
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

add_action("manage_posts_custom_column",  "pin_custom_columns");
add_filter("manage_edit-pin_columns", "pin_edit_columns");
 
function pin_edit_columns($columns){
  $columns = array(
    "cb" => "<input type=\"checkbox\" />",
    "title" => "Pin Title",
    "pin_source_url" => "Pin source URL",
	"boards" => "Boards"
  );
 
  return $columns;
}

function pin_custom_columns($column){
  global $post;
 
  switch ($column) {
    case "pin_source_url":
      $custom = get_post_custom();
      echo $custom["pin_source_url"][0];
      break;
	case "boards":
	  echo get_the_term_list($post->ID, 'Boards', '', ', ','');
      break;
  }
}

function script_url() {
	$url = $_SERVER['REQUEST_URI']; //returns the current URL
	$parts = explode('/',$url);
	$dir = $_SERVER['SERVER_NAME'];
	for ($i = 0; $i < count($parts) - 1; $i++) {
	 $dir .= $parts[$i] . "/";
	}
	return $dir;
}

function pinpress_board_shortcode($atts) {
	$board_text = '';
	if ($atts['board']) {
		$columns = 3;
		if ($atts['columns']) {
			$columns = $atts['columns'];
		}
		$args = array('post_type' => 'pin', 'boards' => $atts['board']);
		$query = new WP_Query($args);
		$count = 0;
		$columns_text = array();
		while ($query->have_posts()) : $query->the_post();
			$post_custom = get_post_custom();
			$text = pinpress_board_pin($post_custom['pin_source_url'][0], $post_custom['pin_local_url'][0], get_the_title(), get_the_content());
			$columns_text[$count % $columns] = $columns_text[$count % $columns] . $text;
			$count += 1;
		endwhile;
		for ($i=0;$i<$columns;$i++) {
			$board_text = $board_text . '<div style="display:inline-block;align:top;vertical-align:top;">' . $columns_text[$i] . '</div>';
		}
	}
	return $board_text;
}

add_shortcode('pinpress_board', 'pinpress_board_shortcode');

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

function horizontal($width, $height) {
	return $width > $height;
}

?>