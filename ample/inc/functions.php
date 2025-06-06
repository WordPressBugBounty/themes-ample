<?php
/**
 * Ample functions and definitions
 *
 * @package    ThemeGrill
 * @subpackage Ample
 * @since      Ample 0.1
 */

if ( ! function_exists( 'ample_option' ) ) :
	/**
	 * Ample options
	 */
	function ample_option( $name, $default = false ) {

		$ample_options = get_option( 'ample' );

		if ( isset( $ample_options[ $name ] ) ) {
			return $ample_options[ $name ];
		} else {
			return $default;
		}
	}
endif;

add_action( 'wp_enqueue_scripts', 'ample_scripts' );
/**
 * Enqueue scripts and styles.
 */
function ample_scripts() {

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	// Load bxslider CSS
	wp_enqueue_style( 'ample-bxslider', get_template_directory_uri() . '/js/jquery.bxslider/jquery.bxslider.css', array(), '4.1.2' );

	wp_enqueue_style( 'ample-google-fonts', '//fonts.googleapis.com/css?family=Roboto:400,300&display=swap' );

	// Font Awesome 6.7.2.
	$font_awesome_styles = array(
		array(
			'handle'  => 'font-awesome-4',
			'file'    => '/v4-shims',
			'version' => '4.7.0',
		),
		array(
			'handle'  => 'font-awesome-all',
			'file'    => '/all',
			'version' => '6.7.2',
		),
		array(
			'handle'  => 'font-awesome-solid',
			'file'    => '/solid',
			'version' => '6.7.2',
		),
		array(
			'handle'  => 'font-awesome-regular',
			'file'    => '/regular',
			'version' => '6.7.2',
		),
		array(
			'handle'  => 'font-awesome-brands',
			'file'    => '/brands',
			'version' => '6.7.2',
		),
	);

	foreach ( $font_awesome_styles as $style ) {
		wp_register_style(
			$style['handle'],
			get_template_directory_uri() . '/font-awesome/css' . $style['file'] . $suffix . '.css',
			false,
			$style['version']
		);
		wp_enqueue_style( $style['handle'] );
	}
//	wp_enqueue_style( 'ample-fontawesome', get_template_directory_uri() . '/font-awesome/css/font-awesome.min.css', array(), '4.7.0' );

	/**
	 * Loads our main stylesheet.
	 */
	wp_enqueue_style( 'ample-style', get_stylesheet_uri() );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// Register bxslider Script
	wp_register_script( 'ample-bxslider', get_template_directory_uri() . '/js/jquery.bxslider/jquery.bxslider.min.js', array( 'jquery' ), false, true );

	$slider = 0;
	for ( $i = 1; $i <= 4; $i ++ ) {
		$slider_image = ample_option( 'ample_slider_image' . $i, '' );
		if ( ! empty( $slider_image ) ) {
			$slider ++;
		}
	}

	if ( ( $slider > 1 ) && is_front_page() && ample_option( 'ample_activate_slider', '0' ) == '1' ) {
		wp_enqueue_script( 'ample-bxslider' );
	}
	wp_enqueue_script( 'ample-custom', get_template_directory_uri() . '/js/theme-custom.js', array( 'jquery' ), false, true );

	wp_enqueue_script( 'ample-navigation', get_template_directory_uri() . '/js/navigation.js', array( 'jquery' ), false, true );

	// Skip link focus fix JS enqueue.
	wp_enqueue_script( 'ample-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), false, true );
}

function ample_block_editor_styles() {
	wp_enqueue_style( 'ample-editor-googlefonts', '//fonts.googleapis.com/css?family=Roboto:400,300&display=swap' );
	wp_enqueue_style( 'ample-block-editor-styles', get_template_directory_uri() . '/style-editor-block.css' );
}

add_action( 'enqueue_block_editor_assets', 'ample_block_editor_styles', 1, 1 );

/**************************************************************************************/

// Add admin scripts
add_action( 'admin_enqueue_scripts', 'ample_image_uploader' );

function ample_image_uploader() {
	//For image uploader
	wp_enqueue_media();
	wp_enqueue_script( 'ample-script', get_template_directory_uri() . '/js/image-uploader.js', false, '1.0', true );

	wp_enqueue_style( 'wp-color-picker' );
	//For Color Picker
	wp_enqueue_script( 'ample-color-picker', get_template_directory_uri() . '/js/color-picker.js', array( 'wp-color-picker' ), false );
}

/**************************************************************************************/

add_action( 'pre_get_posts', 'ample_exclude_category' );
/**
 * Function to exclude category
 */
function ample_exclude_category( $query ) {
	$ample_hide_categories = array();
	$ample_cat_num         = ample_option( 'ample_hide_category', '' );
	if ( ! empty( $ample_cat_num ) ) {
		if ( is_array( $ample_cat_num ) ) {
			foreach ( $ample_cat_num as $key => $value ) {
				if ( $value ) {
					array_push( $ample_hide_categories, $key );
				}
			}
		} else {
			$ample_hide_categories = explode( ',', $ample_cat_num );
		}
	}

	if ( $query->is_home() && $query->is_main_query() ) {
		$query->set( 'category__not_in', $ample_hide_categories );
	}
}

/**************************************************************************************/

// Adding the support for the entry-title tag for Google Rich Snippets
function ample_add_mod_hatom_data( $content ) {
	$title = get_the_title();
	if ( is_single() ) {
		$content .= '<div class="extra-hatom-entry-title"><span class="entry-title">' . $title . '</span></div>';
	}

	return $content;
}

add_filter( 'the_content', 'ample_add_mod_hatom_data' );

/**************************************************************************************/

add_action( 'ample_footer_copyright', 'ample_footer_copyright', 10 );
/**
 * Function to show the footer info, copyright information
 */
if ( ! function_exists( 'ample_footer_copyright' ) ) :

	function ample_footer_copyright() {

		$site_link = '<a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" ><span>' . get_bloginfo( 'name', 'display' ) . '</span></a>';

		$wp_link = '<a href="' . 'https://wordpress.org' . '" target="_blank" title="' . esc_attr__( 'WordPress', 'ample' ) . '" rel="nofollow"><span>' . esc_html__( 'WordPress', 'ample' ) . '</span></a>';

		$tg_link = '<a href="' . 'https://themegrill.com/themes/ample' . '" target="_blank" title="' . esc_attr__( 'Ample', 'ample' ) . '" rel="nofollow"><span>' . esc_html__( 'Ample', 'ample' ) . '</span></a>';

		$default_footer_value = sprintf( esc_html__( 'Copyright &copy; %1$s %2$s. All rights reserved.', 'ample' ), date( 'Y' ), $site_link ) . '<br>' . sprintf( esc_html__( 'Theme: %1$s by %2$s.', 'ample' ), $tg_link, 'ThemeGrill' ) . ' ' . sprintf( esc_html__( 'Powered by %s.', 'ample' ), $wp_link );

		$ample_footer_copyright = '<div class="copyright">' . $default_footer_value . '</div>';

		echo $ample_footer_copyright;

	}

endif;

/**************************************************************************************/

/**
 * Change hex code to RGB
 * Source: https://css-tricks.com/snippets/php/convert-hex-to-rgb/#comment-1052011
 */
function ample_hex2rgb( $hexstr ) {
	$int = hexdec( str_replace( '#', '', $hexstr ) );
	$rgb = array( "red" => 0xFF & ( $int >> 0x10 ), "green" => 0xFF & ( $int >> 0x8 ), "blue" => 0xFF & $int );
	$r   = $rgb['red'];
	$g   = $rgb['green'];
	$b   = $rgb['blue'];

	return "rgba($r,$g,$b, 0.85)";
}

/**
 * Generate darker color
 * Source: http://stackoverflow.com/questions/3512311/how-to-generate-lighter-darker-color-with-php
 */
function ample_darkcolor( $hex, $steps ) {
	// Steps should be between -255 and 255. Negative = darker, positive = lighter
	$steps = max( - 255, min( 255, $steps ) );

	// Normalize into a six character long hex string
	$hex = str_replace( '#', '', $hex );
	if ( strlen( $hex ) == 3 ) {
		$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
	}

	// Split into three parts: R, G and B
	$color_parts = str_split( $hex, 2 );
	$return      = '#';

	foreach ( $color_parts as $color ) {
		$color  = hexdec( $color ); // Convert to decimal
		$color  = max( 0, min( 255, $color + $steps ) ); // Adjust color
		$return .= str_pad( dechex( $color ), 2, '0', STR_PAD_LEFT ); // Make two char hex code
	}

	return $return;
}

/**************************************************************************************/

add_action( 'wp_head', 'ample_custom_css', 100 );
/**
 * Hooks the Custom Internal CSS to head section
 */
function ample_custom_css() {
	$primary_color      = ample_option( 'ample_primary_color', '#80abc8' );
	$primary_opacity    = ample_hex2rgb( $primary_color );
	$primary_dark       = ample_darkcolor( $primary_color, - 50 );
	$ample_internal_css = '';
	if ( $primary_color != '#80abc8' ) {
		$ample_internal_css = '.main-navigation .menu>ul>li.current_page_ancestor,.main-navigation .menu>ul>li.current_page_item,.main-navigation .menu>ul>li:hover,.main-navigation ul.menu>li.current-menu-ancestor,.main-navigation ul.menu>li.current-menu-item,.main-navigation ul.menu>li:hover,blockquote,.services-header h2,.slider-button:hover,.portfolio-button:hover,.call-to-action-button:hover,.read-btn a:hover, .single-page p a:hover, .single-page p a:hover,.read-btn a{border-color :' . $primary_color . '}a,.big-slider .entry-title a:hover,.main-navigation :hover,
.main-navigation li.menu-item-has-children:hover>a:after,.main-navigation li.page_item_has_children:hover>a:after,.main-navigation ul li ul li a:hover,.main-navigation ul li ul li:hover>a,.main-navigation ul li.current-menu-ancestor a,.main-navigation ul li.current-menu-ancestor a:after,.main-navigation ul li.current-menu-item a,.main-navigation ul li.current-menu-item a:after,.main-navigation ul li.current-menu-item ul li a:hover,.main-navigation ul li.current_page_ancestor a,.main-navigation ul li.current_page_ancestor a:after,.main-navigation ul li.current_page_item a,.main-navigation ul li.current_page_item a:after,.main-navigation ul li:hover>a,.main-navigation ul.menu li.current-menu-ancestor ul li.current-menu-item> a,#secondary .widget li a,#tertiary .widget li a,.fa.search-top,.widget_service_block h5 a:hover,.single-post-content a,.single-post-content .entry-title a:hover,.single-header h2,.single-page p a,.single-service span i,#colophon .copyright-info a:hover,#colophon .footer-nav ul li a:hover,#colophon a:hover,.comment .comment-reply-link:before,.comments-area article header .comment-edit-link:before,.copyright-info ul li a:hover,.footer-widgets-area a:hover,.menu-toggle:before,a#scroll-up i{color:' . $primary_color . '}#site-title a:hover,.hentry .entry-title a:hover,#comments i,.comments-area .comment-author-link a:hover,.comments-area a.comment-edit-link:hover,.comments-area a.comment-permalink:hover,.comments-area article header cite a:hover,.entry-meta .fa,.entry-meta a:hover,.nav-next a,.nav-previous a,.next a,.previous a{color:' . $primary_color . '}.ample-button,button,input[type=button],input[type=reset],input[type=submit],.comments-area .comment-author-link span,.slide-next,.slide-prev,.header-post-title-container,.read-btn a:hover,.single-service:hover .icons,.moving-box a,.slider-button:hover,.portfolio-button:hover,.call-to-action-button:hover,.ample-button, input[type="reset"], input[type="button"], input[type="submit"], button{background-color:' . $primary_color . '}.ample-button:hover, input[type="reset"]:hover, input[type="button"]:hover, input[type="submit"]:hover, button:hover{background-color:' . $primary_dark . '} .read-btn a:hover,.single-page p a:hover,.single-page p a:hover,.previous a:hover, .next a:hover,.tags a:hover,.fa.search-top:hover{color:' . $primary_dark . '}.single-service:hover .icons, .moving-box a{background:' . $primary_opacity . '}.read-btn a:hover{color:#ffffff}.woocommerce ul.products li.product .onsale,.woocommerce span.onsale,.woocommerce #respond input#submit, .woocommerce a.button, .woocommerce button.button, .woocommerce input.button, .woocommerce #respond input#submit.alt, .woocommerce a.button.alt, .woocommerce button.button.alt, .woocommerce input.button.alt{ background-color: ' . $primary_color . ';}.woocommerce ul.products li.product .price .amount, .entry-summary .price .amount,
	.woocommerce .woocommerce-message::before{color: ' . $primary_color . ';} .woocommerce .woocommerce-message{border-top-color: ' . $primary_color . ';}.better-responsive-menu .sub-toggle{background:' . $primary_dark . '}';
	}

	if ( ample_option( 'ample_header_title_background_image' ) ) {
		$ample_internal_css .= ' .header-post-title-container { background-image: url("' . ample_option( 'ample_header_title_background_image' ) . '");background-size:cover; }';
	}
	if ( ample_option( 'ample_title_bar_background_color', '#80abc8' ) != '#80abc8' ) {
		$ample_internal_css .= ' .header-post-title-container { background-color: ' . ample_option( 'ample_title_bar_background_color', '#80abc8' ) . '; }';
	}
	if ( ample_option( 'ample_header_title_color', '#ffffff' ) != '#ffffff' ) {
		$ample_internal_css .= ' .header-post-title-class, .breadcrumb, .breadcrumb a { color: ' . ample_option( 'ample_header_title_color', '#ffffff' ) . '; }';
	}

	if ( ! empty( $ample_internal_css ) ) {
		?>
		<style type="text/css"><?php echo $ample_internal_css; ?></style>
		<?php
	}

}

/**************************************************************************************/

add_filter( 'excerpt_length', 'ample_excerpt_length' );
/**
 * Sets the post excerpt length to 40 words.
 *
 * function tied to the excerpt_length filter hook.
 *
 * @uses filter excerpt_length
 */
function ample_excerpt_length( $length ) {
	return 40;
}

add_filter( 'excerpt_more', 'ample_continue_reading' );
/**
 * Returns a "Continue Reading" link for excerpts
 */
function ample_continue_reading() {
	return '';
}

/****************************************************************************************/

/**
 * Removing the default style of wordpress gallery
 */
add_filter( 'use_default_gallery_style', '__return_false' );

/**
 * Filtering the size to be medium from thumbnail to be used in WordPress gallery as a default size
 */
function ample_gallery_atts( $out, $pairs, $atts ) {
	$atts = shortcode_atts( array(
		'size' => 'medium',
	), $atts );

	$out['size'] = $atts['size'];

	return $out;
}

add_filter( 'shortcode_atts_gallery', 'ample_gallery_atts', 10, 3 );

/**************************************************************************************/

add_filter( 'body_class', 'ample_body_class' );
/**
 * Filter the body_class
 *
 * Throwing different body class for the different layouts in the body tag
 */
function ample_body_class( $classes ) {
	global $post;

	if ( $post ) {
		$layout_meta = get_post_meta( $post->ID, 'ample_page_layout', true );
	}

	if ( is_home() ) {
		$queried_id  = get_option( 'page_for_posts' );
		$layout_meta = get_post_meta( $queried_id, 'ample_page_layout', true );
	}

	if ( empty( $layout_meta ) || is_archive() || is_search() ) {
		$layout_meta = 'default_layout';
	}

	$ample_default_layout      = ample_option( 'ample_default_layout', 'right_sidebar' );
	$ample_default_page_layout = ample_option( 'ample_pages_default_layout', 'right_sidebar' );
	$ample_default_post_layout = ample_option( 'ample_single_posts_default_layout', 'right_sidebar' );

	if ( $layout_meta == 'default_layout' ) {
		if ( is_page() ) {
			if ( $ample_default_page_layout == 'right_sidebar' ) {
				$classes[] = '';
			} elseif ( $ample_default_page_layout == 'left_sidebar' ) {
				$classes[] = 'left-sidebar';
			} elseif ( $ample_default_page_layout == 'no_sidebar_full_width' ) {
				$classes[] = 'no-sidebar-full-width';
			} elseif ( $ample_default_page_layout == 'no_sidebar_content_centered' ) {
				$classes[] = 'no-sidebar';
			} elseif ( $ample_default_page_layout == 'both_sidebar' ) {
				$classes[] = 'both-sidebar';
			}
		} elseif ( is_single() ) {
			if ( $ample_default_post_layout == 'right_sidebar' ) {
				$classes[] = '';
			} elseif ( $ample_default_post_layout == 'left_sidebar' ) {
				$classes[] = 'left-sidebar';
			} elseif ( $ample_default_post_layout == 'no_sidebar_full_width' ) {
				$classes[] = 'no-sidebar-full-width';
			} elseif ( $ample_default_post_layout == 'no_sidebar_content_centered' ) {
				$classes[] = 'no-sidebar';
			} elseif ( $ample_default_post_layout == 'both_sidebar' ) {
				$classes[] = 'both-sidebar';
			}
		} elseif ( $ample_default_layout == 'right_sidebar' ) {
			$classes[] = '';
		} elseif ( $ample_default_layout == 'left_sidebar' ) {
			$classes[] = 'left-sidebar';
		} elseif ( $ample_default_layout == 'no_sidebar_full_width' ) {
			$classes[] = 'no-sidebar-full-width';
		} elseif ( $ample_default_layout == 'no_sidebar_content_centered' ) {
			$classes[] = 'no-sidebar';
		} elseif ( $ample_default_layout == 'both_sidebar' ) {
			$classes[] = 'both-sidebar';
		}
	} elseif ( $layout_meta == 'right_sidebar' ) {
		$classes[] = '';
	} elseif ( $layout_meta == 'left_sidebar' ) {
		$classes[] = 'left-sidebar';
	} elseif ( $layout_meta == 'no_sidebar_full_width' ) {
		$classes[] = 'no-sidebar-full-width';
	} elseif ( $layout_meta == 'no_sidebar_content_centered' ) {
		$classes[] = 'no-sidebar';
	} elseif ( $layout_meta == 'both_sidebar' ) {
		$classes[] = 'both-sidebar';
	}

	if ( ample_option( 'ample_new_menu_enable', '1' ) == '1' ) {
		$classes[] = 'better-responsive-menu';
	}
	if ( ample_option( 'ample_site_layout', 'wide' ) == 'wide' ) {
		$classes[] = 'wide';
	} else {
		$classes[] = 'boxed';
	}

	if ( is_page_template( 'page-templates/template-business.php' ) ) {
		$classes[] = 'business-template';
	}

	return $classes;
}

/****************************************************************************************/

if ( ! function_exists( 'ample_both_sidebar_select' ) ) :
	/**
	 * Fucntion to select the sidebar
	 */
	function ample_both_sidebar_select() {
		global $post;

		if ( $post ) {
			$layout_meta = get_post_meta( $post->ID, 'ample_page_layout', true );
		}

		if ( is_home() ) {
			$queried_id  = get_option( 'page_for_posts' );
			$layout_meta = get_post_meta( $queried_id, 'ample_page_layout', true );
		}

		if ( empty( $layout_meta ) || is_archive() || is_search() ) {
			$layout_meta = 'default_layout';
		}

		$ample_default_layout      = ample_option( 'ample_default_layout', 'right_sidebar' );
		$ample_default_page_layout = ample_option( 'ample_pages_default_layout', 'right_sidebar' );
		$ample_default_post_layout = ample_option( 'ample_single_posts_default_layout', 'right_sidebar' );

		if ( $layout_meta == 'default_layout' ) {
			if ( is_page() ) {
				if ( $ample_default_page_layout == 'both_sidebar' ) {
					get_sidebar( 'left' );
				}
			}
			if ( is_single() ) {
				if ( $ample_default_post_layout == 'both_sidebar' ) {
					get_sidebar( 'left' );
				}
			} elseif ( $ample_default_layout == 'both_sidebar' ) {
				get_sidebar( 'left' );
			}
		} elseif ( $layout_meta == 'both_sidebar' ) {
			get_sidebar( 'left' );
		}

	}
endif;


/****************************************************************************************/

if ( ! function_exists( 'ample_sidebar_select' ) ) :
	/**
	 * Fucntion to select the sidebar
	 */
	function ample_sidebar_select() {
		global $post;

		if ( $post ) {
			$layout_meta = get_post_meta( $post->ID, 'ample_page_layout', true );
		}

		if ( is_home() ) {
			$queried_id  = get_option( 'page_for_posts' );
			$layout_meta = get_post_meta( $queried_id, 'ample_page_layout', true );
		}

		if ( empty( $layout_meta ) || is_archive() || is_search() ) {
			$layout_meta = 'default_layout';
		}

		$ample_default_layout      = ample_option( 'ample_default_layout', 'right_sidebar' );
		$ample_default_page_layout = ample_option( 'ample_pages_default_layout', 'right_sidebar' );
		$ample_default_post_layout = ample_option( 'ample_single_posts_default_layout', 'right_sidebar' );

		if ( $layout_meta == 'default_layout' ) {
			if ( is_page() ) {
				if ( $ample_default_page_layout == 'right_sidebar' || $ample_default_page_layout == 'both_sidebar' ) {
					get_sidebar();
				} elseif ( $ample_default_page_layout == 'left_sidebar' ) {
					get_sidebar( 'left' );
				}
			}
			if ( is_single() ) {
				if ( $ample_default_post_layout == 'right_sidebar' || $ample_default_post_layout == 'both_sidebar' ) {
					get_sidebar();
				} elseif ( $ample_default_post_layout == 'left_sidebar' ) {
					get_sidebar( 'left' );
				}
			} elseif ( $ample_default_layout == 'right_sidebar' || $ample_default_layout == 'both_sidebar' ) {
				get_sidebar();
			} elseif ( $ample_default_layout == 'left_sidebar' ) {
				get_sidebar( 'left' );
			}
		} elseif ( $layout_meta == 'right_sidebar' || $layout_meta == 'both_sidebar' ) {
			get_sidebar();
		} elseif ( $layout_meta == 'left_sidebar' ) {
			get_sidebar( 'left' );
		}
	}
endif;

/**************************************************************************************/

if ( ! function_exists( 'ample_meta_select' ) ) :
	/**
	 * Fucntion to select Meta
	 */
	function ample_meta_select() {
		if ( 'post' == get_post_type() ) : ?>
			<div class="entry-meta clearfix">
			<span class="author vcard"><i class="fa fa-aw fa-user"></i>
				<span class="fn"><a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"><?php the_author(); ?></a></span>
			</span>

				<?php
				$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
				if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
					$time_string .= '<time class="updated" datetime="%3$s">%4$s</time>';
				}
				$time_string = sprintf( $time_string,
					esc_attr( get_the_date( 'c' ) ),
					esc_html( get_the_date() ),
					esc_attr( get_the_modified_date( 'c' ) ),
					esc_html( get_the_modified_date() )
				);
				printf( __( '<span class="entry-date"><i class="fa fa-aw fa-calendar-o"></i> <a href="%1$s" title="%2$s" rel="bookmark">%3$s</a></span>', 'ample' ),
					esc_url( get_permalink() ),
					esc_attr( get_the_time() ),
					$time_string
				); ?>

				<?php if ( has_category() ) { ?>
					<span class="category"><i class="fa fa-aw fa-folder-open"></i><?php the_category( ', ' ); ?></span>
				<?php } ?>

				<?php if ( comments_open() ) { ?>
					<span class="comments"><i class="fa fa-aw fa-comment"></i><?php comments_popup_link( __( 'No Comments', 'ample' ), __( '1 Comment', 'ample' ), __( '% Comments', 'ample' ), '', __( 'Comments Off', 'ample' ) ); ?></span>
				<?php } ?>
			</div>
		<?php endif;
	}
