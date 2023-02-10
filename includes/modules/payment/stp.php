<?php
/**
 * payment stp.php
 */
class stp
{
	function process($payment)
	{
		if (!isset($_POST['client_ip'])) {
			$PayUrl = href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
			echo '
				<html>
				<head></head>
					<body>
						<form name="payForm" action="' . $PayUrl . '" method="post">
				        <input type="hidden" name="client_ip" id="client_ip">
						<script type="text/javascript" src="https://risk.hdkhdkrisk.com/sslcsid.js"></script>
			            <script src="https://pv.sohu.com/cityjson?ie=utf-8"></script>
			            <script type="text/javascript">document.getElementById("client_ip").value = returnCitySN["cip"];</script>
						</form>
					</body>
				</html>
				<script type="text/javascript">
					function myfunction(){
  						payForm.submit();
					}
					window.onload=myfunction();
				</script>
				 ';
			die;
		}

		global $orderInfo, $orderProductInfo, $currencies, $mobileDetect;

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
		$data['NoticeUrl']      = $request_type == 'SSL' ? HTTPS_SERVER : HTTP_SERVER . '/stpNotify.php';

		// 限制卡种
		$data['PaymentMethod']  = '';
		$data['csid']           = '';
		$data['client_ip']      = $_POST['client_ip'];

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

			// 只有未支付的提交到银行
			if ($result['order']['Status'] != '2') {
				redirect(href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL'));
			}

			$midUrl = $result['order']['postUrl'];
			unset($result['order']['postUrl']);
			if ($mobileDetect->isMobile()) {
				$result['order']['isMobile'] = '1';
			}

			$payment_form = '<form method="post" action="' . $midUrl . '" id="stpCheckout" name="stpCheckout" target="_top">' . "\n";

			foreach ($result['order'] as $key => $val) {
				$payment_form .= '<input type="hidden" value="' . $val . '" name="' . $key . '">' . "\n";
			}

			$payment_form .= '</form>' . "\n";
			$payment_form .= '<script type="text/javascript">' . "\n";
			$payment_form .= 'window.onload = function() {' . "\n";
			$payment_form .= 'document.getElementById(\'stpCheckout\').submit();' . "\n";
			$payment_form .= '}' . "\n";
			$payment_form .= '</script>' . "\n";

			echo $payment_form; die;
		}
	}

	function result($payment)
	{
		$result = array('order_status_id' => '4', 'billing' => '', 'remarks' => '');

		if (!empty($_POST)) {
			$transNo           = trim($payment->get_mark1());
			$MD5key            = trim($payment->get_md5key());
			$orderID           = $_POST['order']['OrderID'];
			$currency          = $_POST['order']['Currency'];
			$amount            = $_POST['order']['Amount'];
			$code              = $_POST['order']['Code'];
			$status            = $_POST['order']['Status'];
			$result['billing'] = $_POST['order']['Billing'];

			$MD5Validate = strtoupper(md5($MD5key . $transNo . $orderID . $currency . $amount . $code . $status));
			$MD5info     = isset($_POST['order']['MD5info']) ? $_POST['order']['MD5info'] : '';

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
