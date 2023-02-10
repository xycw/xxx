<?php
/**
 * search header_php.php
 */

function parse_search_string($search_str = '', &$objects) {
	$search_str = trim(strtolower($search_str));
	// Break up $search_str on whitespace; quoted string will be reconstructed later
	$pieces = preg_split('/[[:space:]]+/', $search_str);
	$objects = array();
	$tmpstring = '';
	$flag = '';
	for ($k=0; $k<count($pieces); $k++) {
		while (substr($pieces[$k], 0, 1) == '(') {
			$objects[] = '(';
			if (strlen($pieces[$k]) > 1) {
				$pieces[$k] = substr($pieces[$k], 1);
			} else {
				$pieces[$k] = '';
			}
		}
		$post_objects = array();
		while (substr($pieces[$k], -1) == ')') {
			$post_objects[] = ')';
			if (strlen($pieces[$k]) > 1) {
				$pieces[$k] = substr($pieces[$k], 0, -1);
			} else {
				$pieces[$k] = '';
			}
		}
		// Check individual words
		if ( (substr($pieces[$k], -1) != '"') && (substr($pieces[$k], 0, 1) != '"') ) {
			$objects[] = trim($pieces[$k]);
			for ($j=0; $j<count($post_objects); $j++) {
				$objects[] = $post_objects[$j];
			}
		} else {
			/* This means that the $piece is either the beginning or the end of a string.
			So, we'll slurp up the $pieces and stick them together until we get to the
			end of the string or run out of pieces.
			*/
			// Add this word to the $tmpstring, starting the $tmpstring
			$tmpstring = trim(preg_replace('/"/', ' ', $pieces[$k]));
			// Check for one possible exception to the rule. That there is a single quoted word.
			if (substr($pieces[$k], -1 ) == '"') {
				// Turn the flag off for future iterations
				$flag = 'off';
				$objects[] = trim($pieces[$k]);
				for ($j=0; $j<count($post_objects); $j++) {
					$objects[] = $post_objects[$j];
				}
				unset($tmpstring);
				// Stop looking for the end of the string and move onto the next word.
				continue;
			}
			// Otherwise, turn on the flag to indicate no quotes have been found attached to this word in the string.
			$flag = 'on';
			// Move on to the next word
			$k++;
			// Keep reading until the end of the string as long as the $flag is on
			while ( ($flag == 'on') && ($k < count($pieces)) ) {
				while (substr($pieces[$k], -1) == ')') {
					$post_objects[] = ')';
					if (strlen($pieces[$k]) > 1) {
						$pieces[$k] = substr($pieces[$k], 0, -1);
					} else {
						$pieces[$k] = '';
					}
				}
				// If the word doesn't end in double quotes, append it to the $tmpstring.
				if (substr($pieces[$k], -1) != '"') {
					// Tack this word onto the current string entity
					$tmpstring .= ' ' . $pieces[$k];
					// Move on to the next word
					$k++;
					continue;
				} else {
					/* If the $piece ends in double quotes, strip the double quotes, tack the
					$piece onto the tail of the string, push the $tmpstring onto the $haves,
					kill the $tmpstring, turn the $flag "off", and return.
					*/
					$tmpstring .= ' ' . trim(preg_replace('/"/', ' ', $pieces[$k]));
					// Push the $tmpstring onto the array of stuff to search for
					$objects[] = trim($tmpstring);
					for ($j=0; $j<count($post_objects); $j++) {
						$objects[] = $post_objects[$j];
					}
					unset($tmpstring);
					// Turn off the flag to exit the loop
					$flag = 'off';
				}
			}
		}
	}
	// add default logical operators if needed
	$temp = array();
	for ($i=0; $i<(count($objects)-1); $i++) {
		$temp[] = $objects[$i];
		if ( ($objects[$i] != 'and') &&
			($objects[$i] != 'or') &&
			($objects[$i] != '(') &&
			($objects[$i+1] != 'and') &&
			($objects[$i+1] != 'or') &&
			($objects[$i+1] != ')') ) {
			$temp[] = SEARCH_DEFAULT_OPERATOR;
		}
	}
	$temp[] = $objects[$i];
	$objects = $temp;
	$keyword_count = 0;
	$operator_count = 0;
	$balance = 0;
	for ($i=0; $i<count($objects); $i++) {
		if ($objects[$i] == '(') $balance --;
		if ($objects[$i] == ')') $balance ++;
		if ( ($objects[$i] == 'and') || ($objects[$i] == 'or') ) {
			$operator_count ++;
		} elseif ( (is_string($objects[$i]) && $objects[$i] == '0') || ($objects[$i]) && ($objects[$i] != '(') && ($objects[$i] != ')') ) {
			$keyword_count ++;
		}
	}
	if ( ($operator_count < $keyword_count) && ($balance == 0) ) {
		return true;
	} else {
		return false;
	}
}

