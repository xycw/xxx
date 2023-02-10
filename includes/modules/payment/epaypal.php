<?php
/**
 * payment epaypal.php
 */
class epaypal
{
	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;
		$paymentAccount = trim($payment->get_account());
		$paymentMd5key  = trim($payment->get_md5key());
		$orderNumber = put_orderNO($orderInfo['order_id']);
		$currency    = $orderInfo['currency']['code'];
		$amount      = $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
		$returnUrl   = href_link(FILENAME_CHECKOUT_RESULT, 'orderNumber=' . $orderNumber, 'SSL');
		$cancelUrl   = href_link(FILENAME_SHOPPING_CART, '', 'SSL');
		$signature   = md5($paymentMd5key . $paymentAccount . $orderNumber . $currency . $amount);
		
		$paymentForm = '<form method="post" action="' . $payment->get_submit_url() . '" id="paypalFm">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $returnUrl .'" name="ReturnUrl">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $cancelUrl .'" name="CancelUrl">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $currency .'" name="Currency">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $amount .'" name="Amount">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $orderNumber .'" name="OrderNumber">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $signature .'" name="Signature">' . "\n";
		$paymentForm .= '</form>' . "\n";
		$paymentForm .= '<h2>' . __('You will be redirected to Paypal in a few seconds.') . '</h2>' . "\n";
		$paymentForm .= '<script type="text/javascript">setTimeout(function(){$("#paypalFm").submit();}, 1000);</script>';
		
		echo $paymentForm;
	}
	
	function result($payment)
	{
		$this->addLog(json_encode($_GET));
		$result = array('order_status_id' => 4, 'billing' => $_GET['paymentId'], 'remarks' => $_GET['state']);

		if (isset($_GET['orderNumber'])
			&& $_GET['orderNumber'] == put_orderNO($_SESSION['old_order_id'])
			&& $_GET['state'] == 'completed') {
			$result['order_status_id'] = 3;
		}

		return $result;
	}

	function addLog($log)
    {
        $fp = fopen(DIR_FS_CATALOG . 'cache/epaypal-log-' . date('Y-m-d') . '.txt', 'a');
        flock($fp, LOCK_EX) ;
        fwrite($fp, '[' . date('Y-m-d H:i:s') . ']' . $log . "\r\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}
