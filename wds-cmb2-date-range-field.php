<?php
/**
 * Plugin Name: CMB2 Date Range Field
 * Plugin URI:  http://webdevstudios.com
 * Description: Adds a date range field to CMB2
 * Version:     0.1.2
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

	const VERSION = '0.1.2';

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
		add_action( 'cmb2_save_field', array( $this, 'save_split_fields' ), 10, 4 );
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
	public function render( $field, $escaped_value, $field_object_id, $field_object_type, $field_type ) {

		wp_enqueue_style( 'jquery-ui-daterangepicker', $this->url . '/assets/jquery-ui-daterangepicker/jquery.comiseo.daterangepicker.css', array(), '0.4.0' );
		wp_enqueue_style( 'jquery-ui-min', $this->url . '/assets/jquery-ui.min.css', array(), '0.4.0' );
		wp_register_script( 'moment', $this->url . '/assets/moment.min.js', array(), '2.10.3' );
		wp_register_script( 'jquery-ui-daterangepicker', $this->url . '/assets/jquery-ui-daterangepicker/jquery.comiseo.daterangepicker.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-button', 'jquery-ui-menu', 'jquery-ui-datepicker', 'moment' ), '0.4.0' );
		wp_enqueue_script( 'cmb2-daterange-picker', $this->url . '/assets/cmb2-daterange-picker.js', array( 'jquery-ui-daterangepicker' ), self::VERSION, true );

		if ( version_compare( CMB2_VERSION, '2.2.2', '>=' ) ) {
			$field_type->type = new CMB2_Type_Text( $field_type );
		}

		$args = array(
			'type'  => 'text',
			'class' => 'regular-text date-range',
			'name'  => $field_type->_name(),
			'id'    => $field_type->_id(),
			'data-daterange' => json_encode( array(
				'id' => '#' . $field_type->_id(),
				'buttontext' => esc_attr( $field_type->_text( 'button_text', __( 'Select date range...' ) ) ),
			) ),
		);

		if ( $js_format = CMB2_Utils::php_to_js_dateformat( $field->args( 'date_format' ) ) ) {

			$atts = $field->args( 'attributes' );

			// Don't override user-provided datepicker values
			$data = isset( $atts['data-daterangepicker'] )
				? json_decode( $atts['data-daterangepicker'], true )
				: array();

			$data['altFormat'] = $js_format;
			$args['data-daterangepicker'] = function_exists( 'wp_json_encode' )
				? wp_json_encode( $data )
				: json_encode( $data );
		}

		// CMB2_Types::parse_args allows arbitrary attributes to be added
		$a = $field_type->parse_args( 'input', array(), $args );

		if ( $escaped_value ) {
			$escaped_value = function_exists( 'wp_json_encode' )
				? wp_json_encode( $escaped_value )
				: json_encode( $escaped_value );
		}

		printf(
			'
			<div class="cmb2-element"><input%1$s value=%2$s /><div id="%3$s-spinner" style="float:none;" class="spinner"></div></div>%4$s
			<script type="text/javascript">
				document.getElementById( \'%3$s\' ).setAttribute( \'type\', \'hidden\' );
				document.getElementById( \'%3$s-spinner\' ).setAttribute( \'class\', \'spinner is-active\' );
			</script>
			',
			$field_type->concat_attrs( $a, array( 'desc' ) ),
			$escaped_value,
			$field_type->_id(),
			$a['desc']
		);
		?>
		<?php

	}

	/**
	 * Convert the json array made by jquery plugin to a regular array to save to db.
	 *
	 * @param mixed $override_value A null value as a placeholder to return the modified value.
	 * @param mixed $value The non-sanitized value.
	 *
	 * @return array|mixed An array of the dates.
	 */
	public function sanitize( $override_value, $value ) {

		if ( ! is_array(  $value  ) ) {
			return $check;
		}

		foreach ( $value as $key => $value ) {
			$value[ $key ] = array_filter( array_map( 'sanitize_text_field', $value ) );
		}

		return array_filter( $value );
	}

	/**
	 * Save the start date and end date into separate fields
	 *
	 * @param string            $field_id The current field id paramater.
	 * @param bool              $updated  Whether the metadata update action occurred.
	 * @param string            $action   Action performed. Could be "repeatable", "updated", or "removed".
	 */
	public function save_split_fields( $field_id, $updated, $action, $cmb2_field ) {

		$value = $cmb2_field->value;
		$object_id  = $cmb2_field->object_id;

		$start_date_key = $field_id . '_start';
		$end_date_key   = $field_id . '_end';

		$split_values = $cmb2_field->args( 'split_values' );
	
		$decoded_value = json_decode($value);

		$start = $decoded_value->start;
		$end = $decoded_value->end;

		if ($action == 'removed') {

			delete_post_meta( $object_id,  $start_date_key, $start );
			delete_post_meta( $object_id,  $end_date_key, $end );
		
		}
		elseif($split_values) {

			update_post_meta( $object_id,  $start_date_key, $start );
			update_post_meta( $object_id,  $end_date_key, $end );

		}
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
