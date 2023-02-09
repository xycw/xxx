<?php
/**
 * payment tde.php
 */
class tde
{

    function before()
    {
        $txtType          = __('Type');
        $txtAccountNumber = __('Account Number');
        $txtBankCode      = __('Bank Code');
		$txtGiropay       = __('Giropay');
		$txtDirectpay     = __('Sofort Banking');

        $html = <<<HTML
<ul>
	<li class="fields">
		<label class="required"><em>*</em>$txtType</label>
		<div class="input-box">
			<select class="required-entry" name="tde_card[type]" style="width: 98%;">
               <option value="Giropay">$txtGiropay</option>
               <option value="Directpay">$txtDirectpay</option>
            </select>
		</div>
	</li>
	<li class="fields">
		<label class="required"><em>*</em>$txtAccountNumber</label>
		<div class="input-box">
			<input type="text" class="input-text required-entry digits" name="tde_card[number]" maxLength="12" style="width: 98%;" />
		</div>
	</li>
	<li class="fields">
		<label class="required"><em>*</em>$txtBankCode</label>
		<div class="input-box">
			<input type="text" class="input-text required-entry digits" name="tde_card[code]" maxLength="8" style="width: 98%;" />
		</div>
	</li>
</ul>
<script type="text/javascript" src="https://risk.hdkhdkrisk.com/ssl.js"></script>
HTML;

        return $html;
    }

    function after()
    {
    	global $message_stack, $error, $current_page;

        if (isset($_POST['tde_card'])) {
            $tde_card = $_POST['tde_card'];
            if (strlen($tde_card['type']) < 1) {
                $error = true;
                $message_stack->add($current_page, __('"Card Type" is a required value. Please enter the Card Type.'));
            } else if (!in_array($tde_card['type'], array('Giropay', 'Directpay'))) {
                $error = true;
                $message_stack->add($current_page, __('"Card Type" is not a valid Card Type.'));
            }
            if (strlen($tde_card['number']) < 4 || strlen($tde_card['number']) > 12 || !is_numeric($tde_card['number'])) {
                $error = true;
                $message_stack->add($current_page, __('"Account Number" is not a valid Account Number.'));
            }
            if (strlen($tde_card['code']) != 8 || !is_numeric($tde_card['code'])) {
                $error = true;
                $message_stack->add($current_page, __('"Bank Code" is a required value. Please enter the Bank Code.'));
            }

            if (!$error){
                $_SESSION['tde_card'] = array(
                    'type'   => $tde_card['type'],
                    'number' => $tde_card['number'],
                    'code'   => $tde_card['code']
                );
            }
        }
    }

	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;

		if ($payment->get_is_inside() == 0
			&& !isset($_POST['tde_card_paymentMethod'])) {
			redirect(href_link('tde_process', '', 'SSL'));
		}

		// 获取订单信息
		$data['MerchantID'] = trim($payment->get_account());
		$data['TransNo']    = trim($payment->get_mark1());
		$MD5key             = trim($payment->get_md5key());
		$data['OrderID']    = put_orderNO($orderInfo['order_id']);
		$data['Currency']   = $orderInfo['currency']['code'];
		$data['Amount']     = $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
		$data['MD5info']    = strtoupper(md5($MD5key . $data['MerchantID'] . $data['TransNo'] . $data['OrderID'] . $data['Currency'] . $data['Amount']));
		$data['Version']    = 'V4.51';
		
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

		// 获取借记卡信息
		$data['PaymentMethod']    = $payment->get_is_inside() == 1 ? $_SESSION['tde_card']['type'] : $_POST['tde_card_type'];	     //支付方式
		$data['PayAccountnumber'] = $payment->get_is_inside() == 1 ? $_SESSION['tde_card']['number'] : $_POST['tde_card_number'];//银行帐号
		$data['PayBankcode']      = $payment->get_is_inside() == 1 ? $_SESSION['tde_card']['code'] : $_POST['tde_card_code'];          //银行代码

