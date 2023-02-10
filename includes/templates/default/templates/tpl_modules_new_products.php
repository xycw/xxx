<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('new_products.php')); ?>
<?php if ($newProductsListCount = count($newProductsList)) {?>
<div class="new-products">
	<div class="page-title">
		<h2 class="subtitle"><?php echo __('New Arrivals'); ?></h2>
	</div>
	<?php $column_count = NEW_PRODUCTS_PER_ROW; ?>
	<?php $i=0; ?>
	<?php foreach ($newProductsList as $_product) { ?>
	<?php if ($i++%$column_count==0) { ?>
    <ul class="products-grid">
    <?php } ?>
    	<li class="item">
    		<a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $_product['product_id']); ?>" title="<?php echo $_product['nameAlt']; ?>" class="product-image"><img width="<?php echo NEW_PRODUCTS_IMAGE_WIDTH; ?>" height="<?php echo NEW_PRODUCTS_IMAGE_HEIGHT; ?>" alt="<?php echo $_product['nameAlt']; ?>" src="<?php echo get_small_image($_product['image'], NEW_PRODUCTS_IMAGE_WIDTH, NEW_PRODUCTS_IMAGE_HEIGHT); ?>" /></a>
    		<div class="product-shop">
	    		<h3 class="product-name"><a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $_product['product_id']); ?>" title="<?php echo $_product['nameAlt']; ?>"><?php echo $_product['name']; ?></a></h3>
	            <div class="price-box">
	            	<?php if ($_product['specials_price'] > 0) { ?>
	            	<p class="old-price">
	            		<span class="price-label"><?php echo __('Regular Price'); ?>:</span>
	                	<span class="price"><?php echo $currencies->display_price($_product['price']); ?></span>
	                </p>
	                <p class="specials-price">
	                	<span class="price-label"><?php echo __('Special Price'); ?>:</span>
	                	<span class="price"><?php echo $currencies->display_price($_product['specials_price']); ?></span>
	                </p>
	                <?php if ($_product['save_off']>0) { ?>
	                <p class="save-off">
	                	<span class="price-label"><?php echo __('Save Off'); ?>:</span>
	                	<span class="price"><?php echo $_product['save_off']; ?>%</span>
	                </p>
	                <?php } ?>
	            	<?php } else { ?>
	            	<span class="regular-price">
	                	<span class="price"><?php echo $currencies->display_price($_product['price']); ?></span>
	                </span>
	            	<?php } ?>
	            </div>
	            <?php if ($_product['review']['total']>0) { ?>
				<div class="review-box">
					<span class="star star<?php echo $_product['review']['rating']; ?>"></span>
					<a rel="nofollow" href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $_product['product_id']); ?>#customer-review">(<?php echo $_product['review']['total']; ?>)</a>
				</div>
				<?php } ?>
			</div>
    	</li>
    <?php if ($i%$column_count==0 || $i==$newProductsListCount) { ?>
    </ul>
    <?php } ?>
    <?php } ?>
    <script type="text/javascript">decorateList($('.new-products ul.products-grid'));</script>
</div>
<?php } ?>
