<?php
/*
Plugin Name: ReactJS
Plugin URI: http://blah.com
Description: None
Version: 0.1
Author: AHWS
Author URI: http://hollysphar.net
*/
// defined blocks direct access to plugin files
defined( 'ABSPATH' ) or die( 'Plugin cannot be accessed directly.');

if ( ! class_exists( 'ReactJS' ) ) {
	class ReactJS
	{
		/**
		 * Tag identifier used by file includes and selector attributes
		 * @var string
		 */
		protected $tag = 'reactjs';

		/**
		 * List of options to determine plugin behavior.
		 * @var array
		 */
		protected $options = array();

		/*
		 * another protected var
		 */
		protected $name = 'ReactJS';

		protected $version = '0.1';

		public function __construct()
		{
			if ( $options = get_option( $this->tag ) ) {
				$this->options = $options;
			}

			add_shortcode( $this->tag, array( &$this, 'shortcode' ) );
			add_action( 'wp_enqueue_scripts', 'wpb_adding_scripts' );
			
			if( is_admin() ) {
				add_action( 'admin_init', array( &$this, 'settings' ) );
			}
		}

		public function shortcode( $atts, $content = null )
		{
			{
				extract( shortcode_atts( array(
					'height' => false,
					'class' => false
				), $atts ) );
				$styles = array();
				if ( is_numeric( $height ) ) {
					$styles[] = esc_attr( 'height: ' . $height . 'px;' );
				}
				$classes = array(
					$this->tag
				);
				if ( !empty( $class ) ) {
					$classes[] = esc_attr( $class );
				} 
				ob_start();
				?><pre class="<?php esc_attr_e( implode( ' ', $classes ) ); ?>"<?php
					echo ( count( $styles ) > 0 ? ' style="' . implode( ' ', $styles ) . '"' : '' );
				?>><p><?php echo $content; ?></p></pre><?php
				return ob_get_clean();
			}
		}

		/**
		 * List of setting sdisplayed on the admin page
		 * @var array
		 **/
		protected $settings = array(
			'typeDelay' => array(
				'description' => 'Minimum delay, in me, between typing characters.',
				'validator' => 'numeric',
				'placeholder' => 100
			),
			'cursor' => array(
				'description' => 'Character used to represent the cursor.',
				'placeholder' => '|'
			),
			'humanize' => array(
				'description' => 'Add a random delay before each character to represent ...',
				'type' => 'checkbox',
				'default' => true
			)
		);

		/**
		 * Add the settings fields to the Reading settings page.
		 * @access public
		 */
		public function settings()
		{
			$section = 'reading';
			add_settings_section(
				$this->tag . '_settings_section',
				$this->name . ' Settings',
				function () {
					echo '<p>Configuration options for the ' . esc_html( $this->name ) .' plugin.</p>';
				},
				$section
			);
			foreach ( $this->settings AS $id => $options ) {
				$options['id'] = $id;
				add_setting_field(
					$this->tag . '_' . $id . '_settings',
					$id,
					array( &$this, 'settings_field' ),
					$section,
					$this->tag . '_settings_section',
					$options
				);
			}
		}

		public function settings_field( array $options = array() )
		{
			$atts =  array(
				'id' => $this->tag . '_' . $options['id'],
				'name' => $this->tag . '[' . $options['id'] . ']',
				'type' => ( isset( $options['type'] ) ? $options['type'] : 'text' ),
				'class' => 'small-text',
				'value' => ( array_key_exists( 'default', $options ) ? $options['default'] : null )
			);
			if ( isset( $this->options[$options['id']] ) ) {
				$atts['value'] = $this->options[$options['id']];
			}
			if ( isset( $this->options['placeholder'] ) ) {
				$atts['value'] = $this->options[$options['id']];
			}
			if ( isset( $options['placeholder'] ) ) {
				$atts['placeholder'] = $options['placeholder'];
			}
			if ( isset( $options['type'] ) && $options['type'] == 'chekbox' ) {
				if ( $atts['value'] ) {
					$atts['checked'] = 'checked';
				}
				$atts['value'] = true;
			}
 		
	 		array_walk( $atts, function( &$item, $key ) {
	 			$item = esc_attr( $key ) . '="' . esc_attr( $item ) . '"';
	 		} );
	 		?>
	 		<label>
		 		<input <?php echo implode( ' ', $atts ); ?> />
		 		<?php if ( array_key_exists( 'description', $options ) ) : ?>
		 		<?php esc_html_e( $options['description'] ); ?>
		 		<?php endif; ?> 
	 		</label>
	 		<?php

	 		register_setting(
	 			$section,
	 			$this->tag,
	 			array( &$this, 'settings_validate' )
	 		);
		}

		public function settings_validate( $input )
		{

			$errors = array();
			foreach ( $input AS $key => $value ) {
				if ( $value == '' ) {
					unset( $input[$key] );
				} elseif ( isset( $this->settings[$key]['validator'] ) ) {
					switch ( $this->settings[$key]['validator'] ) {
						case 'numeric':
						  if ( is_numeric( $value ) ) {
						  	$input[$key] = intval( $value );
						  } else {
						  	$errors[] = $key . ' must be a numeric value.';
						  	unset( $input[$key] );
						  }
						break;
					}
				} else {
					$input[$key] = strip_tags( $value );
				}
			}
			if ( count( $errors ) > 0 ) {
				add_settings_error(
					$this->tag,
					$this->tag,
					implode( '<br />', $errors),
					'error'
				);
			}
			return $input;
		}

		// end class 
	}
}

// @TODO: add this to ReactjJS class and make all 'reactjs' tags $thi->tag
function wpb_adding_scripts() {
	if ( !wp_script_is( 'reactjs', 'registered') ) {
		wp_register_script('reactjs', get_template_directory_uri() . '/js/reactjs/build/react.min.js', 'jsx-transformer','', true);
		wp_register_script('react-addons', get_template_directory_uri() . '/js/reactjs/build/react-with-addons.min.js', 'react', '', true);
		wp_register_script('jsx-transformer', get_template_directory_uri() . '/js/reactjs/build/JSXTransformer.js', '', '', true);
	}
	if ( !wp_script_is( 'reactjs', 'enqueued') ) {
		wp_enqueue_script('jsx-transformer');
		wp_enqueue_script( 'reactjs' );
		wp_enqueue_script('react-addons');
	}
}
// @TODO: after moving wpb_adding_scripts to class, move this to constructor
add_action( 'wp_enqueue_scripts', 'wpb_adding_scripts' );
