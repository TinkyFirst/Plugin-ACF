<?php
/**
 * Neve functions.php file
 *
 * Author:          Andrei Baicus <andrei@themeisle.com>
 * Created on:      17/08/2018
 *
 * @package Neve
 */

define( 'NEVE_VERSION', '3.7.5' );
define( 'NEVE_INC_DIR', trailingslashit( get_template_directory() ) . 'inc/' );
define( 'NEVE_ASSETS_URL', trailingslashit( get_template_directory_uri() ) . 'assets/' );
define( 'NEVE_MAIN_DIR', get_template_directory() . '/' );
define( 'NEVE_BASENAME', basename( NEVE_MAIN_DIR ) );
define( 'NEVE_PLUGINS_DIR', plugin_dir_path( dirname( __DIR__ ) ) . 'plugins/' );

if ( ! defined( 'NEVE_DEBUG' ) ) {
	define( 'NEVE_DEBUG', false );
}
define( 'NEVE_NEW_DYNAMIC_STYLE', true );
/**
 * Buffer which holds errors during theme inititalization.
 *
 * @var WP_Error $_neve_bootstrap_errors
 */
global $_neve_bootstrap_errors;

$_neve_bootstrap_errors = new WP_Error();

if ( version_compare( PHP_VERSION, '7.0' ) < 0 ) {
	$_neve_bootstrap_errors->add(
		'minimum_php_version',
		sprintf(
		/* translators: %s message to upgrade PHP to the latest version */
			__( "Hey, we've noticed that you're running an outdated version of PHP which is no longer supported. Make sure your site is fast and secure, by %1\$s. Neve's minimal requirement is PHP%2\$s.", 'neve' ),
			sprintf(
			/* translators: %s message to upgrade PHP to the latest version */
				'<a href="https://wordpress.org/support/upgrade-php/">%s</a>',
				__( 'upgrading PHP to the latest version', 'neve' )
			),
			'7.0'
		)
	);
}
/**
 * A list of files to check for existance before bootstraping.
 *
 * @var array Files to check for existance.
 */

$_files_to_check = defined( 'NEVE_IGNORE_SOURCE_CHECK' ) ? [] : [
	NEVE_MAIN_DIR . 'vendor/autoload.php',
	NEVE_MAIN_DIR . 'style-main-new.css',
	NEVE_MAIN_DIR . 'assets/js/build/modern/frontend.js',
	NEVE_MAIN_DIR . 'assets/apps/dashboard/build/dashboard.js',
	NEVE_MAIN_DIR . 'assets/apps/customizer-controls/build/controls.js',
];
foreach ( $_files_to_check as $_file_to_check ) {
	if ( ! is_file( $_file_to_check ) ) {
		$_neve_bootstrap_errors->add(
			'build_missing',
			sprintf(
			/* translators: %s: commands to run the theme */
				__( 'You appear to be running the Neve theme from source code. Please finish installation by running %s.', 'neve' ), // phpcs:ignore WordPress.Security.EscapeOutput
				'<code>composer install --no-dev &amp;&amp; yarn install --frozen-lockfile &amp;&amp; yarn run build</code>'
			)
		);
		break;
	}
}
/**
 * Adds notice bootstraping errors.
 *
 * @internal
 * @global WP_Error $_neve_bootstrap_errors
 */
