<div class="checkout">
	<div class="page-title">
		<h1><?php echo __('Checkout'); ?></h1>
	</div>
	<?php if ($message_stack->size('checkout') > 0) echo $message_stack->output('checkout'); ?>
	<?php require($template->get_template_dir('tpl_modules_checkout_info.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_checkout_info.php'); ?>
</div>
