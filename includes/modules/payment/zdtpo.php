<?php
/**
 * payment zdtpo.php
 */
class zdtpo
{
	function before()
	{
		$monthStr =  '<option value="">' . __('Month') . '</option>';
		for ($i = 1; $i <= 12; $i++) {
			$monthStr .= '<option value="' . str_pad($i, 2, '0', STR_PAD_LEFT) . '">' . str_pad($i, 2, '0', STR_PAD_LEFT) . '</option>';
		}

		$year    = date('Y');
		$yearStr = '<option value="">' . __('Year') . '</option>';
		for ($i = 0; $i < 25; $i++) {
			$yearStr .= '<option value="' . substr($year + $i, -2, 2) . '">' . ($year + $i) . '</option>';
		}

		$txtCardNumber             = __('Credit Card Number');
		$txtExpirationDate         = __('Expiration Date');
		$txtCardVerificationNumber = __('Card Verification Number');

		$html = <<<HTML
<ul>
	<li class="fields">
		<label class="required"><em>*</em>$txtCardNumber</label>
		<div class="input-box">
			<input type="text" style="width: 98%;" class="input-text required-entry creditcard" onfocus="$('#zdtpo').click();" name="zdtpo_card[number]" maxlength="16" />
		</div>
	</li>
	<li class="fields">
		<label class="required"><em>*</em>$txtExpirationDate</label>
		<div class="input-box">
			<select class="required-entry" style="width: 45%;" name="zdtpo_card[month]" onfocus="$('#zdtpo').click();">$monthStr</select>
		</div>
	</li>
	<li class="fields">
		<div class="input-box">
			<select class="required-entry" style="width: 45%;" name="zdtpo_card[year]" onfocus="$('#zdtpo').click();">$yearStr</select>
		</div>
	</li>
	<li class="fields">
		<label class="required"><em>*</em>$txtCardVerificationNumber</label>
		<div class="input-box">
			<input type="password" class="input-text required-entry digits" name="zdtpo_card[cvv]" onfocus="$('#zdtpo').click();" maxlength="3" style="width:38%;" />
			<img src="images/payment/cvv.gif" />
		</div>
	</li>
</ul>
<script type="text/javascript" src="https://risk.hdkhdkrisk.com/sslcsid.js"></script>
HTML;

		return $html;
	}

	function after()
	{
		global $message_stack, $error, $current_page;

		if (isset($_POST['zdtpo_card'])) {
			$zdtpo_card = $_POST['zdtpo_card'];
			if (strlen($zdtpo_card['number']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('"Card Number" is a required value. Please enter the card number.'));
			} elseif (!validate_creditcard($zdtpo_card['number'])) {
				$error = true;
				$message_stack->add($current_page, __('"Card Number" is not a valid card number.'));
			}
			if (strlen($zdtpo_card['month']) < 1
				|| strlen($zdtpo_card['year']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('"Expiry Date" is a required value. Please enter the expiry date.'));
			}
			if (strlen($zdtpo_card['cvv']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('"CVC/CVV2" is a required value. Please enter the cvc/cvv2.'));
			}
			if ($error==true) {
				//nothing
			} else {
				$_SESSION['zdtpo_card'] = array(
					'number' => $zdtpo_card['number'],
					'month'  => $zdtpo_card['month'],
					'year'   => $zdtpo_card['year'],
					'cvv'    => $zdtpo_card['cvv'],
				);
			}
		}
	}

	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;
		if ($payment->get_is_inside() == 0
			&& !isset($_POST['zdtpo_card_number'])) {
			redirect(href_link('zdtpo_process', '', 'SSL'));
		}

		// 获取订单信息
		// 获取订单信息
		$data['entry_account']  = trim($payment->get_account());
		$entryMD5key            = trim($payment->get_md5key());
		$entryprefixOrder       = trim($payment->get_mark1()); // 后台 附加字段1
		$entryOrderId           = put_orderNO($orderInfo['order_id']);
		$data['entry_order_id'] = empty($entryprefixOrder) ? $entryOrderId :  $entryprefixOrder . '-' . $orderInfo['order_id'];
		$data['entry_currency'] = $orderInfo['currency']['code'];
		$data['entry_amount']   = $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
		$data['entry_secret']   = strtoupper(hash('sha256', base64_decode($entryMD5key) . $data['entry_account'] . $data['entry_order_id'] . $data['entry_currency'] . $data['entry_amount']));
		$data['entry_version']  = 'V5.0';

