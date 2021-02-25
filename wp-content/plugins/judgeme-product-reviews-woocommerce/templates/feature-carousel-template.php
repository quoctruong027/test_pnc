<div class='jdgm-carousel-wrapper'>
	<h2 class='jdgm-carousel-title'><?php echo $atts['title']; ?></h2>
	<a href='<?php echo $atts['all-reviews-page']; ?>' class='jdgm-all-reviews-rating-wrapper'>
		<div data-score='<?php echo $all_review_rating['all_reviews_rating']; ?>' class='jdgm-all-reviews-rating'></div> from <?php echo $all_review_count['all_reviews_count']; ?> reviews
	</a>
	<?php echo $carousel['featured_carousel']; ?>
</div>
