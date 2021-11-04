<?php
/**
 * Plugin Name: Popup for Contact Form 7
 * Plugin URI: http://www.imaiyaz.com
 * Description: This plugin will show the popup when Contact Form 7 has been submitted. You can customize popup title, message and colors from backend.
 * Version: 1.4
 * Author: aiyaz
 * Author URI: http://www.imaiyaz.com
 * License: GPL2
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/* deactivate plugin if Contact Form 7 are not active*/

function pfcf_show_admin_notice() {
	if ( get_transient( get_current_user_id() . 'cf7error' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		delete_transient( get_current_user_id() . 'cf7error' );
		echo '<div class="error"><p> This plugin is deactivated because it require <a href="plugin-install.php?tab=search&s=contact+form+7">Contact Form 7</a> plugin installed and activated.</p></div>';
	}
}

add_action( 'admin_notices', 'pfcf_show_admin_notice' );

function pfcf_plugin_activate() {
	if ( ! ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) ) {
		set_transient( get_current_user_id() . 'cf7error', 'message' );
	}
}

register_activation_hook( __FILE__, 'pfcf_plugin_activate' );

/* Register style and script */

function pfcf_add_scripts() {
	wp_register_script( 'pfcf-script', plugins_url( '/js/pfcf-script.js', __FILE__ ) );
	wp_register_style( 'pfcf-style', plugins_url( '/css/pfcf-style.css', __FILE__ ) );
	wp_enqueue_script( 'pfcf-script' );
	wp_enqueue_style( 'pfcf-style' );
}

add_action( 'wp_enqueue_scripts', 'pfcf_add_scripts', 15, 0 );


function pfcf_enqueue_scripts() {

	if ( is_admin() ) {
		wp_register_script( 'pfcf-admin-script', plugins_url( '/js/pfcf-admin-script.js', __FILE__ ) );
		wp_register_style( 'pfcf-admin-style', plugins_url( '/css/pfcf-admin-style.css', __FILE__ ) );
		wp_enqueue_script( 'pfcf-admin-script' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'pfcf-admin-style' );
	}
}
add_action( 'admin_enqueue_scripts', 'pfcf_enqueue_scripts' );

/* create custom plugin settings menu */
add_action( 'admin_menu', 'pfcf_create_sub_menu' );

function pfcf_create_sub_menu() {
	add_submenu_page( 'wpcf7', 'Popup Settings', 'Popup Settings', 'manage_options', 'pfcf-setting-page', 'pfcf_settings_page' );
	add_action( 'admin_init', 'pfcf_register_settings' );
}

function pfcf_register_settings() {

	// register our settings
	register_setting( 'pfcf-plugin-settings-group', 'enabled-cf7-form' );
	register_setting( 'pfcf-plugin-settings-group', 'popup_description' );
	register_setting( 'pfcf-plugin-settings-group', 'popup_background' );
	register_setting( 'pfcf-plugin-settings-group', 'image_url' );
	register_setting( 'pfcf-plugin-settings-group', 'popup_font_color' );
	register_setting( 'pfcf-plugin-settings-group', 'popup_width' );
	register_setting( 'pfcf-plugin-settings-group', 'popup_height' );
	register_setting( 'pfcf-plugin-settings-group', 'popup_duration' );
}

