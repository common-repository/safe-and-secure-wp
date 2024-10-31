<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.SafeAndSecureWP.com
 * @since             1.0.0
 * @package           SASWP
 *
 * @wordpress-plugin
 * Plugin Name:       Safe And Secure WP
 * Plugin URI:        https://www.SafeAndSecureWP.com
 * Description:       Your WordPress site is a critical piece of your business success. Stop hackers, malicious attackers, foreign influencers from harming your site. With real-time protection and validation you’ll keep your site – and your business – safe and secure.
 * Version:           1.0.1
 * Author:            Safe And Secure WP
 * Author URI:        https://safeandsecurewp.com/about-us/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       saswp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SASWP_VERSION', '1.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-saswp-activator.php
 */
function saswp_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-saswp-activator.php';
	SASWP_Activator::activate();
}
register_activation_hook( __FILE__, 'saswp_activate' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-saswp-deactivator.php
 */
function saswp_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-saswp-deactivator.php';
	SASWP_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'saswp_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-saswp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function saswp_run() {
	$plugin = new SASWP_SASWP();
	$plugin->run();
}
saswp_run();




// --------------------------------

/**
 * Identify the current host - sanitized and filtered version.
 * If the hostname is not set then exit - this plugin requires a valid hostname for functionality.
 */
function saswp_current_url() {
	$saswp_current_url = null;
	if ( isset( $_SERVER['HTTP_HOST'] ) ) {
		$saswp_current_url .= wp_unslash( filter_input( INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_STRING ) );
	} else {
		exit;
	}
	return $saswp_current_url;
}

/**
 * Identify the user's IP Address.
 */
function saswp_user_ip_address() {
	$current_ip = null;
	if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
		$current_ip .= wp_unslash( filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_STRING ) );
	} else {
		exit;
	}
	return $current_ip;
}

/**
 * Build Javascript Ajax call to enable active protection.
 *
 * @return void
 */
function saswp_enable_protection_javascript() { ?>
	<script type="text/javascript" >

	function saswp_enable_protection_js () {
		var data = {
			'action': 'saswp_enable_protection',
			'security': '<?php echo esc_html( wp_create_nonce( 'SafeAndSecureWP-2020' ) ); ?>'
		};

		jQuery.post(ajaxurl, data, function(response) {
			// Process Response.
			jQuery ("#saswp-status").html("<p>Safe and Secure WP is running in <b>Active Protection</b> mode.  All sign in attempts will be logged and attempts from non-approved countries (US, CA, GB) will be blocked.  If you wish to switch to Logging-only mode and not actively protect this site you may <a href='javascript:saswp_disable_protection_js()''>Disable Active Protection</a>.</p>")
			jQuery ("#saswp-status").removeClass("notice-warning");
			jQuery ("#saswp-status").addClass("notice-success");
		}).done(function() {
			// Completed Condition.
		})
		.fail(function() {
			// Error Condition.
		})
		.always(function() {
			// 
		});
	}

	</script> 
	<?php
}
add_action( 'admin_footer', 'saswp_enable_protection_javascript' ); // Write our JS below here.

/**
 * Ajax handler to enable active protection for Safe and Secure WP.
 *
 * @return void
 */
function saswp_enable_protection() {
	global $wpdb; // This is how you get access to the database.

	$security = filter_input( INPUT_POST, 'security' );
	check_ajax_referer( 'SafeAndSecureWP-2020', 'security' );

	if ( 'log' === get_option( 'saswp_active_protection' ) ) {
		update_option( 'saswp_active_protection', 'protect' );
	}
}
add_action( 'wp_ajax_saswp_enable_protection', 'saswp_enable_protection' );

/**
 * Build Javascript Ajax call to enable active protection.
 *
 * @return void
 */
