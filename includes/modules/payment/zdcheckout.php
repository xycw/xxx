<?php
/**
 * payment zdcheckout.php
 */
class zdcheckout
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
			$yearStr .= '<option value="' . substr($year + $i, -2, 2) . '">' . substr($year + $i, -2, 2) . '</option>';
		}

		$txtCardNumber     = __('Card Number');
		$txtCVC            = __('CVV');
		$v                 = DIR_WS_TEMPLATE_IMAGES . 'v.png';
		$m                 = DIR_WS_TEMPLATE_IMAGES . 'm.png';
		$j                 = DIR_WS_TEMPLATE_IMAGES . 'j.png';
		$a                 = DIR_WS_TEMPLATE_IMAGES . 'a.png';
		$vmj               = DIR_WS_TEMPLATE_IMAGES . 'vmj.png';
		$security          = DIR_WS_TEMPLATE_IMAGES . 'security.jpg';
		$notesTitle        = __('Notes');
		$notesContent      = __('You are now connected to a secure payment site with certificate issued by VeriSign, Your payment details will be securely transmitted to the Bank for transaction authorization in full accordance with PCI standards.');

		$html = <<<HTML
<ul class="inside-payform">
	<li class="field-card form-group">
		<div class="input-box">
			<input type="tel" class="form-control input-text required-entry creditcard" name="zdcheckout_card[number]" id="zdTxtCardNumber" maxLength="16" onkeyup="zdCheckCardNumber();" oninput="zdCheckCardNumber();" placeholder="$txtCardNumber" />
		</div>
		<span class="brand brand-card" id="zdBrandCard"></span>
	</li>
	<li class="field-date form-group">
		<select class="form-control required-entry field-date-month" name="zdcheckout_card[month]">$monthStr</select>
		<select class="form-control required-entry" name="zdcheckout_card[year]">$yearStr</select>
	</li>
	<li class="field-cvv form-group">
		<input type="tel" class="form-control input-text required-entry digits" name="zdcheckout_card[cvv]" id="txtCardCVV" minLength="3" maxLength="4" onkeyup="this.value=this.value.replace(/\D/g,'')" oninput="this.value=this.value.replace(/\D/g,'')" placeholder="$txtCVC"/>
	</li>
	<li class="field-notes">
		<div class="title">$notesTitle</div>
		<div class="content"><p class="std">$notesContent</p></div>
		<img src="$security" />
	</li>
