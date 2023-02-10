<form id="form-validate" method="post" action="<?php echo SHOPPING_CART_MODE == 0 ? href_link(FILENAME_SHOPPING_CART, '', 'SSL') : href_link(FILENAME_CHECKOUT, '', 'SSL'); ?>">
	<div class="no-display">
		<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
		<input type="hidden" value="process" name="action" />
	</div>
	<div id="checkout-steps" class="col2-set">
		<ol class="col-1 col-wide opc">
			<li class="section active" id="opc-billing">
				<?php require($template->get_template_dir('tpl_modules_checkout_billing.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_checkout_billing.php'); ?>
			</li>
			<li class="section" id="opc-shipping">
				<?php require($template->get_template_dir('tpl_modules_checkout_shipping.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_checkout_shipping.php'); ?>
			</li>
		</ol>
		<ol class="col-2 col-narrow opc">
			<li class="section active">
				<?php require($template->get_template_dir('tpl_modules_checkout_shipping_method.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_checkout_shipping_method.php'); ?>
			</li>
			<li class="section active">
				<?php require($template->get_template_dir('tpl_modules_checkout_payment_method.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_checkout_payment_method.php'); ?>
			</li>
			<li class="section active" id="opc-order_review">
				<?php require($template->get_template_dir('tpl_modules_checkout_order_review.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_checkout_order_review.php'); ?>
			</li>
		</ol>
	</div>
</form>
<script type="text/javascript"><!--
function newAddress(prefix) {
	var select = $("#" + prefix + "-address-select");
	var from = $("#" + prefix + "-new-address-form");
	if (select.val()==""||select.length==0) {
		from.show();
	} else {
		from.hide();
	}
}
function same_as_billing()
{
	if ($("input:radio[name='use_for_shipping']:checked").val()==1) {
		$('#opc-shipping').removeClass("active");
	} else {
		$('#opc-shipping').addClass("active");
	}
}
same_as_billing();
newAddress('billing');
newAddress('shipping');
<?php if (defined('FACEBOOK_ID') && strlen(FACEBOOK_ID) > 0) { ?>
$('#form-validate').validate({submitHandler:function(form){fbq('track', 'InitiateCheckout');setTimeout(function(){form.submit();}, 1000);}});
<?php } else { ?>
$('#form-validate').validate();
<?php } ?>
//--></script>
