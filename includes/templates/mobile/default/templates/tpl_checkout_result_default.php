<?php
$titleColor = '';
switch($orderInfo['order_status_id']) {
	case '3':
		$titleColor = 'color:green';
		$message_stack->add('checkout_result', __('Thank you for your order. The order confirm email will send within 24 hours!'), 'success');
		break;
	case '4':
		$titleColor = 'color:red';
		$message_stack->add('checkout_result', __('Sorry, your order didn\'t go through because of unauthorized card. Please make an order with different card.'));
		break;
}
?>
<div class="checkout-result">
	<div class="page-title">
		<h1 style="<?php echo $titleColor; ?>"><?php echo __('Order #%s - %s', put_orderNO($orderInfo['order_id']), __(get_order_status_name($orderInfo['order_status_id']))); ?></h1>
		<p class="order-date"><?php echo __('Order Date'); ?>: <?php echo date_long($orderInfo['date_added']); ?></p>
	</div>
	<div class="my-account-content">
		<?php if ($message_stack->size('checkout_result') > 0) echo $message_stack->output('checkout_result'); ?>
		<div class="box-account box-info">
			<div class="box-head">
				<h2><?php echo __('Order Information'); ?></h2>
			</div>
			<div class="col2-set">
				<div class="col-1">
					<div class="box">
						<div class="box-title">
							<h2><?php echo __('Billing Address'); ?></h2>
						</div>
						<div class="box-content">
							<address><?php echo address_format($orderInfo['billing']); ?></address>
						</div>
					</div>
				</div>
				<div class="col-2">
					<div class="box">
						<div class="box-title">
							<h2><?php echo __('Shipping Address'); ?></h2>
						</div>
						<div class="box-content">
							<address><?php echo address_format($orderInfo['shipping']); ?></address>
						</div>
					</div>
				</div>
			</div>
			<div class="col2-set">
				<div class="col-1">
					<div class="box">
						<div class="box-title">
							<h2><?php echo __('Payment Method'); ?></h2>
						</div>
						<div class="box-content"><?php echo $orderInfo['payment_method']['name']; ?><br /><?php echo $orderInfo['payment_method']['description']; ?></div>
					</div>
				</div>
				<div class="col-2">
					<div class="box">
						<div class="box-title">
							<h2><?php echo __('Shipping Method'); ?></h2>
						</div>
						<div class="box-content"><?php echo $orderInfo['shipping_method']['name']; ?><br /><?php echo $orderInfo['shipping_method']['description']; ?></div>
					</div>
				</div>
			</div>
		</div>
		<div class="box-account order-status">
			<div class="box-head">
				<h2><?php echo __('Order Status'); ?></h2>
			</div>
			<table class="data-table">
				<thead>
				<tr>
					<th><?php echo __('Date'); ?></th>
					<th><?php echo __('Status'); ?></th>
					<th><?php echo __('Remarks'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($orderStatusHistory as $_status) {?>
					<tr>
						<td><?php echo date_short($_status['date_added']); ?></td>
						<td><?php echo __($_status['name']); ?></td>
						<td><?php echo __($_status['remarks']); ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>
		<div class="box-account order-items">
			<div class="box-head">
				<h2><?php echo __('Order Items'); ?></h2>
			</div>
			<table class="data-table cart-table" id="my-orders-table">
				<colgroup>
					<col width="1" />
					<col />
					<col width="1" />
					<col width="1" />
					<col width="1" />
				</colgroup>
				<thead>
				<tr>
					<th><?php echo __('Product Name'); ?></th>
					<th class="a-right"><?php echo __('Price'); ?> * <?php echo __('Qty'); ?></th>
					<th class="a-right"><?php echo __('Subtotal'); ?></th>
				</tr>
				</thead>
				<tfoot>
				<tr class="subtotal">
					<td class="a-right" colspan="2"><?php echo __('Subtotal'); ?></td>
					<td class="a-right"><span class="price"><?php echo $currencies->display_price($orderInfo['order_subtotal'], $orderInfo['currency']['code'], $orderInfo['currency']['value']); ?></span></td>
				</tr>
				<?php if ($orderInfo['order_discount'] > 0) { ?>
					<tr class="discount">
						<td class="a-right" colspan="2"><?php echo __('Discount'); ?></td>
						<td class="a-right"><span class="price">-<?php echo $currencies->display_price($orderInfo['order_discount'], $orderInfo['currency']['code'], $orderInfo['currency']['value']); ?></span></td>
					</tr>
				<?php } ?>
				<?php if ($orderInfo['coupon']['code'] != '') { ?>
					<tr class="coupon_discount">
						<td class="a-right" colspan="2"><?php echo __('Coupon (<span>%s</span>)', $orderInfo['coupon']['code']); ?></td>
						<td class="a-right"><span class="price">-<?php echo $currencies->display_price($orderInfo['coupon']['discount'], $orderInfo['currency']['code'], $orderInfo['currency']['value']); ?></span></td>
					</tr>
				<?php } ?>
				<tr class="shipping_method_fee">
					<td class="a-right" colspan="2"><?php echo __('Shipping & Handling'); ?></td>
					<td class="a-right"><span class="price"><?php echo $currencies->display_price($orderInfo['shipping_method']['fee'], $orderInfo['currency']['code'], $orderInfo['currency']['value']); ?></span></td>
				</tr>
				<tr class="shipping_method_insurance_fee">
					<td class="a-right" colspan="2"><?php echo __('Insurance Fee'); ?></td>
					<td class="a-right"><span id="order_review_shipping_method_insurance_fee" class="price"><?php echo $currencies->display_price($orderInfo['shipping_method']['insurance_fee'], $orderInfo['currency']['code'], $orderInfo['currency']['value']); ?></span></td>
				</tr>
				<tr class="grand_total">
					<td class="a-right" colspan="2"><strong><?php echo __('Grand Total'); ?></strong></td>
					<td class="a-right"><strong><span class="price"><?php echo $currencies->display_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']); ?></span></strong></td>
				</tr>
				</tfoot>
				<tbody>
				<?php foreach ($orderProductInfo as $_product) {?>
					<tr>
						<td>
							<img width="<?php echo MOBILE_ORDER_REVIEW_IMAGE_WIDTH; ?>" height="<?php echo MOBILE_ORDER_REVIEW_IMAGE_HEIGHT; ?>" alt="<?php echo $_product['name']; ?>" src="<?php echo get_small_image($_product['image'], ORDER_REVIEW_IMAGE_WIDTH, ORDER_REVIEW_IMAGE_HEIGHT); ?>" />
							<h3 class="product-name"><?php echo $_product['name']; ?></h3>
							<dl class="item-options">
								<?php foreach (json_decode($_product['attribute'], true) as $_option_name => $_option_value_name) {?>
									<dt><?php echo __($_option_name); ?></dt>
									<dd><?php echo __($_option_value_name); ?></dd>
								<?php } ?>
							</dl>
						</td>
						<td class="a-right"><span class="cart-price"><span class="price"><?php echo $currencies->display_price($_product['price'], $orderInfo['currency']['code'], $orderInfo['currency']['value']); ?></span></span> * <?php echo $_product['qty']; ?></td>
						<td class="a-right"><span class="cart-price"><span class="price"><?php echo $currencies->display_price($_product['price']*$_product['qty'], $orderInfo['currency']['code'], $orderInfo['currency']['value']); ?></span></span></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
			<script type="text/javascript">decorateTable($('#my-orders-table'));</script>
		</div>
		<div class="buttons-set">
			<a class="btn btn-default" title="<?php echo __('Continue Shopping'); ?>" href="<?php echo href_link(FILENAME_INDEX); ?>"><small>« </small><?php echo __('Continue Shopping'); ?></a>
		</div>
	</div>
</div>
<script type="text/javascript">if(top.location!==self.location){top.location=self.location;}</script>