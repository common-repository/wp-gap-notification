<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$table_name = $wpdb->prefix . "wgn_logs";
/**
* Add table to db for logs
*/
function wgn_install() {
   if (!get_option('wgn_db_version')) add_option('wgn_db_version', wgn_DB_VERSION);
   if ( wgn_db_need_update() ) wgn_install_db_tables();
}
register_activation_hook( wgn_PLUGIN_DIR.'/wgn.php', 'wgn_install' );

/**
 * Install wgn table
 */
function wgn_install_db_tables() {
	global $wpdb;
	$table_name = $wpdb->prefix . "wgn_logs";
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		post_id bigint(20) NOT NULL,
		sending_result text NOT NULL,
		UNIQUE KEY id (id)
		) $charset_collate;";
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $sql );
}

//forked from alo-easymail plugin. Thanks!
/**
 * Check if plugin tables are already properly installed
 */
function wgn_db_need_update() {
	global $wpdb;
	global $table_name;
	$installed_db = get_option('wgn_db_version');
	$missing_table = false; // Check if tables not yet installed
	if ( $wpdb->get_var("show tables like '$table_name'") != $table_name ) $missing_table = true;
	return ( $missing_table || wgn_DB_VERSION != $installed_db ) ? true : false;
}
/**
 * Since 3.1 the register_activation_hook is not called when a plugin
 * is updated, so to run the above code on automatic upgrade you need
 * to check the plugin db version on another hook.
 */
function wgn_check_db_when_loaded() {
   if ( wgn_db_need_update() ) wgn_install_db_tables();
}
add_action('plugins_loaded', 'wgn_check_db_when_loaded');
//End forked functions

/**
 * An optimized function for getting our options from db
 *
 * @since 1.5
 */
function wgn_get_option() {
	global $wpdb;
	$query = "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'wgn_%%'";
	$wgn_data = $wpdb->get_results($query, OBJECT_K);
	return $wgn_data;
}

/**
* Print admin settings page
*/
function wgn_settings_page() {
require_once('admin/settings-page.php');
}

/**
* Print admin subpage
*/
function wgn_broadcast_page_callback() {
    require_once('broadcast.php');
}

/**
 * Enqueue scripts in the WordPress admin, excluding edit.php.
 *
 * @param int $hook Hook suffix for the current admin page.
 */
function wgn_enqueue_script( $hook ) {
	$pages_array = array('toplevel_page_wgn', 'post-new.php', 'post.php');
	if (!in_array($hook, $pages_array)){
		return;
	}
	wp_enqueue_script( 'textrange', wgn_PLUGIN_URL. '/inc/js/textrange.js', array(), '', true );
	wp_enqueue_script( 'emojione', wgn_PLUGIN_URL. '/inc/js/emojione.js', array(), '', true );
	wp_enqueue_script( 'wgn-functions', wgn_PLUGIN_URL. '/inc/js/fn.js', array(), '', true );
	$translation_array = array(
		'frame_title' => __("Select or Upload the custom photo", "wgn-plugin"),
		'button_text' => __("Use this image", "wgn-plugin"),
		'file_frame_title' => __("Select or Upload the files", "wgn-plugin"),
		'file_button_text' => __("Use this file(s)", "wgn-plugin"),
		'edit_file' => __("Edit file", "wgn-plugin")
		);
	wp_localize_script( 'wgn-functions', 'wgn_js_obj', $translation_array );
}
add_action( 'admin_enqueue_scripts', 'wgn_enqueue_script' );

/**
 * Sanitize a string from user input or from the db
 *
 * check for invalid UTF-8,
 * Convert single < characters to entity,
 * strip all tags,
 * strip octets.
 *
 * @since 2.9.0
 *
 * @param string $str
 * @return string
 */
function wgn_sanitize_text_field($str) {
	$filtered = wp_check_invalid_utf8( $str );

	if ( strpos($filtered, '<') !== false ) {
		$filtered = wp_pre_kses_less_than( $filtered );
		// This will strip extra whitespace for us.
		$filtered = strip_tags( $filtered, "<b><strong><em><i><a><code><pre>");
	}
	$found = false;
	while ( preg_match('/%[a-f0-9]{2}/i', $filtered, $match) ) {
		$filtered = str_replace($match[0], '', $filtered);
		$found = true;
	}
	if ( $found ) {
		// Strip out the whitespace that may now exist after removing the octets.
		$filtered = trim( preg_replace('/ +/', ' ', $filtered) );
	}

	/**
	 * Filter a sanitized text field string.
	 *
	 * @since 2.9.0
	 *
	 * @param string $filtered The sanitized string.
	 * @param string $str      The string prior to being sanitized.
	 */
	return apply_filters( 'wgn_sanitize_text_field', $filtered, $str );
}

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function wgn_load_textdomain() {
	load_plugin_textdomain( 'wgn-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' ); 
}
add_action( 'plugins_loaded', 'wgn_load_textdomain' );