$_GET['q'] = isset($_GET['q']) ? trim($_GET['q']) : '';
$productListQuery = "SELECT p.product_id, p.name, p.short_description, p.image, p.price,
					 p.specials_price, p.specials_expire_date, p.in_stock, p.filter_1
					 FROM   " . TABLE_PRODUCT . " p
					 WHERE  p.status = 1 " . (MAIN_PRODUCT_ONLY == 1 ? (IS_ZP == '1'? "":" AND p.main_sku=1 ") : '') ;

if (not_null($_GET['q'])
	&& parse_search_string(stripslashes($_GET['q']), $searchKeywords)) {
	$productListQuery .= ' AND (';
	foreach ($searchKeywords as $val) {
		switch ($val) {
			case '(':
			case ')':
			case 'and':
			case 'or':
			$productListQuery .= ' ' . $val . ' ';
			break;
			default:
			$productListQuery .= "(p.name LIKE '%:keywords%' OR p.sku LIKE '%:keywords%'";
			$productListQuery = $db->bindVars($productListQuery, ':keywords', $val, 'noquotestring');

			$productListQuery .= " OR (p.short_description LIKE '%:keywords%' AND p.short_description !='')";
			$productListQuery = $db->bindVars($productListQuery, ':keywords', $val, 'noquotestring');

			$productListQuery .= " OR (p.meta_title LIKE '%:keywords%' AND p.meta_title !='')";
			$productListQuery = $db->bindVars($productListQuery, ':keywords', $val, 'noquotestring');

			$productListQuery .= " OR (p.meta_keywords LIKE '%:keywords%' AND p.meta_keywords !='')";
			$productListQuery = $db->bindVars($productListQuery, ':keywords', $val, 'noquotestring');

			$productListQuery .= " OR (p.meta_description LIKE '%:keywords%' AND p.meta_description !='')";
			$productListQuery = $db->bindVars($productListQuery, ':keywords', $val, 'noquotestring');

			$productListQuery .= ')';
			break;
		}
	}

	$productListQuery .= ')';

	//subcategories
	if (isset($_GET['cID']) && not_null($_GET['cID'])) {
		$subcategories = $category_tree->getSubcategories('', $_GET['cID']);
		if (count($subcategories) > 0) {
			$sql = "SELECT product_id FROM " . TABLE_PRODUCT_TO_CATEGORY . " WHERE category_id IN (:categoryIDS)";
			$sql = $db->bindVars($sql, ':categoryIDS', implode(',', $subcategories), 'noquotestring');
		} else {
			$sql = "SELECT product_id FROM " . TABLE_PRODUCT_TO_CATEGORY . " WHERE category_id = :categoryID";
			$sql = $db->bindVars($sql, ':categoryID', $_GET['cID'], 'integer');
		}
		$productListQuery .= " AND p.product_id IN ({$sql})";
	}

	$sql = "SELECT COUNT(*) AS total
			FROM   " . TABLE_POPULAR_SEARCH . "
			WHERE  search = :search";
	$sql = $db->bindVars($sql, ':search', $_GET['q'], 'string');
	$popularResult = $db->Execute($sql);
	if ($popularResult->fields['total']>0) {
		$db->Execute("UPDATE " . TABLE_POPULAR_SEARCH . " SET freq = freq + 1 WHERE search = '". db_input($_GET['q']) . "'");
	} else {
		$db->Execute("INSERT INTO " . TABLE_POPULAR_SEARCH . " (search) VALUES ('" . db_input($_GET['q']) . "')");
	}
} else {
	$productListQuery .= " AND p.product_id = 0";
}

//Breadcrumb
$breadcrumb->add(__('Search results for: "%s"', $_GET['q']), 'root');
