<?php
// Подключение стилей и скриптов дочерней темы
function storefront_child_enqueue_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'storefront_child_enqueue_styles');

// Регистрация пользовательского типа записи "Cities"
function register_cities_post_type() {
    $labels = array(
        'name'               => _x('Cities', 'post type general name', 'text_domain'),
        'singular_name'      => _x('City', 'post type singular name', 'text_domain'),
        'menu_name'          => _x('Cities', 'admin menu', 'text_domain'),
        'name_admin_bar'     => _x('City', 'add new on admin bar', 'text_domain'),
        'add_new'            => _x('Add New', 'city', 'text_domain'),
        'add_new_item'       => __('Add New City', 'text_domain'),
        'new_item'           => __('New City', 'text_domain'),
        'edit_item'          => __('Edit City', 'text_domain'),
        'view_item'          => __('View City', 'text_domain'),
        'all_items'          => __('All Cities', 'text_domain'),
        'search_items'       => __('Search Cities', 'text_domain'),
        'parent_item_colon'  => __('Parent Cities:', 'text_domain'),
        'not_found'          => __('No cities found.', 'text_domain'),
        'not_found_in_trash' => __('No cities found in Trash.', 'text_domain'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'cities'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'supports'           => array('title'),
    );

    register_post_type('cities', $args);
}
add_action('init', 'register_cities_post_type');

// Регистрация таксономии "Countries"
function create_countries_taxonomy() {
    register_taxonomy(
        'countries',
        'cities',
        array(
            'label' => __('Countries'),
            'rewrite' => array('slug' => 'countries'),
            'hierarchical' => true,
        )
    );
}
add_action('init', 'create_countries_taxonomy');

// Метабоксы для широты и долготы
function add_cities_meta_boxes() {
    add_meta_box(
        'cities_location',
        'City Location',
        'cities_location_meta_box_callback',
        'cities'
    );
}
add_action('add_meta_boxes', 'add_cities_meta_boxes');

function cities_location_meta_box_callback($post) {
    wp_nonce_field('save_cities_location_data', 'cities_location_meta_box_nonce');

    $latitude = get_post_meta($post->ID, '_cities_latitude', true);
    $longitude = get_post_meta($post->ID, '_cities_longitude', true);

    echo '<label for="cities_latitude">Latitude:</label>';
    echo '<input type="text" id="cities_latitude" name="cities_latitude" value="' . esc_attr($latitude) . '" size="25" />';
    echo '<br><br>';
    echo '<label for="cities_longitude">Longitude:</label>';
    echo '<input type="text" id="cities_longitude" name="cities_longitude" value="' . esc_attr($longitude) . '" size="25" />';
}

function save_cities_meta_box_data($post_id) {
    if (!isset($_POST['cities_location_meta_box_nonce']) || !wp_verify_nonce($_POST['cities_location_meta_box_nonce'], 'save_cities_location_data')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['cities_latitude'])) {
        $latitude = sanitize_text_field($_POST['cities_latitude']);
        update_post_meta($post_id, '_cities_latitude', $latitude);
    }

    if (isset($_POST['cities_longitude'])) {
        $longitude = sanitize_text_field($_POST['cities_longitude']);
        update_post_meta($post_id, '_cities_longitude', $longitude);
    }
}
add_action('save_post', 'save_cities_meta_box_data');

// Виджет температуры города
class City_Temperature_Widget extends WP_Widget {
    function __construct() {
        parent::__construct(
            'city_temperature_widget',
            __('City Temperature Widget', 'text_domain'),
            array('description' => __('A Widget to display city temperature', 'text_domain'))
        );
    }

    public function widget($args, $instance) {
        $city_id = !empty($instance['city_id']) ? $instance['city_id'] : '';

        $city_name = get_the_title($city_id);
        $latitude = get_post_meta($city_id, '_cities_latitude', true);
        $longitude = get_post_meta($city_id, '_cities_longitude', true);

        $api_key = 'your_openweathermap_api_key';
        $response = wp_remote_get("https://api.openweathermap.org/data/2.5/weather?lat={$latitude}&lon={$longitude}&appid={$api_key}&units=metric");
        $data = wp_remote_retrieve_body($response);
        $weather = json_decode($data);
        $temperature = isset($weather->main->temp) ? $weather->main->temp : 'N/A';

        echo $args['before_widget'];
        echo $args['before_title'] . esc_html($city_name) . $args['after_title'];
        echo '<p>Temperature: ' . esc_html($temperature) . '°C</p>';
        echo $args['after_widget'];
    }

    public function form($instance) {
        $city_id = !empty($instance['city_id']) ? $instance['city_id'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('city_id'); ?>"><?php _e('City ID:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('city_id'); ?>" name="<?php echo $this->get_field_name('city_id'); ?>" type="text" value="<?php echo esc_attr($city_id); ?>" />
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['city_id'] = (!empty($new_instance['city_id'])) ? sanitize_text_field($new_instance['city_id']) : '';
        return $instance;
    }
}

function register_city_temperature_widget() {
    register_widget('City_Temperature_Widget');
}
add_action('widgets_init', 'register_city_temperature_widget');
?>
