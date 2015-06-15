<?php
/**
 * Plugin Name: Chill Mode
 * Description: Simple Wordpress maintenance mode plugin with custom message display. Just add water and activate.
 * Original Author: Johannes Reis (https://github.com/wpdocde/slim-maintenance-mode)
 * Version: 0.2
 */

/**
 * Avoid direct calls
*/
defined('ABSPATH') or die("No direct requests for security reasons.");

/**
 * Activation and deactivation with Cache Support
*/

function chillModeActivation()  {
  if ( ! current_user_can( 'activate_plugins' ) )
  return;
  $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
  check_admin_referer( "activate-plugin_{$plugin}" );

    // Clear Cachify Cache
    if ( has_action('cachify_flush_cache') ) {
    do_action('cachify_flush_cache');
    }

    // Clear Super Cache
    if ( function_exists( 'wp_cache_clear_cache' ) ) {
    ob_end_clean();
    wp_cache_clear_cache();
    }

    // Clear W3 Total Cache
    if ( function_exists( 'w3tc_pgcache_flush' ) ) {
    ob_end_clean();
    w3tc_pgcache_flush();
    }
}

function chillModeDeactivation() {
  if ( ! current_user_can( 'activate_plugins' ) )
  return;
  $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
  check_admin_referer( "deactivate-plugin_{$plugin}" );

    // Clear Cachify Cache
    if ( has_action('cachify_flush_cache') ) {
    do_action('cachify_flush_cache');
    }

    // Clear Super Cache
    if ( function_exists( 'wp_cache_clear_cache' ) ) {
    ob_end_clean();
    wp_cache_clear_cache();
    }

    // Clear W3 Total Cache
    if ( function_exists( 'w3tc_pgcache_flush' ) ) {
    ob_end_clean();
    w3tc_pgcache_flush();
  }
}

register_activation_hook(   __FILE__, 'chillModeActivation' );
register_deactivation_hook( __FILE__, 'chillModeDeactivation' );

/*
 * Alert message when active
*/
function chillModeAdminAlert() {
  echo '<div id="message" class="error fade"><p>' . __( '<strong>Maintenance mode</strong> is <strong>active</strong>!', 'chill-mode' ) . ' <a href="plugins.php#maintenance-mode">' . __( 'Deactivate it, when work is done.', 'chill-mode' ) . '</a></p></div>';
}
if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
add_action( 'network_admin_notices', 'chillModeAdminAlert' );
add_action( 'admin_notices', 'chillModeAdminAlert' );
add_filter( 'login_message', function() {
  return '<div id="login_error">' . __( '<strong>Maintenance mode</strong> is active!', 'chill-mode' ) . '</div>'; }
);

/*
 * Admin Menu Class
*/

class chillMode {
  function __construct() {
    add_action('admin_menu', array(&$this,'chillModeAdminActions'));
  }

  function chillModeAdminActions() {
    add_options_page( 'Chill Mode', 'Chill Mode', 'administrator', 'chill_mode', array(&$this,'chillModeAdminOptions'));
  }

  function chillModeAdminOptions() {
    include('chill-mode-admin.php');
  }
}

new chillMode();

/*
 * Maintenance message when active
*/

function wp_killer($heading, $message, $title, $styles, $scripts) {
  nocache_headers();
  if(!current_user_can('edit_themes') || !is_user_logged_in()) {

    if (!function_exists('http_response_code')) {
      function http_response_code($newcode = NULL) {
        static $code = 503;
        if($newcode !== NULL) {
          header('X-PHP-Response-Code: '.$newcode, true, $newcode);
          if(!headers_sent())
            $code = $newcode;
        }
        return $code;
      }
    }

    $errorTemplate = 'template.php';
    require_once($errorTemplate);
    die();
  }
}

/*
 * Get admin options for use on front end when active
*/

function chillMode() {

  $pageTitle = get_option('chillModeTitle') ? get_option('chillModeTitle') : 'We\'ll be right back.';
  $pageHeading = get_option('chillModeHeading') ? get_option('chillModeHeading') : 'Undergoing Maintenance.';
  $pageMessage = get_option('chillModeMessage') ? get_option('chillModeMessage') : 'Hang tight. We\'ll be right back.';
  $pageStyles = get_option('chillModeStyling') ? get_option('chillModeStyling') : '';
  $pageScripts = get_option('chillModeScripts') ? get_option('chillModeScripts') : '';

  return wp_killer($pageHeading, $pageMessage, $pageTitle, $pageStyles, $pageScripts);
}

add_action('get_header', 'chillMode');

?>
