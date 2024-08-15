<?php
// Подключение стилей и скриптов дочерней темы
function storefront_child_enqueue_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'storefront_child_enqueue_styles');

// Подключение скриптов
function storefront_child_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('custom-ajax-script', get_stylesheet_directory_uri() . '/js/custom-ajax.js', array('jquery'), null, true);

    // Передаем ajaxurl в скрипт
    wp_localize_script('custom-ajax-script', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'storefront_child_enqueue_scripts');

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
        'cities',
        'side'
    );
}
add_action('add_meta_boxes', 'add_cities_meta_boxes');

function cities_location_meta_box_callback($post) {
    wp_nonce_field('save_cities_location_data', 'cities_location_meta_box_nonce');

    $latitude = get_post_meta($post->ID, '_cities_latitude', true);
    $longitude = get_post_meta($post->ID, '_cities_longitude', true);

    echo '<p><label for="cities_latitude">Latitude:</label>';
    echo '<input type="text" id="cities_latitude" name="cities_latitude" value="' . esc_attr($latitude) . '" size="25" /></p>';
    echo '<p><label for="cities_longitude">Longitude:</label>';
    echo '<input type="text" id="cities_longitude" name="cities_longitude" value="' . esc_attr($longitude) . '" size="25" /></p>';
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

        $api_key = '9336f7d22c260f217273cbeb601d978f';
        $response = wp_remote_get("https://api.openweathermap.org/data/2.5/weather?lat={$latitude}&lon={$longitude}&appid={$api_key}&units=metric");
        
        if (is_wp_error($response)) {
            $temperature = 'Error fetching data';
        } else {
            $data = wp_remote_retrieve_body($response);
            $weather = json_decode($data);
            $temperature = isset($weather->main->temp) ? $weather->main->temp : 'N/A';
        }

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

// Обработчик AJAX-запросов для поиска городов
function custom_ajax_search() {
    global $wpdb;
    $search = sanitize_text_field($_POST['search']);

    $query = $wpdb->prepare("
        SELECT p.ID, p.post_title, t.name as country_name, pm.meta_value as latitude, pm2.meta_value as longitude
        FROM {$wpdb->prefix}posts p
        LEFT JOIN {$wpdb->prefix}term_relationships tr ON (p.ID = tr.object_id)
        LEFT JOIN {$wpdb->prefix}term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
        LEFT JOIN {$wpdb->prefix}terms t ON (tt.term_id = t.term_id)
        LEFT JOIN {$wpdb->prefix}postmeta pm ON (p.ID = pm.post_id AND pm.meta_key = '_cities_latitude')
        LEFT JOIN {$wpdb->prefix}postmeta pm2 ON (p.ID = pm2.post_id AND pm2.meta_key = '_cities_longitude')
        WHERE p.post_type = 'cities' AND p.post_status = 'publish' AND tt.taxonomy = 'countries' AND p.post_title LIKE %s
    ", '%' . $wpdb->esc_like($search) . '%');

    $cities = $wpdb->get_results($query);

    if ($cities) {
        foreach ($cities as $city) {
            $weather_data = wp_remote_get("https://api.openweathermap.org/data/2.5/weather?lat={$city->latitude}&lon={$city->longitude}&units=metric&appid=9336f7d22c260f217273cbeb601d978f");
            if (is_wp_error($weather_data)) {
                $temperature = 'Error fetching data';
            } else {
                $weather = wp_remote_retrieve_body($weather_data);
                $weather = json_decode($weather, true);
                $temperature = isset($weather['main']['temp']) ? esc_html($weather['main']['temp']) . '°C' : 'N/A';
            }

            echo '<tr>';
            echo '<td>' . esc_html($city->country_name) . '</td>';
            echo '<td>' . esc_html($city->post_title) . '</td>';
            echo '<td>' . esc_html($city->latitude) . '</td>';
            echo '<td>' . esc_html($city->longitude) . '</td>';
            echo '<td>' . $temperature . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5">No cities found.</td></tr>';
    }

    wp_die();
}
add_action('wp_ajax_nopriv_custom_ajax_search', 'custom_ajax_search');
add_action('wp_ajax_custom_ajax_search', 'custom_ajax_search');
?>
