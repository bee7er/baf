<?php
defined('ABSPATH') or die('<meta http-equiv="refresh" content="0;url='.WP_OB_URL_AUTHOR.'">');

/* SECTIONS - FIELDS
------------------------------------------------------*/
if( !function_exists('offline_box_init_theme_opotion') ):
function offline_box_init_theme_opotion() {
	// 
	add_settings_section(
		'offline_box_display_section',
		'Display Options',		
		'offline_box_display_section_display',
		'offline_box-display-section'
	);

	register_setting( 'offline_box_settings','offline_box_display');
	wp_enqueue_style( 'offline-box-style-admin', WP_OB_URL_MEDIA. 'admin.css');
	
	if( preg_match('/options/i',$_SERVER['REQUEST_URI']) ){
		wp_enqueue_media();
		wp_register_script('offline-box-upload', WP_OB_URL_MEDIA. 'admin.js', array('jquery'), '1.0', true);
		wp_enqueue_script('offline-box-upload');
	}
}
endif;
add_action('admin_init', 'offline_box_init_theme_opotion');

/* CALLBACK
------------------------------------------------------*/
if( !function_exists('offline_box_setting_display') ):
function offline_box_setting_display(){
	
	extract(shortcode_atts(array(
		'enable' => 'no',
		'theme'	=> 'default',
	), (array)get_option('offline_box_display')));
	
	$file_setting 		= WP_OB_PATH_THEMES.'/'.$theme.'/setting.php';
	
?>
	<div class="wrap offline_box_settings clearfix">
		<?php screen_icon() ?>
		<h2>Offline Box</h2>
		<?php offline_box_links(); ?>
		<div class="offline_box_advanced clearfix">
			<h3>Settings</h3>
			<form action="options.php" method="post">
				<?php
					settings_fields( 'offline_box_settings' );
					submit_button();
				?>
				<h4>Enable Offline Box</h4>
				<p>
					<select name="offline_box_display[enable]" id="offline_box_display_enable">
						<option value="yesbutnotadmin" <?php echo $enable=='yesbutnotadmin'?'selected':'';?>>Yes for all but exclude Admin</option>
						<option value="yesforguest" <?php echo $enable=='yesforguest'?'selected':'';?>>Yes for Guest</option>
						<option value="yes" <?php echo $enable=='yes'?'selected':'';?>>Yes for All</option>
						<option value="no" <?php echo $enable=='no'?'selected':'';?>>No</option>
					</select>
				</p>
				<?php 
					
					
					
					// if( function_exists('offline_box_theme_settings') ) offline_box_theme_settings(); 
					
					if( file_exists($file_setting) )
						include $file_setting;
					
					submit_button(); 
					
				?>
				<h4 style="color: #f00">The "Offline Kite Box" plugin</h4>
				<ol>
					<li>Custom Color schemes</li>
					<li>Custom Background</li>
					<li>W3C validate HTML &amp; CSS</li>
					<li>Countdown</li>
					<li>Full screen</li>
					<li>Cross Browser</li>
					<li>Built with Bootstrap v3.1.1</li>
					<li>Font Awesome</li>
				</ol>
				<p><a style="color: #f00; font-size: 20px;" target="_blank" href="http://photoboxone.com/offline-kite-box/">Download Now</a></p>
			</form>
		</div>
		<?php offline_box_links(); ?>
	</div>
<?php
}
endif;

if( !function_exists('offline_box_links') ):
function offline_box_links(){
?>
	<div class="offline_box_links clearfix">
		<ul>
			<li class="first"><a target="_blank" href="http://photoboxone.com/" title="Photo Box plugin">Home</a></li>
			<li><a target="_blank" href="http://photoboxone.com/category/documents/" title="Documents Wordpress">Help</a></li>
			<li><a target="_blank" href="http://photoboxone.com/donate/">Donate</a></li>
		</ul>
	</div>
<?php
}
endif;