function pfcf_settings_page() {
?>
<div class="popup-cf7-wrap">
<h2>Popup Settings</h2>
<script type="text/javascript">
jQuery(document).ready(function($){
	$('#upload-btn').click(function(e) {
		e.preventDefault();
		var image = wp.media({ 
			title: 'Upload Image',
			multiple: false
		}).open()
		.on('select', function(e){
			// This will return the selected image from the Media Uploader, the result is an object
			var uploaded_image = image.state().get('selection').first();
			console.log(uploaded_image);
			var image_url = uploaded_image.toJSON().url;
			$('#image_url').val(image_url);
		});
	});
});
</script>
<form method="post" action="options.php">
	<?php
	settings_fields( 'pfcf-plugin-settings-group' );
	?>
	<?php
	do_settings_sections( 'pfcf-plugin-settings-group' );
	?>
	<!-- Start tabs -->
	<ul class="wp-tab-bar">
		<li class="wp-tab-active"><a href="#tabs-1">General Settings</a></li>
		<li><a href="#tabs-2">Popup Text</a></li>
		<li><a href="#tabs-3">Popup Design</a></li>
	</ul>
	<div class="wp-tab-panel" id="tabs-1">
		<p>
		<?php
		$args           = array(
			'post_type'      => 'wpcf7_contact_form',
			'posts_per_page' => -1,
		);
		$cf7_forms      = get_posts( $args );
		$selected_forms = ( get_option( 'enabled-cf7-form' ) ) ? get_option( 'enabled-cf7-form' ) : array();
		?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Choose a forms to display Popup</th>
				<td>
					<?php
					if ( ! empty( $cf7_forms ) ) {
						foreach ( $cf7_forms as $form ) {
						?>
							<input type="checkbox" name="enabled-cf7-form[]" value="<?php echo esc_attr( $form->ID ); ?>" <?php echo in_array( $form->ID, $selected_forms ) ? esc_attr( 'checked' ) : ''; ?>><?php echo esc_attr( $form->post_title ); ?><br/>
						<?php
						}
					}
					?>
					<span class="description">Popup will display on selected forms only.</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Popup Width</th>
				<td><input type="text" name="popup_width" class="small-text"  value="<?php echo esc_attr( get_option( 'popup_width' ) ); ?>" />
				<span class="description">Value must be like: 500px / auto / 50%</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Popup Height</th>
				<td><input type="text" name="popup_height" class="small-text"  value="<?php echo esc_attr( get_option( 'popup_height' ) ); ?>" />
					<span class="description">Value must be like: 200px / auto / 50%</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Auto Hide after</th>
				<td><input type="text" name="popup_duration" class="medium-text"  value="<?php	echo esc_attr( get_option( 'popup_duration' ) ); ?>" />
				<span class="description">Duration in milliseconds eg. 5000 (Popup will hide after 5 Seconds).</span></td>
				</td>
			</tr>
		</table>
		</p>
	</div>
	<div class="wp-tab-panel" id="tabs-2" style="display: none;">
		<p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Popup Text/Content</th>
				<td>
				<?php
				$content   = get_option( 'popup_description' );
				$editor_id = 'popup_description';
				wp_editor( $content, $editor_id );
				?>
				</td>
			</tr>
		</table>
		</p>
	</div>
	<div class="wp-tab-panel" id="tabs-3" style="display: none;">
		<p>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Font Color</th>
					<td><input type="text" name="popup_font_color" class="color-pick regular-text"  value="<?php	echo esc_attr( get_option( 'popup_font_color' ) ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Background Color</th>
					<td><input type="text" name="popup_background" class="color-pick regular-text"  value="<?php	echo esc_attr( get_option( 'popup_background' ) ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Background Image</th>
					<td><input type="text" name="image_url" id="image_url" class="regular-text" value="<?php echo esc_attr( get_option( 'image_url' ) ); ?>">
					<input type="button" name="upload-btn" id="upload-btn" class="button-secondary" value="Upload Image"></td>
				</tr>
			</table>
		</p>
	</div>
	<!-- End tabs -->
	<?php
	wp_enqueue_script( 'jquery' );
	wp_enqueue_media();
	submit_button();
	?>
</form>
</div>
<?php
}

function pfcf_render_popup() {
	$description		= get_option( 'popup_description' );
	$description        = trim( preg_replace( '/\s+/', ' ', $description ) );
	$background         = get_option( 'popup_background' );
	$background_image   = get_option( 'image_url' );
	$font_color         = get_option( 'popup_font_color' );
	$width              = get_option( 'popup_width' );
	$height             = get_option( 'popup_height' );
	$duration           = get_option( 'popup_duration' );
	$enabled_forms      = (get_option( 'enabled-cf7-form' )) ? get_option( 'enabled-cf7-form' ) : array(); 
	$popup_enabled_form = wp_json_encode( $enabled_forms );

	//_wpcf7

	$popup_description = ! empty( $description ) ? $description : 'Form has been submitted successfully.';
	$popup_background  = ! empty( $background ) ? $background : '#fff';
	$background_img    = ! empty( $background_image ) ? $background_image : '';
	$popup_font_color  = ! empty( $font_color ) ? $font_color : '#000000';
	$popup_width       = ! empty( $width ) ? $width : '500px';
	$popup_height      = ! empty( $height ) ? $height : 'auto';
	$popup_duration    = ! empty( $duration ) ? $duration : '100000000000';
	$return            = <<<EOT
	<script>

var popup_enabled_form = {$popup_enabled_form};
var appendthis =  ("<div class='modal-overlay js-modal-close'></div>");
var popHtml = '<div id="pfcf-popup" style="width:{$popup_width}; height: {$popup_height}; background: {$popup_background} url({$background_img}) no-repeat right top; background-size:cover; color: {$popup_font_color} !important;" class="modal-box"><a href="#" class="js-modal-close close">×</a><div class="modal-body"><span style="color:{$popup_font_color}">{$popup_description}</span></div></div>';
jQuery("body").append(popHtml);	

	jQuery(".wpcf7-submit").click(function(event) {
		
		var this_id = jQuery(this).closest('form').find('input[name=_wpcf7]').val();
		
		var need_popup = jQuery.inArray( this_id, popup_enabled_form );
		
		if( jQuery.inArray( this_id, popup_enabled_form ) > -1 ){
			
			jQuery( document ).ajaxComplete(function(event, xhr, settings) {
				var data = xhr.responseText;
				var jsonResponse = JSON.parse(data);
				if(jsonResponse["status"] === 'mail_sent')
				{
					event.preventDefault();
					jQuery("body").append(appendthis);
					jQuery(".modal-overlay").fadeTo(500, 0.7);
					jQuery('#pfcf-popup').fadeIn("pfcf-popup");
					jQuery(".wpcf7-response-output").css( "display", "none" ); 
					setTimeout(function(){
						jQuery( ".js-modal-close" ).trigger( "click" );
					}, {$popup_duration});
				}
			});
		}
	});
	</script>
	
EOT;
	echo $return;
}

add_action( 'wp_footer', 'pfcf_render_popup', 20 );
