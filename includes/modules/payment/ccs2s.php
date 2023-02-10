<?php

class ccs2s
{
    function before()
    {
        $monthStr = '<option value="">' . __('Month') . '</option>';
        for ($i = 1; $i <= 12; $i++) {
            $monthStr .= '<option value="' . str_pad($i, 2, '0', STR_PAD_LEFT) . '">' . str_pad($i, 2, '0', STR_PAD_LEFT) . '</option>';
        }

        $year    = date('Y');
        $yearStr = '<option value="">' . __('Year') . '</option>';
        for ($i = 0; $i < 25; $i++) {
            $yearStr .= '<option value="' . substr($year + $i, -2, 2) . '">' . substr($year + $i, -2, 2) . '</option>';
        }

        $txtCardNumber = __('Card Number');
        $txtCVC        = __('CVV');
        $v             = DIR_WS_TEMPLATE_IMAGES . 'v.png';
        $m             = DIR_WS_TEMPLATE_IMAGES . 'm.png';
        $j             = DIR_WS_TEMPLATE_IMAGES . 'j.png';
        $a             = DIR_WS_TEMPLATE_IMAGES . 'a.png';
        $vmj           = DIR_WS_TEMPLATE_IMAGES . 'vmj.png';
        $security      = DIR_WS_TEMPLATE_IMAGES . 'security.jpg';
        $notesTitle    = __('Notes');
        $notesContent  = __('You are now connected to a secure payment site with certificate issued by VeriSign, Your payment details will be securely transmitted to the Bank for transaction authorization in full accordance with PCI standards.');

        $html = <<<HTML
<ul class="inside-payform">
    <li class="field-card form-group">
        <div class="input-box">
            <input type="tel" class="form-control input-text required-entry creditcard" name="ccscheckout_card[number]" id="ccTxtCardNumber" maxLength="16" onkeyup="ccsCheckCardNumber();" oninput="ccsCheckCardNumber();" placeholder="$txtCardNumber" />
        </div>
        <span class="brand brand-card" id="ccBrandCard"></span>
    </li>
    <li class="field-date form-group">
        <select class="form-control required-entry field-date-month" name="ccscheckout_card[month]">$monthStr</select>
        <select class="form-control required-entry" name="ccscheckout_card[year]">$yearStr</select>
    </li>
    <li class="field-cvv form-group">
        <input type="tel" class="form-control input-text required-entry digits" name="ccscheckout_card[cvv]" id="txtCardCVV" minLength="3" maxLength="4" onkeyup="this.value=this.value.replace(/\D/g,'')" oninput="this.value=this.value.replace(/\D/g,'')" placeholder="$txtCVC"/>
    </li>
    <li class="field-notes">
        <div class="title">$notesTitle</div>
        <div class="content"><p class="std">$notesContent</p></div>
        <img src="$security" />
    </li>
</ul>
<script type="text/javascript">
function ccsCheckCardNumber(){
    var txtCardNumber = document.getElementById('ccTxtCardNumber'),
        brandCard = document.getElementById('ccBrandCard');

    txtCardNumber.value = txtCardNumber.value.replace(/\D/g, '');
    if ((/^[4]{1}/).test(txtCardNumber.value)) {
        brandCard.style.backgroundImage = 'url("$v")';
    } else if ((/^[5]{1}[1-5]{1}/).test(txtCardNumber.value)) {
        brandCard.style.backgroundImage = 'url("$m")';
    } else if ((/^[3]{1}[5]{1}/).test(txtCardNumber.value)) {
        brandCard.style.backgroundImage = 'url("$j")';
    } else if ((/^[3]{1}[47]{1}/).test(txtCardNumber.value)) {
        brandCard.style.backgroundImage = 'url("$a")';
    } else {
        brandCard.style.backgroundImage = 'url("$vmj")';
    }
}
</script>
HTML;

        return $html;
    }

