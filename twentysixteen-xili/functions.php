<?php
// dev.xiligroup.com - msc - 2015-09-01 - first test with pre-release 0.1

define( 'TWENTYSIXTEEN_XILI_VER', '1.1'); // as parent style.css

function twentysixteen_xilidev_setup () {

	$theme_domain = 'twentysixteen';

	$minimum_xl_version = '2.21.0'; // >

	$xl_required_version = false;

	load_theme_textdomain( $theme_domain, get_stylesheet_directory() . '/langs' ); // now use .mo of child

	if ( class_exists('xili_language') ) { // if temporary disabled

		$xl_required_version = version_compare ( XILILANGUAGE_VER, $minimum_xl_version, '>' );

		global $xili_language;

		$xili_language_includes_folder = $xili_language->plugin_path .'xili-includes';

		$xili_functionsfolder = get_stylesheet_directory() . '/functions-xili' ;

		if ( file_exists( $xili_functionsfolder . '/multilingual-functions.php') ) {
			require_once ( $xili_functionsfolder . '/multilingual-functions.php' );
		}

		global $xili_language_theme_options ; // used on both side
		// Args dedicated to this theme named Twenty Sixteen
		$xili_args = array (
			'customize_clone_widget_containers' => true, // comment or set to true to clone widget containers
			'settings_name' => 'xili_2016_theme_options', // name of array saved in options table
			'theme_name' => 'Twenty Sixteen',
			'theme_domain' => $theme_domain,
			'child_version' => TWENTYSIXTEEN_XILI_VER
		);

		add_action( 'widgets_init', 'twentysixteen_xili_add_widgets' );

		// new in WP 4.1 - now in XL 2.17.1
		if ( !has_filter ( 'get_the_archive_description', array($xili_language, 'get_the_archive_description' ) ) ) {
			add_filter ( 'get_the_archive_description', 'xili_get_the_archive_description' );
		}

		if ( is_admin() ) {

		// Admin args dedicaced to this theme

			$xili_admin_args = array_merge ( $xili_args, array (
				'customize_adds' => true, // add settings in customize page
				'customize_addmenu' => false,
				'capability' => 'edit_theme_options',
				'authoring_options_admin' => false
			) );

			if ( class_exists ( 'xili_language_theme_options_admin' ) ) {
				$xili_language_theme_options = new xili_language_theme_options_admin ( $xili_admin_args );
				$class_ok = true ;
			} else {
				$class_ok = false ;
			}


		} else { // visitors side - frontend

			if ( class_exists ( 'xili_language_theme_options' ) ) {
				$xili_language_theme_options = new xili_language_theme_options ( $xili_args );
				$class_ok = true ;
			} else {
				$class_ok = false ;
			}
		}
		// new ways to add parameters in authoring propagation
		add_theme_support('xiliml-authoring-rules', array (
			'post_format' => array('default' => '1',
				'data' => 'attribute',
				'hidden' => '1',
				'name' => 'Post Format',
				/* translators: added in child functions by xili */
				'description' => __('Will copy post_format in the future translated posts', 'twentysixteen')
			),
			'post_content' => array('default' => '',
				'data' => 'post',
				'hidden' => '',
				'name' => 'Post Content',
				/* translators: added in child functions by xili */
				'description' => __('Will copy content in the future translated post', 'twentysixteen')
			),
			'post_parent' => array('default' => '1', // (checked)
				'data' => 'post',
				'name' => 'Post Parent',
				'hidden' => '', // checkbox not visible in dashboard UI
				/* translators: added in child functions by xili */
				'description' => __('Will copy translated parent id (if original has parent and translated parent)!', 'twentysixteen')
			))
		); //

		if ( $class_ok ) {
			$xili_theme_options = get_theme_xili_options() ;
			// to collect checked value in xili-options of theme
			if ( file_exists( $xili_functionsfolder . '/multilingual-permalinks.php') && $xili_language->is_permalink && isset( $xili_theme_options['perma_ok'] ) && $xili_theme_options['perma_ok']) {
				require_once ( $xili_functionsfolder . '/multilingual-permalinks.php' ); // require subscribing premium services
			}
			if ( $xl_required_version ) { // msg choice is inside class
				$msg = $xili_language_theme_options->child_installation_msg( $xl_required_version, $minimum_xl_version, $class_ok );
			} else {
				$msg = '
				<div class="error">'.
					/* translators: added in child functions by xili */
					'<p>' . sprintf ( __('The %1$s child theme requires xili_language version more recent than %2$s installed', 'twentysixteen' ), get_option( 'current_theme' ), $minimum_xl_version ).'</p>
				</div>';

			}
		} else {

			$msg = '
			<div class="error">'.
				/* translators: added in child functions by xili */
				'<p>' . sprintf ( __('The %s child theme requires xili_language_theme_options class installed and activated', 'twentysixteen' ), get_option( 'current_theme' ) ).'</p>
			</div>';

		}

	} else {

		$msg = '
		<div class="error">'.
			/* translators: added in child functions by xili */
			'<p>' . sprintf ( __('The %s child theme requires xili-language plugin installed and activated', 'twentysixteen' ), get_option( 'current_theme' ) ).'</p>
		</div>';

	}

	// errors and installation informations
	// after activation and in themes list
	if ( isset( $_GET['activated'] ) || ( ! isset( $_GET['activated'] ) && ( ! $xl_required_version || ! $class_ok ) ) )
		add_action( 'admin_notices', $c = create_function( '', 'echo "' . addcslashes( $msg, '"' ) . '";' ) );

	// end errors...
	add_filter( 'pre_option_link_manager_enabled', '__return_true' ); // comment this line if you don't want links/bookmarks features

	//remove_filter( 'walker_nav_menu_start_el', 'twentysixteen_nav_description');

}
add_action( 'after_setup_theme', 'twentysixteen_xilidev_setup', 11 ); // called after parent

