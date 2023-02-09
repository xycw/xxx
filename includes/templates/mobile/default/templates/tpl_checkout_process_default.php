<div class="checkout-process">
	<img src="images/payment/load.gif" />
	<?php if ($message_stack->size('checkout_process') > 0) echo $message_stack->output('checkout_process'); ?>
	<?php echo $payment_method->process(); ?>
</div>
