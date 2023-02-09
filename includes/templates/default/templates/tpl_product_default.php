<?php if ($message_stack->size('product') > 0) echo $message_stack->output('product'); ?>
<div class="product-view">
	<div class="product-essential">
	<form id="form-validate" method="post" action="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $productInfo['product_id'] . '&action=add_product'); ?>">
		<div class="no-display">
			<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
			<input type="hidden" value="<?php echo $productInfo['product_id']; ?>" name="pID" />
		</div>
		<div class="product-shop-box">
			<div class="product-name">
                <h1><?php echo $productInfo['name']; ?></h1>
            </div>
            <div class="review-box">
            	<?php if ($productInfo['review']['total']>0) { ?>
				<span class="star star<?php echo $productInfo['review']['rating']; ?>"></span>
				<a rel="nofollow" href="javascript:void(0)" onclick="reviewTab();">(<?php echo $productInfo['review']['total']; ?>)</a>&nbsp;
				<?php } else { ?>
				<a rel="nofollow" href="javascript:void(0)" onclick="reviewTab();"><?php echo __('Write a review'); ?></a>
				<?php } ?>
				<!-- AddThis Button BEGIN -->
				<a class="addthis_button" href="https://www.addthis.com/bookmark.php?v=250&amp;pubid=xa-4fc579603c9e81d7"><img src="https://s7.addthis.com/static/btn/v2/lg-share-en.gif" width="125" height="16" alt="Bookmark and Share" style="border:0"/></a>
				<script type="text/javascript" src="https://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4fc579603c9e81d7"></script>
				<!-- AddThis Button END -->
			</div>
			<div class="divider"></div>
			<p class="sku"><?php echo __('Model'); ?>: <span><?php echo $productInfo['sku']; ?></span></p>
            <?php if ($productInfo['in_stock']==1) { ?>
            <p class="availability in-stock"><?php echo __('Availability'); ?>: <span><?php echo __('In stock'); ?></span></p>
            <?php } else { ?>
            <p class="availability out-of-stock"><?php echo __('Availability'); ?>: <span><?php echo __('Out of Stock'); ?></span></p>
            <?php } ?>
            <div class="price-box">
            	<?php if ($productInfo['specials_price'] > 0) { ?>
            	<p class="old-price">
            		<span class="price-label"><?php echo __('Regular Price'); ?>:</span>
                	<span class="price"><?php echo $currencies->display_price($productInfo['price']); ?></span>
                </p>
                <p class="specials-price">
                	<span class="price-label"><?php echo __('Special Price'); ?>:</span>
                	<span class="price"><?php echo $currencies->display_price($productInfo['specials_price']); ?></span>
                </p>
                <?php if ($productInfo['save_off']>0) { ?>
                <p class="save-off">
                	<span class="price-label"><?php echo __('Save Off'); ?>:</span>
                	<span class="price"><?php echo $productInfo['save_off']; ?>%</span>
                </p>
                <?php } ?>
            	<?php } else { ?>
            	<span class="regular-price">
                	<span class="price"><?php echo $currencies->display_price($productInfo['price']); ?></span>
                </span>
            	<?php } ?>
            </div>
            <div class="divider"></div>
            <ul class="product-shop-links">
            	<li><a target="_blank" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=3'); ?>"><?php echo __('Privacy & Security'); ?></a></li>
            	<li>|</li>
            	<li><a target="_blank" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=5'); ?>"><?php echo __('Returns Policy'); ?></a></li>
            	<?php if (isset($productInfo['attribute']) && count($productInfo['attribute']) > 0) { ?>
            	<li>|</li>
            	<li><a target="_blank" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=8'); ?>"><?php echo __('Size Chart'); ?></a></li>
            	<?php } ?>
            	<li>|</li>
            	<li><a target="_blank" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=7'); ?>"><?php echo __('Faq'); ?></a></li>
            </ul>
            <?php require($template->get_template_dir('tpl_modules_color.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_color.php'); ?>
            <?php if (isset($productInfo['attribute']) && count($productInfo['attribute']) > 0) { ?>
            <?php if (not_null($productInfo['short_description'])) { ?>
            <div class="short-description">
				<h2><?php echo __('Quick Overview'); ?></h2>
				<div class="std"><?php echo $productInfo['short_description']; ?></div>
			</div>
			<?php } ?>
            <?php require($template->get_template_dir('tpl_modules_attribute.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_attribute.php'); ?>
			<?php if ($productInfo['in_stock']==1) { ?>
			<div class="product-options-bottom">
            	<div class="add-to-cart">
            		<?php if ($productInfo['qty'] == true) { ?>
                	<label for="qty"><?php echo __('Qty'); ?></label>
        			<input type="text" class="input-text required-entry qty" title="<?php echo __('Qty'); ?>" value="1" maxlength="3" id="qty" name="qty" />
        			<?php } ?>
                	<button type="submit" class="button btn-incart" title="<?php echo __('Add to Cart'); ?>"><span><span><?php echo __('Add to Cart'); ?></span></span></button>
            	</div>
			</div>
			<?php } ?>
			<?php } else { ?>
			<?php if ($productInfo['in_stock']==1) { ?>
			<div class="add-to-cart">
				<?php if ($productInfo['qty'] == true) { ?>
				<label for="qty"><?php echo __('Qty'); ?></label>
				<input type="text" class="input-text required-entry qty" title="<?php echo __('Qty'); ?>" value="1" maxlength="3" id="qty" name="qty" />
				<?php } ?>
				<button type="submit" class="button btn-incart" title="<?php echo __('Add to Cart'); ?>"><span><span><?php echo __('Add to Cart'); ?></span></span></button>
			</div>
			<?php } ?>
			<?php if (not_null($productInfo['short_description'])) { ?>
			<div class="short-description">
				<h2><?php echo __('Quick Overview'); ?></h2>
				<div class="std"><?php echo $productInfo['short_description']; ?></div>
			</div>
			<?php } ?>
			<?php } ?>
		</div>
		<div class="product-img-box">
			<p class="product-image">
				<a href="<?php echo get_large_image($productInfo['image'], POPUP_IMAGE_WIDTH, POPUP_IMAGE_HEIGHT); ?>" data-lightbox="lightbox-images"><img width="<?php echo THUMBNAIL_IMAGE_WIDTH; ?>" height="<?php echo THUMBNAIL_IMAGE_HEIGHT; ?>" alt="<?php echo $productInfo['nameAlt']; ?>" src="<?php echo get_large_image($productInfo['image'], THUMBNAIL_IMAGE_WIDTH, THUMBNAIL_IMAGE_HEIGHT); ?>" /></a>
			</p>
			<?php require($template->get_template_dir('tpl_modules_additional_image.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_additional_image.php'); ?>
			<?php require($template->get_template_dir('tpl_modules_product_prev_next.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_product_prev_next.php'); ?>
		</div>
	</form>
<script type="text/javascript"><!--
<?php if (defined('FACEBOOK_ID') && strlen(FACEBOOK_ID) > 0) { ?>
$('#form-validate').validate({submitHandler:function(form){fbq('track', 'AddToCart');setTimeout(function(){form.submit();}, 1000);}});
<?php } else { ?>
$('#form-validate').validate();
<?php } ?>
//--></script>
	</div>
	<div class="product-collateral">
		<ul class="box-tab">
			<?php if (not_null($productInfo['description'])) { ?>
        	<li><h2><a href="#description"><?php echo __('Description'); ?></a></h2></li>   
        	<?php } ?>
            <li><h2><a id="customer-review-tab" href="#customer-review"><?php echo __('Reviews') ?></a></h2></li>
        </ul>
		<?php if (not_null($productInfo['description'])) { ?>
		<div class="box-collateral box-description" id="description">
    		<div class="std"><?php echo $productInfo['description']; ?></div>
    	</div>
    	<?php } ?>
    	<?php require($template->get_template_dir('tpl_modules_review.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_review.php'); ?>
    	<?php require($template->get_template_dir('tpl_modules_related.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_related.php'); ?>
		<?php require($template->get_template_dir('tpl_modules_also_purchased.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_also_purchased.php'); ?>
	</div>
</div>
<script type="text/javascript"><!--
$('.box-tab a').tabs();
function reviewTab(){
	if($('#customer-review-tab').length > 0){
		$('#customer-review-tab').click();
	}
	$('html,body').animate({scrollTop:$('#customer-review').offset().top});
}
if(window.location.hash == "#customer-review"){
    reviewTab();
}
//--></script>
<script type="text/javascript">decorateList($('ul.box-tab'));</script>