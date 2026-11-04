<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package aThemes
 */
?>
		</div>
	<!-- #main --></div>

	<?php
		/* A sidebar in the footer? Yep. You can can customize
		 * your footer with up to four columns of widgets.
		 */
		get_sidebar( 'footer' );
	?>

	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="clearfix container">
			<?php do_action('athemes_footer'); ?>
		</div>
	<!-- #colophon --></footer>

<?php wp_footer(); ?>

</body>
</html>