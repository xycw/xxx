<div class="shopping_cart">
<?php if ($shoppingCart['items'] > 0) { ?>
<?php $tempTotalQty = 0; ?>
	<div class="page-title">
		<h1><?php echo __('Shopping Cart'); ?></h1>
	</div>
	<?php if ($message_stack->size('shopping_cart') > 0) echo $message_stack->output('shopping_cart'); ?>
	<table class="table table-bordered data-table cart-table" id="shopping-cart-table">
		<colgroup>
			<col width="1" />
			<col />
			<col width="1" />
		</colgroup>
		<thead class="hidden-xs">
			<tr>
				<th rowspan="1">&nbsp;</th>
				<th rowspan="1"><span class="nobr"><?php echo __('Product Name'); ?></span></th>
				<th rowspan="1" class="a-center"><?php echo __('Qty'); ?></th>
				<th rowspan="1" class="a-center"><?php echo __('Subtotal'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr class="subtotal">
				<td colspan="3" class="a-right"><?php echo __('Subtotal'); ?><span class="price visible-xs"><?php echo $currencies->display_price($shoppingCart['subtotal']); ?></span></td>
				<td class="a-right hidden-xs"><span class="price"><?php echo $currencies->display_price($shoppingCart['subtotal']); ?></span></td>
			</tr>
			<?php if ($shoppingCart['discount'] > 0) { ?>
			<tr class="discount">
				<td colspan="3" class="a-right"><?php echo __('Discount'); ?><span class="price visible-xs">-<?php echo $currencies->display_price($shoppingCart['discount']); ?></span></td>
				<td class="a-right hidden-xs"><span class="price">-<?php echo $currencies->display_price($shoppingCart['discount']); ?></span></td>
			</tr>
			<?php } ?>
			<?php if ($shoppingCart['coupon_code'] != '') { ?>
			<tr class="coupon_discount">
				<td colspan="3" class="a-right"><?php echo __('Coupon (<span>%s</span>)', $shoppingCart['coupon_code']); ?><a class="btn-remove f-right" title="<?php echo __('Cancel Coupon'); ?>" href="<?php echo href_link(FILENAME_SHOPPING_CART, 'action=remove_coupon', 'SSL'); ?>"><?php echo __('Cancel Coupon'); ?></a><span class="price visible-xs">-<?php echo $currencies->display_price($shoppingCart['coupon_discount']); ?></span></td>
				<td class="a-right hidden-xs"><span class="price">-<?php echo $currencies->display_price($shoppingCart['coupon_discount']); ?></span></td>
			</tr>
			<?php } ?>
			<tr class="grand_total">
				<?php $grand_total = $shoppingCart['subtotal'] - $shoppingCart['discount'] - $shoppingCart['coupon_discount'];?>
				<td colspan="3"><a class="btn-continue f-left" title="<?php echo __('Continue Shopping'); ?>" href="<?php echo back_url(); ?>"><small>« </small><?php echo __('Continue Shopping'); ?></a><strong class="f-right"><?php echo __('Grand Total'); ?><span class="price visible-xs"><?php echo $currencies->display_price($grand_total); ?></span></strong></td>
				<td class="a-right hidden-xs"><strong><span class="price"><?php echo $currencies->display_price($grand_total); ?></span></strong></td>
			</tr>
		</tfoot>
		<tbody>
		<form method="post" id="fmShopCart" action="<?php echo href_link(FILENAME_SHOPPING_CART, 'action=update_product', 'SSL'); ?>">
		<div class="no-display">
			<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
		</div>
		<?php foreach ($shoppingCart['product'] as $cartID => $cartProduct) { ?>
		<?php $tempTotalQty += $cartProduct['qty']; ?>
			<tr>
				<td class="td-product-image">
					<a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $cartProduct['product_id']); ?>" title="<?php echo $cartProduct['name']; ?>" class="product-image">
						<img width="<?php echo SHOPPING_CART_IMAGE_WIDTH; ?>" height="<?php echo SHOPPING_CART_IMAGE_HEIGHT; ?>" alt="<?php echo $cartProduct['name']; ?>" src="<?php echo get_small_image($cartProduct['image'], SHOPPING_CART_IMAGE_WIDTH, SHOPPING_CART_IMAGE_HEIGHT); ?>" />
					</a>
				</td>
				<td>
					<h2 class="product-name">
						<a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $cartProduct['product_id']); ?>">
							<?php echo $cartProduct['name']; ?>
						</a>
					</h2>
					<ul class="item-options">
						<?php foreach ($cartProduct['attribute'] as $_option_name => $_option_value_name) {?>
							<li><span class="option-name"><?php echo __($_option_name); ?> :</span> <?php echo __($_option_value_name); ?></li>
						<?php } ?>
					</ul>
					<span class="cart-price">
						<span class="price"><?php echo $currencies->display_price($cartProduct['price']); ?></span>
					</span>
				</td>
				<td class="a-right" id="tdQty">
					<select class="form-control" name="cartQty[<?php echo $cartID; ?>]" onchange="$('#fmShopCart').submit()">
						<?php for($i = 1; $i < 100; $i++){ ?>
							<?php if($cartProduct['qty'] == $i) { ?>
								<option value="<?php echo $i; ?>" selected="selected"><?php echo $i; ?></option>
							<?php } else { ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
							<?php } ?>
						<?php } ?>
					</select>
					<div class="action-cart">
						<a class="btn-remove-new" title="<?php echo __('Delete'); ?>" href="<?php echo href_link(FILENAME_SHOPPING_CART, 'action=remove_product&cartpID='.$cartID, 'SSL'); ?>"><?php echo __('Delete'); ?></a>
					</div>
				</td>
				<td class="a-right hidden-xs">
					<span class="cart-price">
						<span class="price"><?php echo $currencies->display_price($cartProduct['price']*$cartProduct['qty']); ?></span>
					</span>
				</td>
			</tr>
		<?php } ?>
		</form>
		</tbody>
		</table>