endif;

/**************************************************************************************/

if ( ! function_exists( 'ample_comment' ) ) :
	/**
	 * Template for comments and pingbacks.
	 *
	 * Used as a callback by wp_list_comments() for displaying the comments.
	 */
	function ample_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) :
			case 'pingback' :
			case 'trackback' :
				// Display trackbacks differently than normal comments.
				?>
				<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
				<p><?php _e( 'Pingback:', 'ample' ); ?><?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'ample' ), '<span class="edit-link">', '</span>' ); ?></p>
				<?php
				break;
			default :
				// Proceed with normal comments.
				global $post;
				?>
			<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
				<article id="comment-<?php comment_ID(); ?>" class="comment">
					<header class="comment-meta comment-author vcard">
						<?php
						echo get_avatar( $comment, 74 );
						printf( '<div class="comment-author-link"><i class="fa fa-user"></i>%1$s%2$s</div>',
							get_comment_author_link(),
							// If current post author is also comment author, make it known visually.
							( $comment->user_id === $post->post_author ) ? '<span>' . __( 'Post author', 'ample' ) . '</span>' : ''
						);
						printf( '<div class="comment-date-time"><i class="fa fa-calendar-o"></i>%1$s</div>',
							sprintf( __( '%1$s at %2$s', 'ample' ), get_comment_date(), get_comment_time() )
						);
						printf( '<a class="comment-permalink" href="%1$s"><i class="fa fa-link"></i>Permalink</a>', esc_url( get_comment_link( $comment->comment_ID ) ) );
						edit_comment_link();
						?>
					</header><!-- .comment-meta -->

					<?php if ( '0' == $comment->comment_approved ) : ?>
						<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'ample' ); ?></p>
					<?php endif; ?>

					<section class="comment-content comment">
						<?php comment_text(); ?>
						<?php comment_reply_link( array_merge( $args, array(
							'reply_text' => __( 'Reply', 'ample' ),
							'after'      => '',
							'depth'      => $depth,
							'max_depth'  => $args['max_depth'],
						) ) ); ?>
					</section>

				</article><!-- #comment -->
				<?php
				break;
		endswitch; // end comment_type check
	}
