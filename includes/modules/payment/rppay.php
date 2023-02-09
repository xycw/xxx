<?php
/**
 * payment rppay.php
 */
class rppay
{
	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;
		if (!isset($_COOKIE['RpCookie'])
			|| empty($_COOKIE['RpCookie'])) {
			die('cookie error');
		}
		$merchantno = trim($payment->get_account());
		$siteid = trim($payment->get_mark1());
		$key = trim($payment->get_md5key());
		$order_sn = put_orderNO($orderInfo['order_id']);
		$verifyCode = md5($order_sn . $siteid . $key);
		$gateway = $this->get_gateway($merchantno, $siteid, $key);
		$action = $gateway['gurl'];
		$gid = $gateway['gid'];
		$rpcookie = $_COOKIE['RpCookie'];
		$BackUrl = href_link(FILENAME_INDEX);
		$returnUrl = href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL');
        $notifyUrl = HTTP_SERVER . DIR_WS_CATALOG . 'rppay_check.php';
		$currency = $orderInfo['currency']['code'];
		$ShippingFee = $currencies->get_price($orderInfo['shipping_method']['fee'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
		$vat = 0;
        $discount = $currencies->get_price(($orderInfo['order_discount'] + $orderInfo['coupon']['discount']), $orderInfo['currency']['code'], $orderInfo['currency']['value']);
        
		$shipfirstname = $orderInfo['shipping']['firstname'];
		$shiplastname = $orderInfo['shipping']['lastname'];
		$address = trim($orderInfo['shipping']['street_address'] . ' ' . $orderInfo['shipping']['suburb']);
		$city = $orderInfo['shipping']['city'];
		$state_code = get_region_code($orderInfo['shipping']['region_id']);
		$state = not_null($state_code)?$state_code:$orderInfo['shipping']['region'];
		$postcode = $orderInfo['shipping']['postcode'];
		$country_iso = get_country_iso($orderInfo['shipping']['country_id']);
		$country = $country_iso['iso_code_2'];
		$email = $orderInfo['customer']['email_address'];
		$tel = $orderInfo['shipping']['telephone'];
		
		$billfirstname = $orderInfo['billing']['firstname'];
		$billlastname = $orderInfo['billing']['lastname'];
		$billaddress = trim($orderInfo['billing']['street_address'] . ' ' . $orderInfo['billing']['suburb']);
		$billcity = $orderInfo['billing']['city'];
		$billstate_code = get_region_code($orderInfo['billing']['region_id']);
		$billstate = not_null($billstate_code)?$billstate_code:$orderInfo['billing']['region'];
		$billpostcode = $orderInfo['billing']['postcode'];
		$billcountry_iso = get_country_iso($orderInfo['billing']['country_id']);
		$billcountry = $billcountry_iso['iso_code_2'];
		$billphone = $orderInfo['billing']['telephone'];
		
		$strProducts = '';
		$i = 1;
		foreach ($orderProductInfo as $_product) {
			$strProducts .= '<input type="hidden" value="' . $_product['product_id'] . '" name="product_no[' . $i . ']">' . "\n";
			$strProducts .= '<input type="hidden" value="' . $_product['name'] . '" name="product_name[' . $i . ']">' . "\n";
			$strProducts .= '<input type="hidden" value="' . $currencies->get_price($_product['price'], $orderInfo['currency']['code'], $orderInfo['currency']['value']) . '" name="price_unit[' . $i . ']">' . "\n";
			$strProducts .= '<input type="hidden" value="' . $_product['qty'] . '" name="quantity[' . $i . ']">' . "\n";
			$i++;
		}
		
		$payment_form = '<form method="post" action="' . $action . '" id="checkout" name="checkout">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $merchantno . '" name="merchantno">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $siteid . '" name="siteid">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $order_sn . '" name="order_sn">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $verifyCode . '" name="verifyCode">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $gid . '" name="gid">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $rpcookie . '" name="rpcookie">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $BackUrl . '" name="BackUrl">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $returnUrl . '" name="returnUrl">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $notifyUrl . '" name="notifyUrl">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $currency . '" name="currency">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $ShippingFee . '" name="ShippingFee">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $vat . '" name="vat">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $discount . '" name="discount">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $shipfirstname . '" name="shipfirstname">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $shiplastname . '" name="shiplastname">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $address . '" name="address">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $city . '" name="city">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $state . '" name="state">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $postcode . '" name="postcode">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $country . '" name="country">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $email . '" name="email">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $tel . '" name="tel">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $billfirstname . '" name="billfirstname">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $billlastname . '" name="billlastname">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $billaddress . '" name="billaddress">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $billcity . '" name="billcity">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $billstate . '" name="billstate">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $billpostcode . '" name="billpostcode">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $billcountry . '" name="billcountry">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $billphone . '" name="billphone">' . "\n";
		$payment_form .= $strProducts;
		$payment_form .= '</form>' . "\n";
		if ($payment->get_is_inside()==1) {
			$payment_form .= '<iframe width="100%" height="1200" scrolling="no" style="border:none;margin:0 auto;overflow:hidden;" id="ifrm_checkout" name="ifrm_checkout"></iframe>' . "\n";
			$payment_form .= '<script type="text/javascript">' . "\n";
			$payment_form .= 'if (window.XMLHttpRequest) {' . "\n";
			$payment_form .= 'document.checkout.target="ifrm_checkout";' . "\n";
			$payment_form .= '}' . "\n";
			$payment_form .= 'document.checkout.action="' . $action . '";' . "\n";
			$payment_form .= 'document.checkout.submit();' . "\n";
			$payment_form .= 'window.status="' . $action . '";' . "\n";
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
		$siteid        = $_REQUEST['siteid'];
		$order_sn      = $_REQUEST['order_sn'];
		$total         = $_REQUEST['total'];
		$verifyCode    = $_REQUEST['verifyCode'];
		$verified      = $_REQUEST['verified'];
		$transactionid = $_REQUEST['transactionid'];
		$key = trim($payment->get_md5key());
		if ($verifyCode == md5($order_sn . $siteid . $key)) {
			switch ($verified) {
				case 'approved':
					$order_status = 3;
				break;
				case 'declined':
					$order_status = 4;
				break;
				case 'refund':
				case 'unpaid':
				case 'pending':
				case 'pos':
				case 'error':
				case 'test approve':
				case 'canceled':
				case 'chargeback':
				case 'fraud':
				default:
					$order_status = 1;
				break;
			}
		} else {
			$order_status = 4;
		}
		
		return $order_status;
	}
	
	function get_gateway($merchantno, $siteid, $key)
	{
		global $db;
		$ip = $this->getIp();
		$data = array(
			'merchantno' => $merchantno,
			'siteid'     => $siteid,
			'ip'         => $ip,
			'sign'       => md5($merchantno.$siteid.$ip.$key)
		);
		$url = "http://www.billingconfirm.net/gateway.html?" . http_build_query($data);

		$file_contents = '';
		$gateway = '';
		if (ini_get("allow_url_fopen") == 1) {
			if (!function_exists("file_get_contents")) {
				function file_get_contents($filename) {
					$handle = fopen($filename, "rb");
					$contents = fread($handle, filesize($filename));
					fclose($handle);
				}
			}
			$file_contents = @file_get_contents($url);
		} else if (is_callable('curl_init')) {
			$ch = curl_init();
			$timeout = 10;
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$file_contents = curl_exec($ch);
			curl_close($ch);
		} 

		parse_str($file_contents);
		if(isset($rpsuccess) && $rpsuccess!=1){
			die($msg);
		}
		
		if(!empty($gurl)){
			$sql_data_array = array(
				array('fieldName'=>'submit_url', 'value'=>$gurl, 'type'=>'string'),
				array('fieldName'=>'mark2', 'value'=>$gid, 'type'=>'string')
			);
			$db->perform(TABLE_PAYMENT_METHOD, $sql_data_array, 'UPDATE', "code = 'rppay'");
			return array('gurl' => $gurl, 'gid' => $gid);
		} else {
			$sql = "SELECT submit_url, mark2 FROM " . TABLE_PAYMENT_METHOD . " WHERE code = 'rppay'";
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				return array('gurl' => $result->fields['submiturl'], 'gid' => $result->fields['mark2']);
			} else {
				die('system error');
			}
		}
	}

	function getIp()
	{
		if (getenv('HTTP_CLIENT_IP')
			&& strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$ip = getenv('HTTP_CLIENT_IP');
		} elseif (getenv('HTTP_X_FORWARDED_FOR')
			&& strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif (getenv('REMOTE_ADDR')
			&& strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$ip = getenv('REMOTE_ADDR');
		} elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']
			&& strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return preg_match("/[\d\.]{7,15}/", $ip, $matches) ? $matches[0] : 'unknown';
	}
}
