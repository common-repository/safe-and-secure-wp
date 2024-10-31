<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.SafeAndSecureWP.com
 * @since      1.0.0
 *
 * @package    SASWP
 * @subpackage SASWP/admin
 * @author     SafeAndSecureWP <info@SafeAndSecureWP.com>
 */

/**
 * SASWP_Admin
 */
class SASWP_Admin {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param string $plugin_name       The name of this plugin.
	 * @param string $version           The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ), 9 );
		add_action( 'admin_init', array( $this, 'register_and_build_fields' ) );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		$current_page = wp_unslash( filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING ) );
		if ( 'saswp' === $current_page || 'saswp-logview' === $current_page ) {
			wp_enqueue_style( $this->plugin_name . '-bootstrap', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name . '-datatables', plugin_dir_url( __FILE__ ) . 'css/datatables.min.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'css/saswp-admin.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$current_page = wp_unslash( filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING ) );
		if ( 'saswp' === $current_page || 'saswp-logview' === $current_page ) {
			wp_enqueue_script( $this->plugin_name . '-bootstrap', plugin_dir_url( __FILE__ ) . 'js/bootstrap.min.js', array(), $this->version, false );
			wp_enqueue_script( $this->plugin_name . '-jsviews', plugin_dir_url( __FILE__ ) . 'js/jsviews.min.js', array(), $this->version, false );
			wp_enqueue_script( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'js/saswp-admin.js', array(), $this->version, false );
		}
	}

	/**
	 * Add sidebar plugins
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		add_menu_page( $this->plugin_name, 'Safe & Secure', 'administrator', $this->plugin_name, array( $this, 'display_plugin_admin_dashboard' ), 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iODg5cHgiIGhlaWdodD0iNTI4cHgiIHZpZXdCb3g9IjAgMCA4ODkgNTI4IiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPCEtLSBHZW5lcmF0b3I6IFNrZXRjaCA1OCAoODQ2NjMpIC0gaHR0cHM6Ly9za2V0Y2guY29tIC0tPgogICAgPHRpdGxlPkFydGJvYXJkPC90aXRsZT4KICAgIDxkZXNjPkNyZWF0ZWQgd2l0aCBTa2V0Y2guPC9kZXNjPgogICAgPGcgaWQ9IkFydGJvYXJkIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4KICAgICAgICA8ZyBpZD0ibm91bl9leWUtbG9ja18yMTI4Nzg0IiBmaWxsPSIjMDAwMDAwIiBmaWxsLXJ1bGU9Im5vbnplcm8iPgogICAgICAgICAgICA8ZyBpZD0iR3JvdXAiPgogICAgICAgICAgICAgICAgPHBhdGggZD0iTTQ0NCw3MiBDMTg1LjUsNzIgMzkuNCwyOTEuMiAzOCwyOTMuNCBDMzUuNCwyOTcuNCAzNS40LDMwMi41IDM4LDMwNi41IEMzOS40LDMwOC43IDE4NS41LDUyNy45IDQ0NCw1MjcuOSBDNzAyLjUsNTI3LjkgODQ4LjYsMzA4LjcgODUwLDMwNi41IEM4NTIuNiwzMDIuNSA4NTIuNiwyOTcuNCA4NTAsMjkzLjQgQzg0OC42LDI5MS4yIDcwMi41LDcyIDQ0NCw3MiBaIE00NDQsNTA0IEMyMjMuNiw1MDQgODguNywzMzUuNiA2Mi43LDMwMCBDODguNywyNjQuMyAyMjMuMyw5NiA0NDQsOTYgQzY2NC40LDk2IDc5OS4zLDI2NC40IDgyNS4zLDMwMCBDNzk5LjMsMzM1LjcgNjY0LjgsNTA0IDQ0NCw1MDQgWiIgaWQ9IlNoYXBlIj48L3BhdGg+CiAgICAgICAgICAgICAgICA8cGF0aCBkPSJNODg3LjgsMjI1LjYgQzg4Ny43LDIyNC45IDg4Ny40LDIyNC4yIDg4Ny4xLDIyMy40IEM4ODYuOSwyMjIuNyA4ODYuNSwyMjIgODg2LDIyMS4yIEM4ODUuNiwyMjAuNiA4ODUsMjIwIDg4NC42LDIxOS40IEM4ODMuNCwyMTguMyA4ODIuMSwyMTcuNSA4ODAuNiwyMTYuOSBDODc4LjQsMjE1LjkgODc2LDIxNS43IDg3My42LDIxNi4yIEM4NzIuOSwyMTYuMyA4NzIuMiwyMTYuNiA4NzEuNCwyMTYuOSBDODcwLjcsMjE3LjEgODcwLDIxNy41IDg2OS4yLDIxOCBDODY4LjYsMjE4LjUgODY4LDIxOSA4NjcuNCwyMTkuNCBDODY2LjksMjIwIDg2Ni40LDIyMC42IDg2NiwyMjEuMiBDODY1LjUsMjIxLjkgODY1LjIsMjIyLjYgODY0LjksMjIzLjQgQzg2NC41LDIyNC4xIDg2NC4zLDIyNC44IDg2NC4yLDIyNS42IEM4NjQuMSwyMjYuNCA4NjQsMjI3LjIgODY0LDIyOCBDODY0LDIyOS42IDg2NC40LDIzMS4xIDg2NSwyMzIuNiBDODY1LjYsMjM0LjIgODY2LjQsMjM1LjQgODY3LjUsMjM2LjYgQzg2OC4xLDIzNy4xIDg2OC43LDIzNy43IDg2OS4zLDIzOCBDODcwLDIzOC41IDg3MC43LDIzOC44IDg3MS41LDIzOS4xIEM4NzIuMiwyMzkuNSA4NzIuOSwyMzkuNyA4NzMuNywyMzkuOCBDODc0LjUsMjM5LjkgODc1LjMsMjQwIDg3Ni4xLDI0MCBDODc3LjcsMjQwIDg3OS4yLDIzOS42IDg4MC43LDIzOSBDODgyLjMsMjM4LjQgODgzLjUsMjM3LjYgODg0LjcsMjM2LjUgQzg4NS44LDIzNS4zIDg4Ni42LDIzNC4xIDg4Ny4yLDIzMi41IEM4ODcuOCwyMzEuMSA4ODguMiwyMjkuNSA4ODguMiwyMjcuOSBDODg4LDIyNy4yIDg4Ny45LDIyNi40IDg4Ny44LDIyNS42IFoiIGlkPSJQYXRoIj48L3BhdGg+CiAgICAgICAgICAgICAgICA8cGF0aCBkPSJNNTE0LDI4LjQgQzUxNC41LDI4LjUgNTE1LDI4LjUgNTE1LjYsMjguNSBDNTIxLjUsMjguNSA1MjYuNiwyNC4xIDUyNy41LDE4LjEgQzUyOC4zLDExLjUgNTIzLjcsNS41IDUxNy4xLDQuNyBDNTEwLjYsMy45IDUwNC41LDguNCA1MDMuNywxNSBDNTAyLjcsMjEuNiA1MDcuNCwyNy42IDUxNCwyOC40IFoiIGlkPSJQYXRoIj48L3BhdGg+CiAgICAgICAgICAgICAgICA8cGF0aCBkPSJNMzAyLjgsNDIuNCBDMzAzLjgsNDIuNCAzMDQuOCw0Mi4zIDMwNS45LDQyIEMzMTIuMyw0MC4zIDMxNiwzMy43IDMxNC4zLDI3LjIgQzMxMi42LDIwLjggMzA2LDE3IDI5OS41LDE4LjggQzI5My4xLDIwLjUgMjg5LjQsMjcuMSAyOTEuMSwzMy40IEMyOTIuNiwzOC45IDI5Ny40LDQyLjQgMzAyLjgsNDIuNCBaIiBpZD0iUGF0aCI+PC9wYXRoPgogICAgICAgICAgICAgICAgPHBhdGggZD0iTTM3Mi44LDI4LjYgQzM3My4zLDI4LjYgMzczLjgsMjguNSAzNzQuNCwyOC41IEMzODAuOSwyNy43IDM4NS42LDIxLjcgMzg0LjcsMTUuMSBDMzgzLjksOC41IDM3Ny45LDMuOCAzNzEuMyw0LjcgQzM2NC43LDUuNyAzNjAuMSwxMS43IDM2MSwxOC4xIEMzNjEuNywyNC4xIDM2Ni44LDI4LjYgMzcyLjgsMjguNiBaIiBpZD0iUGF0aCI+PC9wYXRoPgogICAgICAgICAgICAgICAgPHBhdGggZD0iTTQ0NC4xLDI0IEM0NTAuNywyNCA0NTYuMSwxOC42IDQ1Ni4xLDEyIEM0NTYuMSw1LjQgNDUwLjcsMCA0NDQuMSwwIEM0MzcuNSwwIDQzMi4xLDUuNCA0MzIuMSwxMiBDNDMyLjEsMTguNiA0MzcuNSwyNCA0NDQuMSwyNCBaIiBpZD0iUGF0aCI+PC9wYXRoPgogICAgICAgICAgICAgICAgPHBhdGggZD0iTTU4Mi41LDQyLjEgQzU4My41LDQyLjMgNTg0LjUsNDIuNSA1ODUuNiw0Mi41IEM1OTAuOSw0Mi41IDU5NS43LDM5IDU5Ny4xLDMzLjYgQzU5OC45LDI3LjIgNTk1LjEsMjAuNiA1ODguNywxOC44IEM1ODIuMiwxNy4xIDU3NS42LDIxIDU3My45LDI3LjMgQzU3Mi4zLDMzLjcgNTc2LDQwLjMgNTgyLjUsNDIuMSBaIiBpZD0iUGF0aCI+PC9wYXRoPgogICAgICAgICAgICAgICAgPHBhdGggZD0iTTY0OC41LDY0LjggQzY1MC4xLDY1LjUgNjUxLjYsNjUuOCA2NTMuMiw2NS44IEM2NTcuOSw2NS44IDY2Mi4zLDYzIDY2NC4yLDU4LjUgQzY2Ni43LDUyLjQgNjY0LDQ1LjQgNjU3LjgsNDIuOCBDNjUxLjcsNDAuMiA2NDQuNiw0MyA2NDIuMSw0OS4yIEM2MzkuNSw1NS4yIDY0Mi40LDYyLjMgNjQ4LjUsNjQuOCBaIiBpZD0iUGF0aCI+PC9wYXRoPgogICAgICAgICAgICAgICAgPHBhdGggZD0iTTgyMSwxODIgQzgyMy40LDE4NC40IDgyNi41LDE4NS42IDgyOS42LDE4NS42IEM4MzIuNiwxODUuNiA4MzUuNiwxODQuNCA4MzgsMTgyLjEgQzg0Mi43LDE3Ny40IDg0Mi43LDE2OS45IDgzOC4xLDE2NS4yIEM4MzMuNCwxNjAuNCA4MjUuNywxNjAuNCA4MjEuMSwxNjUuMSBDODE2LjQsMTY5LjggODE2LjQsMTc3LjMgODIxLjEsMTgyIEM4MjEsMTgyIDgyMSwxODIgODIxLDE4MiBaIiBpZD0iUGF0aCI+PC9wYXRoPgogICAgICAgICAgICAgICAgPHBhdGggZD0iTTcxMC45LDk2LjIgQzcxMi44LDk3LjQgNzE1LDk3LjkgNzE3LDk3LjkgQzcyMS4xLDk3LjkgNzI1LDk1LjkgNzI3LjMsOTIgQzczMC43LDg2LjIgNzI4LjcsNzguOSA3MjMuMSw3NS42IEM3MTcuMyw3Mi4yIDcxMCw3NC4yIDcwNi43LDc5LjkgQzcwMy4zLDg1LjYgNzA1LjEsOTIuOSA3MTAuOSw5Ni4yIFoiIGlkPSJQYXRoIj48L3BhdGg+CiAgICAgICAgICAgICAgICA8cGF0aCBkPSJNNzc2LDEzOC4yIEM3NzkuNiwxMzguMiA3ODMuMiwxMzYuNSA3ODUuNSwxMzMuNSBDNzg5LjYsMTI4LjIgNzg4LjYsMTIwLjcgNzgzLjUsMTE2LjcgQzc3OC4yLDExMi42IDc3MC43LDExMy41IDc2Ni43LDExOC43IEM3NjIuNiwxMjQgNzYzLjUsMTMxLjUgNzY4LjcsMTM1LjUgQzc3MC45LDEzNy40IDc3My41LDEzOC4yIDc3NiwxMzguMiBaIiBpZD0iUGF0aCI+PC9wYXRoPgogICAgICAgICAgICAgICAgPHBhdGggZD0iTTU4LjYsMTg1LjQgQzYxLjcsMTg1LjQgNjQuNywxODQuMyA2Ny4xLDE4MS45IEM3MS44LDE3Ny4yIDcxLjgsMTY5LjUgNjcsMTY0LjkgQzYyLjMsMTYwLjIgNTQuOCwxNjAuMyA1MC4xLDE2NSBDNDUuNCwxNjkuNyA0NS40LDE3Ny4yIDUwLjEsMTgxLjkgQzUyLjQsMTg0LjMgNTUuNCwxODUuNCA1OC42LDE4NS40IFoiIGlkPSJQYXRoIj48L3BhdGg+CiAgICAgICAgICAgICAgICA8cGF0aCBkPSJNMTEyLjEsMTM4IEMxMTQuNywxMzggMTE3LjMsMTM3LjIgMTE5LjUsMTM1LjUgQzEyNC43LDEzMS40IDEyNS42LDEyMy45IDEyMS41LDExOC43IEMxMTcuNCwxMTMuNCAxMDkuOSwxMTIuNSAxMDQuNywxMTYuNSBDOTkuNCwxMjAuNiA5OC41LDEyOC4xIDEwMi41LDEzMy40IEMxMDUsMTM2LjQgMTA4LjUsMTM4IDExMi4xLDEzOCBaIiBpZD0iUGF0aCI+PC9wYXRoPgogICAgICAgICAgICAgICAgPHBhdGggZD0iTTIzNS4xLDY1LjYgQzIzNi43LDY1LjYgMjM4LjIsNjUuNCAyMzkuOCw2NC44IEMyMzkuOCw2NC43IDIzOS44LDY0LjcgMjM5LjgsNjQuNyBDMjQ1LjksNjIuMiAyNDguOCw1NS4xIDI0Ni4yLDQ5IEMyNDMuNyw0MyAyMzYuNiw0MC4xIDIzMC41LDQyLjYgQzIyNC40LDQ1LjIgMjIxLjYsNTIuMiAyMjQuMSw1OC4zIEMyMjYsNjIuOSAyMzAuNCw2NS42IDIzNS4xLDY1LjYgWiIgaWQ9IlBhdGgiPjwvcGF0aD4KICAgICAgICAgICAgICAgIDxwYXRoIGQ9Ik0xNzEuMiw5Ny44IEMxNzMuMiw5Ny44IDE3NS40LDk3LjMgMTc3LjMsOTYuMSBDMTgyLjksOTIuNyAxODQuOSw4NS40IDE4MS41LDc5LjcgQzE3OC4xLDc0LjEgMTcwLjgsNzIuMSAxNjUuMSw3NS41IEMxNTkuNSw3OC45IDE1Ny41LDg2LjIgMTYwLjksOTEuOSBDMTYzLjEsOTUuNiAxNjcuMiw5Ny44IDE3MS4yLDk3LjggWiIgaWQ9IlBhdGgiPjwvcGF0aD4KICAgICAgICAgICAgICAgIDxwYXRoIGQ9Ik0zLjUsMjE5LjUgQzIuNCwyMjAuNyAxLjYsMjIxLjkgMSwyMjMuNSBDMC40LDIyNC45IDAsMjI2LjUgMCwyMjguMSBDMCwyMzEuMiAxLjMsMjM0LjMgMy41LDIzNi42IEM1LjgsMjM4LjggOC45LDI0MC4xIDEyLDI0MC4xIEMxNS4xLDI0MC4xIDE4LjIsMjM4LjggMjAuNSwyMzYuNiBDMjIuNywyMzQuMyAyNCwyMzEuMiAyNCwyMjguMSBDMjQsMjI1IDIyLjcsMjIxLjkgMjAuNSwyMTkuNiBDMTYsMjE1IDcuOSwyMTUgMy41LDIxOS41IFoiIGlkPSJQYXRoIj48L3BhdGg+CiAgICAgICAgICAgICAgICA8cGF0aCBkPSJNNDQ0LDE0NCBDMzU4LDE0NCAyODgsMjE0IDI4OCwzMDAgQzI4OCwzODYgMzU4LDQ1NiA0NDQsNDU2IEM1MzAsNDU2IDYwMCwzODYgNjAwLDMwMCBDNjAwLDIxNCA1MzAsMTQ0IDQ0NCwxNDQgWiBNNDQ0LDQzMiBDMzcxLjIsNDMyIDMxMiwzNzIuOCAzMTIsMzAwIEMzMTIsMjI3LjIgMzcxLjIsMTY4IDQ0NCwxNjggQzUxNi44LDE2OCA1NzYsMjI3LjIgNTc2LDMwMCBDNTc2LDM3Mi44IDUxNi44LDQzMiA0NDQsNDMyIFoiIGlkPSJTaGFwZSI+PC9wYXRoPgogICAgICAgICAgICAgICAgPHBhdGggZD0iTTUwNCwyNjQgTDQ5MiwyNjQgTDQ5MiwyNDAgQzQ5MiwyMTMuNSA0NzAuNSwxOTIgNDQ0LDE5MiBDNDE3LjUsMTkyIDM5NiwyMTMuNSAzOTYsMjQwIEwzOTYsMjY0IEwzODQsMjY0IEMzNzcuNCwyNjQgMzcyLDI2OS40IDM3MiwyNzYgTDM3MiwzMjQgQzM3MiwzNjMuNyA0MDQuMywzOTYgNDQ0LDM5NiBDNDgzLjcsMzk2IDUxNiwzNjMuNyA1MTYsMzI0IEw1MTYsMjc2IEM1MTYsMjY5LjQgNTEwLjYsMjY0IDUwNCwyNjQgWiBNNDIwLDI0MCBDNDIwLDIyNi44IDQzMC44LDIxNiA0NDQsMjE2IEM0NTcuMiwyMTYgNDY4LDIyNi44IDQ2OCwyNDAgTDQ2OCwyNjQgTDQyMCwyNjQgTDQyMCwyNDAgWiBNNDkyLDMyNCBDNDkyLDM1MC41IDQ3MC41LDM3MiA0NDQsMzcyIEM0MTcuNSwzNzIgMzk2LDM1MC41IDM5NiwzMjQgTDM5NiwyODggTDQ5MiwyODggTDQ5MiwzMjQgWiIgaWQ9IlNoYXBlIj48L3BhdGg+CiAgICAgICAgICAgICAgICA8cGF0aCBkPSJNNDQ0LDMwMCBDNDM3LjQsMzAwIDQzMiwzMDUuNCA0MzIsMzEyIEM0MzIsMzE2LjQgNDM0LjUsMzIwLjEgNDM4LDMyMi4yIEw0MzgsMzM2IEM0MzgsMzM5LjMgNDQwLjcsMzQyIDQ0NCwzNDIgQzQ0Ny4zLDM0MiA0NTAsMzM5LjMgNDUwLDMzNiBMNDUwLDMyMi4yIEM0NTMuNSwzMjAuMSA0NTYsMzE2LjQgNDU2LDMxMiBDNDU2LDMwNS40IDQ1MC42LDMwMCA0NDQsMzAwIFoiIGlkPSJQYXRoIj48L3BhdGg+CiAgICAgICAgICAgIDwvZz4KICAgICAgICA8L2c+CiAgICA8L2c+Cjwvc3ZnPg==', 26 );
		add_submenu_page( $this->plugin_name, 'Safe & Secure Log Viewer', 'Log Viewer', 'administrator', $this->plugin_name . '-logview', array( $this, 'display_plugin_admin_log_viewer' ) );
	}

	/**
	 * Register the Admin Dashboard
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_dashboard() {
		require_once 'partials/' . $this->plugin_name . '-admin-display.php';
	}

	/**
	 * Register the Log Viewer
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_log_viewer() {
		require_once 'partials/' . $this->plugin_name . '-admin-logviewer-display.php';
	}

	/**
	 * Setting: saswp_license_key
	 * Setting: safe_and_secure_site_domain
	 * Setting: safe_and_secure_active_protection
	 *
	 * @return void
	 */
	public function register_and_build_fields() {
		if ( get_option( 'saswp_license_key' ) === false ) {
			update_option( 'saswp_license_key', '725f22365cc94256b66dfcdfd2375f48' );
		}

		if ( get_option( 'saswp_site_domain' ) === false ) {
			update_option( 'saswp_site_domain', saswp_current_url() );
		}

		if ( get_option( 'saswp_active_protection' ) === false ) {
			update_option( 'saswp_active_protection', 'log' );
		}

		// Enable Active Protection checks and messaging.
		add_action( 'admin_notices', 'saswp_active_protection_admin_notice' );
	}
}
