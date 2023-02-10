<?php
/**
 * payment fashionpay.php
 */
class fashionpay
{
	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;
		$MerNo = trim($payment->get_account());
		$BillNo = put_orderNO($orderInfo['order_id']);
		$availabCurrency = array(
			'USD' => '1', 'EUR' => '2', 'CNY' => '3', 'GBP' => '4', 'HKD' => '5',
			'JPY' => '6', 'AUD' => '7', 'KRW' => '8', 'CAD' => '9', 'NOK' => '10'
		);
		$Currency = isset($availabCurrency[$orderInfo['currency']['code']])?$availabCurrency[$orderInfo['currency']['code']]:1;
		$Amount = $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
		$Language = STORE_LANGUAGE;
		$ReturnURL = href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL');
		$MD5key = trim($payment->get_md5key());
		$MD5src = $MerNo . $BillNo . $Currency . $Amount . $Language . $ReturnURL . $MD5key;
		$MD5info = strtoupper(md5($MD5src));
		$Remark = href_link(FILENAME_INDEX);
		
		$FirstName = $orderInfo['billing']['firstname'];
		$LastName  = $orderInfo['billing']['lastname'];
		$Email     = $orderInfo['customer']['email_address'];
		$Phone     = $orderInfo['billing']['telephone'];
		$ZipCode   = $orderInfo['billing']['postcode'];
		$Address   = trim($orderInfo['billing']['street_address'] . ' ' . $orderInfo['billing']['suburb']);
		$City      = $orderInfo['billing']['city'];
		$State     = $orderInfo['billing']['region'];
		$Country   = $orderInfo['billing']['country'];

		$DeliveryFirstName = $orderInfo['shipping']['firstname'];
		$DeliveryLastName  = $orderInfo['shipping']['lastname'];
		$DeliveryEmail     = $orderInfo['customer']['email_address'];
		$DeliveryPhone     = $orderInfo['shipping']['telephone'];
		$DeliveryZipCode   = $orderInfo['shipping']['postcode'];
		$DeliveryAddress   = trim($orderInfo['shipping']['street_address'] . ' ' . $orderInfo['shipping']['suburb']);
		$DeliveryCity      = $orderInfo['shipping']['city'];
		$DeliveryState     = $orderInfo['shipping']['region'];
		$DeliveryCountry   = $orderInfo['shipping']['country'];
		
		$payment_form = '<form method="post" action="' . $payment->get_submit_url() . '" id="checkout" name="checkout">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $MerNo . '" name="MerNo">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $BillNo . '" name="BillNo">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $Currency . '" name="Currency">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $Amount . '" name="Amount">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $Language . '" name="Language">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $ReturnURL . '" name="ReturnURL">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $MD5info . '" name="MD5info">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $Remark . '" name="Remark">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $FirstName . '" name="FirstName">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $LastName . '" name="LastName">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $Email . '" name="Email">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $Phone . '" name="Phone">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $ZipCode . '" name="ZipCode">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $Address . '" name="Address">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $City . '" name="City">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $State . '" name="State">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $Country . '" name="Country">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $DeliveryFirstName . '" name="DeliveryFirstName">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $DeliveryLastName . '" name="DeliveryLastName">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $DeliveryEmail . '" name="DeliveryEmail">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $DeliveryPhone . '" name="DeliveryPhone">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $DeliveryZipCode . '" name="DeliveryZipCode">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $DeliveryAddress . '" name="DeliveryAddress">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $DeliveryCity . '" name="DeliveryCity">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $DeliveryState . '" name="DeliveryState">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $DeliveryCountry . '" name="DeliveryCountry">' . "\n";
		$payment_form .= '</form>' . "\n";
		if ($payment->get_is_inside()==1) {
			$payment_form .= '<iframe width="100%" height="880" scrolling="no" style="border:none;margin:0 auto;overflow:hidden;" id="ifrm_checkout" name="ifrm_checkout"></iframe>' . "\n";
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
		$BillNo       = $_POST["BillNo"];
		$Currency     = $_POST["Currency"];
		$Amount       = $_POST["Amount"];
		$Succeed      = $_POST["Succeed"];
		$Result       = $_POST["Result"];
		$MD5info      = $_POST["MD5info"]; 
		$currencyName = $_POST["currencyName"];
		
		$MD5key = trim($payment->get_md5key());
		$MD5src = $BillNo . $Currency . $Amount . $Succeed . $MD5key;
		$MD5sign = strtoupper(md5($MD5src));
		if ($MD5info == $MD5sign){
			if ($Succeed == '88') {
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
