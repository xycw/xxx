<div class="block block-cart">
	<div class="block-title">
        <strong><span><?php echo __('My Cart'); ?></span></strong>
    </div>
    <div class="block-content">
    	<?php if ($_SESSION['shopping_cart']->getItems() > 0) { ?>
    	 <div class="summary">
			<p class="amount"><?php echo __('There is <a href="%s">%s item(s)</a> in your cart.', href_link(FILENAME_SHOPPING_CART, '', 'SSL'), $_SESSION['shopping_cart']->getItems()); ?></p>
			<p class="subtotal"><span class="label"><?php echo __('Cart Subtotal'); ?>:</span><span class="price"><?php echo $currencies->display_price($_SESSION['shopping_cart']->getSubtotal()); ?></span></p>
		</div>
        <div class="actions">
			<button onclick="setLocation('<?php echo href_link(FILENAME_SHOPPING_CART, '', 'SSL'); ?>');" class="button" title="<?php echo __('Checkout'); ?>" type="button"><span><span><?php echo __('Checkout'); ?></span></span></button>
    	</div>
    	<?php } else { ?>
    	<p class="empty"><?php echo __('You have no items in your shopping cart.'); ?></p>
    	<?php } ?>
    </div>
</div>