function _neve_bootstrap_errors() {
	global $_neve_bootstrap_errors;
	printf( '<div class="notice notice-error"><p>%1$s</p></div>', $_neve_bootstrap_errors->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

if ( $_neve_bootstrap_errors->has_errors() ) {
	/**
	 * Add notice for PHP upgrade.
	 */
	add_filter( 'template_include', '__return_null', 99 );
	switch_theme( WP_DEFAULT_THEME );
	unset( $_GET['activated'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	add_action( 'admin_notices', '_neve_bootstrap_errors' );

	return;
}

/**
 * Themeisle SDK filter.
 *
 * @param array $products products array.
 *
 * @return array
 */
function neve_filter_sdk( $products ) {
	$products[] = get_template_directory() . '/style.css';

	return $products;
}

add_filter( 'themeisle_sdk_products', 'neve_filter_sdk' );
add_filter(
	'themeisle_sdk_compatibilities/' . NEVE_BASENAME,
	function ( $compatibilities ) {

		$compatibilities['NevePro'] = [
			'basefile'  => defined( 'NEVE_PRO_BASEFILE' ) ? NEVE_PRO_BASEFILE : '',
			'required'  => '2.3',
			'tested_up' => '2.7',
		];

		return $compatibilities;
	}
);
require_once 'globals/migrations.php';
require_once 'globals/utilities.php';
require_once 'globals/hooks.php';
require_once 'globals/sanitize-functions.php';
require_once get_template_directory() . '/start.php';

/**
 * If the new widget editor is available,
 * we re-assign the widgets to hfg_footer
 */
if ( neve_is_new_widget_editor() ) {
	/**
	 * Re-assign the widgets to hfg_footer
	 *
	 * @param array  
	 * @param string 
	 * @param string 
	 *
	 * @return mixed
	 */
	function neve_customizer_custom_widget_areas( $section_args, $section_id, $sidebar_id ) {
		if ( strpos( $section_id, 'widgets-footer' ) ) {
			$section_args['panel'] = 'hfg_footer';
		}

		return $section_args;
	}

	add_filter( 'customizer_widgets_section_args', 'neve_customizer_custom_widget_areas', 10, 3 );
}

require_once get_template_directory() . '/header-footer-grid/loader.php';

add_filter(
	'neve_welcome_metadata',
	function() {
		return [
			'is_enabled' => ! defined( 'NEVE_PRO_VERSION' ),
			'pro_name'   => 'Neve Pro Addon',
			'logo'       => get_template_directory_uri() . '/assets/img/dashboard/logo.svg',
			'cta_link'   => tsdk_utmify( 'https://themeisle.com/themes/neve/upgrade/?discount=LOYALUSER582&dvalue=50', 'neve-welcome', 'notice' ),
		];
	}
);

function real_estate_filter_form_shortcode() {
    ob_start();
    ?>
    <div id="real-estate-filter-form">
        <form action="" method="post">
            <input type="text" name="name" placeholder="Название дома" />
            <input type="text" name="coordinates" placeholder="Координаты" />

            <select name="floors">
                <option value="">Выберите количество этажей</option>
                <?php for ($i = 1; $i <= 20; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>

            <fieldset>
                <legend>Тип строения</legend>
                <label><input type="checkbox" name="building_type[]" value="panel"> Панель</label>
                <label><input type="checkbox" name="building_type[]" value="brick"> Кирпич</label>
                <label><input type="checkbox" name="building_type[]" value="block"> Пеноблок</label>
            </fieldset>

            <input type="submit" value="Поиск" />
        </form>
    </div>
    <div id="real-estate-search-results"></div>
    
    <?php
    return ob_get_clean();
}
add_shortcode('real_estate_filter_form', 'real_estate_filter_form_shortcode');

function real_estate_enqueue_scripts() {
    $script_url = get_template_directory_uri() . '/js/real-estate-ajax.js';

    wp_enqueue_script('real-estate-ajax', $script_url, array('jquery'), null, true);

    wp_localize_script('real-estate-ajax', 'realEstateAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}

add_action('wp_enqueue_scripts', 'real_estate_enqueue_scripts');

function filter_real_estate_callback() {
    // Получение параметров из AJAX запроса
    $name = sanitize_text_field($_POST['name']);
    $coordinates = sanitize_text_field($_POST['coordinates']);
    $floors = sanitize_text_field($_POST['floors']);
    $building_type = isset($_POST['building_type']) ? $_POST['building_type'] : [];

    // Начало построения запроса
    $meta_query = ['relation' => 'AND'];

    if (!empty($name)) {
        $meta_query[] = [
            'key' => 'name',
            'value' => $name,
            'compare' => 'LIKE'
        ];
    }

    if (!empty($coordinates)) {
        $meta_query[] = [
            'key' => 'coordinates',
            'value' => $coordinates,
            'compare' => 'LIKE'
        ];
    }

    if (!empty($floors)) {
        $meta_query[] = [
            'key' => 'floors',
            'value' => $floors,
            'compare' => '='
        ];
    }

    if (!empty($building_type)) {
        $building_type_query = ['relation' => 'OR'];
        foreach ($building_type as $type) {
            $building_type_query[] = [
                'key' => 'building_type',
                'value' => $type,
                'compare' => 'LIKE'
            ];
        }
        $meta_query[] = $building_type_query;
    }

    $args = array(
        'post_type' => 'real_estate',
        'posts_per_page' => 10,
        'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
        'meta_query' => $meta_query
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            echo '<div class="real-estate-item">';
            echo '<h2>' . get_the_title() . '</h2>'; 
            echo '<p>Название дома: ' . get_field('name') . '</p>';
            echo '<p>Координаты: ' . get_field('coordinates') . '</p>';
            echo '<p>Количество этажей: ' . get_field('floors') . '</p>';
            
    $building_type = get_field('building_type');
if ($building_type) {
    echo '<p>Тип строения: ' . esc_html($building_type) . '</p>';
} else {
    echo '<p>Тип строения: не указан</p>';
}


            $terms = get_the_terms(get_the_ID(), 'region');
            if (!empty($terms) && !is_wp_error($terms)) {
                echo '<p>Район: ';
                foreach ($terms as $term) {
                    echo $term->name . ' ';
                }
                echo '</p>';
            }

            echo '</div>';
        }
      
        echo '<div class="real-estate-pagination">';
        previous_posts_link('« Предыдущие');
        next_posts_link('Следующие »', $query->max_num_pages);
        echo '</div>';
    } else {
        echo 'Записи не найдены.';
    }

    wp_reset_postdata();
    wp_die();
}

add_action('wp_ajax_filter_real_estate', 'filter_real_estate_callback');
add_action('wp_ajax_nopriv_filter_real_estate', 'filter_real_estate_callback');

 function register_real_estate_filter_widget() {
    register_widget('Real_Estate_Filter_Widget');
}

add_action('widgets_init', 'register_real_estate_filter_widget');

