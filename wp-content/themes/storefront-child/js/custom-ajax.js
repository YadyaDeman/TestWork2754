jQuery(document).ready(function($) {
    // Функция для выполнения AJAX-запроса
    function fetchCities(search = '') {
        $.ajax({
            url: ajax_object.ajax_url, // Используем переменную ajax_object.ajax_url из wp_localize_script
            type: 'POST',
            data: {
                action: 'custom_ajax_search', // Название действия, зарегистрированное в functions.php
                search: search
            },
            success: function(response) {
                $('#cities-table tbody').html(response); // Обновляем содержимое таблицы
            },
            error: function() {
                $('#cities-table tbody').html('<tr><td colspan="5">Error loading data.</td></tr>'); // Сообщение об ошибке
            }
        });
    }

    // Выполняем первоначальный запрос при загрузке страницы
    fetchCities();

    // Обработчик события для поля поиска
    $('#search-city').on('keyup', function() {
        var value = $(this).val(); // Получаем значение из поля поиска
        fetchCities(value); // Выполняем запрос с введенным значением
    });
});
