<?php
if (!strstr($_SERVER['HTTP_REFERER'], 'quick_payment')) {
	die('error1!');
}
$paymentMethod = $db->Execute("SELECT * FROM " . TABLE_PAYMENT_METHOD . " WHERE status = 1 AND mark3 = 'quick' ORDER BY sort_order ASC LIMIT 1");
switch ($paymentMethod->fields['code']) {
	case 'mycheckout':
		$data['MerchantID'] = $paymentMethod->fields['account'];
		$data['TransNo']    = $paymentMethod->fields['mark1'];
		$MD5key             = $paymentMethod->fields['md5key'];
		$data['OrderID']    = $_POST['order_id'];
		$data['Currency']   = $_SESSION['currency'];
		$data['Amount']     = number_format($_POST['amount'], 2, '.', '');
		$data['MD5info']    = strtoupper(md5($MD5key . $data['MerchantID'] . $data['TransNo'] . $data['OrderID'] . $data['Currency'] . $data['Amount']));
		$data['Version']    = 'V4.51';
		
		$data['BName']     = $_POST['firstname'] . ' ' . $_POST['lastname'];
		$data['BEmail']    = $_POST['email'];
		$data['BAddress']  = $_POST['address'];
		$data['BCity']     = $_POST['city'];
		$data['BState']    = $_POST['state'];
		$data['BPostcode'] = $_POST['postcode'];
		$data['BCountry']  = $_POST['country'];
		$data['BPhone']    = $_POST['phone'];
		
		$data['DName']     = $_POST['firstname'] . ' ' . $_POST['lastname'];
		$data['DEmail']    = $_POST['email'];
		$data['DAddress']  = $_POST['address'];
		$data['DCity']     = $_POST['city'];
		$data['DState']    = $_POST['state'];
		$data['DPostcode'] = $_POST['postcode'];
		$data['DCountry']  = $_POST['country'];
		$data['DPhone']    = $_POST['phone'];
		
		$data['CardNumber'] = $_POST['card_number'];
		$data['CardMonth']  = $_POST['card_month'];
		$data['CardYear']   = $_POST['card_year'];
		$data['CardCvv']    = $_POST['card_cvv'];
		
		$data['URL']            = $_SERVER['HTTP_HOST'];
		$data['IP']             = get_ip_address();
		$data['UserAgent']      = $_SERVER['HTTP_USER_AGENT'];
		$data['AcceptLanguage'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		$data['McCookie']       = $_COOKIE['McCookie'];
		$data['csid']           = $_POST['csid'];
		$data['Products']       = 'Quick Payment';
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $paymentMethod->fields['submit_url']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch ,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch ,CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		if (!is_array($result)) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $paymentMethod->fields['submit_url']);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch ,CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch ,CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_TIMEOUT, 120);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			$result = curl_exec($ch);
			curl_close($ch);
			$result = json_decode($result, true);
			if (!is_array($result)) {
				die('Error Code: 2001');
			}
		}
		if ($result['error'] == true) {
			die('Error Code: ' . $result['code']);
		} else {
			$OrderID  = $result['order']['OrderID'];
			$Currency = $result['order']['Currency'];
			$Amount   = $result['order']['Amount'];
			$Code     = $result['order']['Code'];
			$Status   = $result['order']['Status'];
			$MD5info  = $result['order']['MD5info'];
		
			$TransNo = $paymentMethod->fields['mark1'];
			$MD5key  = $paymentMethod->fields['md5key'];
			$MD5src  = $MD5key . $TransNo . $OrderID . $Currency . $Amount . $Code . $Status;
			$MD5sign = strtoupper(md5($MD5src));
			if ($MD5sign != $MD5info) {
				die('Verify MAC Failed!');
			}
		}
	break;
	case 'myorder':
		require(DIR_FS_CATALOG_MODULES . 'payment/myorder.php');
		$payment = new myorder();
		$data['AcctNo']    = trim($paymentMethod->fields['account']);
		$MD5key            = trim($paymentMethod->fields['md5key']);
		$data['OrderID']   = $_POST['order_id'];
		$data['Amount']    = number_format($_POST['amount'], 2, '.', '') * 100;
		$data['CurrCode']  = $payment->getCurrCode($_SESSION['currency']);
		$data['HashValue'] = $payment->szComputeMD5Hash($MD5key . $data['AcctNo'] . $data['OrderID'] . $data['Amount'] . $data['CurrCode']);

		$data['IPAddress']       = get_ip_address();
		$data['Telephone']       = $_POST['phone'];
		$data['CardPAN']         = $_POST['card_number'];
		$data['CName']           = $_POST['firstname'] . ' ' . $_POST['lastname'];
		$data['ExpirationMonth'] = $_POST['card_month'];
		$data['ExpirationYear']  = $_POST['card_year'];
		$data['CVV2']            = $_POST['card_cvv'];
		$data['BAddress']        = $_POST['address'];
        $data['BCity']           = $_POST['city'];
        $data['PostCode']        = $_POST['postcode'];
        $data['Email']           = $_POST['email'];
        $data['Bstate']          = $_POST['state'];
        $data['Bcountry']        = $_POST['country'];
        $data['BCountryCode']    = $_POST['country'];
        $data['IFrame']          = '1';
        $data['URL']             = $_SERVER['HTTP_HOST'];
        $data['OrderUrl']        = href_link('quick_payment_result', '', 'SSL');
        $data['PName']           = 'quick x 1';
        $data['Framework']       = 'PHP';
        $data['IVersion']        = 'V7.0';
        $data['Language']        = STORE_LANGUAGE;

		$url = trim($paymentMethod->fields['submit_url']);
		$result = $payment->curlPost($url, $data);
		$payment->addLog($result);
		$result = json_decode($result, true);
		$OrderID  = $data['OrderID'];
		$Currency = $_SESSION['currency'];
		$Amount   = number_format($_POST['amount'], 2, '.', '');
		$Status   = 0;

		if (!is_array($result)) {
			$url = str_replace('https', 'http', $url);
			$result = $payment->curlPost($url, $data);
			$result = json_decode($result, true);
		}
		if (is_array($result)
			&& ($result['status'] == '0000' || $result['data']['par4'] == '00')) {
			$Status = 1;
		}
	break;
	default:
		die('error2!');
	break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo STORE_LANGUAGE; ?>" lang="<?php echo STORE_LANGUAGE; ?>">
<head>
	<title><?php echo __('Payment Receipt'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
	<meta http-equiv="X-UA-Compatible" content="IE-Edge,chrome">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0, user-scalable=no, minimal-ui">
	<base href="<?php echo (($request_type=='SSL')?HTTPS_SERVER:HTTP_SERVER) . DIR_WS_CATALOG; ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo $code_page_directory; ?>/css/styles.css" />
</head>
<body>
<div class="page">
	<div class="header">
		<h1><?php echo __('Payment Receipt'); ?></h1>
	</div>
	<div class="main">
		<div class="title">
			<p><?php echo __('Order Number'); ?>: <span><?php echo $OrderID; ?></span></p>
			<p><?php echo __('Order Status'); ?>: <span><?php if ($Status == 1) { ?><font color="green"><?php echo __('Successful'); ?></font><?php } else { ?><font color="red"><?php echo __('Failure'); ?></font><?php } ?></span></p>
			<p class="last"><?php echo __('Order Amount'); ?>: <span><?php echo $Currency; ?><?php echo $Amount; ?></span></p>
		</div>
	</div>
	<div class="footer"></div>
</div>
</body>
</html>
<?php die; ?>