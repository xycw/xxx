<?php
function validate_email($email)
{
	$pattern = "/^([\w\.-]+)@([a-zA-Z0-9-]+)(\.[a-zA-Z\.]+)$/i";
	if (preg_match($pattern, $email)) {
		return true;
	}
	
	return false;
}

function disable_email($email)
{
	if (!defined('STORE_DISABLE_EMAIL')) define('STORE_DISABLE_EMAIL', '');
	$email_suffix = strstr($email, '@');
	$disableList = explode(',', STORE_DISABLE_EMAIL);
	if (in_array($email ,$disableList)
		|| in_array($email_suffix ,$disableList)) {
		return true;
	}
	
	return false;
}

function validate_date($date)
{
	$pattern = "/^(((1[6-9]|[2-9]\d)(\d{2})-((0?[13578])|(1[02]))-((0?[1-9])|([12]\d)|(3[01])))|((1[6-9]|[2-9]\d)(\d{2})-((0?[469])|11)-((0?[1-9])|([12]\d)|30))|((1[6-9]|[2-9]\d)(\d{2})-0?2-((0?[1-9])|(1\d)|(2[0-8])))|((1[6-9]|[2-9]\d)([13579][26])-0?2-29)|((1[6-9]|[2-9]\d)([2468][048])-0?2-29)|((1[6-9]|[2-9]\d)(0[48])-0?2-29)|([13579]600-0?2-29)|([2468][048]00-0?2-29)|([3579]200-0?2-29))$/";
	if (preg_match($pattern, $date)) {
		return true;
	}
	
	return false;
}

function validate_datetime($datetime)
{
	$pattern = "/^(((1[6-9]|[2-9]\d)(\d{2})-((0?[13578])|(1[02]))-((0?[1-9])|([12]\d)|(3[01])))|((1[6-9]|[2-9]\d)(\d{2})-((0?[469])|11)-((0?[1-9])|([12]\d)|30))|((1[6-9]|[2-9]\d)(\d{2})-0?2-((0?[1-9])|(1\d)|(2[0-8])))|((1[6-9]|[2-9]\d)([13579][26])-0?2-29)|((1[6-9]|[2-9]\d)([2468][048])-0?2-29)|((1[6-9]|[2-9]\d)(0[48])-0?2-29)|([13579]600-0?2-29)|([2468][048]00-0?2-29)|([3579]200-0?2-29)) (20|21|22|23|[0-1]?\d):[0-5]?\d:[0-5]?\d$/";
	if (preg_match($pattern, $datetime)) {
		return true;
	}
	
	return false;
}

function validate_creditcard($creditcard)
{
	if (preg_match('/[^0-9 \-]+/', $creditcard)
		|| (strlen($creditcard) != 16 && strlen($creditcard) != 15)) {
		return false;
	}
	$nCheck = 0;
	$nDigit = 0;
	$bEven  = false;
	$creditcard = preg_replace('/\D/', '', $creditcard);
	for ($n = strlen('') - 1; $n >= 0; $n--) {
		$cDigit = $creditcard[$n];
		$nDigit = intval($cDigit);
		if ($bEven) {
			if (($nDigit *= 2) > 9) {
				$nDigit -= 9;
			}
		}
		$nCheck += $nDigit;
		$bEven = !$bEven;
	}
	return ($nCheck % 10) === 0;
}

function not_null($value)
{
    if (is_array($value)) {
        if (sizeof($value) > 0) {
			return true;
        } else {
			return false;
        }
    } elseif ( is_a( $value, 'queryFactoryResult' ) ) {
        if (sizeof($value->result) > 0) {
        	return true;
        } else {
        	return false;
        }
    } else {
        if (($value != '') && (strtolower($value) != 'null') && (strlen(trim($value)) > 0)) {
            return true;
        } else {
            return false;
        }
    }
}

function __()
{
	global $translate;
	$args = func_get_args();
	return $translate->translate($args);
}

function es_rand($min = null, $max = null)
{
	static $seeded;
	if (!$seeded) {
		mt_srand((double)microtime()*1000000);
		$seeded = true;
	}
	if (isset($min) && isset($max)) {
		if ($min >= $max) {
			return $min;
		} else {
			return mt_rand($min, $max);
		}
	}
	
	return mt_rand();
}

