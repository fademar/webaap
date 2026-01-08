<?php
/**
 * msh-webaap functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package msh-webaap
 */

if ( ! defined( 'MSH_WEBAAP_VERSION' ) ) {
	/*
	 * Set the theme’s version number.
	 *
	 * This is used primarily for cache busting. If you use `npm run bundle`
	 * to create your production build, the value below will be replaced in the
	 * generated zip file with a timestamp, converted to base 36.
	 */
	define( 'MSH_WEBAAP_VERSION', '0.1.0' );
}

if ( ! defined( 'MSH_WEBAAP_TYPOGRAPHY_CLASSES' ) ) {
	/*
	 * Set Tailwind Typography classes for the front end, block editor and
	 * classic editor using the constant below.
	 *
	 * For the front end, these classes are added by the `msh_webaap_content_class`
	 * function. You will see that function used everywhere an `entry-content`
	 * or `page-content` class has been added to a wrapper element.
	 *
	 * For the block editor, these classes are converted to a JavaScript array
	 * and then used by the `./javascript/block-editor.js` file, which adds
	 * them to the appropriate elements in the block editor (and adds them
	 * again when they’re removed.)
	 *
	 * For the classic editor (and anything using TinyMCE, like Advanced Custom
	 * Fields), these classes are added to TinyMCE’s body class when it
	 * initializes.
	 */
	define(
		'MSH_WEBAAP_TYPOGRAPHY_CLASSES',
		'prose prose-neutral max-w-none prose-a:text-primary'
	);
}

if ( ! function_exists( 'msh_webaap_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function msh_webaap_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on msh-webaap, use a find and replace
		 * to change 'msh-webaap' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'msh-webaap', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in two locations.
		register_nav_menus(
			array(
				'primary' => __( 'Primary', 'msh-webaap' ),
				'footer' => __( 'Footer Menu', 'msh-webaap' ),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
				'post-formats',
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		// Add support for editor styles.
		add_theme_support( 'editor-styles' );

		// Enqueue editor styles.
		add_editor_style( 'style-editor.css' );

		// Add support for responsive embedded content.
		add_theme_support( 'responsive-embeds' );

		// Remove support for block templates.
		remove_theme_support( 'block-templates' );
	}
endif;
add_action( 'after_setup_theme', 'msh_webaap_setup' );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function msh_webaap_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Footer', 'msh-webaap' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Add widgets here to appear in your footer.', 'msh-webaap' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'msh_webaap_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function msh_webaap_scripts() {
	wp_enqueue_style( 'msh-webaap-style', get_stylesheet_uri(), array(), MSH_WEBAAP_VERSION );
	wp_enqueue_script( 'msh-webaap-script', get_template_directory_uri() . '/js/script.min.js', array(), MSH_WEBAAP_VERSION, true );

	// Exposer le nonce REST API pour les appels AJAX
	wp_localize_script( 'msh-webaap-script', 'wpApiSettings', array(
		'root'  => esc_url_raw( rest_url() ),
		'nonce' => wp_create_nonce( 'wp_rest' )
	) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'msh_webaap_scripts' );

/**
 * Enqueue the block editor script.
 */
function msh_webaap_enqueue_block_editor_script() {
	$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if (
		$current_screen &&
		$current_screen->is_block_editor() &&
		'widgets' !== $current_screen->id
	) {
		wp_enqueue_script(
			'msh-webaap-editor',
			get_template_directory_uri() . '/js/block-editor.min.js',
			array(
				'wp-blocks',
				'wp-edit-post',
			),
			MSH_WEBAAP_VERSION,
			true
		);
		wp_add_inline_script( 'msh-webaap-editor', "tailwindTypographyClasses = '" . esc_attr( MSH_WEBAAP_TYPOGRAPHY_CLASSES ) . "'.split(' ');", 'before' );
	}
}
add_action( 'enqueue_block_assets', 'msh_webaap_enqueue_block_editor_script' );

/**
 * Add the Tailwind Typography classes to TinyMCE.
 *
 * @param array $settings TinyMCE settings.
 * @return array
 */
function msh_webaap_tinymce_add_class( $settings ) {
	$settings['body_class'] = MSH_WEBAAP_TYPOGRAPHY_CLASSES;
	return $settings;
}
add_filter( 'tiny_mce_before_init', 'msh_webaap_tinymce_add_class' );

/**
 * Limit the block editor to heading levels supported by Tailwind Typography.
 *
 * @param array  $args Array of arguments for registering a block type.
 * @param string $block_type Block type name including namespace.
 * @return array
 */
function msh_webaap_modify_heading_levels( $args, $block_type ) {
	if ( 'core/heading' !== $block_type ) {
		return $args;
	}

	// Remove <h1>, <h5> and <h6>.
	$args['attributes']['levelOptions']['default'] = array( 2, 3, 4 );

	return $args;
}
add_filter( 'register_block_type_args', 'msh_webaap_modify_heading_levels', 10, 2 );

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Helpers pour l'affichage des champs ACF
 */
require get_template_directory() . '/inc/acf-helpers.php';

/**
 * Traduire le message d'erreur "pending_approval" de WS Form
 */
add_filter( 'gettext', function( $translated_text, $text, $domain ) {
    // On cible uniquement le message d'erreur spécifique
    if ( 'Unknown error: pending_approval' === $text || 'pending_approval' === $text ) {
        return 'Votre inscription est en attente de validation par nos services.';
    }
    return $translated_text;
}, 20, 3 );

// Dans functions.php ou ton plugin
wp_enqueue_style( 'fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', [], '6.5.1' );