endif;

/**************************************************************************************/

add_action( 'admin_init', 'ample_textarea_sanitization_change', 100 );
/**
 * Override the default textarea sanitization.
 */
function ample_textarea_sanitization_change() {
	remove_filter( 'of_sanitize_textarea', 'of_sanitize_textarea' );
	add_filter( 'of_sanitize_textarea', 'ample_sanitize_textarea_custom' );
}

/**
 * sanitize the input
 */
function ample_sanitize_textarea_custom( $input ) {
	$output = wp_filter_nohtml_kses( $input );

	return $output;
}

/**************************************************************************************/

/**
 * Making the theme Woocommrece compatible
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

add_filter( 'woocommerce_show_page_title', '__return_false' );

add_action( 'woocommerce_before_main_content', 'ample_wrapper_start', 10 );
add_action( 'woocommerce_before_main_content', 'ample_inner_wrapper_start', 15 );
add_action( 'woocommerce_after_main_content', 'ample_inner_wrapper_end', 10 );
add_action( 'woocommerce_sidebar', 'ample_wrapper_end', 20 );

function ample_wrapper_start() {
	echo '<div class="single-page"> <div class="inner-wrap">';
}

function ample_inner_wrapper_start() {
	echo '<div id="primary"><div id="content">';
}

function ample_inner_wrapper_end() {
	echo '</div>';
	ample_both_sidebar_select();
	echo '</div>';
}

function ample_wrapper_end() {
	ample_sidebar_select();
	echo '</div></div>';
}

// Displays the site logo
if ( ! function_exists( 'ample_the_custom_logo' ) ) {
	/**
	 * Displays the optional custom logo.
	 */
	function ample_the_custom_logo() {
		if ( function_exists( 'the_custom_logo' ) ) {
			the_custom_logo();
		}
	}
}

