<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('sidebar/specials.php')); ?>
<?php if (count($specialsSidebarList)>0) { ?>
<div class="block block-specials">
	<div class="block-title">
        <strong><span><?php echo __('Specials'); ?></span></strong>
    </div>
    <div class="block-content">
    	<ol id="specials-items" class="mini-products-list">
    		<?php foreach ($specialsSidebarList as $_product) { ?>
    		<li class="item">
    			<a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $_product['product_id']); ?>" title="<?php echo $_product['nameAlt']; ?>" class="product-image"><img width="<?php echo SPECIALS_SIDEBAR_IMAGE_WIDTH; ?>" height="<?php echo SPECIALS_SIDEBAR_IMAGE_HEIGHT; ?>" alt="<?php echo $_product['nameAlt']; ?>" src="<?php echo get_small_image($_product['image'], SPECIALS_SIDEBAR_IMAGE_WIDTH, SPECIALS_SIDEBAR_IMAGE_HEIGHT); ?>" /></a>
    			<div class="product-shop">
    				<p class="product-name"><a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $_product['product_id']); ?>" title="<?php echo $_product['nameAlt']; ?>"><?php echo $_product['name']; ?></a></p>
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
    		<?php } ?>
    	</ol>
    	<script type="text/javascript">decorateList($('#specials-items'))</script>
    </div>
</div>
<?php } ?>
