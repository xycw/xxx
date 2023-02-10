<?php
/**
 * payment flashpay.php
 */
class flashpay
{
	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;
		$MerNo = trim($payment->get_account());
		$MD5key = trim($payment->get_md5key());
		$BillNo = put_orderNO($orderInfo['order_id']);
		$CurrencyCode = $orderInfo['currency']['code'];
		$Amount = $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
		$Freight = $currencies->get_price($orderInfo['shipping_method']['fee'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
		
		$BFirstName = $orderInfo['billing']['firstname'];
		$BLastName = $orderInfo['billing']['lastname'];
		$Email = $orderInfo['customer']['email_address'];
		$Phone = $orderInfo['billing']['telephone'];
		$BillZip = $orderInfo['billing']['postcode'];
		$BillAddress = trim($orderInfo['billing']['street_address'] . ' ' . $orderInfo['billing']['suburb']);
		$BillCity = $orderInfo['billing']['city'];
		$BillState = $orderInfo['billing']['region'];
		$BillCountry_iso = get_country_iso($orderInfo['billing']['country_id']);
		$BillCountry = $BillCountry_iso['iso_code_2'];
		
		$SFirstName = $orderInfo['shipping']['firstname'];
		$SLastName = $orderInfo['shipping']['lastname'];
		$SEmail = $orderInfo['customer']['email_address'];
		$SPhone = $orderInfo['shipping']['telephone'];
		$ShipZip = $orderInfo['shipping']['postcode'];
		$ShipAddress = trim($orderInfo['shipping']['street_address'] . ' ' . $orderInfo['shipping']['suburb']);
		$ShipCity = $orderInfo['shipping']['city'];
		$ShipState = $orderInfo['shipping']['region'];
		$ShipCountry_iso = get_country_iso($orderInfo['shipping']['country_id']);
		$ShipCountry = $ShipCountry_iso['iso_code_2'];
		
		$Currency = '15';
		$Language = '2';
		$LangCode = STORE_LANGUAGE;
		$ReturnURL = href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL');
		$Remark = href_link(FILENAME_INDEX);
		 
		$md5src = $MerNo.$BillNo.$Freight.$Amount.$CurrencyCode.$ReturnURL.$Email.$MD5key;	
		$MD5info = strtoupper(md5($md5src));
		
		$baseStr = '<?xml version="1.0" encoding="UTF-8" ?>';
		$baseStr .= "<Order>";
		$baseStr .= "<MerNo>".$MerNo."</MerNo>";
		$baseStr .= "<BillNo>".$BillNo."</BillNo>";
		$baseStr .= "<GoodsList>";
		foreach ($orderProductInfo as $_product) {
			$baseStr .= "<Goods>";
			$baseStr .= "<GoodsName>".$_product['name']."</GoodsName>";
			$baseStr .= "<Qty>".$_product['qty']."</Qty>";
			$baseStr .= "<Price>".$currencies->get_price($_product['price'], $orderInfo['currency']['code'], $orderInfo['currency']['value'])."</Price>";
			$baseStr .= "</Goods>";
		}
		$baseStr .= "</GoodsList>";
		$baseStr .= "<Amount>".$Amount."</Amount>";
		$baseStr .= "<Freight>".$Freight."</Freight>";
		$baseStr .= "<CurrencyCode>".$CurrencyCode."</CurrencyCode>";
		$baseStr .= "<BFirstName>".$BFirstName."</BFirstName>";
		$baseStr .= "<BLastName>".$BLastName."</BLastName>";
		$baseStr .= "<Phone>".$Phone."</Phone>";
		$baseStr .= "<Email>".$Email."</Email>";
		$baseStr .= "<BillAddress>".$BillAddress."</BillAddress>";
		$baseStr .= "<BillCity>".$BillCity."</BillCity>";
		$baseStr .= "<BillState>".$BillState."</BillState>";
		$baseStr .= "<BillCountry>".$BillCountry."</BillCountry>";
		$baseStr .= "<BillZip>".$BillZip."</BillZip>";
		$baseStr .= "<SFirstName>".$SFirstName."</SFirstName>";
		$baseStr .= "<SLastName>".$SLastName."</SLastName>";
		$baseStr .= "<ShipAddress>".$ShipAddress."</ShipAddress>";
		$baseStr .= "<ShipCity>".$ShipCity."</ShipCity>";
		$baseStr .= "<ShipState>".$ShipState."</ShipState>";
		$baseStr .= "<ShipCountry>".$ShipCountry."</ShipCountry>";
		$baseStr .= "<ShipZip>".$ShipZip."</ShipZip>";
		$baseStr .= "<Language>".$Language."</Language>";
		$baseStr .= "<LangCode>".$LangCode."</LangCode>";
		$baseStr .= "<Currency>".$Currency."</Currency>";
		$baseStr .= "<ReturnURL>".$ReturnURL."</ReturnURL>";
		$baseStr .= "<Remark>".$Remark."</Remark>";
		$baseStr .= "<MD5info>".$MD5info."</MD5info>";
		$baseStr .= "</Order>";
		$TradeInfo = base64_encode(urlencode($this->string_replace($baseStr)));
		
		$payment_form = '<form method="post" action="' . $payment->get_submit_url() . '" id="checkout" name="checkout">' . "\n";
		$payment_form .= '<input type="hidden" value="' . $TradeInfo . '" name="TradeInfo">' . "\n";
		$payment_form .= '</form>' . "\n";
		if ($payment->get_is_inside()==1) {
			$payment_form .= '<iframe width="100%" height="1150" scrolling="no" style="border:none;margin:0 auto;overflow:hidden;" id="ifrm_checkout" name="ifrm_checkout"></iframe>' . "\n";
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
		$MD5key = trim($payment->get_md5key());
		$BillNo = $_POST["BillNo"];
		$Currency = $_POST["Currency"];
		$Amount = $_POST["Amount"];
		$CurrencyCode = $_POST["CurrencyCode"];
		$Succeed = $_POST["Succeed"];
		$Result = $_POST["Result"];
		$MD5info = $_POST["MD5info"];
		$Remark = $_POST["Remark"];
		$md5src = $BillNo.$Currency.$Amount.$Succeed.$MD5key;
		$md5sign = strtoupper(md5($md5src));
		if ($MD5info == $md5sign && $Succeed == "1") {
			//支付成功
			$order_status = 3;
		} elseif ($MD5info == $md5sign && $Succeed == "2") {
			//支付待处理
			$order_status = 2;
		} elseif ($MD5info == $md5sign && $Succeed == "0") { 
			//支付失败
			$order_status = 4;
		} else {
			//签名验证失败
			$order_status = 4;
		}
		
		return $order_status;
	}
	
	function string_replace($string_before)
	{
		$string_after = str_replace("\n"," ",$string_before);
		$string_after = str_replace("\r"," ",$string_after);
		$string_after = str_replace("\r\n"," ",$string_after);
		$string_after = str_replace("'","&#39 ",$string_after);
		$string_after = str_replace('"',"&#34 ",$string_after);
		$string_after = str_replace("(","&#40 ",$string_after);
		$string_after = str_replace(")","&#41 ",$string_after);
		return $string_after;
   }
}
