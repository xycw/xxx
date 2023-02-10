<?php
/**
 * payment tpo.php
 */
class tpo
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
			<input type="text" style="width: 98%;" class="input-text required-entry creditcard" onfocus="$('#tpo').click();" name="tpo_card[number]" maxlength="16" />
		</div>
	</li>
	<li class="fields">
		<label class="required"><em>*</em>$txtExpirationDate</label>
		<div class="input-box">
			<select class="required-entry" style="width: 45%;" name="tpo_card[month]" onfocus="$('#tpo').click();">$monthStr</select>
		</div>
	</li>
	<li class="fields">
		<div class="input-box">
			<select class="required-entry" style="width: 45%;" name="tpo_card[year]" onfocus="$('#tpo').click();">$yearStr</select>
		</div>
	</li>
	<li class="fields">
		<label class="required"><em>*</em>$txtCardVerificationNumber</label>
		<div class="input-box">
			<input type="password" class="input-text required-entry digits" name="tpo_card[cvv]" onfocus="$('#tpo').click();" maxlength="3" style="width:38%;" />
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

		if (isset($_POST['tpo_card'])) {
			$tpo_card = $_POST['tpo_card'];
			if (strlen($tpo_card['number']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('"Card Number" is a required value. Please enter the card number.'));
			} elseif (!validate_creditcard($tpo_card['number'])) {
				$error = true;
				$message_stack->add($current_page, __('"Card Number" is not a valid card number.'));
			}
			if (strlen($tpo_card['month']) < 1
				|| strlen($tpo_card['year']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('"Expiry Date" is a required value. Please enter the expiry date.'));
			}
			if (strlen($tpo_card['cvv']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('"CVC/CVV2" is a required value. Please enter the cvc/cvv2.'));
			}
			if ($error==true) {
				//nothing
			} else {
				$_SESSION['tpo_card'] = array(
					'number' => $tpo_card['number'],
					'month'  => $tpo_card['month'],
					'year'   => $tpo_card['year'],
					'cvv'    => $tpo_card['cvv'],
				);
			}
		}
	}

	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;
		if ($payment->get_is_inside() == 0
			&& !isset($_POST['tpo_card_number'])) {
			redirect(href_link('tpo_process', '', 'SSL'));
		}

		// 获取订单信息
		$data['MerchantID'] = trim($payment->get_account());
		$data['TransNo']    = trim($payment->get_mark1());
		$MD5key             = trim($payment->get_md5key());
		$data['OrderID']    = put_orderNO($orderInfo['order_id']);
		$data['Currency']   = $orderInfo['currency']['code'];
		$data['Amount']     = $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
		$data['MD5info']    = strtoupper(md5($MD5key . $data['MerchantID'] . $data['TransNo'] . $data['OrderID'] . $data['Currency'] . $data['Amount']));
		$data['Version']    = 'V5.0';

		// 获取账单人信息
		$data['BName']     = $orderInfo['billing']['firstname'] . ' ' . $orderInfo['billing']['lastname'];
		$data['BEmail']    = $orderInfo['customer']['email_address'];
		$data['BAddress']  = trim($orderInfo['billing']['street_address'] . ' ' . $orderInfo['billing']['suburb']);
		$data['BCity']     = $orderInfo['billing']['city'];
		$data['BState']    = $orderInfo['billing']['region'];
		$data['BPostcode'] = $orderInfo['billing']['postcode'];
		$country_iso       = get_country_iso($orderInfo['billing']['country_id']);
		$data['BCountry']  = $country_iso['iso_code_2'];
		$data['BPhone']    = $orderInfo['billing']['telephone'];

		// 获取收货人信息
		$data['DName']     = $orderInfo['shipping']['firstname'] . ' ' . $orderInfo['shipping']['lastname'];
		$data['DEmail']    = $orderInfo['customer']['email_address'];
		$data['DAddress']  = trim($orderInfo['shipping']['street_address'] . ' ' . $orderInfo['shipping']['suburb']);
		$data['DCity']     = $orderInfo['shipping']['city'];
		$data['DState']    = $orderInfo['shipping']['region'];
		$data['DPostcode'] = $orderInfo['shipping']['postcode'];
		$country_iso       = get_country_iso($orderInfo['shipping']['country_id']);
		$data['DCountry']  = $country_iso['iso_code_2'];
		$data['DPhone']    = $orderInfo['shipping']['telephone'];

		$request_type = (((isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')))
			||(isset($_SERVER['HTTP_X_FORWARDED_BY']) && strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_BY']), 'SSL') !== false)
			||(isset($_SERVER['HTTP_X_FORWARDED_HOST']) && (strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']), 'SSL') !== false
					||strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']), str_replace('https://', '', HTTPS_SERVER)) !== false))
			||(isset($_SERVER['SCRIPT_URI']) && strtolower(substr($_SERVER['SCRIPT_URI'], 0, 6)) == 'https:')
			||(isset($_SERVER["HTTP_SSLSESSIONID"]) && $_SERVER["HTTP_SSLSESSIONID"] != '')
			||(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443')) ? 'SSL' : 'NONSSL';

		// 获取客户端信息
		$data['URL']            = $_SERVER['HTTP_HOST'];
		$data['IP']             = get_ip_address();
		$data['UserAgent']      = $_SERVER['HTTP_USER_AGENT'];
		$data['AcceptLanguage'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		$data['McCookie']       = isset($_COOKIE['McCookie']) ? $_COOKIE['McCookie'] : '';
		$data['ReturnUrl']      = href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL');
		$data['NoticeUrl']      = $request_type == 'SSL' ? HTTPS_SERVER : HTTP_SERVER . '/tpoNotify.php';

		// 限制卡种
		$data['PaymentMethod']  = '';

		// 信用卡信息
		$data['CardNumber'] = $_POST['tpo_card_number'];
		$data['CardMonth']  = $_POST['tpo_card_month'];
		$data['CardYear']   = $_POST['tpo_card_year'];
		$data['CardCvv']    = $_POST['tpo_card_cvv'];

		$data['csid']           = $_POST['csid'];
		$data['client_ip']      = $_POST['client_ip'];

		// 获取商品信息
		$productData = array();

		foreach ($orderProductInfo as $_product) {
			$price      = $currencies->get_price($_product['price'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
			$productUrl = href_link(FILENAME_PRODUCT, 'pID=' . $_product['product_id']);

			$productData[] = array(
				'qty'       => $_product['qty'],
				'name'      => $_product['name'],
				'price'     => $price,
				'url'       => $productUrl,
				'attribute' => ''
			);
		}

		$data['Products'] = json_encode($productData);
		$result           = json_decode($this->_post($payment->get_submit_url(), $data), true);

		if (!is_array($result)) {
			$result = json_decode($this->_post($payment->get_submit_url(), $data), true);
			if (!is_array($result)) {
				redirect(href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL'));
			}
		}

		if ($result['error'] == true) {
			redirect(href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL'));
		} else {
			// 状态为未支付的时候
			if (isset($result['order']['Status']) && $result['order']['Status'] == '2') {
				$postUrl = $result['order']['postUrl'];
				unset($result['order']['postUrl']);

				$payment_form = '<form method="post" action="https://' . $postUrl . '" id="tpo" name="tpo" target="_top">' . "\n";

				foreach ($result['order'] as $key => $val) {
					$payment_form .= '<input type="hidden" value="' . $val . '" name="' . $key . '">' . "\n";
				}

				$payment_form .= '</form>' . "\n";
				$payment_form .= '<script type="text/javascript">' . "\n";
				$payment_form .= 'window.onload = function() {' . "\n";
				$payment_form .= 'document.getElementById(\'tpo\').submit();' . "\n";
				$payment_form .= '}' . "\n";
				$payment_form .= '</script>' . "\n";

				echo $payment_form; die;
			} else {
				redirect(
					href_link(
						FILENAME_CHECKOUT_RESULT,
						'OrderID=' . $result['order']['OrderID'] .
						'&Currency=' . $result['order']['Currency'] .
						'&Amount=' . $result['order']['Amount'] .
						'&Code=' . $result['order']['Code'] .
						'&Status=' . $result['order']['Status'] .
						'&Billing=' . $result['order']['Billing'] .
						'&MD5info=' . $result['order']['MD5info'],
						'SSL'
					)
				);
			}
		}
	}

	function result($payment)
	{
		$result = array('order_status_id' => '4', 'billing' => '', 'remarks' => '');

		if (isset($_REQUEST['order']) || isset($_REQUEST['OrderID'])) {
			$transNo           = trim($payment->get_mark1());
			$MD5key            = trim($payment->get_md5key());
			$orderID           = isset($_REQUEST['order']['OrderID']) ? $_REQUEST['order']['OrderID'] : $_REQUEST['OrderID'];
			$currency          = isset($_REQUEST['order']['Currency']) ? $_REQUEST['order']['Currency'] : $_REQUEST['Currency'];
			$amount            = isset($_REQUEST['order']['Amount']) ? $_REQUEST['order']['Amount'] : $_REQUEST['Amount'];
			$code              = isset($_REQUEST['order']['Code']) ? $_REQUEST['order']['Code'] : $_REQUEST['Code'];
			$status            = isset($_REQUEST['order']['Status']) ? $_REQUEST['order']['Status'] : $_REQUEST['Status'];
			$result['billing'] = isset($_REQUEST['order']['Billing']) ? $_REQUEST['order']['Billing'] : $_REQUEST['Billing'];

			$MD5Validate = strtoupper(md5($MD5key . $transNo . $orderID . $currency . $amount . $code . $status));
			$MD5info     = isset($_REQUEST['order']['MD5info']) ? $_REQUEST['order']['MD5info'] : $_REQUEST['MD5info'];

			if ($MD5Validate == $MD5info) {
				if($status == '1') {
					$result['order_status_id'] = 3;
				} else {
					$result['order_status_id'] = 4;
					$result['remarks']         = 'Code:' . $code;
				}
			} else {
				$result['order_status_id'] = 4;
				$result['remarks']         = 'Verification Failure!';
			}
		}

		return $result;
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
}
