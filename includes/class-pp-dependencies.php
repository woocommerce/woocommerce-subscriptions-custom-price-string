<?php

if ( ! class_exists( 'PP_Dependencies' ) ) :

/**
 * Prospress Dependency Checker
 *
 * Checks if WooCommerce and Subscriptions are enabled
 */
class PP_Dependencies {

	protected static $admin_notices = array();

	protected static $plugin_filenames = array();

	public static function init() {
		add_action( 'admin_notices', __CLASS__ . '::maybe_display_admin_notices' );
	}

	/** API Functions **/

	/**
	 * Check if the if the WooCommerce plugin is installed and active.
	 *
	 * @since 1.0.0
	 * @return boolean
	 */
	public static function is_woocommerce_active( $minimum_version = false ) {
		return self::is_plugin_active( 'woocommerce.php', $minimum_version, 'woocommerce_db_version' );
	}

	/**
	 * Check if the if the WooCommerce Subscriptions plugin is installed and active.
	 *
	 * @since 1.0.0
	 * @return boolean
	 */
	public static function is_subscriptions_active( $minimum_version = false ) {
		return self::is_plugin_active( 'woocommerce-subscriptions.php', $minimum_version, 'woocommerce_subscriptions_active_version' );
	}

	/** Admin Notices **/

	/**
	 * Display any admin notices about plugin dependency failures to admins in the admin area.
	 *
	 * @since 1.0.0
	 */
	public static function maybe_display_admin_notices() {
		if ( ! empty( self::$admin_notices ) && current_user_can( 'activate_plugins' ) ) {
			foreach ( self::$admin_notices as $admin_notice ) { ?>
				<div id="message" class="error">
					<p><?php
					if ( $admin_notice['version_dependency'] ) {
						printf( esc_html( '%1$s is inactive. This version of %1$s requires %2$s version %3$s or newer. %4$sPlease install or update %2$s to version %3$s or newer &raquo;%5$s' ), '<strong>' . esc_html( $admin_notice['plugin_name'] ) . '</strong>', esc_html( $admin_notice['dependency_name'] ), esc_html( $admin_notice['version_dependency'] ), '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">', '</a>' );
					} else {
						// translators: 1$-2$: opening and closing <strong> tags, 3$ plugin name, 4$ required plugin version, 5$-6$: opening and closing link tags, leads to plugins.php in admin
						printf( esc_html( '%1$s is inactive. %1$s requires %2$s to work correctly. %3$sPlease install or activate %2$s &raquo;%4$s' ), '<strong>' . esc_html( $admin_notice['plugin_name'] ) . '</strong>', esc_html( $admin_notice['dependency_name'] ) , '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">', '</a>' );
					} ?>
					</p>
				</div>
			<?php
			}
		}
	}

	/**
	 * Queue an admin notice about a plugin dependency failure.
	 *
	 * Not passed through i18n functions because we cant use a dynamic text domain.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name displayed to the store owner to identify the plugin requiring another plugin.
	 * @param string $dependency_name The required plugin that is not active, as displayed to the store owner.
	 * @param bool|string $version_dependency The minimum version of the plugin required, if any.
	 * @return boolean true if the named plugin is installed and active
	 */
	public static function enqueue_admin_notice( $plugin_name, $dependency_name, $version_dependency = false ) {
		self::$admin_notices[] = array(
			'plugin_name'        => $plugin_name,
			'dependency_name'    => $dependency_name,
			'version_dependency' => $version_dependency,
		);
	}

	/** Helper functions **/

	/**
	 * Helper function to determine whether a plugin is active in the most reliably way possible.
	 *
	 * Based on SkyVerge's WooCommerce Plugin Framework - SV_WC_Plugin class.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name plugin name, as the plugin-filename.php
	 * @param string $minimum_version (optional) Check if the plugin is active that a certain minimum version is also active.
	 * @param string $version_option_name (optional) The key used to identify the plugin's active version in the wp_options table.
	 * @return boolean true if the named plugin is installed and active
	 */
	protected static function is_plugin_active( $plugin_name, $minimum_version = false, $version_option_name = '' ) {

		if ( empty( self::$plugin_filenames ) ) {

			$active_plugins = (array) get_option( 'active_plugins', array() );

			if ( is_multisite() ) {
				$active_plugins = array_merge( $active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', array() ) ) );
			}

			foreach ( $active_plugins as $plugin ) {

				if ( self::str_exists( $plugin, '/' ) ) {

					// normal plugin name (plugin-dir/plugin-filename.php)
					list( , $filename ) = explode( '/', $plugin );

				} else {

					// no directory, just plugin file
					$filename = $plugin;

				}

				self::$plugin_filenames[] = $filename;
			}
		}

		$is_plugin_active = in_array( $plugin_name, self::$plugin_filenames );

		if ( $minimum_version ) {
			return $is_plugin_active && version_compare( get_option( $version_option_name ), $minimum_version, '>=' );
		} else {
			return $is_plugin_active;
		}
	}

	/**
	 * Returns true if the needle exists in haystack
	 *
	 * Note: case-sensitive
	 *
	 * Based on SkyVerge's WooCommerce Plugin Framework - SV_WC_Helper class.
	 *
	 * @since 1.0.0
	 * @param string $haystack
	 * @param string $needle
	 * @return bool
	 */
	protected static function str_exists( $haystack, $needle ) {

		if ( extension_loaded( 'mbstring' ) ) {

			if ( '' === $needle ) {
				return false;
			}

			return false !== mb_strpos( $haystack, $needle, 0, 'UTF-8' );

		} else {

			$needle = self::str_to_ascii( $needle );

			if ( '' === $needle ) {
				return false;
			}

			return false !== strpos( self::str_to_ascii( $haystack ), self::str_to_ascii( $needle ) );
		}
	}

	/**
	 * Returns a string with all non-ASCII characters removed. This is useful
	 * for any string functions that expect only ASCII chars and can't
	 * safely handle UTF-8. Note this only allows ASCII chars in the range
	 * 33-126 (newlines/carriage returns are stripped)
	 *
	 * Based on SkyVerge's WooCommerce Plugin Framework - SV_WC_Helper class.
	 *
	 * @since 1.0.0
	 * @param string $string string to make ASCII
	 * @return string
	 */
	protected static function str_to_ascii( $string ) {

		// strip ASCII chars 32 and under
		$string = filter_var( $string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW );

		// strip ASCII chars 127 and higher
		return filter_var( $string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH );
	}

}

PP_Dependencies::init();

endif;