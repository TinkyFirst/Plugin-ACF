<?php
// Перед циклом WordPress
var_dump(get_field('name_home'));
var_dump(get_field('adress'));
var_dump(get_field('count'));
var_dump(get_field('type'));
?>
<?php
// Перевірка наявності постів
if (have_posts()) {
    while (have_posts()) {
        the_post();
        
        // Отримання і відображення кастомних полів
        $name_home = get_field('name_home');
        $adress = get_field('adress');
        $count = get_field('count');
        $type = get_field('type');

        // Виведення інформації
        echo '<div class="realestate-item">';
        echo '<h3>' . esc_html($name_home) . '</h3>';
        echo '<p>Адреса: ' . esc_html($adress) . '</p>';
        echo '<p>Кількість поверхів: ' . esc_html($count) . '</p>';
        echo '<p>Тип будівлі: ' . esc_html($type) . '</p>';
        echo '</div>';
    }
} else {
    echo '<p>Обєкти нерухомості не знайдено.</p>';
}
?>