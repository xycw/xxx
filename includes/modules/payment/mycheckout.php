<?php
/**
 * payment mycheckout.php
 */
class mycheckout
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
			<input type="tel" class="form-control input-text required-entry creditcard" name="mycheckout_card[number]" id="myTxtCardNumber" maxLength="16" onkeyup="myCheckCardNumber();" oninput="myCheckCardNumber();" placeholder="$txtCardNumber" />
		</div>
		<span class="brand brand-card" id="myBrandCard"></span>
	</li>
	<li class="field-date form-group">
		<select class="form-control required-entry field-date-month" name="mycheckout_card[month]">$monthStr</select>
		<select class="form-control required-entry" name="mycheckout_card[year]">$yearStr</select>
	</li>
	<li class="field-cvv form-group">
		<input type="tel" class="form-control input-text required-entry digits" name="mycheckout_card[cvv]" id="txtCardCVV" minLength="3" maxLength="4" onkeyup="this.value=this.value.replace(/\D/g,'')" oninput="this.value=this.value.replace(/\D/g,'')" placeholder="$txtCVC"/>
	</li>
	<li class="field-notes">
		<div class="title">$notesTitle</div>
		<div class="content"><p class="std">$notesContent</p></div>
		<img src="$security" />
	</li>
</ul>
<script type="text/javascript">
function myCheckCardNumber(){
	var txtCardNumber = document.getElementById('myTxtCardNumber'),
		brandCard = document.getElementById('myBrandCard');

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

		if (isset($_POST['mycheckout_card'])) {
			$mycheckout_card = $_POST['mycheckout_card'];
			if (strlen($mycheckout_card['number']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('"Card Number" is a required value. Please enter the card number.'));
			} elseif (!validate_creditcard($mycheckout_card['number'])) {
				$error = true;
				$message_stack->add($current_page, __('"Card Number" is not a valid card number.'));
			}
			if (strlen($mycheckout_card['month']) < 1
				|| strlen($mycheckout_card['year']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('"Expiry Date" is a required value. Please enter the expiry date.'));
			}
			if (strlen($mycheckout_card['cvv']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('"CVC/CVV2" is a required value. Please enter the cvc/cvv2.'));
			}
			if ($error==true) {
				//nothing
			} else {
				$_SESSION['mycheckout_card'] = array(
					'number' => $mycheckout_card['number'],
					'month'  => $mycheckout_card['month'],
					'year'   => $mycheckout_card['year'],
					'cvv'    => $mycheckout_card['cvv'],
				);
			}
		}
	}

	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;
		if ($payment->get_is_inside()==0
			&& !isset($_POST['mycheckout_card_number'])) {
			redirect(href_link('mycheckout_process', '', 'SSL'));
		}
		$data['MerchantID'] = trim($payment->get_account());
		$data['TransNo']    = trim($payment->get_mark1());
		$MD5key             = trim($payment->get_md5key());
		$data['OrderID']    = put_orderNO($orderInfo['order_id']);
		$data['Currency']   = $orderInfo['currency']['code'];
		$data['Amount']     = $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
		$data['MD5info']    = strtoupper(md5($MD5key . $data['MerchantID'] . $data['TransNo'] . $data['OrderID'] . $data['Currency'] . $data['Amount']));
		$data['Version']    = 'V5.0';
		
		$data['BName']     = $orderInfo['billing']['firstname'] . ' ' . $orderInfo['billing']['lastname'];
		$data['BEmail']    = $orderInfo['customer']['email_address'];
		$data['BAddress']  = trim($orderInfo['billing']['street_address'] . ' ' . $orderInfo['billing']['suburb']);
		$data['BCity']     = $orderInfo['billing']['city'];
		$data['BState']    = $orderInfo['billing']['region'];
		$data['BPostcode'] = $orderInfo['billing']['postcode'];
		$country_iso       = get_country_iso($orderInfo['billing']['country_id']);
		$data['BCountry']  = $country_iso['iso_code_2'];
		$data['BPhone']    = $orderInfo['billing']['telephone'];
		
		$data['DName']     = $orderInfo['shipping']['firstname'] . ' ' . $orderInfo['shipping']['lastname'];
		$data['DEmail']    = $orderInfo['customer']['email_address'];
		$data['DAddress']  = trim($orderInfo['shipping']['street_address'] . ' ' . $orderInfo['shipping']['suburb']);
		$data['DCity']     = $orderInfo['shipping']['city'];
		$data['DState']    = $orderInfo['shipping']['region'];
		$data['DPostcode'] = $orderInfo['shipping']['postcode'];
		$country_iso       = get_country_iso($orderInfo['shipping']['country_id']);
		$data['DCountry']  = $country_iso['iso_code_2'];
		$data['DPhone']    = $orderInfo['shipping']['telephone'];
		
		$data['URL']            = $_SERVER['HTTP_HOST'];
		$data['IP']             = get_ip_address();
		$data['UserAgent']      = $_SERVER['HTTP_USER_AGENT'];
		$data['AcceptLanguage'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		$data['McCookie']       = $_COOKIE['McCookie'];
		$data['csid']           = $_POST['csid'];
		
		$products = array();
		foreach ($orderProductInfo as $_product) {
			$price      = $currencies->get_price($_product['price'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
			$products[] = $_product['qty'] . '#,#' . $_product['name'] . '#,#' . $price;
		}
		$data['Products'] = implode('#;#', $products);

		$data['CardNumber'] = $payment->get_is_inside()==1?$_SESSION['mycheckout_card']['number']:$_POST['mycheckout_card_number'];
		$data['CardMonth']  = $payment->get_is_inside()==1?$_SESSION['mycheckout_card']['month']:$_POST['mycheckout_card_month'];
		$data['CardYear']   = $payment->get_is_inside()==1?$_SESSION['mycheckout_card']['year']:$_POST['mycheckout_card_year'];
		$data['CardCvv']    = $payment->get_is_inside()==1?$_SESSION['mycheckout_card']['cvv']:$_POST['mycheckout_card_cvv'];

		if ($payment->get_is_inside() == 1){
			$_SESSION['mycheckout_card'] = null;
			unset($_SESSION['mycheckout_card']);
		}
		$result = $this->_post($payment->get_submit_url(), $data);
		$result = json_decode($result, true);
		if (!is_array($result)) {
			$result = $this->_post($payment->get_submit_url(), $data);
			$result = json_decode($result, true);
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
					'OrderID=' . $result['order']['OrderID'] .
					'&Currency=' . $result['order']['Currency'] .
					'&Amount=' . $result['order']['Amount'] .
					'&Code=' . $result['order']['Code'] .
					'&Status=' . $result['order']['Status'] .
					'&MD5info=' . $result['order']['MD5info'].
					'&Billing=' . (isset($result['order']['Billing']) ? $result['order']['Billing'] : ''),
					'SSL'
				)
			);
		}
	}
	
	function result($payment)
	{
		$OrderID  = $_GET['OrderID'];
		$Currency = $_GET['Currency'];
		$Amount   = $_GET['Amount'];
		$Code     = $_GET['Code'];
		$Status   = $_GET['Status'];
		$MD5info  = $_GET['MD5info'];
		$Billing  = $_GET['Billing'];
		//MD5私钥
		$MD5key  = trim($payment->get_md5key());
		$TransNo = trim($payment->get_mark1());
		$MD5src  = $MD5key . $TransNo . $OrderID . $Currency . $Amount . $Code . $Status;
		$MD5sign = strtoupper(md5($MD5src));
		$result = array('order_status_id' => '', 'billing' => $Billing, 'remarks' => '');
		if ($MD5sign == $MD5info) {
			if($Status == '1') {
				$result['order_status_id'] = 3;
			} else {
				$result['order_status_id'] = 4;
				$result['remarks']         = 'Code:' . $Code;
			}
		} else {
			$result['order_status_id'] = 4;
			$result['remarks']         = 'Verification Failure!';
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
}