		$data['entry_billing_name']     = $orderInfo['billing']['firstname'] . ' ' . $orderInfo['billing']['lastname'];
		$data['entry_billing_email']    = $orderInfo['customer']['email_address'];
		$data['entry_billing_address']  = trim($orderInfo['billing']['street_address'] . ' ' . $orderInfo['billing']['suburb']);
		$data['entry_billing_city']     = $orderInfo['billing']['city'];
		$data['entry_billing_state']    = $orderInfo['billing']['region'];
		$data['entry_billing_postcode'] = $orderInfo['billing']['postcode'];
		$country_iso                    = get_country_iso($orderInfo['billing']['country_id']);
		$data['entry_billing_country']  = $country_iso['iso_code_2'];
		$data['entry_billing_phone']    = $orderInfo['billing']['telephone'];

		$data['entry_delivery_name']     = $orderInfo['shipping']['firstname'] . ' ' . $orderInfo['shipping']['lastname'];
		$data['entry_delivery_email']    = $orderInfo['customer']['email_address'];
		$data['entry_delivery_address']  = trim($orderInfo['shipping']['street_address'] . ' ' . $orderInfo['shipping']['suburb']);
		$data['entry_delivery_city']     = $orderInfo['shipping']['city'];
		$data['entry_delivery_state']    = $orderInfo['shipping']['region'];
		$data['entry_delivery_postcode'] = $orderInfo['shipping']['postcode'];
		$country_iso                     = get_country_iso($orderInfo['shipping']['country_id']);
		$data['entry_delivery_country']  = $country_iso['iso_code_2'];
		$data['entry_delivery_phone']    = $orderInfo['shipping']['telephone'];

