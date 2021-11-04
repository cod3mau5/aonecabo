<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package understrap
 */

$the_theme = wp_get_theme();
$container = get_theme_mod( 'understrap_container_type' );
?>

<?php get_sidebar( 'footerfull' ); ?>

<div class="wrapper" id="wrapper-footer">
	<div class="<?php echo esc_attr( $container ); ?>">
		<div class="row">
			<div class="col-md-1"></div>
			<div class="col-md-2">
				<a href="/">
					<img src="/wp-content/uploads/2017/11/cropped-logo.png" alt="" class="img-responsive" style="margin-top: 50px;">
				</a>
			</div>
			<div class="col-md-6 text-center">
				<footer class="site-footer" id="colophon">
					<?php wp_nav_menu( array( 'theme_location' => 'footer-menu' ) ); ?>
					<address>
						Calle C Manzana 19 Lote 30. <br>Colonia Villa Bonita Residencial, San José Del Cabo, B.C.S.
					</address>
					<div class="site-info">
						<p>
							&copy; A ONE CABO DELUXE TRANSPORTATION<br>
							<small>
								<a href="http://www.blackkraken.mx" target="_blank">
									POWERED BY BLACKKRAKEN.MX
								</a>
							</small>
						</p>
					</div>
				</footer><!-- #colophon -->
			</div><!--col end -->
			<div class="col-md-2">
				<a href="https://www.tripadvisor.com.mx/Profile/aonecabo2019" target="_blank">
					<img src="http://www.aonecabodeluxetransportation.com/wp-content/uploads/2019/04/trip-logo.png"
						 alt=""
						 class="img-responsive"
						 style="margin-top: 70px;"
					>
				</a>
			</div>
		</div><!-- row end -->
	</div><!-- container end -->
</div><!-- wrapper end -->

</div><!-- #page we need this extra closing tag here -->

<?php wp_footer(); ?>

</body>

</html>