/**
* Add action links to the plugin list for wgn.
*
* @param $links
* @return array
*/
function wgn_plugin_action_links($links) {
	$links[] = '<a href="http://hamyarwp.com/wgn/">' . __('Persian Tutorial in HamyarWP', 'wgn-plugin') . '</a>';
	return $links;
}
add_action('plugin_action_links_' . plugin_basename(__FILE__), 'wgn_plugin_action_links');

/**
* This will get information about sent mail from PHPMailer and send it to user
*/
function wgn_mail_action($result, $to, $cc, $bcc, $subject, $body){
	global $tdata;
	$nt = new wgn_Notifc_Class();
	$_apitoken = $tdata['wgn_api_token']->option_value;
	$_msg = $body;
	if($tdata['wgn_hashtag']->option_value != '') {
		$_msg = $tdata['wgn_hashtag']->option_value."\n".$_msg;
	}
	$nt->wgn_Notifcaster($_apitoken);
	if(mb_strlen($_msg) > 4096){
		$splitted_text = $nt->str_split_unicode($_msg, 4096);
		foreach ($splitted_text as $text_part) {
			$nt->wgn_notify($text_part);
		}
	} else {
		$nt->wgn_notify($_msg);
	}
}
/**
 * Setup a custom PHPMailer action callback. This will let us to fire our action every time a mail sent
 * Thanks to Birgire (http://xlino.com/) for creating this code snippet.
 */
function wgn_phpmailer_hook ( $phpmailer ){
	$phpmailer->action_function = 'wgn_mail_action';
	
}
/**
* Show a warning message for admins.
*/
function wgn_api_admin_notice($message) {
	$class = "updated notice is-dismissible";
	$message = sprintf(__('Your API token isn\'t set. Please go to %s and set it.','wgn-plugin'), "<a href='".admin_url('admin.php?page=wgn/wgn.php')."'>".__("wgn Settings", "wgn-plugin")."</a>");
	echo"<div class=\"$class\"> <p>$message</p></div>"; 
}


/**
* Adds a box to the main column on the Post and Page edit screens.
*/
function wgn_add_meta_box() {
	$screens = get_post_types( '', 'names' );
	foreach ( $screens as $screen ) {
		add_meta_box(
			'wgn_meta_box',
			__( 'Gap Channel Options', 'wgn-plugin' ),
			'wgn_meta_box_callback',
			$screen,
			"normal",
			"high"
			);
	}
}
add_action( 'add_meta_boxes', 'wgn_add_meta_box' );

