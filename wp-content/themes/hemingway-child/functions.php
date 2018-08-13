<?php

include_once realpath(__DIR__ . "/../../bafdev/config/config.php");
include_once realpath(__DIR__ . "/../../bafdev/associationManagement/SiteAdministrator.php");

/*****
$result = add_role(
'society_admin',
__( 'Allotment Society Administrator' ),
array(
'read'         => true,  // true allows this capability
'edit_posts'   => true,
'delete_posts' => true, // Use false to explicitly deny
)
);
if ( null !== $result ) {
echo 'Yay! New role created!';
}
else {
echo 'Oh... the society_admin role already exists.';
}
******/
/*****
$result = add_role(
'association_admin',
__( 'Allotment Association Administrator' ),
array(
'read'         => true,  // true allows this capability
'edit_posts'   => true,
'delete_posts' => true, // Use false to explicitly deny
)
);
if ( null !== $result ) {
echo 'Yay! New role created!';
}
else {
echo 'Oh... the association_admin role already exists.';
}
******/
/******
$result = add_role(
'baf_committee_view',
__( 'BAF Committee View Access' ),
array(
'read'         => true,  // true allows this capability
'edit_posts'   => true,
'delete_posts' => true, // Use false to explicitly deny
)
);
if ( null !== $result ) {
echo 'Yay! New role created!';
}
else {
echo 'The baf_committee_view role already exists.';
}

$result = add_role(
'baf_committee_full',
__( 'BAF Committee Full Access' ),
array(
'read'         => true,  // true allows this capability
'edit_posts'   => true,
'delete_posts' => true, // Use false to explicitly deny
)
);
if ( null !== $result ) {
echo 'Yay! New role created!';
}
else {
echo 'The baf_committee_full role already exists.';
}
******/

/**
 * Child theme added by Brian Etheridge
 * http://www.brianetheridge.com
 */
// See: https://codex.wordpress.org/Child_Themes
// Override styles
function my_theme_enqueue_styles() {

	$parent_style = 'hemingway_style'; // This is the style name for the parent theme.

	wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( $parent_style ),
		wp_get_theme()->get('Version')
	);
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

/**
 * Redirect to the home page when a user logs out.
 */
add_action('wp_logout','go_home');
function go_home(){
	wp_redirect( home_url() );
	exit();
}

/**
 * Start a session if it hasn't already started.
 */
function register_my_session(){
	if( !session_id() ){
		session_start();
	}
}
add_action('init', 'register_my_session');

/**
 * Function to change the 'from' email address.
 */
function wpb_sender_email( $original_email_address ) {
    //return 'secretary@barnetallotments.org.uk';
	return 'webeditor@barnetallotments.org.uk';
}
// Function to change sender name
function wpb_sender_name( $original_email_from ) {
    //return 'BAF Secretary';
	return 'BAF Web Editor';
}
// Hooking up our functions to WordPress filters
add_filter( 'wp_mail_from', 'wpb_sender_email' );
add_filter( 'wp_mail_from_name', 'wpb_sender_name' );

//========================================================
/**
 * Simple helper function for make menu item objects
 *
 * @param $title      - menu item title
 * @param $url        - menu item url
 * @param $order      - where the item should appear in the menu
 * @param int $parent - the item's parent item
 * @return \stdClass
 */
function _custom_nav_menu_item( $title, $url, $order, $parent = 0 ){
	$item = new stdClass();
	$item->ID = 1000000 + $order + parent;
	$item->db_id = $item->ID;
	$item->title = $title;
	$item->url = $url;
	$item->menu_order = $order;
	$item->menu_item_parent = $parent;
	$item->type = '';
	$item->object = '';
	$item->object_id = '';
	$item->classes = array();
	$item->target = '';
	$item->attr_title = '';
	$item->description = '';
	$item->xfn = '';
	$item->status = '';
	return $item;
}
add_filter('wp_get_nav_menu_items', 'custom_nav_menu_items', 20, 2);

function custom_nav_menu_items( $items, $menu )
{
	// add item to a specific menu
	if ($menu->slug == 'top-menu'){
		// Check if the current user is logged in and admin capable
		$user = wp_get_current_user();
		$isSiteAdmin = SiteAdministrator::isAdministrator($user);
		if ($isSiteAdmin) {
			foreach ($items as $key => $menuOption) {
				if ($menuOption->title == 'Update site information') {
					// Add custom menu options for Allotment Sites
					// Get allotments in descending order as we reverse them when adding
					$allotments = SiteAdministrator::getAllotmentListByAdmin(
						$user->user_login,
						'`site-name` DESC'
					);

					if ($allotments) {
						$index = 0;
						foreach ($allotments as $allotment) {
							$newElem = clone $menuOption;
							$newElem->ID += (10000 + $index);
							$newElem->db_id = $newElem->ID;
							$newElem->menu_order = $newElem->ID;
							$newElem->title = ($allotment['site-name'] . ' Site Details');
							$newElem->url = (APP_DIR . '/allotment-finder-details/?allotment_id=' . $allotment['id']);
							// Copy the menu option and insert new one
							array_splice($items, $key, 0, [$newElem]);
							$index++;
						}
					}
				}
			}
//			print '<pre/>';print_r($items);die;
		}
		$isSocietyAdmin = SocietyAdministrator::isAdministrator($user);
		if ($isSocietyAdmin) {
			foreach ($items as $key => $menuOption) {
				if ($menuOption->title == 'Update site information') {
					// Add custom menu options for Allotment Societies
					// Get allotments in descending order as we reverse them when adding
					$societies = SocietyAdministrator::getSocietyListByAdmin(
						$user->user_login,
						'`society-name` DESC'
					);

					if ($societies) {
						$index = 0;
						foreach ($societies as $society) {
							$newElem = clone $menuOption;
							$newElem->ID += (20000 + $index);
							$newElem->db_id = $newElem->ID;
							$newElem->menu_order = $newElem->ID;
							$newElem->title = ($society['society-name'] . ' Society Details');
							$newElem->url = (APP_DIR . '/allotment-society-finder-details/?society_id=' .
								$society['id']);
							// Copy the menu option and insert new one
							array_splice($items, $key, 0, [$newElem]);
							$index++;
						}
					}
					break;
				}
			}
//			print '<pre/>';print_r($items);die;
		}
	}

	return $items;
}

//========================================================

// Add variable names here to add custom query string variables
function add_query_vars($aVars) {
	$aVars[] = "allotment_id";
	$aVars[] = "society_id";
	return $aVars;
}

// Hook add_query_vars function into query_vars
add_filter('query_vars', 'add_query_vars');

// Does not seem to be needed

//function add_rewrite_rules($aRules) {
//	$aNewRules = array(
//		'allotment-finder-details/([^/]+)/([^/]+)/?$'
//			=> 'index.php?pagename=allotment-finder-details&allotment_id=$matches[1]',
//		'allotment-society-finder-details/([^/]+)/([^/]+)/?$'
//			=> 'index.php?pagename=allotment-society-finder-details&society_id=$matches[1]'
//	);
//
//	$aRules = $aNewRules + $aRules;
//	return $aRules;
//}
//
//// hook add_rewrite_rules function into rewrite_rules_array
//add_filter('rewrite_rules_array', 'add_rewrite_rules');
