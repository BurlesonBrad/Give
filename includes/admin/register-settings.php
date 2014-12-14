<?php
/**
 *
 * Register Settings
 *
 * Include and setup custom metaboxes and fields.
 *
 * @package    Give
 * @subpackage Admin
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       https://github.com/webdevstudios/Custom-Metaboxes-and-Fields-for-WordPress
 */

class Give_Plugin_Settings {

	/**
	 * Option key, and option page slug
	 * @var string
	 */
	private $key = 'give_settings';

	/**
	 * Array of metaboxes/fields
	 * @var array
	 */
	protected $option_metabox = array();

	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = '';

	/**
	 * Options Page hook
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Constructor
	 * @since 0.1.0
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'init' ) );

		//Customize CMB2 URL
		add_filter( 'cmb2_meta_box_url', array( $this, 'give_update_cmb_meta_box_url' ) );

		//Custom CMB2 Settings Fields
		add_action( 'cmb2_render_enabled_gateways', 'give_enabled_gateways_callback', 10, 5 );
		add_action( 'cmb2_render_default_gateway', 'give_default_gateway_callback', 10, 5 );


	}

	/**
	 * Register our setting to WP
	 * @since  1.0
	 */
	public function init() {
		register_setting( $this->key, $this->key );
	}


	/**
	 * Filter CMB2 URL
	 *
	 * @description: Required for CMB2 to properly load CSS/JS
	 *
	 * @param $url
	 *
	 * @return mixed
	 */
	public function give_update_cmb_meta_box_url( $url ) {
		//Path to Give's CMB
		return plugin_dir_url( __FILE__ ) . 'cmb2';
	}


	/**
	 * Retrieve settings tabs
	 *
	 * @since 1.0
	 * @return array $tabs
	 */
	public function give_get_settings_tabs() {

		//		$settings = give_get_registered_settings();

		$tabs             = array();
		$tabs['general']  = __( 'General', 'give' );
		$tabs['emails']   = __( 'Emails', 'give' );
		$tabs['gateways'] = __( 'Payment Gateways', 'give' );

		//		if ( ! empty( $settings['extensions'] ) ) {
		//			$tabs['extensions'] = __( 'Extensions', 'give' );
		//		}
		//		if ( ! empty( $settings['licenses'] ) ) {
		//			$tabs['licenses'] = __( 'Licenses', 'give' );
		//		}
		//
		//		$tabs['misc'] = __( 'Misc', 'give' );

		return apply_filters( 'give_settings_tabs', $tabs );
	}


	/**
	 * Admin page markup. Mostly handled by CMB2
	 * @since  1.0
	 */
	public function admin_page_display() {

		$active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $this->give_get_settings_tabs() ) ? $_GET['tab'] : 'general';

		?>

		<div class="wrap give_settings_page cmb2_options_page <?php echo $this->key; ?>">
			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( $this->give_get_settings_tabs() as $tab_id => $tab_name ) {

					$tab_url = add_query_arg( array(
						'settings-updated' => false,
						'tab'              => $tab_id
					) );

					$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

					echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );

					echo '</a>';
				}
				?>
			</h2>

			<?php cmb2_metabox_form( $this->give_settings( $active_tab ), $this->key ); ?>

		</div><!-- .wrap -->

	<?php
	}

	/**
	 * Define General Settings Metabox and field configurations.
	 *
	 * Filters are provided for each settings section to allow extensions and other plugins to add their own settings
	 *
	 * @param $active_tab
	 *
	 * @return array
	 */
	function give_settings( $active_tab ) {

		$give_settings = array(
			/**
			 * General Settings
			 */
			'general'    => apply_filters( 'give_settings_general',
				array(
					'id'       => 'give_settings_general_metabox',
					'title'    => __( 'General Settings', 'give' ),
					'context'  => 'normal',
					'priority' => 'high',
					'show_on'  => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
					'fields'   => array(
						array(
							'name' => __( 'General Settings', 'give' ),
							'desc' => '<hr>',
							'type' => 'title',
							'id'   => 'general_title'
						),
						array(
							'name' => __( 'Test Text Small', 'give' ),
							'desc' => __( 'field description (optional)', 'give' ),
							'id'   => 'test_textsmall',
							'type' => 'text_small',
							// 'repeatable' => true,
						),
						array(
							'name' => __( 'Currency Settings', 'give' ),
							'desc' => '<hr>',
							'type' => 'title',
							'id'   => 'currency_title'
						),
						array(
							'name'    => __( 'Currency', 'cmb' ),
							'desc'    => 'Choose your currency. Note that some payment gateways have currency restrictions.',
							'id'      => 'currency',
							'type'    => 'select',
							'options' => give_get_currencies(),
							'default' => 'USD',
						),
						array(
							'name'    => __( 'Currency Position', 'cmb' ),
							'desc'    => 'Choose the position of the currency sign.',
							'id'      => 'currency_position',
							'type'    => 'select',
							'options' => array(
								'before' => __( 'Before - $10', 'give' ),
								'after'  => __( 'After - 10$', 'give' )
							),
							'default' => 'before',
						),
						array(
							'name'    => __( 'Thousands Separator', 'give' ),
							'desc'    => __( 'The symbol (typically , or .) to separate thousands', 'give' ),
							'id'      => 'thousands_separator',
							'type'    => 'text_small',
							'default' => ',',
						),
						array(
							'name'    => __( 'Decimal Separator', 'give' ),
							'desc'    => __( 'The symbol (usually , or .) to separate decimal points', 'give' ),
							'id'      => 'decimal_separator',
							'type'    => 'text_small',
							'default' => '.',
						),
					)
				)
			),
			/**
			 * Emails Options
			 */
			'emails'     => apply_filters( 'give_settings_emails',
				array(
					'id'      => 'options_page',
					'title'   => __( 'Theme Options Metabox', 'give' ),
					'show_on' => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
					'fields'  => array()
				)
			),
			/**
			 * Payment Gateways
			 */
			'gateways'   => apply_filters( 'give_settings_gateways',
				array(
					'id'      => 'options_page',
					'title'   => __( 'Payment Gateways', 'give' ),
					'show_on' => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
					'fields'  => array(
						array(
							'name' => __( 'Payment Gateways', 'give' ),
							'desc' => '<hr>',
							'type' => 'title',
							'id'   => 'general_title'
						),
						array(
							'name' => __( 'Enabled Gateways', 'give' ),
							'desc' => __( 'Choose the payment gateways you want enabled.', 'give' ),
							'id'   => 'gateways',
							'type' => 'enabled_gateways'
						),
						array(
							'name' => __( 'Default Gateway', 'give' ),
							'desc' => __( 'This is the gateways that will be selected by default.', 'give' ),
							'id'   => 'default_gateway',
							'type' => 'default_gateway'
						),
						array(
							'name'    => 'Test Select',
							'desc'    => 'Select an option',
							'id'      => 'test_select',
							'type'    => 'select',
							'options' => array(
								'standard' => __( 'Option One', 'cmb' ),
								'custom'   => __( 'Option Two', 'cmb' ),
								'none'     => __( 'Option Three', 'cmb' ),
							),
							'default' => 'custom',
						),

					)
				)
			),
			/** Extension Settings */
			'extensions' => apply_filters( 'give_settings_extensions',
				array()
			),
			'licenses'   => apply_filters( 'give_settings_licenses',
				array()
			),
		);

		// Add other metaboxes as needed
		return apply_filters( 'give_registered_settings', $give_settings[ $active_tab ] );

	}


	/**
	 * Public getter method for retrieving protected/private variables
	 *
	 * @since  1.0
	 *
	 * @param  string $field Field to retrieve
	 *
	 * @return mixed          Field value or exception is thrown
	 */
	public function __get( $field ) {

		// Allowed fields to retrieve
		if ( in_array( $field, array( 'key', 'fields', 'title', 'options_page' ), true ) ) {
			return $this->{$field};
		}
		if ( 'option_metabox' === $field ) {
			return $this->option_metabox();
		}

		throw new Exception( 'Invalid property: ' . $field );
	}


}