/**
* Prints the box content.
* 
* @param WP_Post $post The object for the current post/page.
*/
function wgn_meta_box_callback( $post ) {
	global $wpdb;
	global $table_name;
	global $tdata;
	// Add a nonce field so we can check for it later.
	$ID = $post->ID;
	wp_nonce_field( 'wgn_save_meta_box_data', 'wgn_meta_box_nonce' );
	$error = "";
	$dis = "";
	$check_state = "";
	$is_product = false;
	$wgn_log = $wpdb->get_row( "SELECT * FROM $table_name WHERE post_id = $ID", ARRAY_A );
	if ($tdata['wgn_channel_username']->option_value == "" || $tdata['wgn_bot_token']->option_value == "")
		{
			$dis = "disabled=disabled"; 
			$error = "<span style='color:red;font-weight:700;'>".__("Bot token or Channel username aren't set!", "wgn-plugin")."</span><br>";
		}
	$tstc = get_post_meta($ID, '_wgn_send_to_channel', true);
	$tstc = $tstc != "" ? $tstc : $tdata['wgn_send_to_channel']->option_value;
	$cp = get_post_meta($ID, '_wgn_meta_pattern', true);
	$cp = $cp != "" ? $cp : $tdata['wgn_channel_pattern']->option_value;
	$s = get_post_meta($ID, '_wgn_send_thumb', true);
	$s = $s != "" ? $s : $tdata[ 'wgn_send_thumb']->option_value;
	if ($post->post_type == 'product'){
		$is_product = true;
	}
	// Custom image upload-link
	$wgn_img_upload_link = esc_url( get_upload_iframe_src( 'image', $ID ));
	// See if there's a media id already saved as post meta
	$wgn_img_id = get_post_meta( $ID, '_wgn_img_id', true );
	// Get the image src
	$wgn_img_src = wp_get_attachment_image_src( $wgn_img_id);
	// For convenience, see if the array is valid
	$wgn_have_img = is_array( $wgn_img_src );

	// File upload-link
	$wgn_file_upload_link = esc_url( get_upload_iframe_src());
	// See if there's a media id already saved as post meta
	$wgn_file_id = get_post_meta( $ID, '_wgn_file_id', true);
	if(!empty($wgn_file_id)){
		$attachment = wp_prepare_attachment_for_js($wgn_file_id);
		$parsed = $attachment->url;
		$wgn_file_src = dirname( $parsed [ 'path' ] ) . '/' . rawurlencode( basename( $parsed[ 'path' ] ) );
		$wgn_have_file = 1;
	}

	?>
	<div id="wgn_metabox">
	<style type="text/css">
		.patterns li {display: inline-block; width: auto; padding: 2px 7px 2px 7px; margin-bottom: 10px; border-radius: 3px; text-decoration: none; background-color: #309152; color: white; cursor: pointer;}
		.wc-patterns li {background-color: #a46497;}
		.wgn-radio-group {line-height: 2em;}
		.wgn-radio-group input {margin-top:1px;}
	</style>
	<?php echo $error ?>
	<table class="form-table">
	<tr>
	<th scope="row"><h3><?php echo __('Send to Gap Status', 'wgn-plugin' ) ?></h3> </th>
	<td>
	<div class="wgn-radio-group">
	<input type="radio" id="wgn_send_to_channel_yes" name="wgn_send_to_channel" <?php echo $dis ?> value="1" <?php checked( '1', $tstc ); ?>/><label for="wgn_send_to_channel"><?php echo __('Send this post to channel', 'wgn-plugin' ) ?> </label><br>
	<input type="radio" id="wgn_send_to_channel_no" name="wgn_send_to_channel" <?php echo $dis ?> value="0" <?php checked( '0', $tstc ); ?>/><label for="wgn_send_to_channel"><?php echo __('Don\'t send this post to channel', 'wgn-plugin' ) ?> </label>
	</div>
	</td>
	</tr>
	<?php require_once(wgn_PLUGIN_DIR."/inc/composer.php");?>
	</fieldset>
	</table>
	<hr>
	<p><?php echo __("Sending result: ", "wgn-plugin") ?></p>
	<span id="wgn_last_publish" style="font-weight:700">
	<?php 
	if($wgn_log['sending_result'] == 1){
		# This prevents adding a repetitive phrase to db.
		$sending_result = __("Published successfully", "wgn-plugin");
	} else {
		$sending_result = $wgn_log['sending_result'];
	}
	echo $sending_result.' || '.__("Date: ", "wgn-plugin").$wgn_log['time'] 
	?>
	</span>
</div>

<?php
}

/**
* When the post is saved, saves our custom data.
*
* @param int $ID The ID of the post being saved.
*/
function wgn_save_meta_box_data( $ID, $post, $update ) {
	global $tdata;
    /*
    * We need to verify this came from our screen and with proper authorization,
    * because the save_post action can be triggered at other times.
    */
    // Check if our nonce is set.
    if ( ! isset( $_POST['wgn_meta_box_nonce'] ) ) {
    	return;
    }
    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['wgn_meta_box_nonce'], 'wgn_save_meta_box_data' ) ) {
    	return;
    }
    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    	return;
    }
    // Return if it's a post revision
    if ( false !== wp_is_post_revision( $ID ) ){
    	return;
    }
    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

    	if ( ! current_user_can( 'edit_page', $ID ) ) {
    		return;
    	}

    } else {

    	if ( ! current_user_can( 'edit_post', $ID ) ) {
    		return;
    	}
    }
    # OK, it's safe for us to save the data now.
    // Make sure that it is set.
    $tstc = $tdata['wgn_send_to_channel']->option_value;
    if ( $tstc != $_POST['wgn_send_to_channel']) {
    	$tstc = $_POST['wgn_send_to_channel'];
    	update_post_meta( $ID, '_wgn_send_to_channel', $tstc);
    } else {
    	delete_post_meta( $ID, '_wgn_send_to_channel');
    }
	# Load global options
    $pattern = $tdata['wgn_channel_pattern']->option_value;
    $thumb_method = $tdata['wgn_send_thumb']->option_value;
    if ( $pattern != $_POST['wgn_channel_pattern']) {
    	$pattern = $_POST['wgn_channel_pattern'];
    	$pattern = sanitize_text_field ($pattern);
    	update_post_meta( $ID, '_wgn_meta_pattern', $pattern);
    } else {
    	delete_post_meta( $ID, '_wgn_meta_pattern');
    }
    if ( $thumb_method != $_POST['wgn_send_thumb']){
    	$thumb_method = $_POST['wgn_send_thumb'];
		$thumb_method = sanitize_text_field ($thumb_method);
    	update_post_meta( $ID, '_wgn_send_thumb', $thumb_method);
    } else {
    	delete_post_meta( $ID, '_wgn_send_thumb');
    }
    if (isset($_POST['wgn_img_id'])){
    	$wgn_img_id = $_POST['wgn_img_id'];
		$wgn_img_id = sanitize_text_field ($wgn_img_id);
    	update_post_meta( $ID, '_wgn_img_id', $wgn_img_id);
    } else {
    	$wgn_img_id = 0;
    	delete_post_meta( $ID, '_wgn_img_id');
    }
    if (isset($_POST['wgn_file_id'])){
    	$wgn_file_id = $_POST['wgn_file_id'];
		$wgn_file_id = intval ($wgn_file_id);
    	update_post_meta( $ID, '_wgn_file_id', $wgn_file_id);
    } else {
    	$wgn_file_id = 0;
    	delete_post_meta( $ID, '_wgn_file_id');
    }
    if($tstc == 1){
    	if ($post->post_status == "publish" && $post->post_password == ""){
    		wgn_post_published ( $ID, $post, $pattern, $thumb_method, $wgn_img_id, $wgn_file_id );
    	}
    }
}
add_action( 'save_post', 'wgn_save_meta_box_data', 10, 3 );

