<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_VERSION', '2.6.1' );

if ( ! isset( $content_width ) ) {
	$content_width = 800; // Pixels.
}

if ( ! function_exists( 'hello_elementor_setup' ) ) {
	/**
	 * Set up theme support.
	 *
	 * @return void
	 */
	function hello_elementor_setup() {
		if ( is_admin() ) {
			hello_maybe_update_theme_version_in_db();
		}

		if ( apply_filters( 'hello_elementor_register_menus', true ) ) {
			register_nav_menus( array( 'menu-1' => esc_html__( 'Header', 'hello-elementor' ) ) );
			register_nav_menus( array( 'menu-2' => esc_html__( 'Footer', 'hello-elementor' ) ) );
		}

		if ( apply_filters( 'hello_elementor_post_type_support', true ) ) {
			add_post_type_support( 'page', 'excerpt' );
		}

		if ( apply_filters( 'hello_elementor_add_theme_support', true ) ) {
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support(
				'html5',
				array(
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
				)
			);
			add_theme_support(
				'custom-logo',
				array(
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				)
			);

			/*
			 * Editor Style.
			 */
			add_editor_style( 'classic-editor.css' );

			/*
			 * Gutenberg wide images.
			 */
			add_theme_support( 'align-wide' );

			/*
			 * WooCommerce.
			 */
			if ( apply_filters( 'hello_elementor_add_woocommerce_support', true ) ) {
				// WooCommerce in general.
				add_theme_support( 'woocommerce' );
				// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
				// zoom.
				add_theme_support( 'wc-product-gallery-zoom' );
				// lightbox.
				add_theme_support( 'wc-product-gallery-lightbox' );
				// swipe.
				add_theme_support( 'wc-product-gallery-slider' );
			}
		}
	}
}
add_action( 'after_setup_theme', 'hello_elementor_setup' );

function hello_maybe_update_theme_version_in_db() {
	$theme_version_option_name = 'hello_theme_version';
	// The theme version saved in the database.
	$theme_db_version = get_option( $theme_version_option_name );

	// If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
	if ( ! $theme_db_version || version_compare( $theme_db_version, HELLO_ELEMENTOR_VERSION, '<' ) ) {
		update_option( $theme_version_option_name, HELLO_ELEMENTOR_VERSION );
	}
}

