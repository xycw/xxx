<?php
/**
 * init_includes init_db_config_read.php
 */
// 屏蔽开始时间
function get_millisecond()
{
    list($msec, $sec) = explode(' ', microtime());
    $millisecond      = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $millisecond;
}

$ipHistoryTimestart = get_millisecond();

// 数据库配置CLOAK_API常量初始化
$configuration = $db->Execute("SELECT configuration_key, configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'CLOAK_API_%'", '', true, 604800);
while (!$configuration->EOF) {
	if(!defined(strtoupper($configuration->fields['configuration_key']))) {
		define(strtoupper($configuration->fields['configuration_key']), $configuration->fields['configuration_value']);
	}
	$configuration->MoveNext();
}

if (isset($_COOKIE['ip_history_json'])) {
	$ipHistoryData = json_decode($_COOKIE['ip_history_json'], true);
} else {
	// 获取客户真实IP地址
	if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
		$ipAddress = $_SERVER['HTTP_CF_CONNECTING_IP'];
	} else {
		if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$ipAddress = getenv('HTTP_CLIENT_IP');
		} elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$ipAddress = getenv('HTTP_X_FORWARDED_FOR');
		} elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$ipAddress = getenv('REMOTE_ADDR');
		} elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$ipAddress = $_SERVER['REMOTE_ADDR'];
		}
	}

	// IP历史表
	$sql = "SELECT *
			FROM   " . TABLE_IP_HISTORY . "
			WHERE  ip_address = :ipAddress
			ORDER BY ip_history_id DESC
			LIMIT  1";
	$sql = $db->bindVars($sql, ':ipAddress', $ipAddress, 'string');
	$ipHistoryResult = $db->Execute($sql);

	if ($ipHistoryResult->RecordCount() > 0) {
		$ipHistoryData = array(
			'ipAddress'     => $ipHistoryResult->fields['ip_address'],
			'isCloak'       => $ipHistoryResult->fields['is_cloak'],
			'continentCode' => $ipHistoryResult->fields['continent_code'],
			'countryCode'   => $ipHistoryResult->fields['country_code'],
			'currencyCode'  => $ipHistoryResult->fields['currency_code']
		);
	} else {
		$cloakApiPostFields = array(
			'server' => json_encode($_SERVER, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
			'ip'     => $ipAddress,
			'domain' => $_SERVER['HTTP_HOST']
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, CLOAK_API_URL);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $cloakApiPostFields);
		curl_setopt($ch, CURLOPT_TIMEOUT, 2);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$cloakApiJson = curl_exec($ch);
		$cloakApiData = json_decode($cloakApiJson, true);
		$ipHistoryData = array(
			'ipAddress'     => $cloakApiData['ipAddress'],
			'isCloak'       => isset($cloakApiData['cloak']['status']) ? $cloakApiData['cloak']['status'] : '0',
			'continentCode' => $cloakApiData['continentCode'],
			'countryCode'   => $cloakApiData['countryCode'],
			'currencyCode'  => $cloakApiData['currencyCode']
		);
		setcookie('ip_history_json', json_encode($ipHistoryData), time() + 60 * 60 * 24 * 180, '/', '', false);
	}
}

// 定义IS_ZP常量值
$isZp = 0;

if (defined('CLOAK_API_IP_WHITELIST')
	&& strlen(CLOAK_API_IP_WHITELIST) > 0
	&& strstr(CLOAK_API_IP_WHITELIST, $ipHistoryData['ipAddress']) != false) {
	$isZp = 0;
} else {
	// 0:关闭斗篷功能 1:开启斗篷功能 2:只显示审核站
	switch (CLOAK_API_SWITCH) {
		case '0':
			$isZp = 0;
		break;
		case '1':
			if ($ipHistoryData['isCloak'] == '1') {
				$isZp = 1;
			}
		break;
		case '2':
		default:
			$isZp = 1;
	}
}

define('IS_ZP', $isZp);