    function after()
    {
        global $message_stack, $error, $current_page;

        if (isset($_POST['ccscheckout_card'])) {
            $ccscheckout_card = $_POST['ccscheckout_card'];
            if (strlen($ccscheckout_card['number']) < 1) {
                $error = true;
                $message_stack->add($current_page, __('"Card Number" is a required value. Please enter the card number.'));
            } elseif (!validate_creditcard($ccscheckout_card['number'])) {
                $error = true;
                $message_stack->add($current_page, __('"Card Number" is not a valid card number.'));
            }
            if (strlen($ccscheckout_card['month']) < 1
                || strlen($ccscheckout_card['year']) < 1) {
                $error = true;
                $message_stack->add($current_page, __('"Expiry Date" is a required value. Please enter the expiry date.'));
            }
            if (strlen($ccscheckout_card['cvv']) < 1) {
                $error = true;
                $message_stack->add($current_page, __('"CVC/CVV2" is a required value. Please enter the cvc/cvv2.'));
            }
            if ($error == true) {
                //nothing
            } else {
                $_SESSION['ccscheckout_card'] = array(
                    'number' => $ccscheckout_card['number'],
                    'month'  => $ccscheckout_card['month'],
                    'year'   => $ccscheckout_card['year'],
                    'cvv'    => $ccscheckout_card['cvv'],
                );
            }
        }
    }

