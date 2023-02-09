<?php
function validate_product($product_id)
{
	global $db;
	$sql = "SELECT count(*) AS total
			FROM   " . TABLE_PRODUCT . "
			WHERE  status = 1
			AND    in_stock = 1
			AND    product_id = :productID";
	$sql = $db->bindVars($sql, ':productID', $product_id, 'integer');
	$result = $db->Execute($sql);
	if ($result->fields['total'] > 0) {
		return true;
	}
	
	return false;
}

function get_product($product_id)
{
	global $db;
	$sql = "SELECT product_id, sku, name, image,
				   IF(specials_price AND DATEDIFF(IF(ISNULL(specials_expire_date),
				   CURRENT_DATE(), specials_expire_date), CURRENT_DATE()) >= 0,
				   specials_price, price) AS price
			FROM   " . TABLE_PRODUCT . "
			WHERE  product_id = :productID
			LIMIT 1";
	$sql = $db->bindVars($sql, ':productID', $product_id, 'integer');
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) {
		return array(
			'product_id' => $result->fields['product_id'],
			'sku' => $result->fields['sku'],
			'name' => $result->fields['name'],
			'image' => $result->fields['image'],
			'price' => $result->fields['price']
		);
	}
	
	return false;
}

function get_product_cid($product_id)
{
	global $db;
	$sql = "SELECT master_category_id
			FROM   " . TABLE_PRODUCT . "
			WHERE  product_id = :productID
			LIMIT 1";
	$sql = $db->bindVars($sql, ':productID', $product_id, 'integer');
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) {
		return $result->fields['master_category_id'];
	}
	
	return false;
}

function has_product_color($sku)
{
	global $db;
	$sku_array = explode("-", $sku);
	if (count($sku_array) > 1) {
		$sql = "SELECT COUNT(*) AS total
				FROM   " . TABLE_PRODUCT . "
				WHERE  sku LIKE ':sku-%'
				AND    in_stock = 1
				AND    status = 1";
		$sql = $db->bindVars($sql, ':sku', $sku_array[1], 'noquotestring');
		$result = $db->Execute($sql);
		
		if ($result->fields['total'] > 1) {
			return true;
		}
	}
	
	return false;
}

function get_product_color($sku)
{
	global $db;
	$color = array();
	$sku_array = explode("-", $sku);
	if (count($sku_array) > 1) {
		$sql = "SELECT product_id, image
				FROM   " . TABLE_PRODUCT . "
				WHERE  sku LIKE ':sku-%'
				AND    in_stock = 1
				AND    status = 1
				ORDER BY sort_order, name";
		$sql = $db->bindVars($sql, ':sku', $sku_array[0], 'noquotestring');
		$result = $db->Execute($sql);
		while (!$result->EOF) {
			$color[] = array(
				'product_id'   => $result->fields['product_id'],
				'image'        => $result->fields['image']
			);
			$result->MoveNext();
		}
	}
	
	return $color;
}

function get_product_group($group_name)
{
	global $db;
	$product_group = array();
	if ($group_name != '0') {
		$sql = "SELECT product_id, image
				FROM   " . TABLE_PRODUCT . "
				WHERE  group_name = ':group_name'
				AND    in_stock = 1
				AND    status = 1
				ORDER BY sort_order, name";
		$sql = $db->bindVars($sql, ':group_name', $group_name, 'noquotestring');
		$result = $db->Execute($sql);
		while (!$result->EOF) {
			$product_group[] = array(
				'product_id'   => $result->fields['product_id'],
				'image'        => $result->fields['image']
			);
			$result->MoveNext();
		}
	}

	return $product_group;
}

function has_product_attribute($product_id)
{
	global $db;
	$sql = "SELECT COUNT(*) AS total
			FROM   " . TABLE_PRODUCT_ATTRIBUTE . "
			WHERE  product_id = :productID";
	$sql = $db->bindVars($sql, ':productID', $product_id, 'integer');
	$result = $db->Execute($sql);
	if ($result->fields['total'] > 0) {
		return true;
	}
	
	return false;
}

