<?php
/* Template Name: Cities Table */
get_header();

// Custom action hook до таблицы
do_action('before_cities_table');

global $wpdb;
$query = "SELECT p.ID, p.post_title, t.name as country_name, pm.meta_value as latitude, pm2.meta_value as longitude
          FROM {$wpdb->prefix}posts p
          LEFT JOIN {$wpdb->prefix}term_relationships tr ON (p.ID = tr.object_id)
          LEFT JOIN {$wpdb->prefix}term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
          LEFT JOIN {$wpdb->prefix}terms t ON (tt.term_id = t.term_id)
          LEFT JOIN {$wpdb->prefix}postmeta pm ON (p.ID = pm.post_id AND pm.meta_key = '_cities_latitude')
          LEFT JOIN {$wpdb->prefix}postmeta pm2 ON (p.ID = pm2.post_id AND pm2.meta_key = '_cities_longitude')
          WHERE p.post_type = 'cities' AND p.post_status = 'publish' AND tt.taxonomy = 'countries'";

$cities = $wpdb->get_results($query);

?>
<input type="text" id="search-city" placeholder="Search cities...">
<table id="cities-table">
    <thead>
        <tr>
            <th>Country</th>
            <th>City</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <th>Temperature</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($cities as $city) { ?>
            <tr>
                <td><?php echo esc_html($city->country_name); ?></td>
                <td><?php echo esc_html($city->post_title); ?></td>
                <td><?php echo esc_html($city->latitude); ?></td>
                <td><?php echo esc_html($city->longitude); ?></td>
                <td>
                    <?php
                    $weather_data = file_get_contents("https://api.openweathermap.org/data/2.5/weather?lat={$city->latitude}&lon={$city->longitude}&units=metric&appid=9336f7d22c260f217273cbeb601d978f");
                    $weather = json_decode($weather_data, true);
                    echo esc_html($weather['main']['temp']) . '°C';
                    ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<script>
jQuery(document).ready(function($) {
    $('#search-city').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#cities-table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>

<?php
// Custom action hook после таблицы
do_action('after_cities_table');

get_footer();