function saswp_disable_protection_javascript() {
	?>
	<script type="text/javascript" >

	function saswp_disable_protection_js () {
		var data = {
			'action': 'saswp_disable_protection',
			'security': '<?php echo esc_html( wp_create_nonce( 'SafeAndSecureWP-2020' ) ); ?>'
		};

		jQuery.post(ajaxurl, data, function(response) {
			// Process Response.
			jQuery ("#saswp-status").html("<p>Safe and Secure WP is <b>NOT</b> running in Active Protection mode.  Sign in attempts will still be logged but no protections against attackers will be taken.  <a href='javascript:saswp_enable_protection_js()'>Enable Active Protection</a>.</p>")
			jQuery ("#saswp-status").removeClass("notice-success");
			jQuery ("#saswp-status").addClass("notice-warning");

		}).done(function() {
			// Completed Condition.
		})
		.fail(function() {
			// Error Condition.
		})
		.always(function() {
			// 
		});
	}

	</script> 
	<?php
}
add_action( 'admin_footer', 'saswp_disable_protection_javascript' ); // Write our JS below here.

/**
 * Ajax handler to disable active protection for Safe and Secure WP.
 *
 * @return void
 */
function saswp_disable_protection() {
	global $wpdb; // This is how you get access to the database.

	$security = filter_input( INPUT_POST, 'security' );
	check_ajax_referer( 'SafeAndSecureWP-2020', 'security' );

	if ( 'protect' === get_option( 'saswp_active_protection' ) ) {
		update_option( 'saswp_active_protection', 'log' );
	}
}
add_action( 'wp_ajax_saswp_disable_protection', 'saswp_disable_protection' );

/**
 * Admin Notice for Active Protection
 *
 * If Safe and Secure WP is not in live mode put up a notice on certain admin pages to alert admin and encourage activation.
 *
 * Notification types: notice-error, notice-warning, notice-success, or notice-info.
 *
 * @return void
 */
function saswp_active_protection_admin_notice() {
	global $pagenow;
	$current_page = wp_unslash( filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING ) );

	if ( 'log' === get_option( 'saswp_active_protection' ) ) {
		if ( 'admin.php' === $pagenow || 'index.php' === $pagenow || 'plugins.php' === $pagenow ) {
			$user = wp_get_current_user();
			if ( in_array( 'administrator', (array) $user->roles, true ) ) {
				// Active Protection is disabled.  If Admin is from a protected country offer to turn it on.
				$api_url_country = 'https://beholder.azure-api.net/ip_to_country/' . saswp_user_ip_address();

				$json_country = wp_remote_get(
					$api_url_country,
					array(
						'headers' => array(
							'subscription-key' => get_option( 'saswp_license_key' ),
						),
					)
				);

				$obj_country  = json_decode( wp_remote_retrieve_body( $json_country ), true );
				$country_code = $obj_country['country']['iso_code'];

				if ( 'CA' === $country_code || 'US' === $country_code || 'GB' === $country_code ) {
					?>
					<script>
					jQuery(document).ready(function() {
						jQuery ("#saswp-status").html("<p>Safe and Secure WP is <b>NOT</b> running in Active Protection mode.  Sign in attempts will still be logged but no protections against attackers will be taken.  <a href='javascript:saswp_enable_protection_js()'>Enable Active Protection</a>.</p><p>To learn more please visit us at <a href='https://safeandsecurewp.com/?utm_source=wordpress&utm_medium=banner&utm_campaign=learn_more' target='_blank'>SafeAndSecureWP.com</a></p>")
						jQuery ("#saswp-status").addClass("notice-warning");
					});
					</script>
					<?php
				}
			}
		}
	} elseif ( 'protect' === get_option( 'saswp_active_protection' ) ) {
		if ( 'saswp' === $current_page || 'saswp-logview' === $current_page ) {
			$user = wp_get_current_user();
			if ( in_array( 'administrator', (array) $user->roles, true ) ) {
				// Active Protection is enabled.  Offer to disable it - may wish to move this to a settings page.
				?>
				<script>
				jQuery(document).ready(function() {
					jQuery ("#saswp-status").html("<p>Safe and Secure WP is running in <b>Active Protection</b> mode.  All sign in attempts will be logged and attempts from non-approved countries (US, CA, GB) will be blocked.  If you wish to switch to Logging-only mode and not actively protect this site you may <a href='javascript:saswp_disable_protection_js()''>Disable Active Protection</a>.</p><p>To learn more please visit us at <a href='https://safeandsecurewp.com/?utm_source=wordpress&utm_medium=banner&utm_campaign=learn_more' target='_blank'>SafeAndSecureWP.com</a></p>")
					jQuery ("#saswp-status").addClass("notice-success");
				});
				</script>
				<?php
			}
		}
	}
}