function date_long($raw_date)
{
	if (($raw_date == '0001-01-01 00:00:00') || ($raw_date == '')) return false;
	
	$year = (int)substr($raw_date, 0, 4);
	$month = (int)substr($raw_date, 5, 2);
	$day = (int)substr($raw_date, 8, 2);
	$hour = (int)substr($raw_date, 11, 2);
	$minute = (int)substr($raw_date, 14, 2);
	$second = (int)substr($raw_date, 17, 2);
	
	return strftime('%A %d %B, %Y', mktime($hour, $minute, $second, $month, $day, $year));
}


function date_short($raw_date)
{
	if (($raw_date == '0001-01-01 00:00:00') || empty($raw_date)) return false;
	
	$year = substr($raw_date, 0, 4);
	$month = (int)substr($raw_date, 5, 2);
	$day = (int)substr($raw_date, 8, 2);
	$hour = (int)substr($raw_date, 11, 2);
	$minute = (int)substr($raw_date, 14, 2);
	$second = (int)substr($raw_date, 17, 2);

	// error on 1969 only allows for leap year
	if ($year != 1969 && @date('Y', mktime($hour, $minute, $second, $month, $day, $year)) == $year) {
		return date('m/d/Y', mktime($hour, $minute, $second, $month, $day, $year));
	} else {
		return preg_replace('/2037$/', $year, date('m/d/Y', mktime($hour, $minute, $second, $month, $day, 2037)));
	}
}

function datetime_short($raw_datetime)
{
	if(($raw_datetime == '0001-01-01 00:00:00') || ($raw_datetime == '')) return false;

	$year = (int)substr($raw_datetime, 0, 4);
	$month = (int)substr($raw_datetime, 5, 2);
	$day = (int)substr($raw_datetime, 8, 2);
	$hour = (int)substr($raw_datetime, 11, 2);
	$minute = (int)substr($raw_datetime, 14, 2);
	$second = (int)substr($raw_datetime, 17, 2);

	return strftime('%m/%d/%Y %H:%M:%S', mktime($hour, $minute, $second, $month, $day, $year));
 }

function parse_input_field_data($data, $parse)
{
	return strtr(trim($data), $parse);
}

function output_string($string, $translate = false, $protected = false)
{
	if ($protected == true) {
		return htmlspecialchars($string);
	} else {
		if ($translate == false) {
			return parse_input_field_data($string, array('"' => '&quot;'));
		} else {
			return parse_input_field_data($string, $translate);
		}
	}
}

function output_string_protected($string)
{
    return output_string($string, false, true);
}

function sanitize_string($string)
{
	$string = preg_replace('/ +/', ' ', $string);
	return preg_replace("/[<>]/", '_', $string);
}

function db_input($string)
{
	return addslashes($string);
}

function db_prepare_input($string)
{
	if (is_string($string)) {
		return trim(sanitize_string(stripslashes($string)));
	} elseif (is_array($string)) {
		reset($string);
		while (list($key, $value) = each($string)) {
			$string[$key] = db_prepare_input($value);
		}
		return $string;
	} else {
		return $string;
	}
}

function currency_exists($code, $getFirstDefault = false)
{
	global $db;
	$sql = "SELECT code
			FROM   " . TABLE_CURRENCY . "
			WHERE code = :code
			LIMIT 1";
	$sql = $db->bindVars($sql, ':code', $code, 'string');
	
	$sql_first = "SELECT code
				  FROM   " . TABLE_CURRENCY . "
				  ORDER BY value ASC
				  LIMIT 1";
	$result = $db->Execute(($getFirstDefault == false)?$sql:$sql_first);
	
	if ($result->RecordCount()) {
		return strtoupper($result->fields['code']);
	}
	
	return false;
}

function get_countries()
{
	global $db;
	$sql = "SELECT country_id, name
			FROM   " . TABLE_COUNTRY . "
			ORDER BY name";
	$result = $db->Execute($sql, false, true, 604800);
	$arr = array();
	while (!$result->EOF) {
		$arr[$result->fields['country_id']] = $result->fields['name'];
		$result->MoveNext();
	}
	
	return $arr;
}

