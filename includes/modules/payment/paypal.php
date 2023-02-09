<?php
/**
 * payment paypal.php
 */
class paypal
{
	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;
		$business      = $payment->get_account();
		$currency_code = $orderInfo['currency']['code'];
		$item_name     = put_orderNO($orderInfo['order_id']);
		$item_number   = '';
		$amount        = $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
		$return        = href_link(FILENAME_CHECKOUT_RESULT, 'item_name=' . $item_name, 'SSL');
		$cancel_return = href_link(FILENAME_SHOPPING_CART, '', 'SSL');
		$shopping_url  = href_link(FILENAME_SHOPPING_CART, '', 'SSL');
		$notify_url    = HTTP_SERVER . DIR_WS_CATALOG . 'notify_paypal.php';
		$custom        = put_orderNO($orderInfo['order_id']);
		$invoice       = put_orderNO($orderInfo['order_id']);
		
		$first_name  = $orderInfo['billing']['firstname'];
		$last_name   = $orderInfo['billing']['lastname'];
		$address1    = $orderInfo['billing']['street_address'];
		$address2    = $orderInfo['billing']['suburb'];
		$city        = $orderInfo['billing']['city'];
		$state_code  = get_region_code($orderInfo['billing']['region_id']);
		$state       = ($state_code != '' ? $state_code : $orderInfo['billing']['region']);
		$zip         = $orderInfo['billing']['postcode'];
		$country_iso = get_country_iso($orderInfo['billing']['country_id']);
		$country     = $country_iso['iso_code_2'];
		$lc          = $country_iso['iso_code_2'];
		$telephone   = preg_replace('/\D/', '', $orderInfo['billing']['telephone']);
		$email       = $orderInfo['customer']['email_address'];
		
		$payment_form = '<form method="post" action="' . $payment->get_submit_url() . '" id="paypalCheckout">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $lc .'" name="lc">' . "\n";
		$payment_form .= '<input type="hidden" value="utf-8" name="charset">' . "\n";
		$payment_form .= '<input type="hidden" value="_ext-enter" name="cmd">' . "\n";
		$payment_form .= '<input type="hidden" value="_xclick" name="redirect_cmd">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $business .'" name="business">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $return .'" name="return">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $cancel_return .'" name="cancel_return">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $shopping_url .'" name="shopping_url">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $notify_url .'" name="notify_url">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $custom .'" name="custom">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $invoice .'" name="invoice">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $currency_code .'" name="currency_code">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $item_name .'" name="item_name">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $item_number .'" name="item_number">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $amount .'" name="amount">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $first_name .'" name="first_name">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $last_name .'" name="last_name">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $address1 .'" name="address1">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $address2 .'" name="address2">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $city .'" name="city">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $state .'" name="state">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $zip .'" name="zip">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $country .'" name="country">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $telephone .'" name="H_PhoneNumber">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $telephone .'" name="night_phone_b">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $telephone .'" name="day_phone_b">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $email .'" name="email">' . "\n";
		$payment_form .= '</form>' . "\n";
		$payment_form .= '<h2>' . __('You will be redirected to Paypal in a few seconds.') . '</h2>' . "\n";
		$payment_form .= '<script type="text/javascript">';
		if (defined('FACEBOOK_ID') && strlen(FACEBOOK_ID) > 0 && !isset($_SESSION['facebook_purchase'])) {
			$_SESSION['facebook_purchase'] = 'ok';
			$payment_form .= 'fbq(\'track\', \'Purchase\', {value: \'' . $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']) . '\', currency: \'' . $orderInfo['currency']['code'] . '\'});';
		}
		$payment_form .= 'setTimeout(function(){$("#paypalCheckout").submit();}, 1000);';
		$payment_form .= '</script>';
		
		echo $payment_form;
	}
	
	function result($payment)
	{
		$this->addLog(json_encode($_GET));
		if (isset($_GET['item_name'])
			&& $_GET['item_name'] == put_orderNO($_SESSION['old_order_id'])) {
			$order_status = 3;
		} else {
			$order_status = 4;
		}
		return $order_status;
	}

	function addLog($log)
    {
        $fp = fopen(DIR_FS_CATALOG ."cache/paypal-log-" . date("Y-m-d") . ".txt", "a");
        flock($fp, LOCK_EX) ;
        fwrite($fp, "[" . date("Y-m-d H:i:s") . "]" . $log . "\r\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}