</ul>
<script type="text/javascript">
function zdCheckCardNumber(){
	var txtCardNumber = document.getElementById('zdTxtCardNumber'),
		brandCard = document.getElementById('zdBrandCard');

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
<script type="text/javascript" src="https://risk.hdkhdkrisk.com/sslcsid.js"></script>
HTML;

		return $html;
	}

	function after()
	{
		global $message_stack, $error, $current_page;

		if (isset($_POST['zdcheckout_card'])) {
			$zdcheckout_card = $_POST['zdcheckout_card'];
			if (strlen($zdcheckout_card['number']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('"Card Number" is a required value. Please enter the card number.'));
			} elseif (!validate_creditcard($zdcheckout_card['number'])) {
				$error = true;
				$message_stack->add($current_page, __('"Card Number" is not a valid card number.'));
			}
			if (strlen($zdcheckout_card['month']) < 1
				|| strlen($zdcheckout_card['year']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('"Expiry Date" is a required value. Please enter the expiry date.'));
			}
			if (strlen($zdcheckout_card['cvv']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('"CVC/CVV2" is a required value. Please enter the cvc/cvv2.'));
			}
			if ($error==true) {
				//nothing
			} else {
				$_SESSION['zdcheckout_card'] = array(
					'number' => $zdcheckout_card['number'],
					'month'  => $zdcheckout_card['month'],
					'year'   => $zdcheckout_card['year'],
					'cvv'    => $zdcheckout_card['cvv'],
				);
			}
		}
	}

	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;
		if ($payment->get_is_inside() == 0
			&& !isset($_POST['zdcheckout_card_number'])) {
			redirect(href_link('zdcheckout_process', '', 'SSL'));
		}

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
		
		$data['entry_url']             = $_SERVER['HTTP_HOST'];
		$data['entry_ip']              = get_ip_address();
		$data['entry_user_agent']      = $_SERVER['HTTP_USER_AGENT'];
		$data['entry_accept_language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		$data['McCookie']              = $_COOKIE['McCookie'];
		$data['csid']                  = $_POST['csid'];
		
		$products = array();
		foreach ($orderProductInfo as $_product) {
			$price      = $currencies->get_price($_product['price'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
			$products[] = $_product['qty'] . '#,#' . $_product['name'] . '#,#' . $price;
		}
		$data['Products'] = implode('#;#', $products);

		$data['CardNumber'] = $payment->get_is_inside() == 1 ? $_SESSION['zdcheckout_card']['number'] : $_POST['zdcheckout_card_number'];
		$data['CardMonth']  = $payment->get_is_inside() == 1 ? $_SESSION['zdcheckout_card']['month'] : $_POST['zdcheckout_card_month'];
		$data['CardYear']   = $payment->get_is_inside() == 1 ? $_SESSION['zdcheckout_card']['year'] : $_POST['zdcheckout_card_year'];
		$data['CardCvv']    = $payment->get_is_inside() == 1 ? $_SESSION['zdcheckout_card']['cvv'] : $_POST['zdcheckout_card_cvv'];

		if ($payment->get_is_inside() == 1){
			$_SESSION['zdcheckout_card'] = null;
			unset($_SESSION['zdcheckout_card']);
		}

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
			redirect(
				href_link(
					FILENAME_CHECKOUT_RESULT,
					'OrderID=' . $result['order']['entry_order_id'] .
					'&Currency=' . $result['order']['entry_currency'] .
					'&Amount=' . $result['order']['entry_amount'] .
					'&Code=' . $result['order']['entry_code'] .
					'&Status=' . $result['order']['entry_status'] .
					'&MD5info=' . $result['order']['entry_secret'] .
					'&Billing=' . (isset($result['order']['entry_billing']) ? $result['order']['entry_billing'] : ''),
					'SSL'
				)
			);
		}
	}
	
	function result($payment)
	{
		$entryOrderID  = $_GET['OrderID'];
		$entryCurrency = $_GET['Currency'];
		$entryAmount   = $_GET['Amount'];
		$entryCode     = $_GET['Code'];
		$entryStatus   = $_GET['Status'];
		$entrySecret   = $_GET['MD5info'];
		$entryBilling  = $_GET['Billing'];

		//MD5私钥
		$entryMD5key    = trim($payment->get_md5key());
		$entryMD5src    = base64_decode($entryMD5key) . $entryOrderID . $entryCurrency . $entryAmount . $entryCode . $entryStatus;
		$secretValidate = strtoupper(hash('sha256', $entryMD5src));
		$result = array('order_status_id' => '', 'billing' => $entryBilling, 'remarks' => '');
		if ($secretValidate == $entrySecret) {
			if($entryStatus == '1') {
				$result['order_status_id'] = 3;
			} else {
				$result['order_status_id'] = 4;
				$result['remarks']         = 'Code:' . $entryCode;
			}
		} else {
			$result['order_status_id'] = 4;
			$result['remarks']         = 'Verification Failure!';
		}
		
		return $result;
	}
	
	function _post($url, $data)
    {
	    $opts = array (
		    'http' => array (
			    'method'  => 'POST',
			    'header'  => 'Content-type: application/x-www-form-urlencoded',
			    'timeout' => 120,
			    'content' => http_build_query($data, '', '&')
		    )
	    );
	    return file_get_contents($url, false, stream_context_create($opts));
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
