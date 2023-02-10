<?php
/**
 * payment rxh.php
 */
class rxh
{

	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;
		if ($payment->get_is_inside()==0
			&& !isset($_POST['card_number'])) {
			redirect(href_link('rxh_process', '', 'SSL'));
		}
		$MD5key            = trim($payment->get_md5key());
		$data['MerNo']     = trim($payment->get_account());
		$data['BillNo']    = put_orderNO($orderInfo['order_id']);
		$data['Freight']   = $currencies->get_price($orderInfo['shipping_method']['fee'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
		$data['Amount']    = $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
		$data['Fee']       = $currencies->get_price($orderInfo['shipping_method']['insurance_fee'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
		$data['Currency']  = $orderInfo['currency']['code'];
		$data['ReturnURL'] = href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL');
		$data['NotifyURL'] = href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL');
		$data['Remark']    = href_link(FILENAME_INDEX);
		$md5Src            = $data['MerNo'] . $data['BillNo'] . $data['Freight'] . $data['Amount'] . $data['Fee'] . $data['Currency'] . $data['ReturnURL'] . $orderInfo['customer']['email_address'] . $MD5key;
		$data['Md5Info']   = strtoupper(md5($md5Src));
		$data['PayMode']   = 'Credit';
		$data['PayType']   = '';
		$data['Lang']      = STORE_LANGUAGE;
		$data['UserAgent'] = $_SERVER['HTTP_USER_AGENT'];
		$data['Ip']        = get_ip_address();

		$data['BillFirstName'] = $orderInfo['billing']['firstname'];
		$data['BillLastName']  = $orderInfo['billing']['lastname'];
		$data['BillEmail']     = $orderInfo['customer']['email_address'];
		$data['BillPhone']     = $orderInfo['billing']['telephone'];
		$data['BillZip']       = $orderInfo['billing']['postcode'];
		$data['BillAddress']   = trim($orderInfo['billing']['street_address'] . ' ' . $orderInfo['billing']['suburb']);
		$data['BillCity']      = $orderInfo['billing']['city'];
		$data['BillState']     = $orderInfo['billing']['region'];
		$countryIso            = get_country_iso($orderInfo['billing']['country_id']);
		$data['BillCountry']   = $countryIso['iso_code_2'];

		$data['ShipFirstName'] = $orderInfo['shipping']['firstname'];
		$data['ShipLastName']  = $orderInfo['shipping']['lastname'];
		$data['ShipEmail']     = $orderInfo['customer']['email_address'];
		$data['ShipPhone']     = $orderInfo['shipping']['telephone'];
		$data['ShipZip']       = $orderInfo['shipping']['postcode'];
		$data['ShipAddress']   = trim($orderInfo['shipping']['street_address'] . ' ' . $orderInfo['shipping']['suburb']);
		$data['ShipCity']      = $orderInfo['shipping']['city'];
		$data['ShipState']     = $orderInfo['shipping']['region'];
		$countryIso            = get_country_iso($orderInfo['shipping']['country_id']);
		$data['ShipCountry']   = $countryIso['iso_code_2'];

		$data['CardNo']      = $payment->get_is_inside() == 1 ? $_SESSION['card']['number'] : $_POST['card_number'];
		$data['ExpireMonth'] = $payment->get_is_inside() == 1 ? $_SESSION['card']['month'] : $_POST['card_month'];
		$data['ExpireYear']  = $payment->get_is_inside() == 1 ? $_SESSION['card']['year'] : $_POST['card_year'];
		$data['SecurityNum'] = $payment->get_is_inside() == 1 ? $_SESSION['card']['cvv'] : $_POST['card_cvv'];

		//字符串组合成XML
		$basexml  = '<?xml version="1.0" encoding="UTF-8" ?><Order>';
		$basexml .= "<MerNo>{$data['MerNo']}</MerNo>";
		$basexml .= "<BillNo>{$data['BillNo']}</BillNo>";
		$basexml .= "<Freight>{$data['Freight']}</Freight>";
		$basexml .= "<Amount>{$data['Amount']}</Amount>";
		$basexml .= "<Fee>{$data['Fee']}</Fee>";
		$basexml .= "<Currency>{$data['Currency']}</Currency>";
		$basexml .= "<PayMode>{$data['PayMode']}</PayMode>";
		$basexml .= "<PayType>{$data['PayType']}</PayType>";
		$basexml .= "<Lang>{$data['Lang']}</Lang>";
		$basexml .= "<UserAgent>{$data['UserAgent']}</UserAgent>";
		$basexml .= "<Ip>{$data['Ip']}</Ip>";
		$basexml .= "<ReturnURL>{$data['ReturnURL']}</ReturnURL>";
		$basexml .= "<NotifyURL>{$data['NotifyURL']}</NotifyURL>";
		$basexml .= "<Remark>{$data['Remark']}</Remark>";
		$basexml .= "<Md5Info>{$data['Md5Info']}</Md5Info>";
		$basexml .= "<GoodList>";
		foreach ($orderProductInfo as $_product) {
			$basexml .= "<Goods>";
			$basexml .= "<GoodId>{$_product['product_id']}</GoodId>";
			$basexml .= "<GoodsName>{$_product['name']}</GoodsName>";
			$basexml .= "<Qty>{$_product['qty']}</Qty>";
			$basexml .= "<Price>" . $currencies->get_price($_product['price'], $orderInfo['currency']['code'], $orderInfo['currency']['value']) . "</Price>";
			$basexml .= "</Goods>";
		}
		$basexml .= "</GoodList>";
		$basexml .= "<BillFirstName>{$data['BillFirstName']}</BillFirstName>";
		$basexml .= "<BillLastName>{$data['BillLastName']}</BillLastName>";
		$basexml .= "<BillEmail>{$data['BillEmail']}</BillEmail>";
		$basexml .= "<BillPhone>{$data['BillPhone']}</BillPhone>";
		$basexml .= "<BillAddress>{$data['BillAddress']}</BillAddress>";
		$basexml .= "<BillCity>{$data['BillCity']}</BillCity>";
		$basexml .= "<BillState>{$data['BillState']}</BillState>";
		$basexml .= "<BillCountry>{$data['BillCountry']}</BillCountry>";
		$basexml .= "<BillZip>{$data['BillZip']}</BillZip>";
		$basexml .= "<ShipFirstName>{$data['ShipFirstName']}</ShipFirstName>";
		$basexml .= "<ShipLastName>{$data['ShipLastName']}</ShipLastName>";
		$basexml .= "<ShipEmail>{$data['ShipEmail']}</ShipEmail>";
		$basexml .= "<ShipPhone>{$data['ShipPhone']}</ShipPhone>";
		$basexml .= "<ShipAddress>{$data['ShipAddress']}</ShipAddress>";
		$basexml .= "<ShipCountry>{$data['ShipCountry']}</ShipCountry>";
		$basexml .= "<ShipState>{$data['ShipState']}</ShipState>";
		$basexml .= "<ShipCity>{$data['ShipCity']}</ShipCity>";
		$basexml .= "<ShipZip>{$data['ShipZip']}</ShipZip>";
		$basexml .= "<CardNo>{$data['CardNo']}</CardNo>";
		$basexml .= "<SecurityNum>{$data['SecurityNum']}</SecurityNum>";
		$basexml .= "<ExpireYear>{$data['ExpireYear']}</ExpireYear>";
		$basexml .= "<ExpireMonth>{$data['ExpireMonth']}</ExpireMonth>";
		$basexml .= '</Order>';

		$tradeInfo = base64_encode(urlencode($basexml));
		$resultArr = array();
		$temp      = $this->_post($payment->get_submit_url(), 'tradeInfo=' . $tradeInfo);
		$tempArr   = explode(';', $temp);
		foreach($tempArr as $key => $val){
			$resultArr[$key] = explode('=', $val);
		}

		if (empty($resultArr)) {
			redirect(href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL'));
		} else {
			redirect(
				href_link(
					FILENAME_CHECKOUT_RESULT,
					'tradeNo=' . $resultArr[0][1] .
					'&billNo=' . $resultArr[1][1] .
					'&currency=' . $resultArr[6][1] .
					'&amount=' . $resultArr[7][1] .
					'&succeed=' . $resultArr[2][1] .
					'&result=' . $resultArr[3][1] .
					'&md5Info=' . $resultArr[4][1] .
					'&remark=' . $resultArr[5][1],
					'SSL'
				)
			);
		}
	}
	
	function result($payment)
	{
		$tradeNo  = $_GET['tradeNo'];
		$billNo   = $_GET['billNo'];
		$currency = $_GET['currency'];
		$amount   = $_GET['amount'];
		$succeed  = $_GET['succeed'];
		$result   = $_GET['result'];
		$md5Info  = strtoupper($_GET['md5Info']);
		$remark   = $_GET['remark'];

		$md5Key  = trim($payment->get_md5key());
		$md5src  = $billNo . $currency . $amount . $succeed . $md5Key;
		$md5sign = strtoupper(md5($md5src));
		if ($md5Info == $md5sign) {
			if ($succeed == '0') {
				$order_status = 3;
			} else {
				$order_status = 4;
			}
		} else {
			$order_status = 4;
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
		curl_setopt($ch, CURLOPT_REFERER, href_link(FILENAME_INDEX));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
    }
}
