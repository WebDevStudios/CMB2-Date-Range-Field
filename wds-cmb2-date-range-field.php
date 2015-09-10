<?php
/**
 * Plugin Name: CMB2 Date Range Field
 * Plugin URI:  http://webdevstudios.com
 * Description: Adds a date range field to CMB2
 * Version:     0.1.1
 * Author:      WebDevStudios
 * Author URI:  http://webdevstudios.com
 * Donate link: http://webdevstudios.com
 * License:     GPLv2
 * Text Domain: wds-cmb2-date-range-field
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2015 WebDevStudios (email : contact@webdevstudios.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


/**
 * Main initiation class
 */
class WDS_CMB2_Date_Range_Field {

	const VERSION = '0.1.1';

	protected $url      = '';
	protected $path     = '';
	protected $basename = '';

	/**
	 * Creates or returns an instance of this class.
	 * @since  0.1.0
	 * @return WDS_CMB2_Date_Range_Field A single instance of this class.
	 */
	public static function get_instance() {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new self();
		}
		$instance->hooks();

		return $instance;
	}

	/**
	 * Sets up our plugin
	 * @since  0.1.0
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );
	}

	/**
	 * Add hooks and filters
	 * @since 0.1.0
	 */
	public function hooks() {
		register_activation_hook( __FILE__, array( $this, '_activate' ) );
		register_deactivation_hook( __FILE__, array( $this, '_deactivate' ) );

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'cmb2_render_date_range', array( $this, 'render' ), 10, 5 );
		add_filter( 'cmb2_sanitize_date_range', array( $this, 'sanitize' ), 10, 2 );
	}

	/**
	 * Activate the plugin
	 * @since  0.1.0
	 */
	function _activate() {}

	/**
	 * Deactivate the plugin
	 * Uninstall routines should be in uninstall.php
	 * @since  0.1.0
	 */
	function _deactivate() {}

	/**
	 * Init hooks
	 * @since  0.1.0
	 * @return null
	 */
	public function init() {
		load_plugin_textdomain( 'wds-cmb2-date-range-field', false, dirname( $this->basename ) . '/languages/' );
	}

	/**
	 * Renders the date range field in CMB2.
	 *
	 * @param object $field         The CMB2 Field Object.
	 * @param mixed  $escaped_value The value after being escaped, by default, with sanitize_text_field.
	 */
	function render( $field, $escaped_value, $field_object_id, $field_object_type, $field_type ) {

		wp_enqueue_style( 'jquery-ui-daterangepicker', $this->url . '/assets/jquery-ui-daterangepicker/jquery.comiseo.daterangepicker.css', array(), '0.4.0' );
		wp_register_script( 'moment', $this->url . '/assets/moment.min.js', array(), '2.10.3' );
		wp_register_script( 'jquery-ui-daterangepicker', $this->url . '/assets/jquery-ui-daterangepicker/jquery.comiseo.daterangepicker.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-button', 'jquery-ui-menu', 'jquery-ui-datepicker', 'moment' ), '0.4.0' );
		wp_enqueue_script( 'cmb2-daterange-picker', $this->url . '/assets/cmb2-daterange-picker.js', array( 'jquery-ui-daterangepicker' ), self::VERSION, true );

		// CMB2_Types::parse_args allows arbitrary attributes to be added
		$a = $field_type->parse_args( array(), 'input', array(
			'type'  => 'text',
			'class' => 'date-range button-secondary',
			'name'  => $field_type->_name(),
			'id'    => $field_type->_id(),
			'desc'  => $field_type->_desc( true ),
			'data-daterange' => json_encode( array(
				'id' => '#' . $field_type->_id(),
				'buttontext' => esc_attr( $field_type->_text( 'button_text', __( 'Select date range...' ) ) ),
				'format' => $field->args( 'date_format' ) ? $field->args( 'date_format' ) : 'mm/dd/yy',
			) ),
		) );

		printf( '<div class="cmb2-element"><input%s value=\'%s\'/></div>%s', $field_type->concat_attrs( $a, array( 'desc' ) ), json_encode( $escaped_value ), $a['desc'] );
	}

	/**
	 * Convert the json array made by jquery plugin to a regular array to save to db.
	 *
	 * @param mixed $override_value A null value as a placeholder to return the modified value.
	 * @param mixed $value The non-sanitized value.
	 *
	 * @return array|mixed An array of the dates.
	 */
	function sanitize( $override_value, $value ) {

		$value = json_decode( $value, true );
		if ( is_array( $value ) ) {
			$value = array_map( 'sanitize_text_field', $value );
		} else {
			sanitize_text_field( $value );
		}

		return $value;

	}
}

/**
 * Grab the WDS_CMB2_Date_Range_Field object and return it.
 * Wrapper for WDS_CMB2_Date_Range_Field::get_instance()
 */
function wds_cmb2_date_range_field() {
	return WDS_CMB2_Date_Range_Field::get_instance();
}

// Kick it off
add_action( 'plugins_loaded', 'wds_cmb2_date_range_field' );
