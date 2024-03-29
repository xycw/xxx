<div class="step-title">
	<span class="number">5</span>
	<h2><?php echo __('Order Review'); ?></h2>
</div>
<div class="step" id="checkout-step-order_review">
	<table id="checkout-order_review-table" class="data-table cart-table">
	<colgroup>
		<col />
		<col width="1" />
		<col width="1" />
	</colgroup>
	<thead>
		<tr>
			<th rowspan="1"><span class="nobr"><?php echo __('Product Name'); ?></span></th>
			<th rowspan="1" class="a-center"><?php echo __('Qty'); ?></th>
			<th colspan="1" class="a-center"><?php echo __('Subtotal'); ?></th>
		</tr>
	</thead>
	<tfoot>
        <tr class="subtotal">
			<td colspan="2" class="a-right"><?php echo __('Subtotal'); ?></td>
			<td class="a-right"><span class="price"><?php echo $currencies->display_price($shoppingCart['subtotal']); ?></span></td>
		</tr>
		<?php if ($shoppingCart['discount'] > 0) { ?>
		<tr class="discount">
			<td colspan="2" class="a-right"><?php echo __('Discount'); ?></td>
			<td class="a-right"><span class="price">-<?php echo $currencies->display_price($shoppingCart['discount']); ?></span></td>
		</tr>
		<?php } ?>
		<?php if ($shoppingCart['coupon_code'] != '') { ?>
		<tr class="coupon_discount">
			<td colspan="2" class="a-right"><?php echo __('Coupon (<span>%s</span>)', $shoppingCart['coupon_code']); ?></td>
			<td class="a-right"><span class="price">-<?php echo $currencies->display_price($shoppingCart['coupon_discount']); ?></span></td>
		</tr>
		<?php } ?>
		<tr class="shipping_method_fee">
			<td colspan="2" class="a-right"><?php echo __('Shipping & Handling'); ?></td>
			<td class="a-right"><span id="order_review_shipping_method_fee" class="price"><?php echo $shippingPaymentMethodJSON[$shipping_method_default][$payment_method_default]['shipping_method_fee']; ?></span></td>
		</tr>
		<tr class="shipping_method_insurance_fee">
			<td colspan="2" class="a-right"><?php echo __('Insurance Fee'); ?></td>
			<td class="a-right"><span id="order_review_shipping_method_insurance_fee" class="price"><?php echo $shippingPaymentMethodJSON[$shipping_method_default][$payment_method_default]['shipping_method_insurance_fee']; ?></span></td>
		</tr>
		<tr>
			<td colspan="2" class="a-right"><?php echo __('Tax Fee'); ?></td>
			<td class="a-right"><span id="order_review_payment_method_fee" class="price"><?php echo $shippingPaymentMethodJSON[$shipping_method_default][$payment_method_default]['payment_method_fee']; ?></span></td>
		</tr>
		<tr class="grand_total">
			<td colspan="2" class="a-right"><strong><?php echo __('Grand Total'); ?></strong></td>
			<td class="a-right" ><strong><span id="order_review_grand_total" class="price"><?php echo $shippingPaymentMethodJSON[$shipping_method_default][$payment_method_default]['order_total']; ?></span></strong></td>
		</tr>
	</tfoot>
	<tbody>
	<?php foreach ($shoppingCart['product'] as $cartID => $cartProduct) { ?>
		<tr>
			<td>
				<h2 class="product-name"><?php echo $cartProduct['name']; ?></h2>
				<dl class="item-options">
				<?php foreach ($cartProduct['attribute'] as $_option_name => $_option_value_name) {?>
					<dt><?php echo __($_option_name); ?></dt>
					<dd><?php echo __($_option_value_name); ?></dd>
				<?php } ?>
				</dl>
			</td>
			<td class="a-center">
	        	<?php echo $cartProduct['qty']; ?>
	    	</td>
	    	<td class="a-right">
				<span class="cart-price">
					<span class="price"><?php echo $currencies->display_price($cartProduct['price']*$cartProduct['qty']); ?></span>
				</span>
	        </td>
		</tr>
	<?php } ?>
    </tbody>
	</table>
    <div id="checkout-order_review-submit" class="buttons-set">
        <button type="submit" class="button btn-checkout" title="<?php echo __('Place Order'); ?>"><span><span><?php echo __('Place Order'); ?></span></span></button>
    </div>
</div>