function wgn_on_publish_future_post( $post ) {
    global $tdata;
    $ID = $post->ID;
	$ID = intval ($ID);
    $tstc = get_post_meta($ID, '_wgn_send_to_channel', true);
    $tstc = $tstc != "" ? $tstc : $tdata['wgn_send_to_channel']->option_value;
    $pattern = get_post_meta($ID, '_wgn_meta_pattern', true);
    $pattern = $pattern != "" ? $pattern : $tdata['wgn_channel_pattern']->option_value;
    $thumb_method = get_post_meta($ID, '_wgn_send_thumb', true);
    $thumb_method = $thumb_method != "" ? $thumb_method : $tdata[ 'wgn_send_thumb']->option_value;
    $wgn_img_id = get_post_meta( $ID, '_wgn_img_id', true );
	$wgn_img_id = intval ($wgn_img_id);
    $wgn_file_id = get_post_meta( $ID, '_wgn_file_id', true );
	$wgn_file_id = intval ($wgn_file_id);
    if($tstc == 1){
    	wgn_post_published ( $ID, $post, $pattern, $thumb_method, $wgn_img_id, $wgn_file_id );
    }
}
add_action(  'future_to_publish',  'wgn_on_publish_future_post', 10, 1 );

/**
* When the post is published, send the messages.
* @param int $ID
* @param obj $post
*/
function wgn_post_published ( $ID, $post, $pattern, $thumb_method, $wgn_img_id, $wgn_file_id ) {
	global $wpdb;
	global $table_name;
	global $tdata;
	if ($pattern == "" || $pattern == false){
		$pattern = $tdata['wgn_channel_pattern']->option_value;
	}
	if( $thumb_method != $tdata['wgn_send_thumb']->option_value ) {
		$thumb_method = get_post_meta($ID, '_wgn_send_thumb', true);
	}
	// If there is no pattern then return!
	if ($pattern == ""){
		return;
	}
	switch ($thumb_method) {
		case 1:
			$method = 'photo';
			$img_id = get_post_thumbnail_id($ID);
			$photo =  get_attached_file($img_id);
			break;
		case 2:
			$method = 'photo';
			$img_id = $wgn_img_id;
			$photo =  get_attached_file($img_id);
			break;
		default:
			$method = false;
			break;
	}
	// Initialize Gap information
	switch ($tdata['wgn_markdown']->option_value) {
		case 0:
		$parse_mode = null;
			break;
		case 1:
		$parse_mode = "Markdown";
			break;
		case 2:
		$parse_mode = "HTML";
			break;
		default:
		$parse_mode = null;
			break;
	}
	$ch_name = $tdata['wgn_channel_username']->option_value;
	$token = $tdata['wgn_bot_token']->option_value;
	$web_preview = $tdata['wgn_web_preview']->option_value;
	$excerpt_length = intval($tdata['wgn_excerpt_length']->option_value);
	if ($token == "" || $ch_name == ""){
		update_post_meta( $ID, '_wgn_meta_data', __('Bot token or Channel username aren\'t set!', 'wgn-plugin') );
		return;
	}
	if($post->post_type == 'product'){
		$_pf = new WC_Product_Factory();
		$product = $_pf->get_product($ID);
		$tags_array = explode(', ' , $product->get_tags());
		$categories_array = explode(', ' ,$product->get_categories());
	} else {
		$tags_array = wp_get_post_tags( $ID, array( 'fields' => 'names' ) );
		$categories_array = wp_get_post_categories($ID, array( 'fields' => 'names' ));	
	}
	foreach ($tags_array as $tag) {
		$tags .= " #".$tag;
	}
	foreach ($categories_array as $cat) {
		$categories .= "|".$cat;
	}
	// Preparing message for sending
	// Wordpress default tags and substitutes array
	$wp_tags = array("{ID}","{title}","{excerpt}","{content}","{author}","{short_url}","{full_url}","{tags}","{categories}");
	$wp_subs = array(
		$post->ID,
		$post->post_title,
		wp_trim_words($post->post_content, $excerpt_length, "..."),
		$post->post_content,
		get_the_author_meta("display_name",$post->post_author),
		wp_get_shortlink($ID),
		get_permalink($ID),
		$tags,
		$categories
		);
	// WooCommerce tags and substitutes array
	$wc_tags = array("{width}", "{length}", "{height}", "{weight}", "{price}", "{regular_price}", "{sale_price}", "{sku}", "{stock}", "{downloadable}", "{virtual}", "{sold_indiidually}", "{tax_status}", "{tax_class}", "{stock_status}", "{backorders}", "{featured}", "{visibility}");
	 if ($post->post_type == 'product'){
		$p = $product;
		$wc_subs = array ($p->width, $p->length, $p->height, $p->weight, $p->price, $p->regular_price, $p->sale_price, $p->sku, $p->stock, $p->downloadable, $p->virtual, $p->sold_individually, $p->tax_status, $p->tax_class, $p->stock_status, $p->backorders, $p->featured, $p->visibility);
	}
	// The variables are case-sensitive.
	$re = $wp_tags;
	$subst = $wp_subs;
	if ($post->post_type == 'product'){
		$p = $product;
		$re = array_merge($re, $wc_tags);
		$subst = array_merge($subst, $wc_subs);
	} else {
	// If it's not a product post then strip out all of the WooCommerce tags
		$strip_wc = 1;
	}

	if($parse_mode == "Markdown"){
		$subst = str_replace(array("_", "*"), array("\_", "\*"), $subst);
	}

	$msg = str_replace($re, $subst, $pattern);

	if ($strip_wc == 1){
		$msg = str_replace($wc_tags, '', $msg);
	}
	
	// Search for custom field pattern
	$re = "/%(#)?([\w\s]+)%/iu";
	$number_of_cf = preg_match_all($re, $msg, $matches);
	if ($number_of_cf != 0){
		$cf_tags_array = array();
		$cf_value_array = array();
		for ($i=0; $i < $number_of_cf; $i++) { 
		$cf_value = get_post_meta($ID, $matches[2][$i], true);
		if ($matches[1][0] != ""){
			if ($parse_mode == "Markdown") {
				$cf_value = str_replace(" ", "\_", $cf_value);
			} else {
				$cf_value = str_replace(" ", "_", $cf_value);
			}
		array_push($cf_value_array, "#".$cf_value);
		}
		array_push($cf_tags_array, $matches[0][$i]);
		}
		$msg = str_replace($cf_tags_array, $cf_value_array, $msg);
	}
	
	$msg = str_replace('&nbsp;','', $msg);

	$nt = new wgn_Notifc_Class();
	$nt->wgn_Gap($token, $parse_mode, $web_preview);
	if ($method == 'photo' && $photo != false ) {
		if($tdata['wgn_img_position']->option_value == 1){
			$msg = '<a href="'.wp_get_attachment_url($img_id).'">‌</a>'.$msg;
			$nt->web_preview = 0;
			$r = $nt->wgn_channel_text($ch_name, $msg);
		} else {
			$attachment = wp_prepare_attachment_for_js($img_id);
			if (mb_strlen($msg) < 200){
				$file_caption = $msg;
				$file_format = 'image';
				$file = $photo;
				$r1 = $nt->wgn_channel_file($ch_name, $file_caption, $file, $file_format );
			} else {
				$file_caption = $attachment['caption'];
				$file_format = 'image';
				$file = $photo;
				$r1 = $nt->wgn_channel_file($ch_name, $file_caption, $file, $file_format );
				$r = $nt->wgn_channel_text($ch_name, $msg);
			}
		}
	} else {
		$r = $nt->wgn_channel_text($ch_name, $msg);
	}
	if($wgn_file_id != 0){
		$file = get_attached_file($wgn_file_id);
		$attachment = wp_get_attachment($wgn_file_id);
		$file_caption = $attachment['caption'];
		$file_format = $attachment['fileformat'];
		$nt->wgn_channel_file($ch_name, $file_caption, $file, $file_format);
	}
	$publish_date = current_time( "mysql", $gmt = 0 );
	if ($r["ok"] == true){
		$sending_result = 1;
	} else {
		$sending_result = $r["description"]."|| ".$msg;  
	}
	$wgn_log = $wpdb->get_row( "SELECT * FROM $table_name WHERE post_id = $ID");
	if($wgn_log == null){
		$wpdb->replace( 
			$table_name, 
			array( 
				'time' => $publish_date,
				'post_id' => $ID,
				'sending_result' => $sending_result
				)
			);	
	} else {
		$wpdb->update( 
			$table_name, 
			array( 
				'time' => $publish_date,
				'post_id' => $ID,
				'sending_result' => $sending_result
				),
			array ('post_id' => $ID)
			);
	}
	//update_post_meta( $ID, '_wgn_send_to_channel', 0);
	//unset($_POST['wgn_send_to_channel']);
}
// add_action( 'publish_post', 'wgn_post_published', 10, 2 );
// add_action( 'publish_page', 'wgn_post_published', 10, 2 );

