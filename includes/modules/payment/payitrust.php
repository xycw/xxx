<?php
/**
 * payment payitrust.php
 */
class payitrust
{
	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;
		$version = '1.0.0';
		$encoding = 'utf-8';
		$merchantid = trim($payment->get_account());
		$hashkey = trim($payment->get_md5key());
		$datestr = date('YmdHis');
		$orderid = put_orderNO($orderInfo['order_id']);
		$currency = $orderInfo['currency']['code'];
		$orderamount = $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);
		$strServerUrl  = href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL');
		$strBrowserurl = href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL');
		$strAccessurl  = href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL');
		$strlang = 'en-us';
		$shipfee = $currencies->get_price($orderInfo['shipping_method']['fee'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);

		$billaddress = trim($orderInfo['billing']['street_address'] . ' ' . $orderInfo['billing']['suburb']);
		$billcountry_iso = get_country_iso($orderInfo['billing']['country_id']);
		$billcountry = $billcountry_iso['iso_code_2'];
		$billstate   = $orderInfo['billing']['region'];
		$billcity    = $orderInfo['billing']['city'];
		$billemail   = $orderInfo['customer']['email_address'];
		$billphone   = $orderInfo['billing']['telephone'];
		$billpost    = $orderInfo['billing']['postcode'];

		$deliveryname    = $orderInfo['shipping']['firstname'] . ' ' . $orderInfo['shipping']['lastname'];
		$deliveryaddress = trim($orderInfo['shipping']['street_address'] . ' ' . $orderInfo['shipping']['suburb']);
		$deliverycountry_iso = get_country_iso($orderInfo['shipping']['country_id']);
		$deliverycountry = $deliverycountry_iso['iso_code_2'];
		$deliverystate   = $orderInfo['shipping']['region'];
		$deliverycity    = $orderInfo['shipping']['city'];
		$deliveryemail   = $orderInfo['customer']['email_address'];
		$deliveryphone   = $orderInfo['shipping']['telephone'];
		$deliverypost    = $orderInfo['shipping']['postcode'];

		$strProduct  = '';
		$strProducts = '';

		$i = 1;
		foreach ($orderProductInfo as $_product) {
			if ($i > 10) {
				break;
			}
			$pname = $_product['name'];
			$psku  = $_product['product_id'];
			$pqty  = $_product['qty'];
			$ppri  = $currencies->get_price($_product['price'], $orderInfo['currency']['code'], $orderInfo['currency']['value']);

			$strProducts = $strProducts . $pname . $psku . $pqty . $ppri;
			$strProduct  = $strProduct . '<input type="hidden" name="productname'.$i.'" value="'.$this->utf8_htmldecode($pname).'">' . "\n";
			$strProduct  = $strProduct . '<input type="hidden" name="productsn'.$i.'" value="'.$psku.'">' . "\n";
			$strProduct  = $strProduct . '<input type="hidden" name="quantity'.$i.'" value="'.$pqty.'">' . "\n";
			$strProduct  = $strProduct . '<input type="hidden" name="unit'.$i.'" value="'.$ppri.'">' . "\n";
			$i++;
		}

		$remark1 = href_link(FILENAME_INDEX);
		$remark2 = '';
		$remark3 = '';

		$value = $version . $encoding . $strlang . $merchantid .
			$orderid . $datestr . $currency . $orderamount . 'ic' . $strServerUrl . 
			$strBrowserurl . $strAccessurl . $remark1 . $remark2 . $remark3 . $strProducts .
			$shipfee . $billaddress . $billcountry . $billstate . $billcity . 
			$billemail . $billphone . $billpost . 
			$deliveryname . $deliveryaddress . $deliverycountry .
			$deliverystate . $deliverycity . $deliveryemail . $deliveryphone . $deliverypost;

		$value = $hashkey . $value;
		$signature = md5($value);

		$payment_form = '<form method="post" action="' . $payment->get_submit_url() . '" id="checkout" name="checkout">' . "\n";
		$payment_form .= '<input type="hidden" name="version" value="' . $version . '">' . "\n";
		$payment_form .= '<input type="hidden" name="encoding" value="' . $encoding . '">' . "\n";
		$payment_form .= '<input type="hidden" name="language" value="' . $strlang . '">' . "\n";
		$payment_form .= '<input type="hidden" name="merchantid" value="' . $merchantid . '">' . "\n";
		$payment_form .= '<input type="hidden" name="orderid" value="' . $orderid . '">' . "\n";
		$payment_form .= '<input type="hidden" name="orderdate" value="' . $datestr . '">' . "\n";
		$payment_form .= '<input type="hidden" name="currency" value="' . $currency . '">' . "\n";
		$payment_form .= '<input type="hidden" name="orderamount" value="' . $orderamount . '">' . "\n";
		$payment_form .= '<input type="hidden" name="transtype" value="ic">' . "\n";
		$payment_form .= '<input type="hidden" name="callbackurl" value="' . $strServerUrl . '">' . "\n";
		$payment_form .= '<input type="hidden" name="browserbackurl" value="' . $strBrowserurl . '">' . "\n";
		$payment_form .= '<input type="hidden" name="accessurl" value="' . $strAccessurl . '">' . "\n";
		$payment_form .= '<input type="hidden" name="remark1" value="' . $remark1 . '">' . "\n";
		$payment_form .= '<input type="hidden" name="remark2" value="' . $remark2 . '">' . "\n";
		$payment_form .= '<input type="hidden" name="remark3" value="' . $remark3 . '">' . "\n";
		$payment_form .= '<input type="hidden" name="shippingfee" value="' . $shipfee . '">' . "\n";
		$payment_form .= '<input type="hidden" name="billaddress" value="' . $this->utf8_htmldecode($billaddress) . '">' . "\n";
		$payment_form .= '<input type="hidden" name="billcountry" value="' . $this->utf8_htmldecode($billcountry) . '">' . "\n";
		$payment_form .= '<input type="hidden" name="billprovince" value="' . $this->utf8_htmldecode($billstate) . '">' . "\n";
		$payment_form .= '<input type="hidden" name="billcity" value="' . $this->utf8_htmldecode($billcity) . '">' . "\n";
		$payment_form .= '<input type="hidden" name="billemail" value="' . $this->utf8_htmldecode($billemail) . '">' . "\n";
		$payment_form .= '<input type="hidden" name="billphone" value="' . $this->utf8_htmldecode($billphone) . '">' . "\n";
		$payment_form .= '<input type="hidden" name="billpost" value="' . $this->utf8_htmldecode($billpost) . '">' . "\n";
		$payment_form .= '<input type="hidden" name="deliveryname" value="' . $this->utf8_htmldecode($deliveryname) . '">' . "\n";
		$payment_form .= '<input type="hidden" name="deliveryaddress" value="' . $this->utf8_htmldecode($deliveryaddress) . '">' . "\n";
		$payment_form .= '<input type="hidden" name="deliverycountry" value="' . $this->utf8_htmldecode($deliverycountry) . '">' . "\n";
		$payment_form .= '<input type="hidden" name="deliveryprovince" value="' . $this->utf8_htmldecode($deliverystate) . '">' . "\n";
		$payment_form .= '<input type="hidden" name="deliverycity" value="' . $this->utf8_htmldecode($deliverycity) . '">' . "\n";
		$payment_form .= '<input type="hidden" name="deliveryemail" value="' . $this->utf8_htmldecode($deliveryemail) . '">' . "\n";
		$payment_form .= '<input type="hidden" name="deliveryphone" value="' . $this->utf8_htmldecode($deliveryphone) . '">' . "\n";
		$payment_form .= '<input type="hidden" name="deliverypost" value="' . $this->utf8_htmldecode($deliverypost) . '">' . "\n";
		$payment_form .= '<input type="hidden" name="signature" value="' . $signature . '">' . "\n" . $strProduct;
		$payment_form .= '</form>' . "\n";
		if ($payment->get_is_inside()==1) {
			$payment_form .= '<iframe width="100%" height="820" scrolling="no" style="border:none;margin:0 auto;overflow:hidden;" id="ifrm_checkout" name="ifrm_checkout"></iframe>' . "\n";
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
		$version     = $_GET["version"];
		$encoding    = $_GET["encoding"];
		$lang        = $_GET["language"];
		$merchantid  = $_GET["merchantid"];
		$transtype   = $_GET["transtype"];
		$orderid     = $_GET["orderid"];
		$orderdate   = $_GET["orderdate"];
		$currency    = $_GET["currency"];
		$orderamount = $_GET["orderamount"];
		$paycurrency = $_GET["paycurrency"];
		$payamount   = $_GET["payamount"];
		$remark1     = $_GET["remark1"];
		$remark2     = $_GET["remark2"];
		$remark3     = $_GET["remark3"];
		$product = '';
		for ($i=1; $i<=10; $i++) {
			if (!isset($_GET["productname".$i])
				|| $_GET["productname".$i]=='') {
				break;
			}
			$product .= $_GET["productname".$i].$_GET["productsn".$i].$_GET["quantity".$i].$_GET["unit".$i];
		}
		$shippingfee      = $_GET["shippingfee"];
		$deliveryname     = $_GET["deliveryname"];
		$deliveryaddress  = $_GET["deliveryaddress"];
		$deliverycountry  = $_GET["deliverycountry"];
		$deliveryprovince = $_GET["deliveryprovince"];
		$deliverycity     = $_GET["deliverycity"];
		$deliveryemail    = $_GET["deliveryemail"];
		$deliveryphone    = $_GET["deliveryphone"];
		$deliverypost     = $_GET["deliverypost"];
		$moneybraceid     = $_GET["transid"];
		$moneybracedate   = $_GET["transdate"];
		$status           = $_GET["status"];
		$signature        = $_GET["signature"];

		$hashkey = trim($payment->get_md5key());
		$value = $hashkey . $version . $encoding . $lang . $merchantid . $transtype . $orderid .
        	$orderdate . $currency . $orderamount . $paycurrency . $payamount .$remark1 . $remark2 .
            $remark3 .  $product . $shippingfee . $deliveryname . $deliveryaddress . $deliverycountry . $deliveryprovince .
            $deliverycity . $deliveryemail . $deliveryphone . $deliverypost . $moneybraceid . $moneybracedate . $status;

		$getsignature = md5($value);
		if ($getsignature == $signature) {
			if ($status=="Y") {
				//支付成功
				$order_status = 3;
			} elseif ($status=="T") {
				//支付处理中
				$order_status = 2;
			} else {
				//支付失败
				$order_status = 4;
			}
		} else {
			//支付失败
			$order_status = 4;
		}
		
		return $order_status;
	}
	
	function utf8_htmldecode($str)
	{
		$str=str_replace("&","&amp;",$str);
		$str=str_replace("\"","&quot;",$str);
		$str=str_replace("<","&lt;",$str);
		$str=str_replace(">","&gt;",$str);
		$str=str_replace("'","&#39;",$str);
		return $str;
	}
}
