<?php
/**
 * Plugin Name: Недвижимость
 * Description: Плагин для управления объектами недвижимости.
 * Version: 1.0
 * Author: Andrii P
 */

function my_real_estate_init() {
    $args = array(
        'public' => true,
        'label'  => 'Объекты недвижимости',
    );
    register_post_type('real_estate', $args);

    $args = array(
        'label' => 'Районы',
        'rewrite' => array('slug' => 'region'),
        
    );
    register_taxonomy('region', 'real_estate', $args);
}

add_action('init', 'my_real_estate_init');

class Real_Estate_Filter_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'real_estate_filter_widget',
            'Фильтр Недвижимости', 
            array('description' => __('Виджет для фильтрации объектов недвижимости', 'text_domain')) // Args
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        
        echo do_shortcode('[real_estate_filter_form]');

        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Новый заголовок', 'text_domain');
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Заголовок:'); ?></label> 
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';

        return $instance;
    }
}
