<?php
/**
 * payment tpo.php
 */
class hotpay
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
			<input type="text" style="width: 98%;" class="input-text required-entry creditcard" onfocus="$('#hotpay').click();" name="hotpay_card[number]" maxlength="16" />
		</div>
	</li>
	<li class="fields">
		<label class="required"><em>*</em>$txtExpirationDate</label>
		<div class="input-box">
			<select class="required-entry" style="width: 45%;" name="hotpay_card[month]" onfocus="$('#hotpay').click();">$monthStr</select>
		</div>
	</li>
	<li class="fields">
		<div class="input-box">
			<select class="required-entry" style="width: 45%;" name="hotpay_card[year]" onfocus="$('#hotpay').click();">$yearStr</select>
		</div>
	</li>
	<li class="fields">
		<label class="required"><em>*</em>$txtCardVerificationNumber</label>
		<div class="input-box">
			<input type="password" class="input-text required-entry digits" name="hotpay_card[cvv]" onfocus="$('#hotpay').click();" maxlength="3" style="width:38%;" />
			<img src="images/payment/cvv.gif" />
		</div>
	</li>
</ul>
HTML;

		return $html;
	}

	function after()
	{
		global $message_stack, $error, $current_page;

		if (isset($_POST['hotpay_card'])) {
			$hotpay_card = $_POST['hotpay_card'];
			if (strlen($hotpay_card['number']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('"Card Number" is a required value. Please enter the card number.'));
			} elseif (!validate_creditcard($hotpay_card['number'])) {
				$error = true;
				$message_stack->add($current_page, __('"Card Number" is not a valid card number.'));
			}
			if (strlen($hotpay_card['month']) < 1
				|| strlen($hotpay_card['year']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('"Expiry Date" is a required value. Please enter the expiry date.'));
			}
			if (strlen($hotpay_card['cvv']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('"CVC/CVV2" is a required value. Please enter the cvc/cvv2.'));
			}
			if ($error==true) {
				//nothing
			} else {
				$_SESSION['hotpay_card'] = array(
					'number' => $hotpay_card['number'],
					'month'  => $hotpay_card['month'],
					'year'   => $hotpay_card['year'],
					'cvv'    => $hotpay_card['cvv'],
				);
			}
		}
	}

	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;
		if ($payment->get_is_inside() == 0
			&& !isset($_POST['hotpay_card_number'])) {
			redirect(href_link('hotpay_process', '', 'SSL'));
		}

		// 获取订单信息
		$data['MerchantID'] = trim($payment->get_account());
		$data['TerNo']    = trim($payment->get_mark1());
		$MD5key             = trim($payment->get_md5key());
        $shipFee       = number_format($orderInfo['shipping_method']['fee'] * $orderInfo['currency']['value'], '2', '.', '');
		$data['OrderID']    = put_orderNO($orderInfo['order_id']);
		$data['Currency']   = $orderInfo['currency']['code'];
		$data['Amount']     = $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);


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
		$data['NoticeUrl']      = $request_type == 'SSL' ? HTTPS_SERVER : HTTP_SERVER . '/hotpayNotify.php';

		// 限制卡种
		$data['PaymentMethod']  = '';

		$data['csid']           = $_POST['csid'];
		$data['client_ip']      = $_POST['client_ip'];

		// 获取商品信息
		$productData = array();

		foreach ($orderProductInfo as $_product) {
			$price      = $currencies->get_price($_product['price'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
			$productUrl = href_link(FILENAME_PRODUCT, 'pID=' . $_product['product_id']);

			$productData[] = array(
			    'gdNo'      => $_product['product_id'],
				'gdName'      => $_product['name'],
                'gdQty'       => $_product['qty'],
				'gdPrice'     => $price,
			);
		}

		$data['Products'] = json_encode($productData);



        // 其他信息
        $lang          = explode ( ";", $_SERVER ['HTTP_ACCEPT_LANGUAGE'] );
        $acceptLang    = $lang [0]; // 接受的语言
        $userAgent     = $_SERVER ['HTTP_USER_AGENT']; // 浏览器信息
        $interfaceMode = "ECShop_direct"; // 网店类型

        $client        = $this->isMobile () ? "Mobile" : "PC";

        $version       = '2.0'; // 插件版本号
        // 获取信用卡信息
        $data['CardNumber'] = $_POST['hotpay_card_number'];
        $data['CardMonth']  = $_POST['hotpay_card_month'];
        $data['CardYear']   = $_POST['hotpay_card_year'];
        $data['CardCvv']    = $_POST['hotpay_card_cvv'];
        $issuingBank      = '';

        // 组合加密
        $signSrc = $data['MerchantID'] . $data['TerNo'] . $data['OrderID'] . $data['Currency'] . $data['Amount'] . $data['CardNumber'] . $data['CardYear'] . $data['CardMonth'] . $data['CardCvv'] . $MD5key;
        $signInfo = hash ( 'sha256', $signSrc );

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



        // 组装参数
        $data = array (
            'version'          => $version,
            'cardNo'           => $data['CardNumber'],
            'cardSecurityCode' => $data['CardCvv'],
            'cardExpireMonth'  => $data['CardMonth'],
            'cardExpireYear'   => $data['CardYear'],
            'issuingBank'      => $issuingBank,
            'merNo'            => $data['MerchantID'],
            'terNo'            => $data['TerNo'],
            'orderNo'          => $data['OrderID'],
            'orderAmount'      => $data['Amount'],
            'orderCurrency'    => $data['Currency'],
            'shipFee'          => $shipFee,
            'billFirstName'    => $orderInfo['billing']['firstname'],
            'billLastName'     => $orderInfo['billing']['lastname'],
            'billEmail'        => $orderInfo['customer']['email_address'],
            'billPhone'        => $orderInfo['billing']['telephone'],
            'billZip'          => $orderInfo['billing']['postcode'],
            'billAddress'      => $data['BAddress'],
            'billCity'         => $data['BCity'],
            'billState'        => $data['BState'],
            'billCountry'      => $data['BCountry'],
            'shipFirstName'    => $orderInfo['shipping']['firstname'],
            'shipLastName'     => $orderInfo['shipping']['lastname'],
            'shipPhone'        => $orderInfo['shipping']['telephone'],
            'shipEmail'        => $orderInfo['customer']['email_address'],
            'shipCountry'      => $country_iso['iso_code_2'],
            'shipState'        => $data['DState'],
            'shipCity'         => $data['DCity'],
            'shipAddress'      => $data['DAddress'],
            'shipZip'          => $data['DPostcode'],
            'returnUrl'        => $data['ReturnUrl'],
            'notifyUrl'        => $data['NoticeUrl'],
            'webSite'          => $data['URL'],
            'shipMethod'       => $orderInfo['shipping_method']['code'], // $shipMethod
            'signInfo'         => $signInfo,
            'interfaceMode'    => $interfaceMode,
            'client'           => $client,
            'ip'               => $data['IP'],
            'acceptLang'       => $acceptLang,
            'userAgent'        => $userAgent,
            'goodsInfo'        => $data['Products'],
            'remark'           => ''
        );

        // print_r($data);

        $data = http_build_query ($data, '', '&' );

		$result           = json_decode($this->_post($payment->get_submit_url(), $data), true);

		// $this->addLog(json_encode($result));

		if (!is_array($result)) {
			$result = json_decode($this->_post($payment->get_submit_url(), $data), true);
			if (!is_array($result)) {
				redirect(href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL'));
			}
		}

        $str     = $result['merNo'].$result['terNo'].$result['tradeNo'].$result['orderNo'].$result['orderCurrency'].$result['orderAmount'].$result['orderSucceed'].$MD5key;
        $mySign  = strtoupper(hash('sha256',$str));


        if($result['orderSucceed'] == '-1' || $result['orderSucceed'] == '-2'){
            //支付处理中/Payment Processing
            // echo "Transaction processing !";
            $redirect = $result['redirectURL'];
            //注意：待处理时,如果重定向地址:redirectURL 不为空时,则表示交易还未完成,必须把地址进行重定向出去,最终支付结果将跳转到returnUrl页面。
            //如果重定向地址(redirectURL)为空,此时支付结果为最终结果，将不会跳转到returnUrl页面
            //Note: When pending,if the redirectURL value is not empty, it means that the transaction has not been completed, the address must be redirected out, and the final payment result will be redirected to the returnUrl page.
            //If the redirectURL value is empty,it means payment result has been completed, and it will not be redirected to the returnUrl page.
			
            if(isset($redirect) && !empty($redirect) && $redirect != 'null'){
                header("Location: $redirect");
                exit();
            }
            redirect(
                href_link(
                    FILENAME_CHECKOUT_RESULT,
                    'OrderID=' . $result['orderNo'] .
                    'orderResult='. $result['orderResult'].
                    '&Status=1' ,
                    'SSL'
                )
            );
        }else if ($result['orderSucceed'] == 1) {
            //支付成功
            redirect(
                href_link(
                    FILENAME_CHECKOUT_RESULT,
                    'OrderID=' . $result['orderNo'] .
                    '&orderResult='. $result['orderResult'].
                    '&Status=1' ,
                    'SSL'
                )
            );
        }else{
            redirect(href_link(FILENAME_CHECKOUT_RESULT,http_build_query ($result, '', '&' )));
        }

	}

    //判断客户端是否为移动端
    function isMobile() {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ( $_SERVER ['HTTP_X_WAP_PROFILE'] )) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ( $_SERVER ['HTTP_VIA'] )) {
            // 找不到为flase,否则为true
            return stristr ( $_SERVER ['HTTP_VIA'], "wap" ) ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是电脑微信
        if (isset ( $_SERVER ['HTTP_USER_AGENT'] )) {
            $clientkeywords = array (
                'nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile',
                'MicroMessenger'
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match ( "/(" . implode ( '|', $clientkeywords ) . ")/i", strtolower ( $_SERVER ['HTTP_USER_AGENT'] ) )) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ( $_SERVER ['HTTP_ACCEPT'] )) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos ( $_SERVER ['HTTP_ACCEPT'], 'vnd.wap.wml' ) !== false) && (strpos ( $_SERVER ['HTTP_ACCEPT'], 'text/html' ) === false || (strpos ( $_SERVER ['HTTP_ACCEPT'], 'vnd.wap.wml' ) < strpos ( $_SERVER ['HTTP_ACCEPT'], 'text/html' )))) {
                return true;
            }
        }
        return false;
    }

	function result($payment)
	{
		$result = array('order_status_id' => '4', 'billing' => '', 'remarks' => '');

		if (isset($_REQUEST['OrderID'])) {

			 if (isset($_REQUEST['orderSucceed'])) {
				 $status = (int)$_REQUEST['orderSucceed'];
			 } else {
				 $status = isset($_REQUEST['Status'])?(int)$_REQUEST['Status']:0;
			 }

            $orderResult = isset($_REQUEST['orderResult'])?$_REQUEST['orderResult']:'';

            if($status == '1') {
                $result['order_status_id'] = 3;
            } else {
                $result['order_status_id'] = 4;
                $result['remarks']         = 'Result:' . $orderResult;
            }
		}else if (isset($_REQUEST['orderNo'])) {
			if (isset($_REQUEST['orderSucceed'])) {
				$status = (int)$_REQUEST['orderSucceed'];
			} else {
				$status = isset($_REQUEST['Status'])?(int)$_REQUEST['Status']:0;
			}
			
            $orderResult = isset($_REQUEST['orderResult'])?$_REQUEST['orderResult']:'';
            if($status == '1') {
                $result['order_status_id'] = 3;
            } else {
                $result['order_status_id'] = 4;
                $result['remarks']         = 'Result:' . $orderResult;
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

	function addLog($log)
    {
        $fp = fopen(DIR_FS_CATALOG . 'cache/hotpay-log-' . date('Y-m-d') . '.txt', 'a');
        flock($fp, LOCK_EX) ;
        fwrite($fp, '[' . date('Y-m-d H:i:s') . ']' . $log . "\r\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}