/**
 * Capture all authentication attempts for auditing
 *
 * @param  mixed $user                      user.
 * @param  mixed $username                  username.
 * @param  mixed $password                  password.
 */
function authenticate_override( $user, $username, $password ) {
	$result          = '';
	$severity        = '';
	$error           = new WP_Error();
	$site_domain     = get_option( 'saswp_site_domain' );
	$api_url_country = 'https://beholder.azure-api.net/ip_to_country/' . saswp_user_ip_address();
	$valid_country   = false;
	$protection      = get_option( 'saswp_active_protection' );

	$action = 'sign in';
	if ( strpos( saswp_current_url(), '/wp-login.php?loggedout=true' ) !== false ) {
		$action = 'sign out';
	}

	$json_country = wp_remote_get(
		$api_url_country,
		array(
			'headers' => array(
				'subscription-key' => get_option( 'saswp_license_key' ),
			),
		)
	);
	$obj_country  = json_decode( wp_remote_retrieve_body( $json_country ), true );
	$country_code = $obj_country['country']['iso_code'];

	if ( 'CA' === $country_code || 'US' === $country_code || 'GB' === $country_code ) {
		$valid_country = true;
	}

	if ( 'protect' === $protection ) {
		if ( $valid_country ) {
			if ( is_email( $username ) ) {
				$user = wp_authenticate_email_password( null, $username, $password );
			} else {
				$user = wp_authenticate_username_password( null, $username, $password );
			}
		} else {
			$error->add( 'sign_in_issue', __( 'Sorry, something went wrong.  Please try again.' ) );
		}
	} else {
		// Logging only mode.
		if ( is_email( $username ) ) {
			$user = wp_authenticate_email_password( null, $username, $password );
		} else {
			$user = wp_authenticate_username_password( null, $username, $password );
		}
	}

	if ( ! empty( $error->errors ) ) {
		$result   = 'invalid_country,blocked_country';
		$severity = 'WARN';
	} elseif ( ! isset( $user->errors ) ) {
		$result   = 'success';
		$severity = 'INFO';
	} else {
		if ( $user->errors['invalid_username'] ) {
			$result   = 'invalid_username';
			$severity = 'NOTIFICATION';
		} elseif ( $user->errors['incorrect_password'] ) {
			$result   = 'incorrect_password';
			$severity = 'NOTIFICATION';
		} elseif ( $user->errors['empty_username'] ) {
			$result   = 'empty_username';
			$severity = '';
		} else {
			$result   = 'error';
			$severity = 'NOTIFICATION';
		}
	}

	if ( 'INFO' === $severity || 'WARN' === $severity || 'NOTIFICATION' === $severity ) {
		$message_to_send = (object) array(
			'site'        => $site_domain,
			'action'      => $action,
			'source_url'  => saswp_current_url(),
			'user_id'     => $user->ID,
			'user_login'  => $user->user_login,
			'username'    => $username,
			'result'      => $result,
			'country'     => $country_code,
			'alert_level' => $severity,
			'timestamp'   => time(),
			'user_ip'     => saswp_user_ip_address(),
			'protection'  => $protection,
		);
		process_authentication_request( $message_to_send );
	}

	// If there are no errors allow the sign in to continue.
	if ( ! isset( $user->errors ) && ( empty( $error->errors ) ) ) {
		return $user;
	} else {
		return $error;
	}
}
add_filter( 'authenticate', 'authenticate_override', 10, 3 );
remove_action( 'authenticate', 'wp_authenticate_username_password', 20, 3 );
remove_action( 'authenticate', 'wp_authenticate_email_password', 20, 3 );

/**
 * Record action for log review.
 *
 * @param  mixed $message_to_send   JSON Document to send through to logging API.
 * @return void
 */
function process_authentication_request( $message_to_send ) {
	$site_domain = get_option( 'saswp_site_domain' );
	$api_url     = 'https://beholder.azure-api.net/authentication-list/v1/authentication-request/' . $site_domain;

	$json = wp_remote_post(
		$api_url,
		array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
				'subscription-key' => get_option( 'saswp_license_key' ),
				'Content-type'     => 'application/json; charset=utf-8',
			),
			'body'        => wp_json_encode( $message_to_send ),
		)
	);
}