function get_icon_for_attachment($attachment_id) {
	$base = includes_url() . "images/media/";
	$type = get_post_mime_type($attachment_id);
	switch ($type) {
		case 'audio/mpeg':
		case 'audio/vorbis':
		case 'application/ogg':
		return $base . "image.png"; break;
		case 'video/mpeg':
		case 'video/mp4': 
		case 'video/quicktime':
		return $base . "video.png"; break;
		default:
		return $base . "default.png";
	}
}
function wp_get_attachment( $attachment_id ) {

    $attachment = get_post( $attachment_id );
    $attachment_path = basename(get_attached_file($attachment_id));
    error_log(print_r(wp_prepare_attachment_for_js($attachment_id),1));
    return true;
}

function wgn_ajax_test_callback() {
	$nt = new wgn_Notifc_Class();
	switch ($_POST['subject']) {
		case 'm':
		//Send a test message using Notifcaster.
			$nt->wgn_Notifcaster($_POST['api_token']);
			$result = $nt->wgn_notify($_POST['msg']);
			echo json_encode($result);
			wp_die();
			break;
		case 'c':
		//Send a test message to channel
			$nt->wgn_Gap($_POST['bot_token'], $_POST['markdown'], $_POST['web_preview']);
			$msg = str_replace("\\", "", $_POST['msg']);
			$result = $nt->wgn_channel_text($_POST['channel_username'], $msg );
			echo json_encode($result);
			wp_die();
			break;
		case 'b':
		//Get bot info
			$nt->wgn_Gap($_POST['bot_token']);
			$result = $nt->wgn_get_bot();
			echo json_encode($result);
			wp_die();
			break;
		case 'gm':
		//Get the number of members in a chat.
			$nt->wgn_Gap($_POST['bot_token']);
			$result = $nt->wgn_get_members_count($_POST['channel_username']);
			echo json_encode($result);
			wp_die();
			break;
		default:
			return "Invalid POST request";
			break;
	}
}
add_action( 'wp_ajax_wgn_ajax_test', 'wgn_ajax_test_callback' );

?>