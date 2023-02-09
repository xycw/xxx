<?php if ($message_stack->size('product') > 0) echo $message_stack->output('product'); ?>
<div class="product-view">
	<div class="product-essential">
		<div class="product-img-box">
			<?php require($template->get_template_dir('tpl_modules_additional_image.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_additional_image.php'); ?>
			<?php if ($productInfo['specials_price'] > 0 && $productInfo['save_off'] > 0) { ?>
				<p class="save-off">
					<span class="price-label"><?php echo __('Off'); ?>:</span>
					<span class="price"><?php echo $productInfo['save_off']; ?>%</span>
				</p>
			<?php } ?>
		</div>
	<form id="form-validate" method="post" action="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $productInfo['product_id'] . '&action=add_product'); ?>">
		<div class="no-display">
			<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
			<input type="hidden" value="<?php echo $productInfo['product_id']; ?>" name="pID" />
		</div>
		<?php require($template->get_template_dir('tpl_modules_product_prev_next.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_product_prev_next.php'); ?>
		<div class="product-shop-box">
			<div class="product-name">
                <h1><?php echo $productInfo['name']; ?></h1>
				<p class="sku"><?php echo __('Model'); ?>: <span><?php echo $productInfo['sku']; ?></span></p>
				<?php if ($productInfo['in_stock']==1) { ?>
					<p class="availability in-stock"><?php echo __('Availability'); ?>: <span><?php echo __('In stock'); ?></span></p>
				<?php } else { ?>
					<p class="availability out-of-stock"><?php echo __('Availability'); ?>: <span><?php echo __('Out of Stock'); ?></span></p>
				<?php } ?>
            </div>
			<div class="price-box">
				<?php if ($productInfo['specials_price'] > 0) { ?>
					<p class="specials-price">
						<span class="price-label"><?php echo __('Special Price'); ?>:</span>
						<span class="price"><?php echo $currencies->display_price($productInfo['specials_price']); ?></span>
					</p>
					<p class="old-price">
						<span class="price-label"><?php echo __('Regular Price'); ?>:</span>
						<span class="price"><?php echo $currencies->display_price($productInfo['price']); ?></span>
					</p>
				<?php } else { ?>
					<span class="regular-price">
                	<span class="price"><?php echo $currencies->display_price($productInfo['price']); ?></span>
                </span>
				<?php } ?>
			</div>
            <div class="attribute-add">
            <?php require($template->get_template_dir('tpl_modules_color.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_color.php'); ?>
            <?php if (isset($productInfo['attribute']) && count($productInfo['attribute']) > 0) { ?>
            <?php require($template->get_template_dir('tpl_modules_attribute.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_attribute.php'); ?>
            <?php } ?>
            <?php if ($productInfo['in_stock']==1) { ?>
            <?php if ($productInfo['qty'] == true) { ?>
            <div class="add-to-qty">
				<select id="qty" name="qty" class="required-entry qty form-control">
					<?php for ($i = 1; $i < 101; $i++) { ?>
						<option value="<?php echo $i ?>"><?php echo 'QTY: ' . $i ?></option>
					<?php } ?>
				</select>
            </div>
            <?php } ?>
			<div class="add-to-cart">
				<button type="submit" class="btn btn-black btn-block btn-lg btn-cart" title="<?php echo __('Add to Cart'); ?>"><i class="iconfont btn-icon">&#xe600;</i><?php echo __('Add to Cart'); ?></button>
			</div>
			<?php } ?>
			</div>
			<div class="review-box">
				<?php if ($productInfo['review']['total']>0) { ?>
					<a class="btn btn-default btn-block btn-lg btn-review" rel="nofollow" href="javascript:;" onclick="reviewTab();"><span class="star star<?php echo $productInfo['review']['rating']; ?>"></span>(<?php echo $productInfo['review']['total']; ?>)</a>&nbsp;
				<?php } else { ?>
					<a class="btn btn-default btn-block btn-lg btn-review" rel="nofollow" href="javascript:;" onclick="reviewTab();"><?php echo __('Write a review'); ?></a>
				<?php } ?>
			</div>
            <?php if (not_null($productInfo['short_description'])) { ?>
            <div class="short-description">
				<h2><?php echo __('Quick Overview'); ?></h2>
				<div class="std"><?php echo $productInfo['short_description']; ?></div>
			</div>
			<?php } ?>
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
		<div class="product-tab">
			<ul>
				<?php if (not_null($productInfo['description'])) { ?>
				<li>
					<div class="tab-title"><h2><?php echo __('Description'); ?></h2><i class="iconfont">&#xe609;</i></div>
					<div class="tab-content box-description" id="description"><?php echo $productInfo['description']; ?></div>
				</li>
				<?php } ?>
				<li><?php require($template->get_template_dir('tpl_modules_review.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_review.php'); ?></li>
			</ul>
		</div>

		<script type="text/javascript">
			$(function(){
				$('.tab-title').on('click', function(){
					var $this = $(this);
					if ($this.hasClass('on')){
						$this.find('.iconfont').html('&#xe609;');
						$this.removeClass('on').next('.tab-content').stop(true, true).slideUp(300);
					} else {
						$this.parents('.product-tab').find('.iconfont').html('&#xe609;');
						$this.find('.iconfont').html('&#xe643;');
						$this.parent().siblings('li').find('.tab-title').removeClass('on').next('.tab-content').stop(true, true).slideUp(300);
						$this.addClass('on').next('.tab-content').stop(true, true).slideDown(300).show();
					}
				});
			});
		</script>
    	<?php require($template->get_template_dir('tpl_modules_related.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_related.php'); ?>
		<?php require($template->get_template_dir('tpl_modules_also_purchased.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_also_purchased.php'); ?>
	</div>
</div>
<script type="text/javascript"><!--
function reviewTab(){
	if ($('#customer-review').is(':hidden')){
		$('#customer-review').siblings('.tab-title').addClass('on').find('.iconfont').html('&#xe643;');
		$('#customer-review').stop(true, true).slideDown(300).show();
		$('html,body').animate({scrollTop:$('#customer-review').offset().top},500);
	} else {
		$('html,body').animate({scrollTop:$('#customer-review').offset().top},500);
	}
}
//--></script>