		$request_type = (((isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')))
			||(isset($_SERVER['HTTP_X_FORWARDED_BY']) && strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_BY']), 'SSL') !== false)
			||(isset($_SERVER['HTTP_X_FORWARDED_HOST']) && (strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']), 'SSL') !== false
					||strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']), str_replace('https://', '', HTTPS_SERVER)) !== false))
			||(isset($_SERVER['SCRIPT_URI']) && strtolower(substr($_SERVER['SCRIPT_URI'], 0, 6)) == 'https:')
			||(isset($_SERVER["HTTP_SSLSESSIONID"]) && $_SERVER["HTTP_SSLSESSIONID"] != '')
			||(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443')) ? 'SSL' : 'NONSSL';

		// 获取客户端信息
		$data['entry_payment_method']  = '';
		$data['entry_url']             = $_SERVER['HTTP_HOST'];
		$data['entry_ip']              = get_ip_address();
		$data['entry_user_agent']      = $_SERVER['HTTP_USER_AGENT'];
		$data['entry_accept_language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		$data['McCookie']              = isset($_COOKIE['McCookie']) ? $_COOKIE['McCookie'] : '';
		$data['entry_return_url']      = href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL');
		$data['entry_notice_url']      = $request_type == 'SSL' ? HTTPS_SERVER : HTTP_SERVER . '/zdtpoNotify.php';
		$data['csid']                  = '';
		$data['client_ip']             = $_POST['client_ip'];

		// 信用卡信息
		$data['CardNumber'] = $_POST['zdtpo_card_number'];
		$data['CardMonth']  = $_POST['zdtpo_card_month'];
		$data['CardYear']   = $_POST['zdtpo_card_year'];
		$data['CardCvv']    = $_POST['zdtpo_card_cvv'];

		// 获取商品信息
		$productArr = array();

		foreach ($orderProductInfo as $_product) {
			$price = $currencies->get_price($_product['price'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
			$url   = href_link(FILENAME_PRODUCT, 'pID=' . $_product['product_id']);

			$productArr[] = array(
				'qty'       => $_product['qty'],
				'name'      => $_product['name'],
				'price'     => $price,
				'url'       => $url,
				'attribute' => '',
			);
		}

		$data['Products'] = json_encode($productArr);
		$xmls             = $this->_post($payment->get_submit_url(), $data);
		$result           = $this->_xmlToArr($xmls);

		if (!is_array($result)) {
			$xmls   = $this->_post($payment->get_submit_url(), $data);
			$result = $this->_xmlToArr($xmls);

			if (!is_array($result)) {
				redirect(href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL'));
			}
		}

		if ($result['error'] == true) {
			redirect(href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL'));
		} else {
			// 状态为未支付的时候
			if (isset($result['order']['entry_status']) && $result['order']['entry_status'] == '2') {
				$postUrl = $result['order']['postUrl'];
				unset($result['order']['postUrl']);

				$payment_form = '<form method="post" action="https://' . $postUrl . '" id="zdtpo" name="zdtpo" target="_top">' . "\n";

				foreach ($result['order'] as $key => $val) {
					$payment_form .= '<input type="hidden" value="' . $val . '" name="' . $key . '">' . "\n";
				}

				$payment_form .= '</form>' . "\n";
				$payment_form .= '<script type="text/javascript">' . "\n";
				$payment_form .= 'window.onload = function() {' . "\n";
				$payment_form .= 'document.getElementById(\'zdtpo\').submit();' . "\n";
				$payment_form .= '}' . "\n";
				$payment_form .= '</script>' . "\n";

				echo $payment_form; die;
			} else {
				redirect(
					href_link(
						FILENAME_CHECKOUT_RESULT,
						'OrderID=' . $result['order']['entry_order_id'] .
						'&Currency=' . $result['order']['entry_currency'] .
						'&Amount=' . $result['order']['entry_amount'] .
						'&Code=' . $result['order']['entry_code'] .
						'&Status=' . $result['order']['entry_status'] .
						'&MD5info=' . $result['order']['entry_secret'],
						'SSL'
					)
				);
			}
		}
	}

	function result($payment)
	{
		// 获取银行返回结果
		$order_status = 4;

		if (isset($_REQUEST['order']) || isset($_REQUEST['OrderID'])) {
			$entryMD5key    = trim($payment->get_md5key());
			$entryOrderID   = isset($_REQUEST['order']['entry_order_id']) ? $_REQUEST['order']['entry_order_id'] : $_REQUEST['OrderID'];
			$entryCurrency  = isset($_REQUEST['order']['entry_currency']) ? $_REQUEST['order']['entry_currency'] : $_REQUEST['Currency'];
			$entryAmount    = isset($_REQUEST['order']['entry_amount']) ? $_REQUEST['order']['entry_amount'] : $_REQUEST['Amount'];
			$entryCode      = isset($_REQUEST['order']['entry_code']) ? $_REQUEST['order']['entry_code'] : $_REQUEST['Code'];
			$entryStatus    = isset($_REQUEST['order']['entry_status']) ? $_REQUEST['order']['entry_status'] : $_REQUEST['Status'];
			$entryMD5src    = base64_decode($entryMD5key) . $entryOrderID . $entryCurrency . $entryAmount . $entryCode . $entryStatus;
			$secretValidate = strtoupper(hash('sha256', $entryMD5src));
			$entrySecret    = isset($_REQUEST['order']['entry_secret']) ? $_REQUEST['order']['entry_secret'] : $_REQUEST['MD5info'];
			if (!empty($entrySecret) && ($secretValidate == $entrySecret) && ($entryStatus == '1')) {
				$order_status = 3;
			}
		}

		return $order_status;
	}

	function _post($url, $data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch ,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch ,CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}

	/**
	 * 将xml转为数组
	 *
	 * @param $xml
	 * @return mixed
	 */
	function _xmlToArr($xml)
	{
		$arr = json_decode(json_encode(simplexml_load_string($xml)), true);
		return $this->_cleanArr($arr);
	}

	/**
	 * 将数组中的空数组转为字符串
	 *
	 * @param $arr
	 * @return array
	 */
	function _cleanArr($arr)
	{
		$data = array();

		foreach ($arr as $key => $val) {
			if (is_array($val)) {
				if (empty($val)) {
					$data[$key] = '';
				} else {
					$data[$key] = $this->_cleanArr($val);
				}
			} else {
				$data[$key] = $val;
			}
		}

		return $data;
	}
}
