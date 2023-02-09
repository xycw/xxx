<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('review_list.php')); ?>
<?php if (count($reviewList) > 0) { ?>
<div class="review-list">
	<div class="page-title">
		<h2 class="subtitle"><?php echo __('Review'); ?></h2>
	</div>
	<ol id="review-items">
		<?php foreach ($reviewList as $_review) { ?>
		<li>
			<a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $_review['product_id']); ?>" title="<?php echo $_review['nameAlt']; ?>" class="product-image"><img alt="<?php echo $_review['nameAlt']; ?>" src="<?php echo get_small_image($_review['image'], MOBILE_IMAGE_WIDTH, MOBILE_IMAGE_HEIGHT); ?>" /></a>
			<div class="review-items-box">
				<div class="f-fix">
					<h3 class="product-name"><a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $_review['product_id']); ?>" title="<?php echo $_review['nameAlt']; ?>"><?php echo $_review['name']; ?></a></h3>
					<span class="star star<?php echo $_review['rating']; ?>"></span><br>
					<?php echo __('By <span>%s</span>', $_review['nickname']); ?>&nbsp;<?php echo date_short($_review['date_added']); ?><br>
					<?php echo $_review['content']; ?>
				</div>
			</div>
		</li>
		<?php } ?>
	</ol>
	<script type="text/javascript">decorateList($('#review-items'))</script>
</div>
<?php } ?>
