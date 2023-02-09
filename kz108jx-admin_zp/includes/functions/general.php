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
		return trim(stripslashes($string));
	} elseif (is_array($string)) {
		reset($string);
		foreach ($string as $key => $val) {
			$string[$key] = db_prepare_input($val);
		}
		return $string;
	} else {
		return $string;
	}
}

function back_url()
{
	if (isset($_SERVER['HTTP_REFERER']) && preg_match("~^".HTTP_SERVER."~i", $_SERVER['HTTP_REFERER']) ) {
		$link = $_SERVER['HTTP_REFERER'];
	} else {
		$link = href_link(FILENAME_INDEX);
	}
	return $link;
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
			if ((strlen($value) > 0) && (!in_array($key, $exclude_array))) {
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

function href_link($main_page='index.php', $parameters='')
{
	if (ENABLE_SSL == 'true') {
		$link = HTTPS_SERVER;
    } else {
    	$link = HTTP_SERVER;
    }
	$link .= DIR_WS_ADMIN;
	
	if (!strstr($main_page, '.php')) $main_page .= '.php';
	if (not_null($parameters)) {
		$link .= $main_page . "?" . output_string($parameters);
	} else {
		$link .= $main_page;
	}
	// clean up the link before processing
    while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);
    while (strstr($link, '&amp;&amp;')) $link = str_replace('&amp;&amp;', '&amp;', $link);
    while ((substr($link, -1)=='&')||(substr($link, -1)=='?')) $link = substr($link, 0, -1);
	return $link;
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

function get_currencies()
{
	global $db;
	$sql = "SELECT name, code
			FROM   " . TABLE_CURRENCY . "
			ORDER BY sort_order";
	$result = $db->Execute($sql);
	$arr = array();
	if ($result->RecordCount() > 0) {
		while (!$result->EOF) {
			$arr[strtoupper($result->fields['code'])] = $result->fields['name'];
			$result->MoveNext();
		}
		return $arr;
	}
	return false;
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

function get_payment_methods()
{
	global $db;
	$sql = "SELECT code
			FROM " . TABLE_PAYMENT_METHOD . " ORDER BY sort_order, payment_method_id";
	$result = $db->Execute($sql);
	$arr = array();
	while (!$result->EOF) {
		$arr[$result->fields['code']] = $result->fields['code'];
		$result->MoveNext();
	}

	return $arr;
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

function get_templates($mobile = false)
{
	if (true == $mobile) {
		$path = DIR_FS_CATALOG_TEMPLATES . 'mobile/';
		$dir  = @dir($path);
	} else {
		$path = DIR_FS_CATALOG_TEMPLATES;
		$dir  = @dir($path);
	}
	while ($file = $dir->read()) {
		if (is_dir($path . $file)
			&& is_dir($path . $file . '/css')
			&& is_dir($path . $file . '/images')
			&& is_dir($path . $file . '/js')) {
			$arr[$file] = $file;
		}
	}
	return $arr;
}

function cfg_pull_down_currency_list($key, $currency_code)
{
    $name = 'configuration[' . $key . ']';
    return cfg_pull_down($name, get_currencies(), $currency_code);
}

function cfg_pull_down_country_list($key, $country_id)
{
	$name = 'configuration[' . $key . ']';
    return cfg_pull_down($name, get_countries(), $country_id);
}

function cfg_pull_down_template_list($key, $template_dir)
{
	$name = 'configuration[' . $key . ']';
    return cfg_pull_down($name, get_templates(), $template_dir);
}

function cfg_pull_down_mobile_template_list($key, $template_dir)
{
	$name = 'configuration[' . $key . ']';
	return cfg_pull_down($name, get_templates(true), $template_dir);
}

function cfg_pull_down_custom($values, $key, $default)
{
	$name = 'configuration[' . $key . ']';
	return cfg_pull_down($name, $values, $default);
}

function cfg_pull_down($name, $values, $default = '')
{
    $field = '<select name="' . $name . '">' . "\n";
    foreach ($values as $_kel => $val) {
		$field .= '<option value="' . $_kel . '"';
		if ($default == $_kel) {
			$field .= ' selected="selected"';
		}
		$field .= '>' . $val . '</option>' . "\n";
    }
    $field .= '</select>' . "\n";

    return $field;
}

function cfg_textarea($key, $text) {
	$name = 'configuration[' . $key . ']';
	$field = '<textarea name="' . $name . '" cols="60" rows="5">' . $text . '</textarea>';
    return $field;
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

/**
 * 删除指定目录下的所有文件
 * @param unknown_type $dir
 */
function clearImg($dir = null)
{
	if (empty($dir)) {
		$dir = DIR_FS_CATALOG_IMAGES_CACHE;
	}
	$dirArray = @scandir($dir);
	if (!empty($dirArray)) {
		foreach ($dirArray as $file) {
			if ($file != "." && $file!="..") {
				$fullpath = $dir . $file;
				if (is_dir($fullpath)) {
					if (@rmdir($fullpath) == false) {
						clearImg($fullpath . '/');
					}
				} else {
					unlink($fullpath);
				}
			}
		}
	}
	// 删除当前文件夹, 不删除cache/sql
	if ($dir == DIR_FS_CATALOG_IMAGES_CACHE) {
		return true;
	}
	
	if (@rmdir($dir)) {
		return true;
	} else {
		return false;
	}
}

function get_image($src, $width=0, $height=0)
{
	if (is_file(ROOT_IMAGE . $src)
		&& USE_IMAGE_CACHE == 1) {
		// create an image of the given filetype
		$file_ext = substr($src, strrpos($src, '.'));
		$oldfile = ROOT_IMAGE . $src;
		$oldsrc = DIR_WS_ADMIN_IMAGES . 'no_image.jpg';
		$filename = get_cache_name($oldfile . 'image' . $width . 'x' . $height . $file_ext);
		$newfile = DIR_FS_CATALOG_IMAGES_CACHE . $filename;
		$newsrc = DIR_WS_CATALOG_IMAGES_CACHE . $filename;
		if (is_file($newfile)) {
			return $newsrc;
		} elseif (run_imageGD($oldfile, $width, $height)) {
			return $newsrc;
		}
	} elseif (is_file(DIR_FS_CATALOG_IMAGES . $src)) {
		// create an image of the given filetype
		$file_ext = substr($src, strrpos($src, '.'));
		$oldfile = DIR_FS_CATALOG_IMAGES . $src;
		$oldsrc = DIR_WS_CATALOG_IMAGES . $src;
		if (USE_IMAGE_CACHE == 1) {
			$filename = get_cache_name($oldfile . 'image' . $width . 'x' . $height . $file_ext);
			$newfile = DIR_FS_CATALOG_IMAGES_CACHE . $filename;
			$newsrc = DIR_WS_CATALOG_IMAGES_CACHE . $filename;
			if (is_file($newfile)) {
				return $newsrc;
			} elseif (run_imageGD($oldfile, $width, $height)) {
				return $newsrc;
			}
		}
	} else {
		$oldsrc = DIR_WS_ADMIN_IMAGES . 'no_image.jpg';
	}
	
	return $oldsrc;
}

function get_cache_name($oldfile)
{
	// create an image of the given filetype
	$file_ext = substr($oldfile, strrpos($oldfile, '.'));
	$md5 = md5(STORE_WEBSITE . $oldfile);
	$newfile = $md5{0} . '/' . $md5{1} . '/' . $md5 . $file_ext;
	
	return $newfile;
}

function run_imageGD($oldfile, $width=0, $height=0)
{
	if (extension_loaded('gd2')) return false;
	// create an image of the given filetype
	$file_ext = substr($oldfile, strrpos($oldfile, '.'));
	$oldimg = load_imageGD($oldfile);
	if ($oldimg == false) return false;
	$oldwidth = imagesx($oldimg);
	$oldheight = imagesy($oldimg);
	$newwidth = (int)$width==0||(int)$width>$oldwidth?$oldwidth:(int)$width;
	$newheight = (int)$height==0||(int)$height>$oldheight?$oldheight:(int)$height;
	$newimg = @imagecreatetruecolor($newwidth, $newheight);
	imagealphablending($newimg, false);
	imagesavealpha($newimg, true);
	@imagecopyresampled($newimg, $oldimg, 0, 0, 0, 0, $newwidth, $newheight, $oldwidth, $oldheight);
	imagedestroy($oldimg);
	
	return save_imageGD($newimg, $oldfile . 'image' . $width . 'x' . $height . $file_ext);
}

function load_imageGD($file)
{
	// create an image of the given filetype
	$file_ext = substr($file, strrpos($file, '.'));
	switch (strtolower($file_ext)) {
		case '.gif':
			if (!function_exists("imagecreatefromgif")) return false;
			$image = @imagecreatefromgif($file);
		break;
		case '.png':
			if (!function_exists("imagecreatefrompng")) return false;
			$image = @imagecreatefrompng($file);
		break;
		case '.jpg':
		case '.jpeg':
			if (!function_exists("imagecreatefromjpeg")) return false;
			$image = @imagecreatefromjpeg($file);
		break;
	}
	
	return $image;
}

function save_imageGD($image, $oldfile, $quality=85)
{
	$filename = get_cache_name($oldfile);
	$file = DIR_FS_CATALOG_IMAGES_CACHE . $filename;
	$dir = dirname($file);
	if (!is_dir($dir)) {
		if (!@mkdir($dir, 0777, true)) {
			return false;
		}
	}
	// create an image of the given filetype
	$file_ext = substr($file, strrpos($file, '.'));
	switch (strtolower($file_ext)) {
		case '.gif':
			if (!function_exists("imagegif")) return false;
			$ok = @imagegif($image, $file);
		break;
		case '.png':
			if (!function_exists("imagepng")) return false;
			$quality = (int)$quality/10;
			$ok = @imagepng($image, $file, $quality);
		break;
		case '.jpg':
		case '.jpeg':
			if (!function_exists("imagejpeg")) return false;
			$ok = @imagejpeg($image, $file, $quality);
		break;
		default: $ok = false;
	}
	imagedestroy($image);
	
	return $ok;
}
