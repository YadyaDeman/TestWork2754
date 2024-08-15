<?php
/* Template Name: Cities Table */
get_header();

// Custom action hook до таблицы
do_action('before_cities_table');
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
        <!-- Здесь будут выводиться результаты AJAX-запроса -->
    </tbody>
</table>

<script>
jQuery(document).ready(function($) {
    function fetchCities(search = '') {
        $.ajax({
            url: ajax_object.ajax_url, // Использую глобальную переменную ajax_object
            type: 'POST',
            data: {
                action: 'custom_ajax_search',
                search: search
            },
            success: function(response) {
                $('#cities-table tbody').html(response);
            },
            error: function() {
                $('#cities-table tbody').html('<tr><td colspan="5">Error loading data.</td></tr>');
            }
        });
    }

    // Первоначальная загрузка всех городов
    fetchCities();

    $('#search-city').on('keyup', function() {
        var value = $(this).val();
        fetchCities(value);
    });
});
</script>

<?php
// Custom action hook после таблицы
do_action('after_cities_table');

get_footer();
