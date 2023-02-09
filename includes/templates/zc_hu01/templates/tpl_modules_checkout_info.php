<form id="form-validate" method="post" action="<?php echo SHOPPING_CART_MODE == 0 ? href_link(FILENAME_SHOPPING_CART, '', 'SSL') : href_link(FILENAME_CHECKOUT, '', 'SSL'); ?>">
	<div class="no-display">
		<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
		<input type="hidden" value="process" name="action" />
	</div>
	<div id="checkout-steps">
		<ol class="col-wide opc col-sm-12 col-md-6 col-xs-12">
			<li class="section active" id="opc-billing">
				<?php require($template->get_template_dir('tpl_modules_checkout_billing.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_checkout_billing.php'); ?>
			</li>
			<li class="section" id="opc-shipping">
				<?php require($template->get_template_dir('tpl_modules_checkout_shipping.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_checkout_shipping.php'); ?>
			</li>
		</ol>
		<ol class="col-narrow opc col-sm-12 col-md-6 col-xs-12">
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

<style>
    .shoppingcartBody {position: relative;}
    .masking-layer ,.cart_limit{display: block; margin: 0 auto; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255, 255, 255,0.97); z-index: 9999; }
    .masking-layer div  ,.cart_limit div{width: 210px; height: 60px; position: absolute; top: 50%; left: 50%; margin-top: -30px; margin-left: -105px; font-size: 13px;}
    .masking-layer img {opacity: 0.7;}
	.cart_limit div{height: 200px; border-radius: 15px;box-shadow: 0 0 10px #b5b3b3;padding: 20px;line-height: 21px;font-size: 12px;width: 370px;transform: translateX(-50%);margin-left: unset;}
	.cart_limit_close{position: absolute;left: 50%;transform: translateX(-50%); bottom:20px;display: block;width: 145px;height:50px;cursor: pointer;background: #3783f1;color: #fff;font-size: 22px;line-height: 48px;border-radius: 25px;box-shadow: 0 0 8px #b9b9b9;}
</style>

<script type="text/javascript">
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

$('.shoppingcartBody').append('<div class="masking-layer" style="display: none;"><div><img src="<?php echo DIR_WS_TEMPLATE_IMAGES; ?>load.gif?v1"/><br/><?php echo __('Processing, please wait...'); ?></div></div>');
	$('.shoppingcartBody').append('<div class="cart_limit" style="display: none;"> <div><?php echo __('SINCE THE ORDER AMOUNT EXCEEDS THE MAXIMUM PAYMENT ($250), PLEASE PURCHASE SEPARATELY. THANK YOU FOR YOUR TIME AND UNDERSTANDING.'); ?><span class="cart_limit_close">RETURN</span> </div> </div>');
   var $productMin = <?php echo PRODUCT_MIN; ?>;
   	<?php if (defined('FACEBOOK_ID') && strlen(FACEBOOK_ID) > 0) { ?>
   	$('#form-validate').validate({
   		submitHandler:function(form){
   			if ($productMin == 0 || $productMin <= <?php echo $tempTotalQty; ?>) {
   				fbq('track', 'InitiateCheckout');setTimeout(function(){form.submit();}, 1000);
   			} else {
   				alert('Minimum' + $productMin + 'purchase');
   			}
   		}
   	});
   	<?php } else { ?>
   	$('#form-validate').validate({
   		submitHandler: function(form) {
   			if ($productMin == 0 || $productMin <= <?php echo $tempTotalQty; ?>) {
   				form.submit();
   			} else {
   				alert('Minimum ' + $productMin + ' purchase');
   			}
   		}
   	});
   	<?php } ?>

    $(function () {
        $('.cart_limit_close').click(function () {
            $('.cart_limit').hide();
        })
    })

</script>
