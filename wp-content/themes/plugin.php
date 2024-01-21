<?php
/**
 * Plugin Name: Тестове
 * Description: Плагін для управління об'єктами нерухомості.
 * Version: 1.2
 * Author: Andrii Popovych
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Ініціалізація Custom Post Type та Taxonomy
function realestate_custom_init() {
    $args = array(
        'public' => true,
        'label'  => 'Обєкти нерухомості',
        'supports' => array( 'title', 'editor', 'thumbnail' ),
    );
    register_post_type( 'real_estate', $args );

    $taxonomy_args = array(
        'hierarchical' => true,
        'label' => 'Райони',
        'query_var' => true,
        'rewrite' => true,
    );
    register_taxonomy( 'district', 'real_estate', $taxonomy_args );
}

add_action( 'init', 'realestate_custom_init' );

function realestate_filter_shortcode() {
    ob_start();
    ?>
    <form id="realestate-filter-form">
        <div>
            <label for="district">Район:</label>
            <select id="district" name="district">
                <option value="">Виберіть район</option>
                <?php
                $terms = get_terms( array(
                    'taxonomy' => 'district',
                    'hide_empty' => false,
                ) );
                foreach ( $terms as $term ) {
                    echo '<option value="' . $term->slug . '">' . $term->name . '</option>';
                }
                ?>
            </select>
        </div>
        <div>
            <label for="building_type">Тип будівлі:</label>
            <select id="building_type" name="building_type">
                <option value="">Виберіть тип</option>
                <option value="panel">Панель</option>
                <option value="brick">Кирпич</option>
                <option value="block">Пеноблок</option>
            </select>
        </div>
        <div>
            <input type="submit" value="Пошук">
        </div>
    </form>
    <div id="realestate-filter-results">
        <!-- Тут будуть результати -->
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#realestate-filter-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url : realestateAjax.ajaxurl,
                type : 'post',
                data : {
                    action : 'realestate_filter',
                    query : form.serialize()
                },
                success : function( response ) {
                    $('#realestate-filter-results').html( response );
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode( 'realestate_filter', 'realestate_filter_shortcode' );

function realestate_ajax_filter() {
    // Перевіряємо вхідні дані
    $district = isset( $_POST['district'] ) ? sanitize_text_field( $_POST['district'] ) : '';
    $building_type = isset( $_POST['building_type'] ) ? sanitize_text_field( $_POST['building_type'] ) : '';

    // Підготовка запиту
    $args = array(
        'post_type' => 'real_estate',
        'posts_per_page' => 10,
        'post_status' => 'publish',
        'tax_query' => array(),
        'meta_query' => array()
    );

    // Фільтр за районом
    if ( !empty( $district ) ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'district',
            'field'    => 'slug',
            'terms'    => $district
        );
    }

    // Фільтр за типом будівлі
    if ( !empty( $building_type ) ) {
        $args['meta_query'][] = array(
            'key'     => 'building_type',
            'value'   => $building_type,
            'compare' => '='
        );
    }

    // Виконуємо запит
    $query = new WP_Query( $args );

    // Виведення результатів
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            // Тут може бути ваш код для відображення кожного об'єкта нерухомості
            echo '<div class="realestate-item">';
            echo '<h3>' . get_the_title() . '</h3>';
            // Виведіть інші необхідні поля
            echo '</div>';
        }
    } else {
        echo '<p>Не знайдено</p>';
    }

    wp_reset_postdata();
    wp_die();
}

add_action( 'wp_ajax_realestate_filter', 'realestate_ajax_filter' );
add_action( 'wp_ajax_nopriv_realestate_filter', 'realestate_ajax_filter' );

// Додавання скриптів для AJAX
function realestate_enqueue_scripts() {
    wp_enqueue_script( 'realestate-ajax', plugin_dir_url( __FILE__ ) . 'js/realestate-ajax.js', array( 'jquery' ), '1.0', true );
    wp_localize_script( 'realestate-ajax', 'realestateAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}

add_action( 'wp_enqueue_scripts', 'realestate_enqueue_scripts' );

// Клас віджету
class RealEstate_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            'realestate_widget', // ID віджета
            'Фільтр Нерухомості', // назва віджета
            array( 'description' => 'Форма фільтрації обєктів нерухомості' ) // Опис
        );
    }

    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }

        // Вміст віджета
        echo '<div id="realestate_widget_filter">';
        // Тут може бути ваша форма фільтрації
        echo '</div>';

        echo $args['after_widget'];
    }

    public function form( $instance ) {
        // Форма у адмін-панелі
        $title = ! empty( $instance['title'] ) ? $instance['title'] : 'Новий заголовок';
        ?>
        <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>">Заголовок:</label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

        return $instance;
    }

}

// Реєстрація віджету
function register_realestate_widget() {
    register_widget( 'RealEstate_Widget' );
}

add_action( 'widgets_init', 'register_realestate_widget' );


// Додавання віджету (потрібно додаткове кодування для класу віджету)
// class RealEstate_Widget extends WP_Widget { ... }

// register_widget( 'RealEstate_Widget' );
?>