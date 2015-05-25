<?php
/**
 * Workout_Calendar
 *
 * @package   Workout_Calendar
 * @author    Ben Klein <bklein73@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.cognicio.com
 * @copyright 2015 Ben Klein
 */

/**
 * Workout_Calendar class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * @package Workout_Calendar
 * @author  Ben Klein <bklein73@gmail.com>
 */
class Workout_Calendar
{

    /**
     * Plugin version, used for cache-busting of style and script file references.
     *
     * @since   1.0.0
     *
     * @var     string
     */
    const VERSION = '1.0.0';

    /**
     * Unique identifier for your plugin.
     *
     *
     * The variable name is used as the text domain when internationalizing strings
     * of text. Its value should match the Text Domain file header in the main
     * plugin file.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $plugin_slug = 'workout_calendar';

    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     *
     * @since     1.0.0
     */
    private function __construct()
    {
        add_action('init', array($this, 'load_plugin_textdomain'));
        add_action('wpmu_new_blog', array($this, 'activate_new_site'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('workout_calendar', array($this, 'display_workout_calendar'));
        add_action('init', array($this, 'custom_workout'));
    }

    /**
     * Return the plugin slug.
     *
     * @since    1.0.0
     *
     * @return    Plugin slug variable.
     */
    public function get_plugin_slug()
    {
        return $this->plugin_slug;
    }

    public function custom_workout()
    {
        $labels = array(
            'name' => 'Workouts',
            'singular_name' => 'Workout',
            'menu_name' => 'Workouts',
            'name_admin_bar' => 'Workouts',
            'parent_item_colon' => 'Parent Workout:',
            'all_items' => 'All Workouts',
            'add_new_item' => 'Add New Workout',
            'add_new' => 'Add New',
            'new_item' => 'New Workout',
            'edit_item' => 'Edit Workout',
            'update_item' => 'Update Workout',
            'view_item' => 'View Workout',
            'search_items' => 'Search Workouts',
            'not_found' => 'Not found',
            'not_found_in_trash' => 'Not found in Trash',
        );
        $args = array(
            'label' => 'workout',
            'description' => 'Workouts for Workout Calendar Plugin',
            'labels' => $labels,
            'supports' => array('title', 'editor'),
            'taxonomies' => array('category'),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-universal-access',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'capability_type' => 'post',
        );
        register_post_type('workout', $args);

        $labels = array(
            'name' => 'Workout Schedules',
            'singular_name' => 'Workout Schedule',
            'menu_name' => 'Workout Schedules',
            'name_admin_bar' => 'Workout Schedules',
            'parent_item_colon' => 'Parent Workout Schedule:',
            'all_items' => 'All Workout Schedules',
            'add_new_item' => 'Add New Workout Schedule',
            'add_new' => 'Add New',
            'new_item' => 'New Workout Schedule',
            'edit_item' => 'Edit Workout Schedule',
            'update_item' => 'Update Workout Schedule',
            'view_item' => 'View Workout Schedule',
            'search_items' => 'Search Workout Schedules',
            'not_found' => 'Not found',
            'not_found_in_trash' => 'Not found in Trash',
        );
        $args = array(
            'label' => 'workout schedule',
            'description' => 'Workout Schedule for Workout Calendar Plugin',
            'labels' => $labels,
            'supports' => array('title', 'editor'),
            'taxonomies' => array('category'),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-universal-access',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'capability_type' => 'post',
        );
        register_post_type('workout_schedule', $args);
        add_action('add_meta_boxes', array($this, 'workout_add_meta_box'));
        add_action('save_post', array($this, 'workout_save_meta_box_data'));
        add_action('add_meta_boxes', array($this, 'workout_schedule_add_meta_box'));
        add_action('save_post', array($this, 'workout_schedule_save_meta_box_data'));
        add_action('wp_ajax_nopriv_workoutlookup', array($this, 'workout_callback') );

    }

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance()
    {

        // If the single instance hasn't been set, set it now.
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Fired when the plugin is activated.
     *
     * @since    1.0.0
     *
     * @param    boolean $network_wide True if WPMU superadmin uses
     *                                       "Network Activate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       activated on an individual blog.
     */
    public static function activate($network_wide)
    {

        if (function_exists('is_multisite') && is_multisite()) {

            if ($network_wide) {

                // Get all blog ids
                $blog_ids = self::get_blog_ids();

                foreach ($blog_ids as $blog_id) {

                    switch_to_blog($blog_id);
                    self::single_activate();
                }

                restore_current_blog();

            } else {
                self::single_activate();
            }

        } else {
            self::single_activate();
        }

    }

    /**
     * Fired when the plugin is deactivated.
     *
     * @since    1.0.0
     *
     * @param    boolean $network_wide True if WPMU superadmin uses
     *                                       "Network Deactivate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       deactivated on an individual blog.
     */
    public static function deactivate($network_wide)
    {

        if (function_exists('is_multisite') && is_multisite()) {

            if ($network_wide) {

                // Get all blog ids
                $blog_ids = self::get_blog_ids();

                foreach ($blog_ids as $blog_id) {

                    switch_to_blog($blog_id);
                    self::single_deactivate();

                }

                restore_current_blog();

            } else {
                self::single_deactivate();
            }

        } else {
            self::single_deactivate();
        }

    }

    /**
     * Fired when a new site is activated with a WPMU environment.
     *
     * @since    1.0.0
     *
     * @param    int $blog_id ID of the new blog.
     */
    public function activate_new_site($blog_id)
    {

        if (1 !== did_action('wpmu_new_blog')) {
            return;
        }

        switch_to_blog($blog_id);
        self::single_activate();
        restore_current_blog();

    }

    /**
     * Get all blog ids of blogs in the current network that are:
     * - not archived
     * - not spam
     * - not deleted
     *
     * @since    1.0.0
     *
     * @return   array|false    The blog ids, false if no matches.
     */
    private static function get_blog_ids()
    {

        global $wpdb;

        // get an array of blog ids
        $sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

        return $wpdb->get_col($sql);

    }

    /**
     * Fired for each blog when the plugin is activated.
     *
     * @since    1.0.0
     */
    private static function single_activate()
    {
        // @TODO: Define activation functionality here
    }

    /**
     * Fired for each blog when the plugin is deactivated.
     *
     * @since    1.0.0
     */
    private static function single_deactivate()
    {
        // @TODO: Define deactivation functionality here
    }

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain()
    {

        $domain = $this->plugin_slug;
        $locale = apply_filters('plugin_locale', get_locale(), $domain);

        load_textdomain($domain, trailingslashit(WP_LANG_DIR) . $domain . '/' . $domain . '-' . $locale . '.mo');
        load_plugin_textdomain($domain, FALSE, basename(plugin_dir_path(dirname(__FILE__))) . '/languages/');

    }

    /**
     * Register and enqueue public-facing style sheet.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        //wp_enqueue_style( $this->plugin_slug . '-plugin-styles', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css', array(), self::VERSION );
        //wp_enqueue_style( $this->plugin_slug . '-plugin-styles', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css', array(), self::VERSION );
        //wp_enqueue_style( $this->plugin_slug . '-plugin-styles', 'https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css', array(), self::VERSION );

        wp_register_style('bootstrapcss', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css', array());
        wp_enqueue_style('bootstrapcss');

        wp_register_style('bootstraptheme', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css', array());
        wp_enqueue_style('bootstraptheme');

        wp_register_style('jqueryuicss', 'https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css', array());
        wp_enqueue_style('jqueryuicss');

    }

    /**
     * Register and enqueues public-facing JavaScript files.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_register_script('interact', plugins_url('interact-1.2.4.min.js', __FILE__), array('jquery'));
        wp_enqueue_script('interact');

        wp_register_script('jqueryui', 'https://code.jquery.com/ui/1.11.4/jquery-ui.js', array('jquery'));
        wp_enqueue_script('jqueryui');

        wp_register_script('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js', array('jquery'));
        wp_enqueue_script('bootstrap');

//		wp_enqueue_script( $this->plugin_slug . '-plugin-script', 'https://code.jquery.com/jquery-1.10.2.min.js', array(), self::VERSION );
//      wp_enqueue_script( $this->plugin_slug . '-plugin-script', 'https://code.jquery.com/ui/1.11.4/jquery-ui.js', array('jquery'), self::VERSION );
//		wp_enqueue_script( $this->plugin_slug . '-plugin-script', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js', array('jquery'), self::VERSION );
    }

    /**
     * NOTE:  Actions are points in the execution of a page or process
     *        lifecycle that WordPress fires.
     *
     *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
     *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
     *
     * @since    1.0.0
     */
    public function action_method_name()
    {
        // @TODO: Define your action hook callback here
    }

    /**
     * NOTE:  Filters are points of execution in which WordPress modifies data
     *        before saving it or sending it to the browser.
     *
     *        Filters: http://codex.wordpress.org/Plugin_API#Filters
     *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
     *
     * @since    1.0.0
     */
    public function filter_method_name()
    {
        // @TODO: Define your filter hook callback here
    }

    public function display_workout_calendar($atts)
    {
        //workout: name, description, type, distance, duration
        //schedule: name, [{workout_id: day}]
        ob_start();
        $date = time();
        $day = date('d', $date);
        $month = date('m', $date);
        $year = date('Y', $date);
        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $title = date('F', $firstDay);
        $dayOfWeek = date('D', $firstDay);
        $blank = '';
        switch ($dayOfWeek) {
            case "Sun":
                $blank = 0;
                break;
            case "Mon":
                $blank = 1;
                break;
            case "Tue":
                $blank = 2;
                break;
            case "Wed":
                $blank = 3;
                break;
            case "Thu":
                $blank = 4;
                break;
            case "Fri":
                $blank = 5;
                break;
            case "Sat":
                $blank = 6;
                break;
        }
        $daysInMonth = cal_days_in_month(0, $month, $year);
        include_once('views/workout-calendar.php');
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /**
     * Adds a box to the main column on the Post and Page edit screens.
     */
    public function workout_add_meta_box()
    {
        add_meta_box(
            'workout_detail',
            'Workout Details',
            array($this, 'workout_meta_box_callback'),
            'workout'
        );
    }

    /**
     * Prints the box content.
     *
     * @param WP_Post $post The object for the current post/page.
     */
    public function workout_meta_box_callback($post)
    {

        // Add a nonce field so we can check for it later.
        wp_nonce_field('workout_meta_box', 'workout_meta_box_nonce');

        /*
         * Use get_post_meta() to retrieve an existing value
         * from the database and use the value for the form.
         */
        $value = get_post_meta($post->ID, 'workout_type', true);
        echo '<label for="workout_type">Workout Type:&nbsp;</label>';
        echo '<input type="text" id="workout_type" name="workout_type" value="' . esc_attr($value) . '" size="25" /><br />';

        $value = get_post_meta($post->ID, 'workout_distance', true);
        echo '<label for="workout_type">Workout Distance:&nbsp;</label>';
        echo '<input type="text" id="workout_distance" name="workout_distance" value="' . esc_attr($value) . '" size="25" /><br />';

        $value = get_post_meta($post->ID, 'workout_duration', true);
        echo '<label for="workout_type">Workout Duration:&nbsp;</label>';
        echo '<input type="text" id="workout_duration" name="workout_duration" value="' . esc_attr($value) . '" size="25" /><br />';

    }

    /**
     * When the post is saved, saves our custom data.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function workout_save_meta_box_data($post_id)
    {

        /*
         * We need to verify this came from our screen and with proper authorization,
         * because the save_post action can be triggered at other times.
         */

        // Check if our nonce is set.
        if (!isset($_POST['workout_meta_box_nonce'])) {
            return;
        }

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($_POST['workout_meta_box_nonce'], 'workout_meta_box')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions.
        if (isset($_POST['post_type']) && 'workout' == $_POST['post_type']) {

            if (!current_user_can('edit_page', $post_id)) {
                return;
            }

        } else {

            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
        }

        /* OK, it's safe for us to save the data now. */

        // Make sure that it is set.
        if (!isset($_POST['workout_type'])) {
            return;
        }

        // Sanitize user input.
        $my_data = sanitize_text_field($_POST['workout_type']);

        // Update the meta field in the database.
        update_post_meta($post_id, 'workout_type', $my_data);

        if (!isset($_POST['workout_distance'])) {
            return;
        }
        $my_data = sanitize_text_field($_POST['workout_distance']);
        update_post_meta($post_id, 'workout_distance', $my_data);

        if (!isset($_POST['workout_duration'])) {
            return;
        }
        $my_data = sanitize_text_field($_POST['workout_duration']);
        update_post_meta($post_id, 'workout_duration', $my_data);

    }

    /**
     * Adds a box to the main column on the Post and Page edit screens.
     */
    public function workout_schedule_add_meta_box()
    {
        add_meta_box(
            'workout_schedule_detail',
            'Workout Schedule Details',
            array($this, 'workout_schedule_meta_box_callback'),
            'workout_schedule'
        );
    }

    /**
     * Prints the box content.
     *
     * @param WP_Post $post The object for the current post/page.
     */
    public function workout_schedule_meta_box_callback($post)
    {

        // Add a nonce field so we can check for it later.
        wp_nonce_field('workout_schedule_meta_box', 'workout_schedule_meta_box_nonce');

        /*
         * Use get_post_meta() to retrieve an existing value
         * from the database and use the value for the form.
         */
        $value = get_post_meta($post->ID, 'workout_id', true);
        echo '<label for="workout_id">Select Workout:&nbsp;</label>';
        echo '<select id="workout_id" name="workout_id">';
        query_posts(array('post_type' => 'workout', 'posts_per_page' => -1, 'order' => 'ASC'));
        while (have_posts()) : the_post();
            ?>
            <option value="<?php echo get_the_ID() ?>"><?php echo get_the_title() ?></option>
        <?php
        endwhile;
        echo '</select><br />';

        $value = get_post_meta($post->ID, 'workout_days', true);
        $values = json_decode($value, true);
        echo '<label for="workout_days">Select Workout Days:&nbsp;</label>';
        echo '<select id="workout_days" name="workout_days[]" multiple size="7">';
        echo '<option value="1" ' . ((in_array(1, $values)) ? 'selected' : '') . '>Day 1</option>';
        echo '<option value="2" ' . ((in_array(2, $values)) ? 'selected' : '') . '>Day 2</option>';
        echo '<option value="3" ' . ((in_array(3, $values)) ? 'selected' : '') . '>Day 3</option>';
        echo '<option value="4" ' . ((in_array(4, $values)) ? 'selected' : '') . '>Day 4</option>';
        echo '<option value="5" ' . ((in_array(5, $values)) ? 'selected' : '') . '>Day 5</option>';
        echo '<option value="6" ' . ((in_array(6, $values)) ? 'selected' : '') . '>Day 6</option>';
        echo '<option value="7" ' . ((in_array(7, $values)) ? 'selected' : '') . '>Day 7</option>';
        echo '</select><br />';

    }

    /**
     * When the post is saved, saves our custom data.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function workout_schedule_save_meta_box_data($post_id)
    {

        /*
         * We need to verify this came from our screen and with proper authorization,
         * because the save_post action can be triggered at other times.
         */

        // Check if our nonce is set.
        if (!isset($_POST['workout_schedule_meta_box_nonce'])) {
            return;
        }

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($_POST['workout_schedule_meta_box_nonce'], 'workout_schedule_meta_box')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions.
        if (isset($_POST['post_type']) && 'workout_schedule' == $_POST['post_type']) {

            if (!current_user_can('edit_page', $post_id)) {
                return;
            }

        } else {

            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
        }

        /* OK, it's safe for us to save the data now. */

        if (!isset($_POST['workout_id'])) {
            return;
        }
        $my_data = sanitize_text_field($_POST['workout_id']);
        update_post_meta($post_id, 'workout_id', $my_data);

        if (!isset($_POST['workout_days'])) {
            return;
        }
        $my_data = json_encode($_POST['workout_days']);
        update_post_meta($post_id, 'workout_days', $my_data);

    }

    public function workout_callback()
    {
        $workoutId = $_POST['workout_id'];
        if ($workoutId == '') {
            $data = array();
            $data['error'] = 'Invalid Workout ID';
        } else {
            query_posts(
                array (
                    'post__in' => array($workoutId),
                    'post_type' => 'workout',
                    'posts_per_page' => 1
                )
            );
            $workoutTitle = '';
            while (have_posts()) : the_post();
                $workoutTitle = get_the_title();
            endwhile;
            $data = array();
            $data['workout_id'] = $workoutId;
            $data['workout_title'] = $workoutTitle;
            $data['workout_type'] = get_post_meta($workoutId, 'workout_type', true);
            $data['workout_distance'] = get_post_meta($workoutId, 'workout_distance', true);
            $data['workout_duration'] = get_post_meta($workoutId, 'workout_duration', true);
        }
        echo json_encode($data);
        wp_die();
    }

}