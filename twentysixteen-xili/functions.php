<?php
// dev.xiligroup.com - msc - 2015-09-01 - first test with pre-release 0.1

define( 'TWENTYSIXTEEN_XILI_VER', '0.1'); // as parent style.css

function twentysixteen_xilidev_setup () {

	$theme_domain = 'twentysixteen';

	$minimum_xl_version = '2.19.3'; // >

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
		// Args dedicated to this theme named Twenty Fifteen
		$xili_args = array (
			'customize_clone_widget_containers' => true, // comment or set to true to clone widget containers
			'settings_name' => 'xili_2015_theme_options', // name of array saved in options table
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