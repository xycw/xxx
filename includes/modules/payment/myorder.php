<?php
/**
 * payment myorder.php
 */
class myorder
{

	function process($payment)
	{
		global $orderInfo, $orderProductInfo, $currencies;

		if ($payment->get_is_inside()==0
			&& !isset($_POST['card_number'])) {
			redirect(href_link('myorder_process', '', 'SSL'));
		}

		$data['AcctNo']    = trim($payment->get_account());
		$MD5key            = trim($payment->get_md5key());
		$data['OrderID']   = put_orderNO($orderInfo['order_id']);
		$data['Amount']    = $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']) * 100;
		$data['CurrCode']  = $this->getCurrCode($orderInfo['currency']['code']);
		$data['HashValue'] = $this->szComputeMD5Hash($MD5key . $data['AcctNo'] . $data['OrderID'] . $data['Amount'] . $data['CurrCode']);
		$data['CMSName']   = 'php';
		$data['TxnType']   = '01';
		$data['IVersion']  = 'V5.0';
		$data['IPAddress'] = get_ip_address();
		$data['Email']     = $orderInfo['customer']['email_address'];
		$data['RetURL']    = $_SERVER['HTTP_HOST'];
		$data['Cookie']    = $_COOKIE['PHPSESSID'];
		
		// card
		$data['CardPAN'] = $payment->get_is_inside()==1?$_SESSION['card']['number']:$_POST['card_number'];
		$data['ExpDate'] = $payment->get_is_inside()==1?$_SESSION['card']['year'].$_SESSION['card']['month']:$_POST['card_year'].$_POST['card_month'];
		$data['CVV2']    = $payment->get_is_inside()==1?$_SESSION['card']['cvv']:$_POST['card_cvv'];
		
		// billing
		$data['CName']           = $orderInfo['billing']['firstname'] . ' ' . $orderInfo['billing']['lastname'];
		$data['BAddress']        = trim($orderInfo['billing']['street_address'] . ' ' . $orderInfo['billing']['suburb']);
        $data['BCity']           = $orderInfo['billing']['city'];
        $data['Bstate']          = $orderInfo['billing']['region'];
        $data['Bcountry']        = $orderInfo['billing']['country'];
		$data['PostCode']        = $orderInfo['billing']['postcode'];
		$data['Telephone']       = $orderInfo['billing']['telephone'];

		// shipping
		$data['ShipName']     = $orderInfo['shipping']['firstname'] . ' ' . $orderInfo['shipping']['lastname'];
		$data['ShipAddress']  = trim($orderInfo['shipping']['street_address'] . ' ' . $orderInfo['shipping']['suburb']);
        $data['ShipCity']     = $orderInfo['shipping']['city'];
        $data['Shipstate']    = $orderInfo['shipping']['region'];
        $data['ShipCountry']  = $orderInfo['shipping']['country'];
		$data['ShipPostCode'] = $orderInfo['shipping']['postcode'];
		$data['Shipphone']    = $orderInfo['shipping']['telephone'];
		
		// product
		$data['PName'] = '';
		foreach ($orderProductInfo as $_product) {
			$data['PName'] = $_product['name'] . ',#' . $_product['sku'] . ';';
		}

		$url = trim($payment->get_submit_url());
		$result = $this->curlPost($url, $data);
		$this->addLog($result);
		$resultArr = explode('&', $result);
		$par1      = explode('Par1=', $resultArr[0]);
		$par2      = explode('Par2=', $resultArr[1]);
		$par3      = explode('Par3=', $resultArr[2]);
		$par4      = explode('Par4=', $resultArr[3]);
		$par5      = explode('Par5=', $resultArr[4]);
		$par6      = explode('Par6=', $resultArr[5]);
		$hashValue = explode('HashValue=', $resultArr[6]);
		$signValue = $this->szComputeMD5Hash($MD5key . $par1[1] . $par2[1] . $par3[1] . $par4[1] . $par5[1] . $par6[1]);
		
		//if ($signValue == $hashValue) {
		//}

		redirect(
			href_link(
				FILENAME_CHECKOUT_RESULT,
				'code=' . $par4[1],
				'SSL'
			)
		);
	}
	
	function result($payment)
	{
		$code = $_GET['code'];
		if ($code == 'KO' || $code == '00') {
			$order_status = 3;
		} else {
			$order_status = 4;
		}
		
		return $order_status;
	}

