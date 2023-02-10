<?php
/**
 * payment paycloak.php
 */
class paycloak
{
	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;
		$paymentAccount    = trim($payment->get_account());
		$paymentPrivateKey = trim($payment->get_md5key());
		$orderNumber       = put_orderNO($orderInfo['order_id']);
		$customerIp        = $_SESSION['customer_ip_address'];
//		$currency          = $orderInfo['currency']['code'];
//		$amount            = $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);

		$currency          = 'USD';
		$amount            = $currencies->get_price($orderInfo['order_total'], $currency);
		$returnUrl         = href_link(FILENAME_CHECKOUT_RESULT, 'orderNumber=' . $orderNumber, 'SSL');
		$customerBName     = $orderInfo['billing']['firstname'] . ' ' . $orderInfo['billing']['lastname'];
		$customerBEmail    = $orderInfo['customer']['email_address'];
		$customerBPhone    = $orderInfo['billing']['telephone'];
		$customerBAddress  = trim($orderInfo['billing']['street_address'] . ' ' . $orderInfo['billing']['suburb']);
		$customerBCity     = $orderInfo['billing']['city'];
//		$customerBState    = $orderInfo['billing']['region'];

		$customerBState    = get_region_code($orderInfo['billing']['region_id']);
		$countryIso        = get_country_iso($orderInfo['billing']['country_id']);
		$customerBCountry  = $countryIso['iso_code_2'];;
		$customerBPostcode = $orderInfo['billing']['postcode'];
		$domainUrl         = 'https://' . $_SERVER['HTTP_HOST'];
		$invoiceId         = uniqid();
		$signatureToken    = strtoupper(hash('sha256', $invoiceId . $orderNumber . $paymentPrivateKey));

		$paymentForm = '<form method="post" action="' . $payment->get_submit_url() . '" id="paypalFm">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $paymentAccount .'" name="username">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $orderNumber .'" name="order_no">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $customerIp .'" name="client_ip">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $currency .'" name="currency">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $amount .'" name="amount">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $returnUrl .'" name="success_uri">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $returnUrl .'" name="return_uri">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $customerBName .'" name="name">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $customerBEmail .'" name="email">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $customerBPhone .'" name="telephone">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $customerBAddress .'" name="address">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $customerBCity .'" name="city">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $customerBState .'" name="state">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $customerBCountry .'" name="country">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $customerBPostcode .'" name="zip_code">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $domainUrl .'" name="from_url">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $invoiceId .'" name="invoice_id">' . "\n";
		$paymentForm .= '<input type="hidden" value="' . $signatureToken .'" name="token">' . "\n";
		$paymentForm .= '</form>' . "\n";
		$paymentForm .= '<h2>' . __('You will be redirected to Paypal in a few seconds.') . '</h2>' . "\n";
		$paymentForm .= '<script type="text/javascript">setTimeout(function(){$("#paypalFm").submit();}, 1000);</script>';
		
		echo $paymentForm;
	}
	
	function result($payment)
	{
		$this->addLog(json_encode($_POST));
		$result = array('order_status_id' => 4, 'billing' => $_POST['invoice_id'], 'remarks' => $_POST['failure_msg']);

		if (isset($_POST['order_no'])
			&& $_POST['order_no'] == put_orderNO($_SESSION['old_order_id'])
			&& $_POST['failure_code'] == 'success') {
			$result['order_status_id'] = 3;
		}

		return $result;
	}

	function addLog($log)
    {
        $fp = fopen(DIR_FS_CATALOG . 'cache/paycloak-log-' . date('Y-m-d') . '.txt', 'a');
        flock($fp, LOCK_EX) ;
        fwrite($fp, '[' . date('Y-m-d H:i:s') . ']' . $log . "\r\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}
