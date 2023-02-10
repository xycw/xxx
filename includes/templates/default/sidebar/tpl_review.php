<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('sidebar/review.php')); ?>
<?php if (count($reviewSidebarList)>0) { ?>
<div class="block block-review">
	<div class="block-title">
        <strong><span><?php echo __('Review'); ?></span></strong>
    </div>
    <div class="block-content">
    <ol>
		<?php foreach ($reviewSidebarList as $_review) { ?>
		<li>
			<div class="review-top">
				<a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $_review['product_id']); ?>" title="<?php echo $_review['nameAlt']; ?>" class="product-image"><img width="<?php echo REVIEW_SIDEBAR_IMAGE_WIDTH; ?>" height="<?php echo REVIEW_SIDEBAR_IMAGE_HEIGHT; ?>" alt="<?php echo $_review['nameAlt']; ?>" src="<?php echo get_small_image($_review['image'], REVIEW_SIDEBAR_IMAGE_WIDTH, REVIEW_SIDEBAR_IMAGE_HEIGHT); ?>" /></a>
				<div class="review-details">
					<p class="product-name"><a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $_review['product_id']); ?>" title="<?php echo $_review['nameAlt']; ?>"><?php echo $_review['name']; ?></a></p>
					<span class="star star<?php echo $_review['rating']; ?>"></span>
				</div>
			</div>
			<div class="review-bottom">
				<?php echo __('By <span>%s</span>', $_review['nickname']); ?>&nbsp;<?php echo date_short($_review['date_added']); ?><br>
				<?php echo $_review['content']; ?>
			</div>
		</li>
		<?php } ?>
	</ol>
	<script type="text/javascript">decorateList($('.block-review ol'))</script>
    </div>
</div>
<?php } ?>