// 记录IP历史表
if (isset($ipHistoryResult) && $ipHistoryResult->RecordCount() == 0) {
	$ipHistoryTimeEnd = get_millisecond();
	$sql_data_array = array(
		array('fieldName'=>'ip_address', 'value'=>$ipHistoryData['ipAddress'], 'type'=>'string'),
		array('fieldName'=>'is_cloak', 'value'=>$ipHistoryData['isCloak'], 'type'=>'integer'),
		array('fieldName'=>'cloak_runtime', 'value'=>$ipHistoryTimeEnd - $ipHistoryTimestart, 'type'=>'integer'),
		array('fieldName'=>'continent_code', 'value'=>$ipHistoryData['continentCode'], 'type'=>'string'),
		array('fieldName'=>'country_code', 'value'=>$ipHistoryData['countryCode'], 'type'=>'string'),
		array('fieldName'=>'currency_code', 'value'=>$ipHistoryData['currencyCode'], 'type'=>'string'),
		array('fieldName'=>'http_request', 'value'=>$_SERVER['REQUEST_URI'], 'type'=>'string'),
		array('fieldName'=>'http_referer', 'value'=>isset($_SERVER['HTTP_REFERER']) ? (strstr($_SERVER['HTTP_REFERER'], HTTP_SERVER) != false ? str_replace(HTTP_SERVER, '', $_SERVER['HTTP_REFERER']) : $_SERVER['HTTP_REFERER']) : '', 'type'=>'string'),
		array('fieldName'=>'http_user_agent', 'value'=>$_SERVER['HTTP_USER_AGENT'], 'type'=>'string'),
		array('fieldName'=>'http_accept_language', 'value'=>$_SERVER['HTTP_ACCEPT_LANGUAGE'], 'type'=>'string'),
		array('fieldName'=>'is_zp', 'value'=>$isZp, 'type'=>'integer'),
		array('fieldName'=>'cloak_api_json', 'value'=>$cloakApiJson, 'type'=>'string'),
		array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring')
	);
	$db->perform(TABLE_IP_HISTORY, $sql_data_array);
}

if (IS_ZP == 0) {
	define('DB_SUFFIX', '');
} else {
	define('DB_SUFFIX', '_zp');
}

// 数据库表名常量化
define('TABLE_CATEGORY', DB_PREFIX . 'category' . DB_SUFFIX);
define('TABLE_CMS_PAGE', DB_PREFIX . 'cms_page' . DB_SUFFIX);
define('TABLE_PRODUCT', DB_PREFIX . 'product' . DB_SUFFIX);
define('TABLE_PRODUCT_ATTRIBUTE', DB_PREFIX . 'product_attribute' . DB_SUFFIX);
define('TABLE_PRODUCT_OPTION', DB_PREFIX . 'product_option' . DB_SUFFIX);
define('TABLE_PRODUCT_OPTION_VALUE', DB_PREFIX . 'product_option_value' . DB_SUFFIX);
define('TABLE_PRODUCT_REVIEW', DB_PREFIX . 'product_review' . DB_SUFFIX);
define('TABLE_PRODUCT_TO_CATEGORY', DB_PREFIX . 'product_to_category' . DB_SUFFIX);

// 设置默认国家
if (isset($ipHistoryData['countryCode'])) {
	$sql = "SELECT country_id
			FROM   " . TABLE_COUNTRY . "
			WHERE  iso_code_2 = :isoCode2
			LIMIT  1";
	$sql = $db->bindVars($sql, ':isoCode2', $ipHistoryData['countryCode'], 'string');
	$result = $db->Execute($sql, '', true, 604800);
	if ($result->RecordCount() > 0) {
		define('STORE_COUNTRY', $result->fields['country_id']);
	}
}

// 设置默认货币
if (isset($ipHistoryData['currencyCode'])) {
	$sql = "SELECT code
			FROM   " . TABLE_CURRENCY . "
			WHERE code = :code
			LIMIT 1";
	$sql = $db->bindVars($sql, ':code', $ipHistoryData['currencyCode'], 'string');

	$result = $db->Execute($sql, '', true, 604800);
	if ($result->RecordCount() > 0) {
		define('STORE_CURRENCY', $result->fields['code']);
	}
}

// 数据库配置常量初始化
$configuration = $db->Execute("SELECT configuration_key, configuration_value FROM " . TABLE_CONFIGURATION, '', true, 604800);
while (!$configuration->EOF) {
	if(!defined(strtoupper($configuration->fields['configuration_key']))) {
		define(strtoupper($configuration->fields['configuration_key']), $configuration->fields['configuration_value']);
	}
	$configuration->MoveNext();
}
