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

$pinpress_pins_per_page = 20;

add_action('init', 'pinpress_pin_register');
 
function pinpress_pin_register() {
 
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

add_action("admin_init", "pinpress_admin_init");
 
function pinpress_admin_init(){
  add_meta_box("pin_image_meta", "Pin image", "pinpress_pin_image_meta", "pin", "normal", "low");
}
 
function pinpress_pin_image_meta() {
  global $post;
  $custom = get_post_custom($post->ID);
  $pin_image = $custom["pin_source_image_url"][0];
  $pin_source = $custom["pin_source_url"][0];
  wp_enqueue_script('jquery');
  wp_enqueue_style('pinpress_style', plugins_url('pinpress_style.css', __FILE__));
  ?>
  <p><label>Pin image:</label><br />
  <input id="pinpress_pin_image" name="pinpress_pin_image" value="<?php echo $pin_image; ?>"></input></p>
  <p><label>Pin source:</label><br />
  <input id="pinpress_pin_source" name="pinpress_pin_source" value="<?php echo $pin_source; ?>"></input></p>
  <p>
	<button type="button" id="pinpress_clear_pin_data" class="button">Clear pin data</button>
	<button type="button" id="pinpress_open_pin_data" class="button">Enter pin data</button>
  </p>

<div id="pinpress_pin_data_container">
<div id="pinpress_pin_data">
	<div class="pinpress_dialog_header">
		<div class="pinpress_dialog_title">Enter pin source URL</div>
	</div>
	<div class="pinpress_dialog_body">
		<input type="text" id="pinpress_pin_source_url" value="<?php echo $pin_source; ?>"></input>
		<div class="pinpress_fetch_images_button_container">
			<button id="pinpress_pin_fetch_images" type="button" class="button">Fetch images</button>
		</div>
		<div>Choose image: Image <span id="pinpress_pin_image_index">0</span> of <span id="pinpress_pin_images_count">0</span></div>
		<div id="pinpress_pin_image_chooser">
			<div id="pinpress_previous_button_container">
				<button type="button" class="button" id="pinpress_pin_previous_image">&larr;Previous</button>
			</div>
			<div id="pinpress_pin_image_container">				
				<img src="<?php echo $pin_image; ?>" id="pinpress_pin_current_image"><br/>
			</div>
			<div id="pinpress_next_button_container">
				<button type="button" class="button" id="pinpress_pin_next_image">Next&rarr;</button>
			</div>
		</div>
	</div>
	<div class="pinpress_dialog_footer">
		<div id="pinpress_dialog_footer_buttons">
			<button type="button" id="pinpress_cancel_pin_data" class="button">Cancel</button>
			<button type="button" id="pinpress_submit_pin_data" class="button-primary">Submit</button>
		</div>
	</div>
</div>
<div id="pinpress_pin_data_bg"></div>
</div>
<script type="text/javascript">

	var currentImages = [];
	var currentIndex = 0;
	
	jQuery(document).ready(function() {
		jQuery('#pinpress_open_pin_data').on('click', pinpress_open_pin_data);
		jQuery('#pinpress_clear_pin_data').on('click', pinpress_clear_pin_data);
		jQuery('#pinpress_pin_fetch_images').on('click', pinpress_pin_fetch_images);
		jQuery('#pinpress_submit_pin_data').on('click', pinpress_submit_pin_data);
		jQuery('#pinpress_cancel_pin_data').on('click', pinpress_cancel_pin_data);
		jQuery('#pinpress_pin_previous_image').on('click', pinpress_pin_previous_image);
		jQuery('#pinpress_pin_next_image').on('click', pinpress_pin_next_image);
	});
	
	function pinpress_open_pin_data() {
		jQuery("#pinpress_pin_data_container").show();
	}
	
	function pinpress_clear_pin_data() {
		jQuery('#pinpress_pin_source').val('');
		jQuery('#pinpress_pin_image').val('');
	}
	function pinpress_pin_fetch_images() {
		jQuery('#pinpress_pin_current_image').attr('src', '<?php echo plugins_url("ajax_load.gif", __FILE__); ?>');
		jQuery.get('<?php echo plugin_dir_url(__FILE__); ?>/fetch_images.php', {source_url: jQuery('#pinpress_pin_source_url').val()}, pinpress_pin_process_images);
	}
	
	function pinpress_pin_process_images(data, textStatus, jqXHR) {
		currentImages = data;
		currentIndex = 0;
		if (currentImages.length > 0) {
			jQuery('#pinpress_pin_images_count').html(currentImages.length);
			pinpress_pin_set_image();
		}
	}
	
	function pinpress_submit_pin_data() {
		jQuery('#pinpress_pin_data_container').hide();
		jQuery('#pinpress_pin_source').val(jQuery('#pinpress_pin_source_url').val());
		jQuery('#pinpress_pin_image').val(currentImages[currentIndex]);
	}

	function pinpress_cancel_pin_data() {
		jQuery('#pinpress_pin_data_container').hide();
	}
	
	function pinpress_pin_set_image() {
		jQuery('#pinpress_pin_current_image').attr('src', currentImages[currentIndex]);
		jQuery('#pinpress_pin_image_index').html(currentIndex+1);		
	}
	
	function pinpress_pin_previous_image() {
		if (currentIndex > 0) {
			currentIndex -= 1;
			pinpress_pin_set_image();
		}
	}
	
	function pinpress_pin_next_image() {
		if (currentIndex < (currentImages.length-1)) {
			currentIndex += 1;
			pinpress_pin_set_image();
		}
	}
	
</script>

<?php
}

function pinpress_save_details(){
  global $post;
  if (strlen($_POST['pinpress_pin_image']) > 0) {
	$post_meta = get_post_custom($post->ID);
	if ((strcmp($post_meta['pin_source_image_url'][0], $_POST['pinpress_pin_image']) != 0) || !file_exists($post_meta['pin_local_file'][0])) {
		$pin_local_data = pinpress_handle_upload($_POST['pinpress_pin_image']);
		if (!$pin_local_data['error']) {
			update_post_meta($post->ID, 'pin_source_image_url', $_POST['pinpress_pin_image']);
			update_post_meta($post->ID, 'pin_source_url', $_POST['pinpress_pin_source']);
			update_post_meta($post->ID, 'pin_local_file', $pin_local_data['file']);
			update_post_meta($post->ID, 'pin_local_url', $pin_local_data['url']);
			update_post_meta($post->ID, 'pin_image_width', $pin_local_data['width']);
			update_post_meta($post->ID, 'pin_image_height', $pin_local_data['height']);
		}
	}
  }
}

add_action('save_post', 'pinpress_save_details');

function pinpress_pin_custom_columns($column){
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

function pinpress_pin_edit_columns($columns){
  $columns = array(
    "cb" => "<input type=\"checkbox\" />",
    "title" => "Pin Title",
    "pin_source_url" => "Pin source URL",
	"boards" => "Boards"
  );
 
  return $columns;
}

add_action("manage_posts_custom_column",  "pinpress_pin_custom_columns");
add_filter("manage_edit-pin_columns", "pinpress_pin_edit_columns");

function pinpress_board_shortcode($atts) {
	global $pinpress_pins_per_page;
	wp_enqueue_style('pinpress_style', plugins_url('pinpress_style.css', __FILE__));
	wp_enqueue_script('jquery_scrollspy', plugins_url('jquery-scrollspy.js', __FILE__), array('jquery'));
	$board_text = '';
	if ($atts['board'] || $atts['category']) {
		$board = $atts['board'];
		$category = $atts['category'];
		$columns = 3;
		if ($atts['columns']) {
			$columns = $atts['columns'];
		}
		$page_base_url = plugin_dir_url(__FILE__);
		$board_text = '';
		list($pins_columns, $pin_count) = pinpress_load_pins($board, $columns, 0, $category);
		for ($i=0;$i<$columns;$i++) {
			$board_text = $board_text . '<div id="pinpress_column_' . $i . '" class="pinpress_column">' . $pins_columns[$i] . '</div>';
		}
		$board_text = $board_text . pinpress_get_load_more_pins($board, $columns, $pinpress_pins_per_page, $category);
		$text = <<<EOT1
		<script type="text/javascript">
		
		var pinpress_board_name = '$board';
		var pinpress_category_name = '$category';
		var pinpress_columns = $columns;
		var pinpress_pins_per_page = $pinpress_pins_per_page;
		var pinpress_pin_offset = $pinpress_pins_per_page;
		
		jQuery(document).ready(function() {
			pinpress_setup_scroll_listener();
		});

		function pinpress_setup_scroll_listener() {
			jQuery(window).scrollspy({
				min: jQuery('#pinpress_more_pins_container').offset().top - jQuery(window).height(),
				max: document.height,
				onEnter: function(el, position) { pinpress_load_more_pins(pinpress_board_name, pinpress_columns, pinpress_pin_offset); },
			});
		}

		function pinpress_load_more_pins(board, columns, offset, category) {
			jQuery.get('$page_base_url/pinpress_paging.php', {board: board, columns: columns, offset: offset, category:category}, pinpress_process_more_pins);
		}

		function pinpress_process_more_pins(data, textStatus, jqHXR) {
			if (data.columns && data.columns.length > 0) {
				jQuery('#pinpress_more_pins_container').remove();
				for (var i=0; i<data.columns.length; i++) {
					jQuery('#pinpress_column_' + i).append(data.columns[i]);
				}
				jQuery('#pinpress_pins_container').append(data.more_pins);
				pinpress_pin_offset += data.count;
				pinpress_setup_scroll_listener();
			} else if (data.columns && data.columns.length == 0) {
				jQuery('#pinpress_load_more_pins').remove();
				jQuery('#pinpress_more_pins_container').html("<span>Fin</span>");
			}
		}

		</script>

		<div id="pinpress_pins_container">$board_text</div>
EOT1;
	}
	return $text;
}

add_shortcode('pinpress_board', 'pinpress_board_shortcode');

include 'pinpress_functions.php';

?>