function get_country_name($country_id)
{
	global $db;
	$sql = "SELECT name
			FROM   " . TABLE_COUNTRY . "
			WHERE  country_id = :country_id
			LIMIT  1";
	$sql = $db->bindVars($sql, ':country_id', $country_id, 'integer');
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) {
		return $result->fields['name'];
	}
	
	return false;
}
 
function get_country_iso($country_id)
{
	global $db;
	$sql = "SELECT iso_code_3, iso_code_2
			FROM   " . TABLE_COUNTRY . "
			WHERE  country_id = :country_id
			LIMIT  1";
	$sql = $db->bindVars($sql, ':country_id', $country_id, 'integer');
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) {
		return array(
			'iso_code_3' => $result->fields['iso_code_3'],
			'iso_code_2' => $result->fields['iso_code_2']
		);
	}
	
	return '';
}

function get_region_countries()
{
	global $db;
	$sql = "SELECT region_id, country_id, name
			FROM   " . TABLE_REGION . "
			ORDER BY name";
	$result = $db->Execute($sql, false, true, 604800);
	$arr = array();
	while (!$result->EOF) {
		$arr[$result->fields['country_id']][] = array(
			'region_id' => $result->fields['region_id'],
			'name' => $result->fields['name']
		);
		$result->MoveNext();
	}
	
	return $arr;
}

function has_region_country($country_id)
{
	global $db;
	$sql = "SELECT COUNT(*) AS total
			FROM   " . TABLE_REGION . "
			WHERE  country_id = :country_id";
	$sql = $db->bindVars($sql, ':country_id', $country_id, 'integer');
	$result = $db->Execute($sql);	
	if ($result->fields['total'] > 0) {
		return true;
	}
	return false;
}

function get_region_name($region_id, $country_id)
{
	global $db;
	$sql = "SELECT name
			FROM   " . TABLE_REGION . "
			WHERE  region_id = :region_id
			AND    country_id = :country_id
			LIMIT  1";
	$sql = $db->bindVars($sql, ':region_id', $region_id, 'integer');
	$sql = $db->bindVars($sql, ':country_id', $country_id, 'integer');
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) {
		return $result->fields['name'];
	}
	
	return false;
}

function get_region_code($region_id)
{
	global $db;
	$sql = "SELECT code
			FROM   " . TABLE_REGION . "
			WHERE region_id = :region_id
			LIMIT 1";
	$sql = $db->bindVars($sql, ':region_id', $region_id, 'integer');
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) {
		return $result->fields['code'];
	}
	
	return '';
}

function back_url()
{
	if (isset($_SERVER['HTTP_REFERER'])
		&& (preg_match("~^".HTTP_SERVER."~i", $_SERVER['HTTP_REFERER'])
		|| preg_match("~^".HTTPS_SERVER."~i", $_SERVER['HTTP_REFERER']))) {
		$link = $_SERVER['HTTP_REFERER'];
	} else {
		$link = href_link(FILENAME_INDEX);
	}
	return $link;
}

function trunc_string($str = "", $len = 150, $more = 'true')
{
	if ($str == "") return $str;
	if (is_array($str)) return $str;
	$str = trim($str);
	if ($len==0 || !is_numeric($len)) return $str;
	if (strlen($str) <= $len) return $str;
	$str = substr($str, 0, $len);
	if ($str != "") {
		if (!substr_count($str , " ")) {
			if ($more == 'true') $str .= "...";
			return $str;
		}
		while (strlen($str) && ($str[strlen($str)-1] != " ")) {
			$str = substr($str, 0, -1);
		}
		$str = substr($str, 0, -1);
		if ($more == 'true') $str .= "...";
		if ($more != 'true' && $more != 'false') $str .= $more;
	}
	return $str;
}

function get_all_get_params($exclude_array='')
{
	if (!is_array($exclude_array)) $exclude_array = array();
	$exclude_array[] = 'x';
	$exclude_array[] = 'y';

	$get_url = '';
	if (is_array($_GET) && (sizeof($_GET) > 0)) {
		reset($_GET);
		while (list($key, $value) = each($_GET)) {
			if ((strlen($value) > 0) && ($key != 'main_page') && (!in_array($key, $exclude_array))) {
				$get_url .= sanitize_string($key) . '=' . rawurlencode(stripslashes($value)). '&';
			}
		}
	}
	while (strstr($get_url, '&&')) $get_url = str_replace('&&', '&', $get_url);
    while (strstr($get_url, '&amp;&amp;')) $get_url = str_replace('&amp;&amp;', '&amp;', $get_url);
	return $get_url;
}

