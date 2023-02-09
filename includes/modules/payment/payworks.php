<?php
/**
 * payment payworks.php
 */
class payworks
{
	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;
		//商户编号
		$v_AcctNo = trim($payment->get_account());
		//MD5私钥
		$MD5key = trim($payment->get_md5key());
		//金额（单位分）
		$v_Amount = $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value'])*100;
		//订单编号
		$v_OrderID = put_orderNO($orderInfo['order_id']);
		//货币编码
		switch ($orderInfo['currency']['code']) {
			case 'CNY':
			case 'RMB':
				$v_CurrCode = '156';
			break;
			case 'GBP':
				$v_CurrCode = '826';
			break;
			case 'EUR':
				$v_CurrCode = '978';
			break;
			case 'JPY':
				$v_CurrCode = '392';
			break;
			case 'HKD':
				$v_CurrCode = '344';
			break;
			case 'DKK':
				$v_CurrCode = '208';
			break;
			case 'NOK':
				$v_CurrCode = '578';
			break;
			case 'NLG':
				$v_CurrCode = '528';
			break;
			case 'USD':
			default:
				$v_CurrCode = '840';
			break;
		}
		//账单人地址
		$v_BAddress = trim($orderInfo['billing']['street_address'] . ' ' . $orderInfo['billing']['suburb']);
		//账单人城市
		$v_BCity = $orderInfo['billing']['city'];
		//账单人邮政编码
		$v_PostCode = $orderInfo['billing']['postcode'];
		//账单人邮箱
		$v_Email = $orderInfo['customer']['email_address'];
		//加密数据
		$signMsgVal = $MD5key.$v_AcctNo.$v_OrderID.$v_Amount.$v_CurrCode;
		$v_HashValue = $this->szComputeMD5Hash($signMsgVal, 'O');
		//持卡人的IP地址
		$v_IPAddress = $this->getip();
		//（01）代表消费
		$v_TxnType = '01';
		
		$payment_form = '<form method="post" action="' . $payment->get_submit_url() . '" id="checkout" name="checkout">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_AcctNo . '" name="AcctNo">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_OrderID . '" name="OrderID">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_CurrCode . '" name="CurrCode">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_Amount . '" name="Amount">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_IPAddress . '" name="IPAddress">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BAddress . '" name="BAddress">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_BCity . '" name="BCity">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_PostCode . '" name="PostCode">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_Email . '" name="Email">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_HashValue . '" name="HashValue">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $v_TxnType . '" name="TxnType">' . "\n";
		$payment_form .= '<input type="hidden" value="V3.0" name="IVersion" /></form>' . "\n";
		if ($payment->get_is_inside()==1) {
			$payment_form .= '<iframe width="100%" height="850" scrolling="no" style="border:none;margin:0 auto;overflow:hidden;" id="ifrm_checkout" name="ifrm_checkout"></iframe>' . "\n";
			$payment_form .= '<script type="text/javascript">' . "\n";
			$payment_form .= 'if (window.XMLHttpRequest) {' . "\n";
			$payment_form .= 'document.checkout.target="ifrm_checkout";' . "\n";
			$payment_form .= '}' . "\n";
			$payment_form .= 'document.checkout.action="' . $payment->get_submit_url() . '";' . "\n";
			$payment_form .= 'document.checkout.submit();' . "\n";
			$payment_form .= 'window.status="' . $payment->get_submit_url() . '";' . "\n";
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
		$AcctNo = $_POST["Par1"];
		$OrderID = $_POST["Par2"];
		$PGTxnID = $_POST["Par3"];
		$RespCode = $_POST["Par4"];
		$RespMsg = $_POST["Par5"];
		$Amount = $_POST["Par6"];
		$HashValue = $_POST["HashValue"];
		//MD5私钥
		$MD5key = trim($payment->get_md5key());
		$signMsgVal=$MD5key.$AcctNo.$OrderID.$PGTxnID.$RespCode.$RespMsg.$Amount;
        $v_HashValue = $this->szComputeMD5Hash($signMsgVal, 'O');
		if ($HashValue == $v_HashValue) {
			if($RespCode=='00'||$RespCode=='OK') {
				$order_status = 3;
			} else {
				$order_status = 4;
			}
		} else {
			$order_status = 4;
		}
		
		return $order_status;
	}

	function getip()
	{
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$online_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$online_ip = $_SERVER['HTTP_CLIENT_IP'];
		} else {
			$online_ip = $_SERVER['REMOTE_ADDR'];
		}
		return $online_ip;
	}

	function szComputeMD5Hash($input)
	{
		$md5hex=md5($input);
		$len=strlen($md5hex)/2;
		$md5raw="";
		for ($i=0;$i<$len;$i++) {
			$md5raw=$md5raw.chr(hexdec(substr($md5hex,$i*2,2)));
		}
		$keyMd5=base64_encode($md5raw);
		return $keyMd5;
	}
   
	function szComputeSHA1Hash($input)
	{
		$md5hex=sha1($input);
		$len=strlen($md5hex)/2;
		$md5raw="";
		for ($i=0;$i<$len;$i++) {
			$md5raw=$md5raw.chr(hexdec(substr($md5hex,$i*2,2)));
		}
		$keyMd5=base64_encode($md5raw);
		return $keyMd5;
	}
}