function get_product_attribute($product_id)
{
	global $db;
	$attribute = array();
	$sql = "SELECT DISTINCT po.product_option_id,
				   po.type, po.name, pa.required, po.sort_order
			FROM   " . TABLE_PRODUCT_OPTION . " po, " . TABLE_PRODUCT_ATTRIBUTE . " pa
			WHERE  po.product_option_id = pa.product_option_id
			AND    pa.product_id = :productID
			ORDER BY po.sort_order";
	$sql = $db->bindVars($sql, ':productID', $product_id, 'integer');
	$optionResult = $db->Execute($sql, false, true, 604800);
	while (!$optionResult->EOF) {
		if ($optionResult->fields['type'] == 'text') {
			$sql = "SELECT product_option_value_id, '' name, price, price_prefix
					FROM   " . TABLE_PRODUCT_ATTRIBUTE . "
					WHERE  product_id = :productID
					AND    product_option_id = :productOptionID";
		} else {
			$sql = "SELECT pov.product_option_value_id, pov.name, pa.price, pa.price_prefix
					FROM   " . TABLE_PRODUCT_OPTION_VALUE . " pov, " . TABLE_PRODUCT_ATTRIBUTE . " pa
					WHERE  pov.product_option_value_id = pa.product_option_value_id
					AND    pa.product_id = :productID
					AND    pa.product_option_id = :productOptionID
					ORDER BY pov.sort_order";
		}
		$sql = $db->bindVars($sql, ':productID', $product_id, 'integer');
		$sql = $db->bindVars($sql, ':productOptionID', $optionResult->fields['product_option_id'], 'integer');
		$valueResult = $db->Execute($sql, false, true, 604800);
		$optionValue = array();
		while (!$valueResult->EOF) {
			$optionValue[$valueResult->fields['product_option_value_id']] = array(
				'product_option_value_id' => $valueResult->fields['product_option_value_id'],
				'name'                    => $valueResult->fields['name'],
				'price'                   => $valueResult->fields['price'],
				'price_prefix'            => $valueResult->fields['price_prefix']
			);
			$valueResult->MoveNext();
		}
		$attribute[$optionResult->fields['product_option_id']] =array(
			'product_option_id'    => $optionResult->fields['product_option_id'],
			'type'                 => $optionResult->fields['type'],
			'name'                 => $optionResult->fields['name'],
			'required'             => $optionResult->fields['required'],
			'value'                => $optionValue
		);
		$optionResult->MoveNext();
	}

	return $attribute;
}

function get_product_option($product_option_id)
{
	global $db;
	$sql = "SELECT type, name
			FROM   " . TABLE_PRODUCT_OPTION . "
			WHERE  product_option_id = :product_option_id
			LIMIT 1";
	$sql = $db->bindVars($sql, ':product_option_id', $product_option_id, 'integer');
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) {
		return array(
			'type' => $result->fields['type'],
			'name' => $result->fields['name']
		);
	}
	
	return false;
}

function get_product_option_value($product_option_value_id, $product_option_id)
{
	global $db;
	$sql = "SELECT name
			FROM   " . TABLE_PRODUCT_OPTION_VALUE . "
			WHERE  product_option_value_id = :product_option_value_id
			AND    product_option_id = :product_option_id
			LIMIT 1";
	$sql = $db->bindVars($sql, ':product_option_value_id', $product_option_value_id, 'integer');
	$sql = $db->bindVars($sql, ':product_option_id', $product_option_id, 'integer');
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) {
		return array(
			'name' => $result->fields['name']
		);
	}
	
	return false;
}

function get_product_review($product_id)
{
	global $db;
	$sql = "SELECT AVG(rating) AS average, COUNT(*) AS total
			FROM   " . TABLE_PRODUCT_REVIEW . "
			WHERE  status = 1
			AND    product_id = :productID
			LIMIT 1";
	$sql = $db->bindVars($sql, ':productID', $product_id, 'integer');
	$result = $db->Execute($sql);
	return array(
		'average' => $result->fields['average'],
		'total'   => $result->fields['total'],
		'rating'  => round($result->fields['average'])
	);
}

function getProductReview($productIds)
{
	if (empty($productIds)) {
		return array();
	}
	if (!is_array($productIds)) {
		$productIds = array($productIds);
	}
	
	global $db;
	$sql = "SELECT product_id, AVG(rating) AS average, COUNT(*) AS total
			FROM   " . TABLE_PRODUCT_REVIEW . "
			WHERE  status = 1
			AND    product_id IN (:productIDS)
			GROUP BY product_id";
	$sql = $db->bindVars($sql, ':productIDS', implode(',', $productIds), 'noquotestring');
	$result = $db->Execute($sql, false, true, 604800);
	$review = array();
	while (!$result->EOF) {
		$review[$result->fields['product_id']] = array(
			'average' => $result->fields['average'],
			'total'   => $result->fields['total'],
			'rating'  => round($result->fields['average'])
		);
		$result->MoveNext();
	}
	return $review;
}

function upid($pid, $params=null)
{
	$upid = $pid;
	if (is_array($params) && !strstr($pid, ':')) {
		while (list($option, $value) = each($params)) {
			if (is_array($value)) {
				while (list($opt, $val) = each($value)) {
					$upid = $upid . '{' . $option . '}' . trim($opt);
				}
			} else {
				$upid = $upid . '{' . $option . '}' . trim($value);
			}
		}
		$md_upid = '';
		$md_upid = md5($upid);
		return $pid . ':' . $md_upid;
	}
	
	return $pid;
}