if ( ! function_exists( 'ample_pingback_header' ) ) :

	/**
	 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
	 */
	function ample_pingback_header() {
		if ( is_singular() && pings_open() ) {
			printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
		}
	}

endif;

add_action( 'wp_head', 'ample_pingback_header' );

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require_once get_template_directory() . '/inc/jetpack.php';
}

if ( ! function_exists( 'ample_related_posts_function' ) ) {

	/**
	 * Display the related posts.
	 */
	function ample_related_posts_function() {
		wp_reset_postdata();
		global $post;

		// Define shared post arguments.
		$args = array(
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'ignore_sticky_posts'    => 1,
			'orderby'                => 'rand',
			'post__not_in'           => array( $post->ID ),
			'posts_per_page'         => 3,
		);

		// Related by categories.
		if ( ample_option( 'ample_related_posts', 'categories' ) == 'categories' ) {
			$cats                 = wp_get_post_categories( $post->ID, array( 'fields' => 'ids' ) );
			$args['category__in'] = $cats;
		}

		// Related by tags.
		if ( ample_option( 'ample_related_posts', 'categories' ) == 'tags' ) {
			$tags            = wp_get_post_tags( $post->ID, array( 'fields' => 'ids' ) );
			$args['tag__in'] = $tags;

			// If no tags added, return.
			if ( ! $tags ) {
				$break = true;
			}
		}

		$query = ! isset( $break ) ? new WP_Query( $args ) : new WP_Query;

		return $query;
	}

}

/**
 * Compare user's current version of plugin.
 */
if ( ! function_exists( 'ample_plugin_version_compare' ) ) {
	function ample_plugin_version_compare( $plugin_slug, $version_to_compare ) {

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$installed_plugins = get_plugins();

		// Plugin not installed.
		if ( ! isset( $installed_plugins[ $plugin_slug ] ) ) {
			return false;
		}

		$tdi_user_version = $installed_plugins[ $plugin_slug ]['Version'];

		return version_compare( $tdi_user_version, $version_to_compare, '<' );
	}
}