function redirect($url, $httpResponseCode='')
{
	while (strstr($url, '&&')) $url = str_replace('&&', '&', $url);
	while (strstr($url, '&amp;&amp;')) $url = str_replace('&amp;&amp;', '&amp;', $url);
	while (strstr($url, '&amp;')) $url = str_replace('&amp;', '&', $url);
	if ($httpResponseCode == '') {
		header('Location: ' . $url);
		session_write_close();
	} else {
		header('Location: ' . $url, true, (int)$httpResponseCode);
		session_write_close();
	}
	exit();
}

function href_link($main_page='index', $parameters='', $connection='NOSSL')
{

	global $seo_url;

	if (isset($seo_url) && is_a($seo_url, 'seo_url')) {

		return $seo_url->href_link($main_page, $parameters, $connection);
	}
	if ($connection == 'SSL' && ENABLE_SSL == 'true') {
		$link = HTTPS_SERVER;
    } else {
    	$link = HTTP_SERVER;
    }
	$link .= DIR_WS_CATALOG;

	if (not_null($parameters)) {
		$link .= ($main_page=='index' ? '?' : LOCALHOST.'index.php?main_page='. $main_page . '&') . output_string($parameters);
	} else {
		$link .= $main_page=='index' ? '' : LOCALHOST.'index.php?main_page='. $main_page;

	}


	// clean up the link before processing
    while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);
    while (strstr($link, '&amp;&amp;')) $link = str_replace('&amp;&amp;', '&amp;', $link);
    while ((substr($link, -1)=='&')||(substr($link, -1)=='?')) $link = substr($link, 0, -1);
	return $link;
}

function href_params($url, $parameters='')
{
	return $url.((stristr($url,'?'))? '&':'?').$parameters;
}

function get_module_directory($filename, $dir_only=false)
{
	global $template_dir;
	
	if (file_exists(DIR_FS_CATALOG_MODULES . $template_dir . '/' . $filename)) {
		$template_dir_select = $template_dir . '/';
	} else {
		$template_dir_select = '';
	}
	
	if ($dir_only == true) {
		return $template_dir_select;
	} else {
		return $template_dir_select . $filename;
	}
}

function get_ip_address()
{
	if (isset($_SERVER)) {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
	} else {
		if (getenv('HTTP_X_FORWARDED_FOR')) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif (getenv('HTTP_CLIENT_IP')) {
			$ip = getenv('HTTP_CLIENT_IP');
		} else {
			$ip = getenv('REMOTE_ADDR');
		}
	}
	return $ip;
}

function send_email($to_mail, $to_name, $from_mail, $from_name, $sendSubject, $sendBody)
{
	require_once(DIR_FS_CATALOG_CLASSES . 'PHPMailer/class.phpmailer.php');
	$mail = new PHPMailer();
	$mail->PluginDir = DIR_FS_CATALOG_CLASSES . 'PHPMailer/';
	$mail->SetLanguage(STORE_LANGUAGE);
	$mail->CharSet = 'utf-8';
	$mail->Encoding = '8bit';
	$mail->WordWrap = 76;
	$mail->IsHTML();
	$mail->IsSMTP();
	$mail->SMTPAuth = true;
	$mail->Username = SEND_EMAIL_ACCOUNT;
	$mail->Password = SEND_EMAIL_PASSWORD;
	$mail->Host = SEND_EMAIL_HOST;
	$mail->Port = SEND_EMAIL_PORT;
	switch (SEND_EMAIL_PORT) {
		case '587':
			$mail->SMTPSecure = 'tls';
			break;
		case '465':
			$mail->SMTPSecure = 'ssl';
			break;
	}
	$mail->Subject  = $sendSubject;
	$mail->Body     = $sendBody;
	$mail->From     = SEND_EMAIL_ACCOUNT;
	$mail->FromName = $from_name;
	$mail->AddAddress($to_mail, $to_name);
	$mail->AddReplyTo($from_mail, $from_name);

	if ($mail->Send()) {
		return true;
	}
	return false;
}