if ( ! function_exists( 'hello_elementor_scripts_styles' ) ) {
	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	function hello_elementor_scripts_styles() {
		$min_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( apply_filters( 'hello_elementor_enqueue_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor',
				get_template_directory_uri() . '/style' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( apply_filters( 'hello_elementor_enqueue_theme_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor-theme-style',
				get_template_directory_uri() . '/theme' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_scripts_styles' );

if ( ! function_exists( 'hello_elementor_register_elementor_locations' ) ) {
	/**
	 * Register Elementor Locations.
	 *
	 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
	 *
	 * @return void
	 */
	function hello_elementor_register_elementor_locations( $elementor_theme_manager ) {
		if ( apply_filters( 'hello_elementor_register_elementor_locations', true ) ) {
			$elementor_theme_manager->register_all_core_location();
		}
	}
}
add_action( 'elementor/theme/register_locations', 'hello_elementor_register_elementor_locations' );

if ( ! function_exists( 'hello_elementor_content_width' ) ) {
	/**
	 * Set default content width.
	 *
	 * @return void
	 */
	function hello_elementor_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'hello_elementor_content_width', 800 );
	}
}
add_action( 'after_setup_theme', 'hello_elementor_content_width', 0 );

if ( is_admin() ) {
	if ( ! function_exists( 'hello_elementor_admin_notice_show_reviews' ) ) {
		/**
		 * Add admin notice.
		 *
		 * @return void
		 */
		function hello_elementor_admin_notice_show_reviews() {
			if ( ! did_action( 'elementor/loaded' ) ) {
				return;
			}
			$opt_in = get_option( 'elementor_pro_tracking_opt_in' );
			if ( $opt_in ) {
				return;
			}
			$user_id = get_current_user_id();
			if ( get_user_meta( $user_id, 'hello_elementor_tracking_notice' ) ) {
				return;
			}
			$current_time = time();
			$active_time = get_option( 'hello_elementor_active_time' );
			if ( ! $active_time ) {
				update_option( 'hello_elementor_active_time', $current_time );
				return;
			}
			$notice_timespan = WEEK_IN_SECONDS * 4;
			if ( ( $current_time - $active_time ) < $notice_timespan ) {
				return;
			}
			if ( 'true' === get_user_meta( $user_id, '_hello_elementor_user_clicked_on_notices_action' ) ) {
				return;
			}
			$ajax_url = admin_url( 'admin-ajax.php' );
			$ajax_nonce = wp_create_nonce( 'hello-elementor-admin-notice-show-reviews' );
			?>
			<div class="notice notice-success is-dismissible hello-elementor-notice hello-elementor-notice--show-reviews">
				<div class="hello-elementor-notice-aside">
					<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/elementor-logo-square.svg" alt="<?php esc_attr_e( 'Get Elementor', 'hello-elementor' ); ?>" />
				</div>
				<div class="hello-elementor-notice-jumper-container">
					<div class="hello-elementor-notice-jumper-content">
						<div class="hello-elementor-notice-title">
							<?php
							printf(
								/* translators: %s: Elementor */
								esc_html__( 'Enjoying %s Theme?', 'hello-elementor' ),
								'<strong>' . esc_html__( 'Hello Elementor', 'hello-elementor' ) . '</strong>'
							);
							?>
						</div>
						<div class="hello-elementor-notice-description">
							<?php
							printf(
								/* translators: %1$s: Elementor, %2$s: Elementor */
								esc_html__( 'Become a super contributor by sharing your review. You’ll be helping out fellow %1$s users and powering the future of %2$s!', 'hello-elementor' ),
								'<strong>' . esc_html__( 'Hello Elementor', 'hello-elementor' ) . '</strong>',
								'<strong>' . esc_html__( 'Hello Elementor', 'hello-elementor' ) . '</strong>'
							);
							?>
						</div>
						<div class="hello-elementor-notice-actions">
							<a href="https://go.elementor.com/hello-theme-review/" class="button button-primary hello-elementor-button--show-reviews" target="_blank">
								<?php echo esc_html__( 'Share Your Review', 'hello-elementor' ); ?>
							</a>
							<button class="button button-secondary hello-elementor-button--remind-me-later" data-ajax_url="<?php echo esc_url( $ajax_url ); ?>" data-ajax_nonce="<?php echo esc_attr( $ajax_nonce ); ?>" data-action="hello_elementor_set_admin_notice_viewed">
								<?php echo esc_html__( 'Maybe Later', 'hello-elementor' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
			<script>
				jQuery( document ).ready( function( $ ) {
					$( '.hello-elementor-notice--show-reviews button.hello-elementor-button--remind-me-later' ).on( 'click', function( event ) {
						event.preventDefault();
						const $button = $( this );
						$.post( $button.data( 'ajax_url' ), {
							_ajax_nonce: $button.data( 'ajax_nonce' ),
							action: $button.data( 'action' ),
						} );
						$( '.hello-elementor-notice--show-reviews' ).hide();
					} );
				} );
			</script>
			<?php
		}
	}
	add_action( 'admin_notices', 'hello_elementor_admin_notice_show_reviews' );

	if ( ! function_exists( 'hello_elementor_ajax_set_admin_notice_viewed' ) ) {
		/**
		 * Ajax handler for admin notice.
		 *
		 * @return void
		 */
		function hello_elementor_ajax_set_admin_notice_viewed() {
			check_ajax_referer( 'hello-elementor-admin-notice-show-reviews' );
			update_user_meta( get_current_user_id(), '_hello_elementor_user_clicked_on_notices_action', 'true' );
			wp_die();
		}
	}
	add_action( 'wp_ajax_hello_elementor_set_admin_notice_viewed', 'hello_elementor_ajax_set_admin_notice_viewed' );

	if ( ! function_exists( 'hello_elementor_admin_notice_starter_template' ) ) {
		/**
		 * Add admin notice.
		 *
		 * @return void
		 */
		function hello_elementor_admin_notice_starter_template() {
			if ( ! did_action( 'elementor/loaded' ) ) {
				return;
			}
			if ( get_option( 'hello_elementor_starter_notice' ) ) {
				return;
			}
			$user_id = get_current_user_id();
			if ( 'true' === get_user_meta( $user_id, '_hello_elementor_user_clicked_on_notices_action' ) ) {
				return;
			}
			$ajax_url = admin_url( 'admin-ajax.php' );
			$ajax_nonce = wp_create_nonce( 'hello-elementor-admin-notice-starter-template' );
			?>
			<div class="notice notice-success is-dismissible hello-elementor-notice hello-elementor-notice--starter-template">
				<div class="hello-elementor-notice-aside">
					<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/go-pro-icon.svg" alt="<?php esc_attr_e( 'Get Elementor', 'hello-elementor' ); ?>" />
				</div>
				<div class="hello-elementor-notice-jumper-container">
					<div class="hello-elementor-notice-jumper-content">
						<div class="hello-elementor-notice-title">
							<?php
							printf(
								/* translators: %s: Elementor */
								esc_html__( 'Thank you for installing %s Theme!', 'hello-elementor' ),
								'<strong>' . esc_html__( 'Hello Elementor', 'hello-elementor' ) . '</strong>'
							);
							?>
						</div>
						<div class="hello-elementor-notice-description">
							<?php
							printf(
								/* translators: %s: Elementor */
								esc_html__( 'Did you know that %s has a new Demo Website & Starter Kit? You can now import a full website with a few clicks. Plus, it\'s FREE!', 'hello-elementor' ),
								'<strong>' . esc_html__( 'Hello Elementor', 'hello-elementor' ) . '</strong>'
							);
							?>
						</div>
						<div class="hello-elementor-notice-actions">
							<a href="https://go.elementor.com/hello-theme-starter/" class="button button-primary hello-elementor-button--starter-template" target="_blank">
								<?php echo esc_html__( 'Explore Starter Kit', 'hello-elementor' ); ?>
							</a>
							<button class="button button-secondary hello-elementor-button--remind-me-later" data-ajax_url="<?php echo esc_url( $ajax_url ); ?>" data-ajax_nonce="<?php echo esc_attr( $ajax_nonce ); ?>" data-action="hello_elementor_set_starter_notice_viewed">
								<?php echo esc_html__( 'Dismiss', 'hello-elementor' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
			<script>
				jQuery( document ).ready( function( $ ) {
					$( '.hello-elementor-notice--starter-template button.hello-elementor-button--remind-me-later' ).on( 'click', function( event ) {
						event.preventDefault();
						const $button = $( this );
						$.post( $button.data( 'ajax_url' ), {
							_ajax_nonce: $button.data( 'ajax_nonce' ),
							action: $button.data( 'action' ),
						} );
						$( '.hello-elementor-notice--starter-template' ).hide();
					} );
				} );
			</script>
			<?php
		}
	}
	add_action( 'admin_notices', 'hello_elementor_admin_notice_starter_template' );
	if ( ! function_exists( 'hello_elementor_ajax_set_starter_notice_viewed' ) ) {
		/**
		 * Ajax handler for admin notice.
		 *
		 * @return void
		 */
		function hello_elementor_ajax_set_starter_notice_viewed() {
			check_ajax_referer( 'hello-elementor-admin-notice-starter-template' );
			update_option( 'hello_elementor_starter_notice', 'true' );
			wp_die();
		}
	}
	add_action( 'wp_ajax_hello_elementor_set_starter_notice_viewed', 'hello_elementor_ajax_set_starter_notice_viewed' );
}

/**
 * Required Elementor Plugin
 *
 * Checks if Elementor plugin is activated, if not display an admin notice.
 *
 * @return bool
 */
function is_elementor_activated() {
	return did_action( 'elementor/loaded' );
}

/**
 * When Elementor is not activated display an admin notice.
 *
 * @return void
 */
function hello_elementor_admin_notice_missing_main_plugin() {
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}

	$message = sprintf(
		/* translators: 1: Plugin name 2: Elementor */
		esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'hello-elementor' ),
		'<strong>' . esc_html__( 'Hello Elementor', 'hello-elementor' ) . '</strong>',
		'<strong>' . esc_html__( 'Elementor', 'hello-elementor' ) . '</strong>'
	);

	printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
}

/**
 * Display an admin notice if Elementor Pro is not activated.
 *
 * @return void
 */
function hello_elementor_admin_notice_elementor_pro_inactive() {
	if ( ! is_elementor_activated() || defined( 'ELEMENTOR_PRO_VERSION' ) ) {
		return;
	}

	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}

	$message = sprintf(
		/* translators: 1: Elementor Pro, 2: Elementor */
		esc_html__( '"%1$s" is not a standalone theme and requires "%2$s" version 3.8.0 or higher.', 'hello-elementor' ),
		'<strong>' . esc_html__( 'Hello Elementor Theme', 'hello-elementor' ) . '</strong>',
		'<strong>' . esc_html__( 'Elementor Pro', 'hello-elementor' ) . '</strong>'
	);

	printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
}

/**
 * Register an admin notice to require a minimum Elementor version.
 *
 * @return void
 */
function hello_elementor_admin_notice_required_elementor_version() {
	if ( ! is_elementor_activated() || defined( 'ELEMENTOR_PRO_VERSION' ) ) {
		return;
	}

	$required_elementor_version = '3.8.0';
	if ( version_compare( ELEMENTOR_VERSION, $required_elementor_version, '>=' ) ) {
		return;
	}

	$message = sprintf(
		/* translators: 1: Elementor, 2: Required Elementor version */
		esc_html__( '"%1$s" requires "%2$s" version %3$s or higher.', 'hello-elementor' ),
		'<strong>' . esc_html__( 'Hello Elementor Theme', 'hello-elementor' ) . '</strong>',
		'<strong>' . esc_html__( 'Elementor', 'hello-elementor' ) . '</strong>',
		$required_elementor_version
	);

	printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
}

if ( ! function_exists( 'hello_elementor_check_hide_theme_Actions' ) ) {
	/**
	 * Check is hide theme actions.
	 *
	 * @return bool
	 */
	function hello_elementor_check_hide_theme_Actions() {
		if ( ! is_elementor_activated() ) {
			add_action( 'admin_notices', 'hello_elementor_admin_notice_missing_main_plugin' );
			return true;
		}
		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.8.0', '<' ) && ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			add_action( 'admin_notices', 'hello_elementor_admin_notice_required_elementor_version' );
			return true;
		}
		if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			add_action( 'admin_notices', 'hello_elementor_admin_notice_elementor_pro_inactive' );
			return true;
		}
		return false;
	}
}
hello_elementor_check_hide_theme_Actions();

// Add this custom function to enqueue the Angel Club font
function my_custom_fonts() {
    wp_enqueue_style( 'angel-club-font', get_stylesheet_directory_uri() . '/dists/css/angel-club-font.css', array(), null );
}
add_action( 'wp_enqueue_scripts', 'my_custom_fonts' );

// To disable Hello Elementor Test Experiments by default.
add_filter( 'elementor/experiments/default-value', function( $default_value, $experiment_name ) {
	if ( 0 === strpos( $experiment_name, 'hello-' ) ) {
		return \Elementor\Modules\Experiments\Module::STATE_INACTIVE;
	}
	return $default_value;
}, 10, 2 );
