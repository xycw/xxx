<?php
/**
 * payment abdpay1.php
 */
class abdpay1
{
	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;
		$v_WebsiteId    = trim($payment->get_account());
		$SecretKey      = trim($payment->get_md5key());
		$v_OrderId      = put_orderNO($orderInfo['order_id']);
		$v_Email        = $orderInfo['customer']['email_address'];
		$v_CurrencyType = $orderInfo['currency']['code'];
		$v_Amount       = $orderInfo['order_subtotal'];
		$v_Freight      = $currencies->get_price($orderInfo['shipping_method']['fee'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
        $v_Discount     = $currencies->get_price(($orderInfo['order_discount'] + $orderInfo['coupon']['discount']), $orderInfo['currency']['code'], $orderInfo['currency']['value']);
        $v_Tax          = '0.00';
        $v_Signature    = md5($v_WebsiteId . $v_OrderId . $v_Email . $v_CurrencyType . $v_Amount . $v_Freight . $v_Discount . $v_Tax . $SecretKey);
		$v_Language     = STORE_LANGUAGE;
		
		$strProducts = '';
		$i = 1;
		foreach ($orderProductInfo as $_product) {
			$strProducts = '<input type="hidden" value="' . $_product['name'] . '" name="ProductName' . $i . '">' . "\n";
			$strProducts = '<input type="hidden" value="' . $_product['sku'] . '" name="Sku' . $i . '">' . "\n";
			$strProducts .= '<input type="hidden" value="' . $currencies->get_price($_product['price'], $orderInfo['currency']['code'], $orderInfo['currency']['value']) . '" name="Price' . $i . '">' . "\n";
			$strProducts .= '<input type="hidden" value="' . $_product['qty'] . '" name="Quantity' . $i . '">' . "\n";
			$strProducts .= '<input type="hidden" value="' . get_small_image($_product['image'], SHOPPING_CART_IMAGE_WIDTH, SHOPPING_CART_IMAGE_HEIGHT) . '" name="ProductImage' . $i . '">' . "\n";
			$strProducts .= '<input type="hidden" value="' . href_link(FILENAME_PRODUCT, 'pID=' . $_product['product_id']) . '" name="ProductUrl' . $i . '">' . "\n";
			$i++;
		}
		
		$v_ShippingFirstName = $orderInfo['shipping']['firstname'];
		$v_ShippingLastName  = $orderInfo['shipping']['lastname'];
		$v_ShippingAddress1  = $orderInfo['shipping']['street_address'];
		$v_ShippingAddress2  = $orderInfo['shipping']['suburb'];
		$v_ShippingCity      = $orderInfo['shipping']['city'];
		$country_iso         = get_country_iso($orderInfo['shipping']['country_id']);
		$v_ShippingCountry   = $country_iso['iso_code_2'];
		$state_code          = get_region_code($orderInfo['shipping']['region_id']);
		$v_ShippingState     = not_null($state_code)?$state_code:$orderInfo['shipping']['region'];
		$v_ShippingZipcode   = $orderInfo['shipping']['postcode'];
		$v_ShippingTelephone = $orderInfo['shipping']['telephone'];
		
		$v_BillingFirstName = $orderInfo['billing']['firstname'];
		$v_BillingLastName  = $orderInfo['billing']['lastname'];
		$v_BillingAddress1  = $orderInfo['billing']['street_address'];
		$v_BillingAddress2  = $orderInfo['billing']['suburb'];
		$v_BillingCity      = $orderInfo['billing']['city'];
		$country_iso        = get_country_iso($orderInfo['billing']['country_id']);
		$v_BillingCountry   = $country_iso['iso_code_2'];
		$state_code         = get_region_code($orderInfo['billing']['region_id']);
		$v_BillingState     = not_null($state_code)?$state_code:$orderInfo['billing']['region'];
		$v_BillingZipcode   = $orderInfo['billing']['postcode'];
		$v_BillingTelephone = $orderInfo['billing']['telephone'];
		
		$payment_form = '<form method="post" action="' . $payment->get_submit_url() . '" id="checkout" name="checkout">' . "\n";
		$payment_form .= '<input type="hidden" value="zencart_version6" name="plusversion">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_WebsiteId . '" name="WebsiteId">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_OrderId . '" name="OrderId">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_Email . '" name="Email">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_CurrencyType . '" name="CurrencyType">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_Amount . '" name="Amount">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_Freight . '" name="Freight">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_Discount . '" name="Discount">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_Tax . '" name="Tax">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_Signature . '" name="Signature">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_Language . '" name="Language">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_ShippingFirstName . '" name="ShippingFirstName">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_ShippingLastName . '" name="ShippingLastName">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_ShippingAddress1 . '" name="ShippingAddress1">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_ShippingAddress2 . '" name="ShippingAddress2">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_ShippingCity . '" name="ShippingCity">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_ShippingCountry . '" name="ShippingCountry">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_ShippingState . '" name="ShippingState">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_ShippingZipcode . '" name="ShippingZipcode">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_ShippingTelephone . '" name="ShippingTelephone">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BillingFirstName . '" name="BillingFirstName">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BillingLastName . '" name="BillingLastName">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BillingAddress1 . '" name="BillingAddress1">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BillingAddress2 . '" name="BillingAddress2">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BillingCity . '" name="BillingCity">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BillingCountry . '" name="BillingCountry">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BillingState . '" name="BillingState">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BillingZipcode . '" name="BillingZipcode">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BillingTelephone . '" name="BillingTelephone">' . "\n";
		$payment_form .= $strProducts;
		$payment_form .= '</form>' . "\n";
		if ($payment->get_is_inside()==1) {
			$payment_form .= '<iframe width="100%" height="850" scrolling="no" style="border:none;margin:0 auto;overflow:hidden;" id="ifrm_checkout" name="ifrm_checkout"></iframe>' . "\n";
			$payment_form .= '<script type="text/javascript">' . "\n";
			$payment_form .= 'if (window.XMLHttpRequest) {' . "\n";
			$payment_form .= 'document.checkout.target="ifrm_checkout";' . "\n";
			$payment_form .= '}' . "\n";
			$payment_form .= 'document.checkout.action="' . $payment->get_submit_url() . '";' . "\n";
			$payment_form .= 'document.checkout.submit();' . "\n";
			$payment_form .= 'window.status="' . $payment->get_submit_url() . '";' . "\n";
			$payment_form .= 'function jumpOut() {' . "\n";
			$payment_form .= 'if ($("#ifrm_checkout").contents().find(".checkout-result").length>0) {' . "\n";
			$payment_form .= 'top.location="' . href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL') . '"' . "\n";
			$payment_form .= '}' . "\n";
			$payment_form .= '}' . "\n";
			$payment_form .= 'setInterval(jumpOut, 3000);' . "\n";
			$payment_form .= '</script>' . "\n";
		} else {
			$payment_form .= '<h2>' . __('You will be redirected to Credit Card in a few seconds.') . '</h2>' . "\n";
			$payment_form .= '<script type="text/javascript">' . "\n";
			$payment_form .= '$(function() {' . "\n";
			$payment_form .= 'document.checkout.submit();' . "\n";
			$payment_form .= '});' . "\n";
			$payment_form .= '</script>' . "\n";
		}
		
		echo $payment_form;
	}
	
	function result($payment)
	{
		$TransNo   = $_POST['Par1'];
		$OrderID   = $_POST['Par2'];
		$Status    = $_POST['Par3'];
		$Result    = $_POST['Par4'];
		$Currency  = $_POST['Par5'];
		$Amount    = $_POST['Par6'];
		$MD5info   = $_POST['Par7'];
		//MD5私钥
		$MD5key  = trim($payment->get_md5key());
		$MD5src  = $MD5key . $TransNo . $OrderID . $Status . $Result . $Currency . $Amount;
		$MD5sign = strtoupper(md5($MD5src));
		if ($MD5sign == $MD5info) {
			if($Status == '1') {
				$order_status = 3;
			} else {
				$order_status = 4;
			}
		} else {
			$order_status = 4;
		}
		
		return $order_status;
	}
}
