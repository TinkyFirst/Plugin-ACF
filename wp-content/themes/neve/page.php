<?php
/**
 * The template for displaying all pages.
 *
 * @package Neve
 * @since   1.0.0
 */

$container_class = apply_filters( 'neve_container_class_filter', 'container', 'single-page' );

get_header();

$context = class_exists( 'WooCommerce', false ) && ( is_cart() || is_checkout() || is_account_page() ) ? 'woo-page' : 'single-page';
?>

<div class="<?php echo esc_attr( $container_class ); ?> single-page-container">
	<div class="row">
		<?php do_action( 'neve_do_sidebar', $context, 'left' ); ?>
		<div class="nv-single-page-wrap col">
			<?php
			/**
			 * Executes actions before the page header.
			 *
			 * @since 2.4.0
			 */
			do_action( 'neve_before_page_header' );

			/**
			 * Executes the rendering function for the page header.
			 *
			 * @param string $context The displaying location context.
			 *
			 * @since 1.0.7
			 */
			do_action( 'neve_page_header', $context );

			/**
			 * Executes actions before the page content.
			 *
			 * @param string $context The displaying location context.
			 *
			 * @since 1.0.7
			 */
			do_action( 'neve_before_content', $context );

			if ( have_posts() ) {
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/content', 'page' );
				}
			} else {
				get_template_part( 'template-parts/content', 'none' );
			}

			/**
			 * Executes actions after the page content.
			 *
			 * @param string $context The displaying location context.
			 *
			 * @since 1.0.7
			 */
			do_action( 'neve_after_content', $context );
			
			?>
		</div>
		<?php do_action( 'neve_do_sidebar', $context, 'right' ); ?>
	</div>
</div>

<?php

$args = array(
    'post_type' => 'real_estate', // Указываем, что нужно получить записи real_estate
    'posts_per_page' => -1 // Количество постов на странице (-1 означает без ограничений)
);

/*
$the_query = new WP_Query($args);

if ($the_query->have_posts()) : 
    while ($the_query->have_posts()) : $the_query->the_post(); 
        ?>
        <h1><?php the_title(); ?></h1>
        <p>Назва дому: <?php the_field('name'); ?></p>
        <p>Координаты: <?php the_field('coordinates'); ?></p>
        <p>Количество этажей: <?php the_field('floors'); ?></p>
        <p>Тип строения: <?php the_field('building_type'); ?></p>
        <!-- Другие поля и информация -->
        <?php
    endwhile;
endif;
*/

?>
<?php get_footer(); 