	function addLog($log)
    {
        $fp = fopen(DIR_FS_CATALOG ."cache/myorder-log-" . date("Y-m-d") . ".txt", "a");
        flock($fp, LOCK_EX) ;
        fwrite($fp, "[" . date("Y-m-d H:i:s") . "]" . $log . "\r\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
	
	function curlPost($url, $postfields)
    {
        //通过curl来进行post请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 跳过主机检查
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array());
        curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_REFERER']);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

	function szComputeMD5Hash($input)
	{
		$md5hex=md5($input);
		$len=strlen($md5hex)/2;
		$md5raw="";
		for ($i=0;$i<$len;$i++) {
			$md5raw=$md5raw . chr(hexdec(substr($md5hex,$i*2,2)));
		}
		$HashValue=base64_encode($md5raw);
		return $HashValue;
	}

	function getCurrCode($currency)
	{
		if ($currency == 'ADP') {
            $code = '020';
        } else if ($currency == 'AED') {
            $code = '784';
        } else if ($currency == 'AFA') {
            $code = '004';
        } else if ($currency == 'ALL') {
            $code = '008';
        } else if ($currency == 'AMD') {
            $code = '051';
        } else if ($currency == 'ANG') {
            $code = '532';
        } else if ($currency == 'AOA') {
            $code = '973';
        } else if ($currency == 'AON') {
            $code = '024';
        } else if ($currency == 'ARS') {
            $code = '032';
        } else if ($currency == 'ASF') {
            $code = '999';
        } else if ($currency == 'ATS') {
            $code = '040';
        } else if ($currency == 'AUD') {
            $code = '036';
        } else if ($currency == 'AWG') {
            $code = '533';
        } else if ($currency == 'AZM') {
            $code = '031';
        } else if ($currency == 'BAM') {
            $code = '977';
        } else if ($currency == 'BBD') {
            $code = '052';
        } else if ($currency == 'BDT') {
            $code = '050';
        } else if ($currency == 'BEF') {
            $code = '056';
        } else if ($currency == 'BGL') {
            $code = '100';
        } else if ($currency == 'BGN') {
            $code = '975';
        } else if ($currency == 'BHD') {
            $code = '048';
        } else if ($currency == 'BIF') {
            $code = '108';
        } else if ($currency == 'BMD') {
            $code = '060';
        } else if ($currency == 'BND') {
            $code = '096';
        } else if ($currency == 'BOB') {
            $code = '068';
        } else if ($currency == 'BOV') {
            $code = '984';
        } else if ($currency == 'BRL') {
            $code = '986';
        } else if ($currency == 'BSD') {
            $code = '044';
        } else if ($currency == 'BTN') {
            $code = '064';
        } else if ($currency == 'BWP') {
            $code = '072';
        } else if ($currency == 'BYB') {
            $code = '112';
        } else if ($currency == 'BYR') {
            $code = '974';
        } else if ($currency == 'BZD') {
            $code = '084';
        } else if ($currency == 'CAD') {
            $code = '124';
        } else if ($currency == 'CDF') {
            $code = '976';
        } else if ($currency == 'CHF') {
            $code = '756';
        } else if ($currency == 'CLF') {
            $code = '990';
        } else if ($currency == 'CLP') {
            $code = '152';
        } else if ($currency == 'CNY') {
            $code = '156';
        } else if ($currency == 'COP') {
            $code = '170';
        } else if ($currency == 'CRC') {
            $code = '188';
        } else if ($currency == 'CUP') {
            $code = '192';
        } else if ($currency == 'CVE') {
            $code = '132';
        } else if ($currency == 'CYP') {
            $code = '196';
        } else if ($currency == 'CZK') {
            $code = '203';
        } else if ($currency == 'DEM') {
            $code = '280';
        } else if ($currency == 'DJF') {
            $code = '262';
        } else if ($currency == 'DKK') {
            $code = '208';
        } else if ($currency == 'DOP') {
            $code = '214';
        } else if ($currency == 'DZD') {
            $code = '012';
        } else if ($currency == 'ECS') {
            $code = '218';
        } else if ($currency == 'ECV') {
            $code = '983';
        } else if ($currency == 'EEK') {
            $code = '233';
        } else if ($currency == 'EGP') {
            $code = '818';
        } else if ($currency == 'ERN') {
            $code = '232';
        } else if ($currency == 'ESP') {
            $code = '724';
        } else if ($currency == 'ETB') {
            $code = '230';
        } else if ($currency == 'EUR') {
            $code = '978';
        } else if ($currency == 'FIM') {
            $code = '246';
        } else if ($currency == 'FJD') {
            $code = '242';
        } else if ($currency == 'FKP') {
            $code = '238';
        } else if ($currency == 'FRF') {
            $code = '250';
        } else if ($currency == 'GBP') {
            $code = '826';
        } else if ($currency == 'GEL') {
            $code = '981';
        } else if ($currency == 'GHC') {
            $code = '288';
        } else if ($currency == 'GIP') {
            $code = '292';
        } else if ($currency == 'GMD') {
            $code = '270';
        } else if ($currency == 'GNF') {
            $code = '324';
        } else if ($currency == 'GRD') {
            $code = '300';
        } else if ($currency == 'GTQ') {
            $code = '320';
        } else if ($currency == 'GWP') {
            $code = '624';
        } else if ($currency == 'GYD') {
            $code = '328';
        } else if ($currency == 'HKD') {
            $code = '344';
        } else if ($currency == 'HNL') {
            $code = '340';
        } else if ($currency == 'HRK') {
            $code = '191';
        } else if ($currency == 'HTG') {
            $code = '332';
        } else if ($currency == 'HUF') {
            $code = '348';
        } else if ($currency == 'IDR') {
            $code = '360';
        } else if ($currency == 'IEP') {
            $code = '372';
        } else if ($currency == 'ILS') {
            $code = '376';
        } else if ($currency == 'INR') {
            $code = '356';
        } else if ($currency == 'IRR') {
            $code = '364';
        } else if ($currency == 'ISK') {
            $code = '352';
        } else if ($currency == 'ITL') {
            $code = '380';
        } else if ($currency == 'JMD') {
            $code = '388';
        } else if ($currency == 'JOD') {
            $code = '400';
        } else if ($currency == 'JPY') {
            $code = '392';
        } else if ($currency == 'KES') {
            $code = '404';
        } else if ($currency == 'KGS') {
            $code = '417';
        } else if ($currency == 'KHR') {
            $code = '116';
        } else if ($currency == 'KMF') {
            $code = '174';
        } else if ($currency == 'KPW') {
            $code = '408';
        } else if ($currency == 'KRW') {
            $code = '410';
        } else if ($currency == 'KWD') {
            $code = '414';
        } else if ($currency == 'KYD') {
            $code = '136';
        } else if ($currency == 'KZT') {
            $code = '398';
        } else if ($currency == 'LAK') {
            $code = '418';
        } else if ($currency == 'LBP') {
            $code = '422';
        } else if ($currency == 'LKR') {
            $code = '144';
        } else if ($currency == 'LRD') {
            $code = '430';
        } else if ($currency == 'LSL') {
            $code = '426';
        } else if ($currency == 'LTL') {
            $code = '440';
        } else if ($currency == 'LUF') {
            $code = '442';
        } else if ($currency == 'LVL') {
            $code = '428';
        } else if ($currency == 'LYD') {
            $code = '434';
        } else if ($currency == 'MAD') {
            $code = '504';
        } else if ($currency == 'MDL') {
            $code = '498';
        } else if ($currency == 'MGF') {
            $code = '450';
        } else if ($currency == 'MKD') {
            $code = '807';
        } else if ($currency == 'MMK') {
            $code = '104';
        } else if ($currency == 'MNT') {
            $code = '496';
        } else if ($currency == 'MOP') {
            $code = '446';
        } else if ($currency == 'MRO') {
            $code = '478';
        } else if ($currency == 'MTL') {
            $code = '470';
        } else if ($currency == 'MUR') {
            $code = '480';
        } else if ($currency == 'MVR') {
            $code = '462';
        } else if ($currency == 'MWK') {
            $code = '454';
        } else if ($currency == 'MXN') {
            $code = '484';
        } else if ($currency == 'MXV') {
            $code = '979';
        } else if ($currency == 'MYR') {
            $code = '458';
        } else if ($currency == 'MZM') {
            $code = '508';
        } else if ($currency == 'NAD') {
            $code = '516';
        } else if ($currency == 'NGN') {
            $code = '566';
        } else if ($currency == 'NIO') {
            $code = '558';
        } else if ($currency == 'NLG') {
            $code = '528';
        } else if ($currency == 'NOK') {
            $code = '578';
        } else if ($currency == 'NPR') {
            $code = '524';
        } else if ($currency == 'NZD') {
            $code = '554';
        } else if ($currency == 'OMR') {
            $code = '512';
        } else if ($currency == 'PAB') {
            $code = '590';
        } else if ($currency == 'PEN') {
            $code = '604';
        } else if ($currency == 'PGK') {
            $code = '598';
        } else if ($currency == 'PHP') {
            $code = '608';
        } else if ($currency == 'PKR') {
            $code = '586';
        } else if ($currency == 'PLN') {
            $code = '985';
        } else if ($currency == 'PLZ') {
            $code = '616';
        } else if ($currency == 'PTE') {
            $code = '620';
        } else if ($currency == 'PYG') {
            $code = '600';
        } else if ($currency == 'QAR') {
            $code = '634';
        } else if ($currency == 'ROL') {
            $code = '642';
        } else if ($currency == 'RSD') {
            $code = '941';
        } else if ($currency == 'RUB') {
            $code = '810';
        } else if ($currency == 'RWF') {
            $code = '646';
        } else if ($currency == 'SAR') {
            $code = '682';
        } else if ($currency == 'SBD') {
            $code = '090';
        } else if ($currency == 'SCR') {
            $code = '690';
        } else if ($currency == 'SDD') {
            $code = '736';
        } else if ($currency == 'SDR') {
            $code = '000';
        } else if ($currency == 'SEK') {
            $code = '752';
        } else if ($currency == 'SGD') {
            $code = '702';
        } else if ($currency == 'SHP') {
            $code = '654';
        } else if ($currency == 'SIT') {
            $code = '705';
        } else if ($currency == 'SKK') {
            $code = '703';
        } else if ($currency == 'SLL') {
            $code = '694';
        } else if ($currency == 'SOS') {
            $code = '706';
        } else if ($currency == 'SRG') {
            $code = '740';
        } else if ($currency == 'STD') {
            $code = '678';
        } else if ($currency == 'SVC') {
            $code = '222';
        } else if ($currency == 'SYP') {
            $code = '760';
        } else if ($currency == 'SZL') {
            $code = '748';
        } else if ($currency == 'THB') {
            $code = '764';
        } else if ($currency == 'TJR') {
            $code = '762';
        } else if ($currency == 'TJS') {
            $code = '972';
        } else if ($currency == 'TMM') {
            $code = '795';
        } else if ($currency == 'TND') {
            $code = '788';
        } else if ($currency == 'TOP') {
            $code = '776';
        } else if ($currency == 'TRL') {
            $code = '792';
        } else if ($currency == 'TTD') {
            $code = '780';
        } else if ($currency == 'TWD') {
            $code = '901';
        } else if ($currency == 'TZS') {
            $code = '834';
        } else if ($currency == 'UAH') {
            $code = '980';
        } else if ($currency == 'UAK') {
            $code = '804';
        } else if ($currency == 'UGX') {
            $code = '800';
        } else if ($currency == 'USD') {
            $code = '840';
        } else if ($currency == 'USN') {
            $code = '997';
        } else if ($currency == 'USS') {
            $code = '998';
        } else if ($currency == 'UYU') {
            $code = '858';
        } else if ($currency == 'UZS') {
            $code = '860';
        } else if ($currency == 'VEB') {
            $code = '862';
        } else if ($currency == 'VND') {
            $code = '704';
        } else if ($currency == 'VUV') {
            $code = '548';
        } else if ($currency == 'WST') {
            $code = '882';
        } else if ($currency == 'XAF') {
            $code = '950';
        } else if ($currency == 'XAG') {
            $code = '961';
        } else if ($currency == 'XAU') {
            $code = '959';
        } else if ($currency == 'XBA') {
            $code = '955';
        } else if ($currency == 'XBB') {
            $code = '956';
        } else if ($currency == 'XBC') {
            $code = '957';
        } else if ($currency == 'XBD') {
            $code = '958';
        } else if ($currency == 'XCD') {
            $code = '951';
        } else if ($currency == 'XDR') {
            $code = '960';
        } else if ($currency == 'XEU') {
            $code = '954';
        } else if ($currency == 'XOF') {
            $code = '952';
        } else if ($currency == 'XPD') {
            $code = '964';
        } else if ($currency == 'XPF') {
            $code = '953';
        } else if ($currency == 'XPT') {
            $code = '962';
        } else if ($currency == 'XTS') {
            $code = '963';
        } else if ($currency == 'XXX') {
            $code = '999';
        } else if ($currency == 'YER') {
            $code = '886';
        } else if ($currency == 'YUM') {
            $code = '891';
        } else if ($currency == 'YUN') {
            $code = '890';
        } else if ($currency == 'ZAL') {
            $code = '991';
        } else if ($currency == 'ZAR') {
            $code = '710';
        } else if ($currency == 'ZMK') {
            $code = '894';
        } else if ($currency == 'ZRN') {
            $code = '180';
        } else if ($currency == 'ZWD') {
            $code = '716';
        }
        else{
            $code='000';
        }
        return $code;
	}

}
