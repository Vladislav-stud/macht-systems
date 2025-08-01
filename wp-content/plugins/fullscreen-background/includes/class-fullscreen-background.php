<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.enweby.com/
 * @since      1.0.0
 *
 * @package    Fullscreen_Background
 * @subpackage Fullscreen_Background/includes
 */
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Fullscreen_Background
 * @subpackage Fullscreen_Background/includes
 * @author     Enweby <support@enweby.com>
 */
class Fullscreen_Background {
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Fullscreen_Background_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if ( defined( 'FULLSCREEN_BACKGROUND_VERSION' ) ) {
            $this->version = FULLSCREEN_BACKGROUND_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'fullscreen-background';
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_enwb_fb_cpt_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Fullscreen_Background_Loader. Orchestrates the hooks of the plugin.
     * - Fullscreen_Background_i18n. Defines internationalization functionality.
     * - Fullscreen_Background_Admin. Defines all hooks for the admin area.
     * - Fullscreen_Background_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fullscreen-background-loader.php';
        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fullscreen-background-i18n.php';
        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-fullscreen-background-admin.php';
        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-fullscreen-background-public.php';
        $this->loader = new Fullscreen_Background_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Fullscreen_Background_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new Fullscreen_Background_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new Fullscreen_Background_Admin($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        // Admin menu and settings.
        //$this->loader->add_action( 'admin_menu', $plugin_admin, 'plugin_menu_settings' );
        //$this->loader->add_action( 'admin_menu', $plugin_admin, 'menu_settings_using_helper' );
        $this->loader->add_action( 'wpsf_before_settings_' . ENWEBY_FB_FWAS, $plugin_admin, 'fullscreen_background_upsell_section' );
        $this->loader->add_action( 'wpsf_before_settings_' . ENWEBY_FB_FWAS, $plugin_admin, 'fullscreen_background_main_menu' );
        // Add an optional settings validation filter (recommended).
        $this->loader->add_filter( ENWEBY_FB_FWAS . '_settings_validate', $plugin_admin, 'enwbfb_validate_settings' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_settings_page' );
        $this->loader->add_filter( 'wpsf_menu_position_' . ENWEBY_FB_FWAS, $plugin_admin, 'wpsf_menu_reposition' );
        $this->loader->add_filter( 'wpsf_menu_icon_url_' . ENWEBY_FB_FWAS, $plugin_admin, 'wpsf_menu_icon_replacement' );
        $this->loader->add_filter(
            'admin_menu',
            $plugin_admin,
            'add_fb_plugin_documentation_menu_link',
            20
        );
        // Plugin Row Meta.
        $this->loader->add_action( 'plugin_action_links_' . FULLSCREEN_BACKGROUND_BASE_NAME, $plugin_admin, 'plugin_action_links' );
        $this->loader->add_action(
            'plugin_row_meta',
            $plugin_admin,
            'plugin_row_meta',
            10,
            2
        );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enwbfb_load_wp_media' );
    }

    /**
     * Register all of the hooks related to registering and managing a custom post type
     * as well as customizing the admin columns.
     *
     * @access private
     */
    private function define_enwb_fb_cpt_hooks() {
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new Fullscreen_Background_Public($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        // fullscreen background.
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enwbfb_enqueue' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enwbfb_enqueue_custom_css_single' );
        $this->loader->add_filter( 'body_class', $plugin_public, 'fullscreen_background_body_classes' );
        $this->loader->add_action(
            'wp_body_open',
            $plugin_public,
            'enweby_setup_fullscreen_background_overlay',
            10
        );
        $this->loader->add_action( 'wp_body_open', $plugin_public, 'enweby_setup_fullscreen_background_video' );
        // Adding custom styles based on admin settings.
        $this->loader->add_action(
            'wp_head',
            $plugin_public,
            'enweby_setup_fullscreen_background_styles',
            30
        );
        $this->loader->add_action(
            'wp_head',
            $plugin_public,
            'enwbfb_enqueue_custom_js_single_header',
            200
        );
        $this->loader->add_action(
            'wp_footer',
            $plugin_public,
            'enwbfb_enqueue_custom_js_single_body',
            200
        );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Fullscreen_Background_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

}
