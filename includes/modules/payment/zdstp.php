<?php
/**
 * payment zdstp.php
 */
class zdstp
{
	function process($payment)
	{
		if (!isset($_POST['csid'])) {
			$PayUrl = href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
			echo '
				<html>
				<head></head>
					<body>
						<form name="payForm" action="' . $PayUrl .'" method="post">
				        <input type="hidden" value="" name="client_ip" id="client_ip">
						<script type="text/javascript" src="https://risk.zdadam.com/sslcsid.js"></script>
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

		global $orderInfo, $orderProductInfo, $currencies;

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
		$data['entry_notice_url']      = $request_type == 'SSL' ? HTTPS_SERVER : HTTP_SERVER . '/stpNotify.php';
		$data['csid']                  = $_POST['csid'];
		$data['client_ip']             = $_POST['client_ip'];

		// 获取商品信息
		$products = array();
		foreach ($orderProductInfo as $_product) {
			$price      = $currencies->get_price($_product['price'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
			$products[] = $_product['qty'] . '#,#' . $_product['name'] . '#,#' . $price;
		}

		$data['Products'] = implode('#;#', $products);

		$xmls   = $this->_post($payment->get_submit_url(), $data);
		$result = $this->_xmlToArr($xmls);

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

			// 只有未支付的提交到银行
			if ($result['order']['entry_status'] != '2') {
				redirect(href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL'));
			}

			unset($result['order']['entry_code']);
			unset($result['order']['entry_status']);
			$postUrl = $result['order']['entry_pay_url'];
			$midUrl  = $result['order']['postUrl'];
			unset($result['order']['postUrl']);
			unset($result['order']['entry_pay_url']);

			$payment_form = '<iframe name="zdstpIframe" id="zdstpIframe" style="width:100%;height:100%;border:none;"></iframe>';
			$payment_form .= '<form method="post" action="' . $midUrl . '" id="zdstpCheckout" name="zdstpCheckout" target="zdstpIframe">' . "\n";

			foreach ($result['order'] as $key => $val) {
				$payment_form .= '<input type="hidden" value="' . $val . '" name="' . $key . '">' . "\n";
			}

			$payment_form .= '<input type="hidden" value="' . $postUrl . '" name="entry_payment_url">' . "\n";
			$payment_form .= '</form>' . "\n";
			$payment_form .= '<script type="text/javascript">' . "\n";
			$payment_form .= 'window.onload = function() {' . "\n";
			$payment_form .= 'document.getElementById(\'zdstpCheckout\').submit();' . "\n";
			$payment_form .= '}' . "\n";
			$payment_form .= '</script>' . "\n";

			echo $payment_form; die;
		}
	}

	function result($payment)
	{
		// 获取银行返回结果
		$order_status = 4;

		if (!empty($_POST)) {
			$entryMD5key   = trim($payment->get_md5key());
			$entryOrderID  = $_POST['entry_order_id'];
			$entryCurrency = $_POST['entry_currency'];
			$entryAmount   = $_POST['entry_amount'];
			$entryCode     = $_POST['entry_code'];
			$entryStatus   = $_POST['entry_status'];

			$entryMD5src    = base64_decode($entryMD5key) . $entryOrderID . $entryCurrency . $entryAmount . $entryCode . $entryStatus;
			$secretValidate = strtoupper(hash('sha256', $entryMD5src));
			$entrySecret    = isset($_POST['entry_secret']) ? $_POST['entry_secret'] : '';
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