		if ($payment->get_is_inside() == 1){
			$_SESSION['tde_card'] = null;
			unset($_SESSION['tde_card']);
		}
		// 获取客户端信息
		$data['URL']            = $_SERVER['HTTP_HOST'];
		$data['IP']             = get_ip_address();
		$data['UserAgent']      = $_SERVER['HTTP_USER_AGENT'];
		$data['AcceptLanguage'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		$data['McCookie']       = isset($_COOKIE['McCookie']) ? $_COOKIE['McCookie'] : '';
		$data['ReturnUrl']      = href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL');
		
		// 获取商品信息
		$products = array();
		foreach ($orderProductInfo as $_product) {
			$products[] = $_product['qty'] . 'x' . $_product['name'];
		}
		$data['Products'] = implode(',', $products);
		
		$result = json_decode($this->_post($payment->get_submit_url(), $data), true);
		if (!is_array($result)) {
			$result = json_decode($this->_post($payment->get_submit_url(), $data), true);
			if (!is_array($result)) {
				redirect(href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL'));
			}
		}

		if ($result['error'] == true) {
			redirect(href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL'));
		} else {
            // 获取通道返回数据
            $Remark        = $result['order']['Remark'];	//中外宝系统订单号
            $OrderID       = $result['order']['OrderID'];	//商户网站订单号
            $MerNo         = $result['order']['MerNo'];
            $GatewayNo     = $result['order']['GatewayNo'];
            $Currency      = $result['order']['Currency'];
            $Amount        = $result['order']['Amount'];
            $Code          = $result['order']['Code'];
            $Status        = $result['order']['Status'];
            $Results       = $result['order']['Results'];
            $SignInfo      = $result['order']['SignInfo'];
            $PayUrl        = $result['order']['PayUrl'];

            // 只有未支付的提交到银行
            if ($Status != '2') {
				redirect(href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL'));
            }

			// 拼接form表单字符串
			$payment_form  = '<form method="post" action="' . $PayUrl . '" id="checkout" name="checkout" target="_top">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $MerNo . '" name="merNo">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $GatewayNo . '" name="gatewayNo">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $OrderID . '" name="orderNo">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $Currency . '" name="orderCurrency">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $Amount . '" name="orderAmount">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $data['ReturnUrl'] . '" name="returnUrl">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $SignInfo . '" name="signInfo">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $data['BName'] . '" name="firstName">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $data['BName'] . '" name="lastName">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $data['BEmail'] . '" name="email">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $data['BPhone'] . '" name="phone">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $data['PaymentMethod'] . '" name="paymentMethod">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $data['PayAccountnumber'] . '" name="payAccountnumber">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $data['PayBankcode'] . '" name="payBankcode">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $data['PayCountrycode'] . '" name="payCountrycode">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $data['BCountry'] . '" name="country">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $data['BState'] . '" name="state">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $data['BCity'] . '" name="city">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $data['BAddress'] . '" name="address">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $data['BPostcode'] . '" name="zip">' . "\n";
			$payment_form .= '<input type="hidden" value="' . $Remark . '" name="remark">' . "\n";
			$payment_form .= '<input type="hidden" value="php" name="interfaceInfo">' . "\n";     //网店程序
			$payment_form .= '<input type="hidden" value="V2.1" name="interfaceVersion">' . "\n"; //插件版本
			$payment_form .= '<input type="hidden" value="0" name="isMobile">' . "\n";            //客户端类型:0:PC端,1:移动端
			$payment_form .= '</form>' . "\n";
			$payment_form .= '<h2>' . __('You will be redirected to tde pay in a few seconds.') . '</h2>' . "\n";
			$payment_form .= '<script type="text/javascript">' . "\n";
			$payment_form .= '$(function() {' . "\n";
			$payment_form .= 'document.checkout.submit();' . "\n";
			$payment_form .= '});' . "\n";
			$payment_form .= '</script>' . "\n";

			echo $payment_form;
		}
	}
	
	function result($payment)
	{
        // 如果是出错调用，直接返回失败
        $order_status = 4;
        if (empty($_POST)) return $order_status;

        // 获取银行返回
        $bankSignInfo        = isset($_POST['signInfo']) ? $_POST['signInfo'] : '';
        $_POST['MerchantID'] = trim($payment->get_account());
		
        // 提交数据到网关
        $result = json_decode($this->_post($payment->get_return_url(), $_POST), true);
		if (!is_array($result)) {
		    $result = json_decode($this->_post($payment->get_return_url(), $_POST), true);
		}

		if (!is_array($result) || $result['error']) {
		    return $order_status;
		}

        $signInfo = isset($result['order']['SignInfo']) ? $result['order']['SignInfo'] : '';
        $status   = isset($result['order']['Status']) ? $result['order']['Status'] : '';
		if (!empty($signInfo) && ($signInfo == $bankSignInfo) && ($status == '1')) {
			$order_status = 3;
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
}