<?php if (SHOPPING_CART_MODE == 1) { ?>
	<div class="cart-coupon">
		<div class="col-md-6 col-sm-12 col-xs-12 pd10">
			<?php if ($shoppingCart['coupon_code'] == '') { ?>
				<form class="form-inline" method="post" action="<?php echo href_link(FILENAME_SHOPPING_CART, 'action=add_coupon', 'SSL'); ?>">
					<div class="no-display">
						<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
					</div>
					<div class="form-group">
						<p class="form-control-static"><?php echo __('Coupon Codes'); ?>:</p>
					</div>
					<div class="form-group">
						<input type="text" class="form-control input-text" value="" name="coupon_code" id="coupon_code" />
					</div>
					<button type="submit" class="button btn-coupon" title="<?php echo __('Apply Coupon'); ?>"><span><span><?php echo __('Apply Coupon'); ?></span></span></button>
				</form>
			<?php } ?>
		</div>
		<div class="col-md-6 col-sm-12 col-xs-12 a-right pd10"><button type="button" class="button btn-proceed-checkout" title="<?php echo __('Proceed to Checkout'); ?>" onclick="setLocation('<?php echo href_link(FILENAME_CHECKOUT, '', 'SSL'); ?>');"><span><span><?php echo __('Proceed to Checkout'); ?></span></span></button></div>
	</div>
<?php } else { ?>
	<?php if ($shoppingCart['coupon_code'] == '') { ?>
	<form class="form-inline" method="post" action="<?php echo href_link(FILENAME_SHOPPING_CART, 'action=add_coupon', 'SSL'); ?>">
		<div class="no-display">
			<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
		</div>
		<div class="cart-coupon">
			<div class="col-md-6 col-sm-12 col-xs-12 pd10">
			<div class="form-group">
				<p class="form-control-static"><?php echo __('Coupon Codes'); ?>:</p>
			</div>
			<div class="form-group">
				<input type="text" class="form-control input-text" value="" name="coupon_code" id="coupon_code" />
			</div>
			<button type="submit" class="button btn-coupon" title="<?php echo __('Apply Coupon'); ?>"><span><span><?php echo __('Apply Coupon'); ?></span></span></button>
			</div>
		</div>
	</form>
<?php } ?>
<?php } ?>
	<?php if (SHOPPING_CART_MODE == 0) { ?>
		<?php require($template->get_template_dir('tpl_modules_checkout_info.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_checkout_info.php'); ?>
	<?php } ?>
<?php } else { ?>
	<div class="page-title">
	    <h1><?php echo __('Shopping Cart is Empty'); ?></h1>
	</div>
	<div class="cart-empty">
        <p><?php echo __('You have no items in your shopping cart.'); ?></p>
	    <p><?php echo __('Click <a href="%s">here</a> to continue shopping.', href_link(FILENAME_INDEX)); ?></p>
	</div>
<?php } ?>
</div>
