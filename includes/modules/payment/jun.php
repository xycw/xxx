<?php
/**
 * payment jun.php
 */
class jun
{
	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;
		$WebsiteId    = trim($payment->get_account());
		$MD5key       = trim($payment->get_md5key());
		$OrderId      = put_orderNO($orderInfo['order_id']);
		$Email        = $orderInfo['customer']['email_address'];
		$CurrencyType = $orderInfo['currency']['code'];
		$Amount       = $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
		$Freight      = '0.00';
		$Discount     = '0.00';
		$Tax          = '0.00';
		$Signature    = md5($WebsiteId . $OrderId . $Email . $CurrencyType . $Amount . $Freight . $Discount . $Tax . $MD5key);
		$Language     = STORE_LANGUAGE;
		$Domain       = '';

		$ShippingFirstName = $orderInfo['shipping']['firstname'];
		$ShippingLastName  = $orderInfo['billing']['lastname'];
		$ShippingAddress1  = $orderInfo['shipping']['street_address'];
		$ShippingAddress2  = $orderInfo['shipping']['suburb'];
		$ShippingCity      = $orderInfo['shipping']['city'];
		$country_iso       = get_country_iso($orderInfo['shipping']['country_id']);
		$ShippingCountry   = $country_iso['iso_code_2'];
		$state_code        = get_region_code($orderInfo['shipping']['region_id']);
		$ShippingState     = not_null($state_code)?$state_code:$orderInfo['shipping']['region']; 
		$ShippingZipcode   = $orderInfo['shipping']['postcode'];
		$ShippingTelephone = $orderInfo['shipping']['telephone'];
		
		$BillingFirstName  = $orderInfo['billing']['firstname'];
		$BillingLastName   = $orderInfo['billing']['lastname'];
		$BillingAddress1   = $orderInfo['billing']['street_address'];
		$BillingAddress2   = $orderInfo['billing']['suburb'];
		$BillingCity       = $orderInfo['billing']['city'];
		$country_iso       = get_country_iso($orderInfo['billing']['country_id']);
		$BillingCountry    = $country_iso['iso_code_2'];
		$state_code        = get_region_code($orderInfo['billing']['region_id']);
		$BillingState      = not_null($state_code)?$state_code:$orderInfo['billing']['region'];
		$BillingZipcode    = $orderInfo['billing']['postcode'];
		$BillingTelephone  = $orderInfo['billing']['telephone'];
		
		$strProducts = '';
		$i = 1;
		foreach ($orderProductInfo as $_product) {
			$strProducts .= '<input type="hidden" value="' . $_product['product_id'] . '" name="Sku' . $i . '">' . "\n";
			$strProducts .= '<input type="hidden" value="' . strip_tags($_product['name']) . '" name="ProductName' . $i . '">' . "\n";
			$strProducts .= '<input type="hidden" value="' . $currencies->get_price($_product['price'], $orderInfo['currency']['code'], $orderInfo['currency']['value']) . '" name="Price' . $i . '">' . "\n";
			$strProducts .= '<input type="hidden" value="' . $_product['qty'] . '" name="Quantity' . $i . '">' . "\n";
			$strProducts .= '<input type="hidden" value="" name="ProductImage' . $i . '">' . "\n";
			$strProducts .= '<input type="hidden" value="" name="ProductUrl' . $i . '">' . "\n";
			$i++;
		}
		
		$payment_form = '<form method="post" action="' . $this->get_gateway($payment) . '" id="checkout" name="checkout">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $WebsiteId . '" name="WebsiteId">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $OrderId . '" name="OrderId">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $Email . '" name="Email">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $CurrencyType . '" name="CurrencyType">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $Amount . '" name="Amount">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $Freight . '" name="Freight">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $Discount . '" name="Discount">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $Tax . '" name="Tax">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $Signature . '" name="Signature">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $Language . '" name="Language">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $Domain . '" name="Domain">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $ShippingFirstName . '" name="ShippingFirstName">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $ShippingLastName . '" name="ShippingLastName">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $ShippingAddress1 . '" name="ShippingAddress1">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $ShippingAddress2 . '" name="ShippingAddress2">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $ShippingCity . '" name="ShippingCity">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $ShippingCountry . '" name="ShippingCountry">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $ShippingState . '" name="ShippingState">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $ShippingZipcode . '" name="ShippingZipcode">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $ShippingTelephone . '" name="ShippingTelephone">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $BillingFirstName . '" name="BillingFirstName">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $BillingLastName . '" name="BillingLastName">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $BillingAddress1 . '" name="BillingAddress1">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $BillingAddress2 . '" name="BillingAddress2">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $BillingCity . '" name="BillingCity">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $BillingCountry . '" name="BillingCountry">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $BillingState . '" name="BillingState">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $BillingZipcode . '" name="BillingZipcode">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $BillingTelephone . '" name="BillingTelephone">' . "\n";
		$payment_form .= $strProducts;
		$payment_form .= '</form>' . "\n";
		$payment_form .= '<h2>' . __('You will be redirected to Credit Card in a few seconds.') . '</h2>' . "\n";
		$payment_form .= '<script type="text/javascript">' . "\n";
		$payment_form .= '$(function() {' . "\n";
		$payment_form .= 'document.checkout.submit();' . "\n";
		$payment_form .= '});' . "\n";
		$payment_form .= '</script>' . "\n";
		echo $payment_form;
	}
	
	function result($payment)
	{
		$TransactionId = $_GET['transactionid'];
		$OrderId       = $_GET['orderid'];
		$CurrencyType  = $_GET['CurrencyType'];
		$Amount        = $_GET['Amount'];
		$Status        = $_GET['Status'];
		$Signature     = $_GET['Signature'];
		$MD5key = trim($payment->get_md5key());
		if ($Signature == md5($TransactionId . $OrderId . $CurrencyType . $Amount . $Status . $MD5key)) {
			switch ($Status) {
				case 'Success':
					$order_status = 3;
				break;
				case 'Failure':
					$order_status = 4;
				break;
				default:
					$order_status = 1;
				break;
			}
		} else {
			$order_status = 4;
		}
		
		return $order_status;
	}
	
	function get_gateway($payment)
	{
		global $db;
		$data = array(
			'websiteid'    => trim($payment->get_account()),
			'plusversion'  => '1.0.0.0',
			'websitemodel' => 'easyshop',
			'devicetype'   => $_SERVER['HTTP_USER_AGENT'],
			'ip'           => get_ip_address()
		);
		$url = trim($payment->get_submit_url()) . '?' . http_build_query($data);

		$gateway = '';
		if (is_callable('curl_init')) {
			$ch = curl_init();
			$timeout = 10;
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$gateway = curl_exec($ch);
			curl_close($ch);
		}

		if(!empty($gateway)){
			$gateway .= '/gateway';
			$sql_data_array = array(
				array('fieldName'=>'mark1', 'value'=>$gateway, 'type'=>'string')
			);
			$db->perform(TABLE_PAYMENT_METHOD, $sql_data_array, 'UPDATE', "code = 'junpay'");
			return $gateway;
		}

		return $payment->get_mark1();
	}
}