    public function process($payment)
    {
        /**
         * orderInfo 订单信息
         * $orderProductInfo 产品信息
         * $currencies 币种信息
         * $db payment_method表的信息
         */
        global $orderInfo, $orderProductInfo, $currencies, $db;


        if ($payment->get_is_inside() == 0
            && !isset($_POST['ccscheckout_card_number'])) {
            redirect(href_link('ccs2s_process', '', 'SSL'));
        }

        foreach ($orderProductInfo as $_product) {
            $price = $currencies->get_price($_product['price'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
            $url   = href_link(FILENAME_PRODUCT, 'pID=' . $_product['product_id']);
            // 商品信息
            $productInfo[] = array(
                'qty'       => $_product['qty'],
                'name'      => $_product['name'],
                'price'     => $price,
                'url'       => $url,
                'attribute' => '',
                'image'     => ''
            );

            $goods[] = array(
                'sku'        => $_product['sku'],
                'name'       => $_product['name'],
                'price'      => $price,
                'qty'        => $_product['qty'],
                'url'        => $url,
                'attribute'  => '',
                'is_gift'    => '',
                'is_virtual' => ''
            );
        }

        $productInfo  = json_encode($productInfo);
        $lastModified = '';

        if (isset($_SESSION['customer_id'])) {
            // 查看客户的信息
            $sql = "SELECT `date_added`, `last_modified`
					FROM   " . TABLE_CUSTOMER . "
					WHERE  status = 1
					AND    customer_id = :customerID";
            $sql = $db->bindVars($sql, ':customerID', $_SESSION['customer_id'], 'integer');

            $result = $db->Execute($sql);

            if (!empty($result)) {
                $cust['registration_time'] = $result->fields['date_added'];
                $lastModified              = $result->fields['last_modified'];
            }
            // 查看订单
            $sql    = "SELECT `date_added`
					FROM   " . TABLE_ORDERS . "
					WHERE  customer_id = :customerID
					ORDER BY order_id DESC";
            $sql    = $db->bindVars($sql, ':customerID', $_SESSION['customer_id'], 'integer');
            $result = $db->Execute($sql);

            if (!empty($result)) {
                $cust['last_shopping_time'] = $result->fields['date_added'];
            }
        }

        // 获取国家信息
        $billCountryIso = get_country_iso($orderInfo['billing']['country_id']);
        $shipCountryIso = get_country_iso($orderInfo['shipping']['country_id']);

        // 风控信息
        $riskInfo = array(
            'adjustment_factor' => '',
            'retry_num'         => '',
            'trade'             => array(
                'code' => '',
                'item' => ''
            ),
            'device'            => array(
                'finger_print_id' => '',
                'user_agent'      => $_SERVER['HTTP_USER_AGENT'],
                'accept_lang'     => $_SERVER['HTTP_ACCEPT_LANGUAGE']
            ),
            'cust'              => array(
                'register_user_id'   => isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : '',
                //                'register_user_id' => '',
                'ip'                 => get_ip_address(),
                'email'              => $orderInfo['customer']['email_address'],
                'phone'              => $orderInfo['billing']['telephone'],
                'registration_time'  => '',
                'level'              => '',
                'last_shopping_time' => ''
            ),
            'buried'            => array(
                array(
                    'code' => '',
                    'item' => ''
                )
            ),
            'goods'             => $goods,
            'ship'              => array(
                'first_name'               => $orderInfo['shipping']['firstname'],
                'last_name'                => $orderInfo['shipping']['lastname'],
                'email'                    => $orderInfo['customer']['email_address'],
                'phone'                    => $orderInfo['shipping']['telephone'],
                'address'                  => $orderInfo['shipping']['street_address'],
                'city'                     => $orderInfo['shipping']['city'],
                'state'                    => $orderInfo['shipping']['region'],
                'postcode'                 => $orderInfo['shipping']['postcode'],
                'country'                  => $shipCountryIso['iso_code_2'],
                'address_last_modify_time' => $lastModified,
                'phone_last_modify_time'   => $lastModified
            ),
            'bill'              => array(
                'first_name' => $orderInfo['billing']['firstname'],
                'last_name'  => $orderInfo['billing']['lastname'],
                'email'      => $orderInfo['customer']['email_address'],
                'phone'      => $orderInfo['billing']['telephone'],
                'address'    => $orderInfo['billing']['street_address'],
                'city'       => $orderInfo['billing']['city'],
                'state'      => $orderInfo['billing']['region'],
                'postcode'   => $orderInfo['billing']['postcode'],
                'country'    => $billCountryIso['iso_code_2']
            )
        );

        $riskInfo = json_encode($riskInfo);

        // 支付方式信息
        $payMethodInfo['card_no']          = $payment->get_is_inside() == 1 ? $_SESSION['ccscheckout_card']['number'] : $_POST['ccscheckout_card_number'];
        $payMethodInfo['expiration_month'] = $payment->get_is_inside() == 1 ? $_SESSION['ccscheckout_card']['month'] : $_POST['ccscheckout_card_month'];
        $payMethodInfo['expiration_year']  = $payment->get_is_inside() == 1 ? $_SESSION['ccscheckout_card']['year'] : $_POST['ccscheckout_card_year'];
        $payMethodInfo['cvv']              = $payment->get_is_inside() == 1 ? $_SESSION['ccscheckout_card']['cvv'] : $_POST['ccscheckout_card_cvv'];

        $payMethodInfo['first_name']   = $orderInfo['billing']['firstname'];
        $payMethodInfo['last_name']    = $orderInfo['billing']['lastname'];
        $payMethodInfo['billing_desc'] = '';  // 动态卡账单 默认置空

        $payMethodInfo = json_encode($payMethodInfo);
		
		$tempUrl = trim($payment->get_mark2());
		if (empty($tempUrl)) {
			$tempUrl = $_SERVER['HTTP_HOST'];
		}

        $data = array(
            'version'         => '3.0',
            'merchant_id'     => trim($payment->get_account()),
            'business_id'     => trim($payment->get_mark1()),
            'access_type'     => 's2s',
            'order_number'    => put_orderNO($orderInfo['order_id']),
            'trans_type'      => 'authorization',  //sale
            'trans_channel'   => 'cc',
            'pay_method'      => 'normal',
            'trans_timeout'   => '30',
            'url'             => $tempUrl,
            'currency'        => $orderInfo['currency']['code'],
            'amount'          => $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']),
            'settle_currency' => 'USD',
            'product_info'    => $productInfo,
            'pay_method_info' => $payMethodInfo,
            'country'         => $billCountryIso['iso_code_2'],
            'language'        => 'zh',
            'terminal_type'   => '10',
            'risk_info'       => $riskInfo,
            'skin_code'       => '',
            'logo'            => '',
            'dcc'             => '',
            'notify_url'      => href_link('ccs2s_notify', '', 'SSL'),
			'redirect_url'    => href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL'),
//            'redirect_url'    => $payment->get_return_url(),
            'req_reserved'    => '',
            'reserved'        => '',
            'sign_type'       => 'MD5',
        );
		
        $md5key       = trim($payment->get_md5key());
        $md5Str       = $data['merchant_id'] . $data['business_id'] . $data['order_number'] . $data['trans_type'] . $data['trans_channel'] . $data['pay_method'] . $data['url'] . $data['currency'] . $data['amount'] . $data['settle_currency'] . $md5key;
        $data['sign'] = md5($md5Str);

        // curl请求
        $result = $this->post($payment->get_submit_url(), $data);
        $result = json_decode($result, true);

        if (isset($result['error']) && !empty($result['error'])) {
            redirect(href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL'));
        } else {
            if ($result['status'] == '2') {
                $result['pay_method_resp'] = json_decode($result['pay_method_resp'], true);

                if ($result['pay_method_resp']['is_redirect'] == '1') {
                    echo $result['pay_method_resp']['redirect_param'];
                    die;
                }
            }
			
            // 拼接form表单字符串
            $paymentForm = '<form method="post" action="' . href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL') . '" id="checkout" name="checkout" target="_top">' . "\n";

            foreach ($result as $key => $val) {
                $paymentForm .= '<input type="hidden" value="' . urlencode($val) . '" name="' . $key . '">' . "\n";
            }

            $paymentForm .= '</form>' . "\n";
            $paymentForm .= '<script type="text/javascript">' . "\n";
            $paymentForm .= '$(function() {' . "\n";
            $paymentForm .= 'document.checkout.submit();' . "\n";
            $paymentForm .= '});' . "\n";
            $paymentForm .= '</script>' . "\n";

            echo $paymentForm;
			
            die;
        }
    }

    function result($payment)
    {
        $md5key   = trim($payment->get_md5key());
        $_REQUEST = array_map('urldecode', $_REQUEST);
        $result   = array('order_status_id' => '', 'billing' => '-', 'remarks' => '');

        if ($_REQUEST['resp_code'] == '0000') {
            // capture 交易请款
            $captureData = array(
                'version'           => $_REQUEST['version'],
                'merchant_id'       => $_REQUEST['merchant_id'],
                'business_id'       => $_REQUEST['business_id'],
                'access_type'       => $_REQUEST['access_type'],
                'trans_channel'     => '',
                'original_order_id' => $_REQUEST['order_id'],
                'trans_type'        => 'capture',
                'amount'            => '',
                'notify_url'        => '',
                'req_reserved'      => '',
                'reserved'          => '',
                'sign_type'         => $_REQUEST['sign_type'],
            );

            $md5Capture          = $captureData['version'] . $captureData['merchant_id'] . $captureData['business_id'] . $captureData['access_type'] . $captureData['trans_channel'] . $captureData['original_order_id'] . $captureData['trans_type'] . $captureData['amount'] . $captureData['notify_url'] . $captureData['req_reserved'] . $captureData['reserved'] . $captureData['sign_type'] . $md5key;
            $captureData['sign'] = md5($md5Capture);
            $captureResult       = $this->post($payment->get_submit_url(), $captureData);
            $captureResult       = json_decode($captureResult, true);

            $sign = $captureResult['sign'];
            unset($captureResult['sign']);

            $signStr = implode('', $captureResult) . $md5key;

            if (strtoupper(md5($signStr)) == strtoupper($sign)) {
                $result['order_status_id'] = 3;
            } else {
                $result['order_status_id'] = 4;
                $result['remarks']         = 'risk:' . $_REQUEST['risk_result'];
            }
        } else {
            $result['order_status_id'] = 4;
            $result['remarks']         = 'risk:' . $_REQUEST['risk_result'];
        }

        return $result;
    }

    /**
     * @param $url 请求路径
     * @param array $data 请求参数
     * @return bool|string
     */
    public function post($url, array $data)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        if ($errno > 0) {
            $info          = curl_getinfo($ch);
            $info['errno'] = $errno;
        }
        curl_close($ch);

        return $response;
    }
}

