<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('product_list.php')); ?>
<div class="category-products">
	<?php require($template->get_template_dir('tpl_modules_filter.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_filter.php'); ?>
	<div class="hidden-xs">
		<?php require($template->get_template_dir('tpl_modules_toolbar.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_toolbar.php'); ?>
	</div>
	<?php if ($productListCount = count($productList)) { ?>
		<?php if ($toolbarMode['current']!='list') { ?>
		<div class="row">
			<?php foreach ($productList as $_product) { ?>
				<div class="col-xs-6 col-sm-4 col-md-3 products-grid">
					<a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $_product['product_id']); ?>" title="<?php echo $_product['nameAlt']; ?>" class="product-image">
						<img class="img-responsive" width="<?php echo GRID_IMAGE_WIDTH; ?>" height="<?php echo GRID_IMAGE_HEIGHT; ?>" alt="<?php echo $_product['nameAlt']; ?>" src="<?php echo get_small_image($_product['image'], GRID_IMAGE_WIDTH, GRID_IMAGE_HEIGHT); ?>" />
					</a>
					<div class="product-shop">
						<h2 class="product-name"><a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $_product['product_id']); ?>" title="<?php echo $_product['nameAlt']; ?>"><?php echo $_product['name']; ?></a></h2>
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
				</div>
			<?php } ?>
		</div>
		<?php } ?>
	<?php } else { ?>
	<p class="note-msg"><?php echo __('There are no products matching the selection.'); ?></p>
	<?php } ?>
	<div class="toolbar-bottom">
		<?php require($template->get_template_dir('tpl_modules_toolbar.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_toolbar.php'); ?>
	</div>
</div>