function pid($upid)
{
	$pieces = explode(':', $upid);
	
	return $pieces[0];
}

function get_additional_image($image)
{
	$additionalImage = array();
	if (!defined('ADDITIONAL_IMAGE_LIMIT')) define('ADDITIONAL_IMAGE_LIMIT', '4');
	if (isset($image) && not_null($image)) {
		$additional_image_extension = substr($image, strrpos($image, '.'));
		$additional_image_base = str_replace($additional_image_extension, '', $image) . '_';
		
		$additional_image_directory = str_replace($image, '', substr($image, strrpos($image, '/')));
		if ($additional_image_directory != '') {
			$additional_image_directory =  str_replace($additional_image_directory, '', $image) . "/";
		}
		
		if (strrpos($image, '/')) {
			$additional_image_match = substr($image, strrpos($image, '/') + 1);
			$additionalImage[] = $additional_image_directory . $additional_image_match;
			$additional_image_match = str_replace($additional_image_extension, '', $additional_image_match) . '_';
			$additional_image_base = $additional_image_match;
		} else {
			$additionalImage[] = $image;
		}
		if ($dir = @dir(ROOT_IMAGE . $additional_image_directory)) {
			while ($file = $dir->read()) {
				if (!is_dir(ROOT_IMAGE . $additional_image_directory . $file)) {
					if (substr($file, strrpos($file, '.')) == $additional_image_extension) {
						if (preg_match("/" . $additional_image_base . "/i", $file) == 1) {
							if ($file != $image) {
								if ($additional_image_base . str_replace($additional_image_base, '', $file) == $file) {
		                			if (count($additionalImage) >= ADDITIONAL_IMAGE_LIMIT) break;
									$additionalImage[] = $additional_image_directory . $file;
								}
							}
						}
					}
				}
			}
			sort($additionalImage);
		    $dir->close();
		} elseif ($dir = @dir(DIR_FS_CATALOG_IMAGES . $additional_image_directory)) {
		    while ($file = $dir->read()) {
				if (!is_dir(DIR_FS_CATALOG_IMAGES . $additional_image_directory . $file)) {
					if (substr($file, strrpos($file, '.')) == $additional_image_extension) {
						if (preg_match("/" . $additional_image_base . "/i", $file) == 1) {
							if ($file != $image) {
								if ($additional_image_base . str_replace($additional_image_base, '', $file) == $file) {
		                			if (count($additionalImage) >= ADDITIONAL_IMAGE_LIMIT) break;
									$additionalImage[] = $additional_image_directory . $file;
								}
							}
						}
					}
				}
			}
			sort($additionalImage);
		    $dir->close();
		}
	}
	
	return $additionalImage;
}

function get_small_image($src, $width=0, $height=0)
{
	return get_image($src, $width, $height, false);
}

function get_large_image($src, $width=0, $height=0)
{
	return get_image($src, $width, $height, true);
}

function get_image($src, $width=0, $height=0, $watermark=false)
{
	if (is_file(ROOT_IMAGE . $src)
		&& USE_IMAGE_CACHE == 1) {
		// create an image of the given filetype
		$file_ext = substr($src, strrpos($src, '.'));
		$oldfile = ROOT_IMAGE . $src;
		$oldsrc = DIR_WS_TEMPLATE_IMAGES . 'no_image.jpg';
		$filename = get_cache_name($oldfile . 'image' . $width . 'x' . $height . $file_ext);
		$newfile = DIR_FS_CATALOG_IMAGES_CACHE . $filename;
		$newsrc = DIR_WS_CATALOG_IMAGES_CACHE . $filename;
		if (is_file($newfile)) {
			return $newsrc;
		} elseif (run_imageGD($oldfile, $width, $height, $watermark)) {
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
			} elseif (run_imageGD($oldfile, $width, $height, $watermark)) {
				return $newsrc;
			}
		}
	} else {
		$oldsrc = DIR_WS_TEMPLATE_IMAGES . 'no_image.jpg';
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

function run_imageGD($oldfile, $width=0, $height=0, $watermark=false)
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
    
    if ($watermark == true && $watermarkimg = load_imageGD(DIR_WS_TEMPLATE_IMAGES . 'watermark.png')) {
    	$watermarkwidth = imagesx($watermarkimg);
    	$watermarkheight = imagesy($watermarkimg);
	    imagealphablending($newimg, true);
	    @imagecopyresampled($newimg, $watermarkimg, 0, 0, 0, 0, $newwidth, $newheight, $watermarkwidth, $watermarkheight);
	    imagedestroy($watermarkimg);
    }
    
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
