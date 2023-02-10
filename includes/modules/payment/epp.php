<?php
/**
 * payment epp.php
 */
class epp
{
	function process($payment)
	{
        global $orderInfo, $currencies;

        $orderNumber   = put_orderNO($orderInfo['order_id']);
        $paymentMd5key = trim($payment->get_md5key());
		$currency      = $orderInfo['currency']['code'];

        $order = array(
            'domain'       => $_SERVER['HTTP_HOST'],
            'customerIp'   => $orderInfo['ip_address'],
            'orderNumber'  => $orderNumber,
            'email'        => $orderInfo['customer']['email_address'],
            'currency'     => $currency,
            'amount'       => $currencies->get_price($orderInfo['order_total'], $currency, $orderInfo['currency']['value']),
            'returnUrl'    => href_link(FILENAME_CHECKOUT_RESULT, 'orderNumber=' . $orderNumber, 'SSL'),
            'cancelUrl'    => href_link(FILENAME_SHOPPING_CART, '', 'SSL'),
			'dateOrder'    => $orderInfo['date_added']
        );

        $order['signature']        = md5($paymentMd5key . implode('', $order));
		$order['customerName']     = $orderInfo['billing']['firstname'] . ' ' . $orderInfo['billing']['lastname'];
		$order['customerAddress']  = trim($orderInfo['shipping']['street_address'] . ' ' . $orderInfo['shipping']['suburb']);
		$order['customerCity']     = $orderInfo['shipping']['city'];
		$order['customerState']    = $orderInfo['shipping']['region'];
		$order['customerPostcode'] = $orderInfo['shipping']['postcode'];
		$country_iso               = get_country_iso($orderInfo['shipping']['country_id']);
		$order['customerCountry']  = $country_iso['iso_code_2'];
		$order['customerPhone']    = $orderInfo['shipping']['telephone'];

        $data = array(
            'order' => json_encode($order)
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $payment->get_submit_url());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch ,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch ,CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        curl_close($ch);
        
        // 验证是否有数据返回
        $response = json_decode($response, true);

        if (!isset($response['error_code']) || $response['error_code'] == 1) {
            header('Location: ' . $order['returnUrl']);
            die;
        }

        $redirectUrl = $response['data']['url'];
        $redirectUrl = $redirectUrl . '?invoiceNumber=' . $response['data']['invoiceNumber'];
        $redirectUrl = $redirectUrl . '&notifyUrl=' . $response['data']['notifyUrl'];
        $redirectUrl = $redirectUrl . '&returnUrl=' . $order['returnUrl'];

        // 请求PayPal中转站
        echo '<h2>' . __('You will be redirected to PayPal in a few seconds.') . '</h2>' . "\n";

        header('Location: ' . $redirectUrl);
	}
	
	function result($payment)
	{
		$this->addLog(json_encode($_GET));
		$result = array('order_status_id' => 4, 'billing' => $_GET['invoiceNumber'], 'remarks' => $_GET['state']);

		if (isset($_GET['orderNumber'])
			&& $_GET['orderNumber'] == put_orderNO($_SESSION['old_order_id'])) {
			if ($_GET['state'] == 'completed') {
				$result['order_status_id'] = 3;
			} elseif($_GET['state'] == 'pending') {
				$result['order_status_id'] = 1;
			}
		}

		return $result;
	}

	function addLog($log)
    {
        $fp = fopen(DIR_FS_CATALOG . 'cache/epp-log-' . date('Y-m-d') . '.txt', 'a');
        flock($fp, LOCK_EX) ;
        fwrite($fp, '[' . date('Y-m-d H:i:s') . ']' . $log . "\r\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}
