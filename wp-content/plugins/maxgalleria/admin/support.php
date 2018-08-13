<?php
global $maxgalleria;
$common = $maxgalleria->common;

$theme = wp_get_theme();
$browser = $common->get_browser();
?>

<div id="maxgalleria-admin">
	<div class="wrap">
		<div class="icon32">
			<a href="http://maxgalleria.com" target="_blank"><img src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/maxgalleria-icon-32.png" alt="MaxGalleria" /></a>
		</div>
		
		<h2 class="title"><?php _e('MaxGalleria: Support', 'maxgalleria') ?></h2>
		
		<div class="clear"></div>
		
		<div class="section">
			<div class="inside">
				<h4 style="margin-top: 0px;"><?php printf(__('Support for the core plugin is handled through the %splugin repository%s.', 'maxgalleria'), '<a href="http://wordpress.org/support/plugin/maxgalleria" target="_blank">', '</a>') ?></h4>
				<h4><?php printf(__('Support for any of the addons are handled through the MaxGalleria %ssupport forums%s.', 'maxgalleria'), '<a href="https://maxgalleria.com/forums/" target="_blank">', '</a>') ?></h4>
				<h4><?php _e('You may be asked to provide the information below to help troubleshoot your issue.', 'maxgalleria') ?></h4>
				
				<textarea class="system-info" readonly="readonly" wrap="off">
----- Begin System Info -----

WordPress Version:      <?php echo get_bloginfo('version') . "\n"; ?>
PHP Version:            <?php echo PHP_VERSION . "\n"; ?>
MySQL Version:          <?php 
														global $wpdb;
														$mysql_version = $wpdb->db_version();

														echo $mysql_version . "\n"; 
?>
Web Server:             <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

WordPress URL:          <?php echo get_bloginfo('wpurl') . "\n"; ?>
Home URL:               <?php echo get_bloginfo('url') . "\n"; ?>

PHP cURL Support:       <?php echo (function_exists('curl_init')) ? 'Yes' . "\n" : 'No' . "\n"; ?>
PHP GD Support:         <?php echo (function_exists('gd_info')) ? 'Yes' . "\n" : 'No' . "\n"; ?>
PHP Memory Limit:       <?php echo ini_get('memory_limit') . "\n"; ?>
PHP Post Max Size:      <?php echo ini_get('post_max_size') . "\n"; ?>
PHP Upload Max Size:    <?php echo ini_get('upload_max_filesize') . "\n"; ?>

WP_DEBUG:               <?php echo defined('WP_DEBUG') ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>
Multi-Site Active:      <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>

Operating System:       <?php echo $browser['platform'] . "\n"; ?>
Browser:                <?php echo $browser['name'] . ' ' . $browser['version'] . "\n"; ?>
User Agent:             <?php echo $browser['user_agent'] . "\n"; ?>

Active Theme:
- <?php echo $theme->get('Name') ?> <?php echo $theme->get('Version') . "\n"; ?>
  <?php echo $theme->get('ThemeURI') . "\n"; ?>

Active Plugins:
<?php
$plugins = get_plugins();
$active_plugins = get_option('active_plugins', array());

foreach ($plugins as $plugin_path => $plugin) {
	
	// Only show active plugins
	if (in_array($plugin_path, $active_plugins)) {
		echo '- ' . $plugin['Name'] . ' ' . $plugin['Version'] . "\n";
	
		if (isset($plugin['PluginURI'])) {
			echo '  ' . $plugin['PluginURI'] . "\n";
		}
		
		echo "\n";
	}
}
?>
----- End System Info -----
				</textarea>
			</div>
		</div>
	</div>
</div>
