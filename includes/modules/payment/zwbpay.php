<?php
/**
 * payment zwbpay.php
 */
class zwbpay
{
	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;
		$v_MerchantID = trim($payment->get_account());
		$v_TransNo    = trim($payment->get_mark1());
		$MD5key       = trim($payment->get_md5key());
		$v_OrderID    = put_orderNO($orderInfo['order_id']);
		$v_Currency   = $orderInfo['currency']['code'];
		$v_Amount     = $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
		$v_MD5info    = strtoupper(md5($MD5key . $v_MerchantID . $v_TransNo . $v_OrderID . $v_Currency . $v_Amount));
		
		$v_BFirstname = $orderInfo['billing']['firstname'];
		$v_BLastname  = $orderInfo['billing']['lastname'];
		$v_BEmail     = $orderInfo['customer']['email_address'];
		$v_BAddress   = trim($orderInfo['billing']['street_address'] . ' ' . $orderInfo['billing']['suburb']);
		$v_BCity      = $orderInfo['billing']['city'];
		$v_BState     = $orderInfo['billing']['region'];
		$v_BPostcode  = $orderInfo['billing']['postcode'];
		$country_iso  = get_country_iso($orderInfo['billing']['country_id']);
		$v_BCountry   = $country_iso['iso_code_2'];
		$v_BPhone     = $orderInfo['billing']['telephone'];
		
		$v_DFirstname = $orderInfo['shipping']['firstname'];
		$v_DLastname  = $orderInfo['shipping']['lastname'];
		$v_DEmail     = $orderInfo['customer']['email_address'];
		$v_DAddress   = trim($orderInfo['shipping']['street_address'] . ' ' . $orderInfo['shipping']['suburb']);
		$v_DCity      = $orderInfo['shipping']['city'];
		$v_DState     = $orderInfo['shipping']['region'];
		$v_DPostcode  = $orderInfo['shipping']['postcode'];
		$country_iso  = get_country_iso($orderInfo['shipping']['country_id']);
		$v_DCountry   = $country_iso['iso_code_2'];
		$v_DPhone     = $orderInfo['shipping']['telephone'];
		
		$payment_form = '<form method="post" action="' . $payment->get_submit_url() . '" id="checkout" name="checkout">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_MerchantID . '" name="MerchantID">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_TransNo . '" name="TransNo">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_OrderID . '" name="OrderID">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_Currency . '" name="Currency">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_Amount . '" name="Amount">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_MD5info . '" name="MD5info">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BFirstname . '" name="BFirstname">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BLastname . '" name="BLastname">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BEmail . '" name="BEmail">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BAddress . '" name="BAddress">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BCity . '" name="BCity">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BState . '" name="BState">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BPostcode . '" name="BPostcode">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BCountry . '" name="BCountry">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BPhone . '" name="BPhone">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_DFirstname . '" name="DFirstname">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_DLastname . '" name="DLastname">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_DEmail . '" name="DEmail">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_DAddress . '" name="DAddress">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_DCity . '" name="DCity">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_DState . '" name="DState">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_DPostcode . '" name="DPostcode">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_DCountry . '" name="DCountry">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_DPhone . '" name="DPhone">' . "\n";
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