if ( class_exists('xili_language') ) { // if temporary disabled
	add_action ('after_setup_theme', 'theme_mod_create_array', 11, 1 );

	function theme_mod_create_array () {
		global $xili_language;
		if ( method_exists( $xili_language, 'set_theme_mod_to_be_filtered' ) ) // version 2.18.2
			$xili_language->set_theme_mod_to_be_filtered( 'copyright' ); // used in footer
	}
}

function twentysixteen_xili_add_widgets () {
	register_widget( 'xili_Widget_Categories' ); // in xili-language-widgets.php since 2.16.3
}

function twentysixteen_xili_header_image () {

	$header_image_url = get_header_image();

	$text_color = get_header_textcolor();

	// If no custom options for text are set, let's bail.
	if ( empty( $header_image_url ) )
		return;

	// If we get this far, we have custom styles.

		if ( ! empty( $header_image_url ) ) :
			$header_image_width = get_custom_header()->width; // default values
			$header_image_height = get_custom_header()->height;
			if ( class_exists ( 'xili_language' ) ) {
				$xili_theme_options = get_theme_xili_options() ;
				if ( isset ( $xili_theme_options['xl_header'] ) && $xili_theme_options['xl_header'] ) {
				global $xili_language, $xili_language_theme_options ;
				// check if image exists in current language
				// 2013-10-10 - Tiago suggestion
				$curlangslug = ( '' == the_curlang() ) ? strtolower( $xili_language->default_lang ) : the_curlang() ;


					$headers = get_uploaded_header_images(); // search in uploaded header list

					$this_default_headers = $xili_language_theme_options->get_processed_default_headers () ;
					if ( ! empty( $this_default_headers ) ) {
						$headers = array_merge( $this_default_headers, $headers );
					}
					foreach ( $headers as $header_key => $header ) {

						if ( isset ( $xili_theme_options['xl_header_list'][$curlangslug] ) && $header_key == $xili_theme_options['xl_header_list'][$curlangslug] ) {
							$header_image_url = $header['url'];

							$header_image_width = ( isset($header['width']) ) ? $header['width']: get_custom_header()->width;
							$header_image_height = ( isset($header['height']) ) ? $header['height']: get_custom_header()->height; // not in default (but in uploaded)

							break ;
						}
					}
				}
			}
	?>

	<div class="header-image">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<img src="<?php echo $header_image_url; ?>" width="<?php echo esc_attr($header_image_width); ?>" height="<?php echo esc_attr($header_image_height); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" />
		</a>
	</div>

	<?php endif;
}

function twentysixteen_xilidev_setup_custom_header () {

	// %2$s = in child
	//

	register_default_headers( array(
		'xili2016' => array(

			'url'			=> '%2$s/images/headers/xili2016-h1.jpg',
			'thumbnail_url' => '%2$s/images/headers/xili2016-h1-thumb.jpg',
			/* translators: added in child functions by xili */
			'description'	=> _x( '2016 by xili', 'header image description', 'twentysixteen' )
			),
		'xili2016-2' => array(

			'url'			=> '%2$s/images/headers/xili2016-h2.jpg',
			'thumbnail_url' => '%2$s/images/headers/xili2016-h2-thumb.jpg',
			/* translators: added in child functions by xili */
			'description'	=> _x( '2016.2 by xili', 'header image description', 'twentysixteen' )
			)
		)
	);

	$args = array(
		// Text color and image (empty to use none).
		'default-text-color'	=> 'fffff0', // diff of parent
		'default-image'			=> '%2$s/images/headers/xili2016-h1.jpg',

		// Set height and width, with a maximum value for the width.
		'height'				=> 280,
		'width'					=> 1200,
	);

	add_theme_support( 'custom-header', $args ); // need 8 in add_action to overhide parent

}
add_action( 'after_setup_theme', 'twentysixteen_xilidev_setup_custom_header', 12 ); // 12 - child translation is active



function twentysixteen_xili_credits () {
	/* translators: added in child functions by xili */
	printf( __( 'Multilingual child theme of Twenty Sixteen by %1$s and %2$s', 'twentysixteen' ),
		"<a href=\"http://dev.xiligroup.com\">dev.xiligroup</a>",
		'<span class="site-copyright">' . get_theme_mod('copyright', __('My company','twentysixteen') ) . '</span>'
		) ;
}
add_action ('twentysixteen_credits', 'twentysixteen_xili_credits');


// Admin side
// example with theme_mod_copyright in customizer (filter in xl 2.18.2)

/**
 * Customizer additions.
 *
 * @since
 */
require get_stylesheet_directory() . '/inc/customizer.php';


?>