// Get it started
$Give_Settings = new Give_Plugin_Settings();

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 *
 * @param  string $key Options array key
 *
 * @return mixed        Option value
 */
function give_get_option( $key = '', $default = false ) {
	global $give_options, $Give_Settings;
	$value = ! empty( $give_options[ $key ] ) ? $give_options[ $key ] : $default;
	$value = apply_filters( 'give_get_option', $value, $key, $default );

	return apply_filters( 'give_get_option_' . $key, $value, $key, $default );
	//	return cmb2_get_option( $Give_Settings->key, $key );
}


/**
 * Get Settings
 *
 * Retrieves all Give plugin settings
 *
 * @since 1.0
 * @return array Give settings
 */
function give_get_settings() {

	$settings = get_option( 'give_settings' );

	return apply_filters( 'give_get_settings', $settings );

}

/**
 * Gateways Callback
 *
 * Renders gateways fields.
 *
 * @since 1.0
 *
 * @global $give_options Array of all the Give Options
 * @return void
 */
function give_enabled_gateways_callback( $field_object, $escaped_value, $object_id, $object_type, $field_type_object ) {

	$id                = $field_type_object->field->args['id'];
	$field_description = $field_type_object->field->args['desc'];
	$gateways          = give_get_payment_gateways();

	echo '<ul class="cmb2-checkbox-list cmb2-list">';

	foreach ( $gateways as $key => $option ) :

		if ( is_array($escaped_value) && array_key_exists($key, $escaped_value) ) {
			$enabled = '1';
		} else {
			$enabled = null;
		}

		echo '<li><input name="' . $id . '[' . $key . ']" id="' . $id . '[' . $key . ']" type="checkbox" value="1" ' . checked( '1', $enabled, false ) . '/>&nbsp;';
		echo '<label for="' . $id . '[' . $key . ']">' . $option['admin_label'] . '</label></li>';

	endforeach;

	if ( $field_description ) {
		echo '<p class="cmb2-metabox-description">' . $field_description . '</p>';
	}

	echo '</ul>';


}

/**
 * Gateways Callback (drop down)
 *
 * Renders gateways select menu
 *
 * @since 1.0
 *
 * @param array $args         Arguments passed by the setting
 *
 * @global      $give_options Array of all the EDD Options
 * @return void
 */
function give_default_gateway_callback( $field_object, $escaped_value, $object_id, $object_type, $field_type_object ) {

	$id                = $field_type_object->field->args['id'];
	$field_description = $field_type_object->field->args['desc'];
	$gateways          = give_get_payment_gateways();

	echo '<select class="cmb2_select" name="' . $id . '" id="' . $id . '">';

	foreach ( $gateways as $key => $option ) :

		$selected = isset( $escaped_value ) ? selected( $key, $escaped_value, false ) : '';

		echo '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option['admin_label'] ) . '</option>';

	endforeach;

	echo '</select>';

	echo '<p class="cmb2-metabox-description">' . $field_description . '</p>';

}

/**
 * Get the CMB2 bootstrap!
 *
 * Super important!
 */
require_once __DIR__ . '/cmb2/init.php';