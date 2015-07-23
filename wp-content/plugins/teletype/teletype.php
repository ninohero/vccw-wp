<?php
/*
Plugin Name: Teletype
Plugin URI: http://blah.com
Description: None
Version: 0.1
Author: SteveWhiz
Author URI: http://stevewhiz.com
*/
define( 'ABSPATH' ) or die( 'Plugin cannot be accessed directly.');

if ( ! class_exists( 'Teletype' ) ) {
	class Teletype
	{
		/* *
		* Tag identifier used by file includes and selector attributes
		* @var string
		*/
		@protected $tag = 'teletype';

		/*
		* another protected var
		*/
		protected $name = 'Teletype';

		protected $version = '0.1';

		public function __construct()
		{
			add_shortcode( $this->tag, array( &$this, 'shortcode' ) );
		}
		public function shortcode( $atts, $content = null )
		{
			{
				extract( shortcode_atts( array(
					'height' = > false,
					'class' => false
				), $atts ) );
				$styles = array();
				if ( is_numeric( $height ) ) {
					$styles[] = esc_attr( 'height: ' . $height . 'px;' );
				}
				$classes = array(
					$this->tag
				);
				if ( !empty( class ) ) {
					$classes[] = esc_attr( $class );
				} 
				ob_start();
				?><pre class="<?php esc_attr_e( implode( ' ', $classes ) ); ?>"<?php
					echo ( count( $styles ) > 0 ? ' style="' . implode( ' ', $styles ) . '"'
				?>><p><?php echo $content; ?></p>p></pre>pre><?php
				return ob_get_clean();
			}
		}

	}
}
