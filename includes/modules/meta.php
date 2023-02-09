<?php
/**
 * modules meta.php
 */

switch ($current_page) {
	case FILENAME_ACCOUNT:
		$metaInfo['title'] = __('My Dashboard');
		$metaInfo['canonical'] = href_link(FILENAME_ACCOUNT, '', 'SSL');
	break;
	case FILENAME_ACCOUNT_EDIT:
		$metaInfo['title'] = __('Account Information');
		$metaInfo['canonical'] = href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL');
	break;
	case FILENAME_ACCOUNT_HISTORY:
		$metaInfo['title'] = __('My Orders');
		$metaInfo['canonical'] = href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL');
	break;
	case FILENAME_ACCOUNT_HISTORY_INFO:
		$metaInfo['title'] = __('Order #%s - %s', put_orderNO($orderInfo['order_id']), get_order_status_name($orderInfo['order_status_id']));
		$metaInfo['canonical'] = href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'oID=' . $orderInfo['order_id'], 'SSL');
	break;
	case FILENAME_ACCOUNT_NEWSLETTER:
		$metaInfo['title'] = __('Newsletter Subscription');
		$metaInfo['canonical'] = href_link(FILENAME_ACCOUNT_NEWSLETTER, '', 'SSL');
	break;
	case FILENAME_ACCOUNT_REVIEW:
		$metaInfo['title'] = __('My Product Reviews');
		$metaInfo['canonical'] = href_link(FILENAME_ACCOUNT_REVIEW, '', 'SSL');
	break;
	case FILENAME_ADDRESS:
		$metaInfo['title'] = __('Address Book');
		$metaInfo['canonical'] = href_link(FILENAME_ADDRESS, '', 'SSL');
	break;
	case FILENAME_ADDRESS_EDIT:
		$metaInfo['title'] = __('Edit Address');
		$metaInfo['canonical'] = href_link(FILENAME_ADDRESS_EDIT, '', 'SSL');
	break;
	case FILENAME_ADDRESS_NEW:
		$metaInfo['title'] = __('New Address');
		$metaInfo['canonical'] = href_link(FILENAME_ADDRESS_NEW, '', 'SSL');
	break;
	case FILENAME_ALL_PRODUCTS:
		$metaInfo['title'] = __('All Products');
	break;
	case FILENAME_CATEGORY:
		$metaInfo['title'] = $categoryInfo['meta_title'];
		$metaInfo['keywords'] = $categoryInfo['meta_keywords'];
		$metaInfo['description'] = $categoryInfo['meta_description'];
		$metaInfo['canonical'] = href_link(FILENAME_CATEGORY, 'cID=' . $categoryInfo['category_id']);
	break;
	case FILENAME_CHECKOUT:
		$metaInfo['title'] = __('Checkout');
		$metaInfo['canonical'] = href_link(FILENAME_CHECKOUT, '', 'SSL');
	break;
	case FILENAME_CHECKOUT_PROCESS:
		$metaInfo['title'] = __('Checkout Process');
		$metaInfo['canonical'] = href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
	break;
	case FILENAME_CHECKOUT_RESULT:
		$metaInfo['title'] = __('Checkout Result');
		$metaInfo['canonical'] = href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL');
	break;
	case FILENAME_CMS_PAGE:
		$metaInfo['title'] = $cmsPageInfo['meta_title'];
		$metaInfo['keywords'] = $cmsPageInfo['meta_keywords'];
		$metaInfo['description'] = $cmsPageInfo['meta_description'];
		$metaInfo['canonical'] = href_link(FILENAME_CMS_PAGE, 'cpID=' . $cmsPageInfo['cms_page_id']);
	break;
	case FILENAME_CREATE_ACCOUNT:
		$metaInfo['title'] = __('Create New Customer Account');
		$metaInfo['canonical'] = href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL');
	break;
	case FILENAME_FEATURED:
		$metaInfo['title'] = __('Featured');
	break;
	case FILENAME_INDEX:
		$metaInfo['title'] = (IS_ZP == '0') ? HEAD_DEFAULT_TITLE : HEAD_DEFAULT_TITLE_ZP;
		$metaInfo['keywords'] = (IS_ZP == '0') ? HEAD_DEFAULT_KEYWORDS : HEAD_DEFAULT_KEYWORDS_ZP;
		$metaInfo['description'] = (IS_ZP == '0') ? HEAD_DEFAULT_DESCRIPTION : HEAD_DEFAULT_DESCRIPTION_ZP;
		$metaInfo['canonical'] = href_link(FILENAME_INDEX);
	break;
	case FILENAME_LOGIN:
		$metaInfo['title'] = __('Customer Login');
		$metaInfo['canonical'] = href_link(FILENAME_LOGIN, '', 'SSL');
	break;
	case FILENAME_LOGOUT:
		$metaInfo['title'] = __('Logout');
		$metaInfo['canonical'] = href_link(FILENAME_LOGOUT, '', 'SSL');
	break;
	case FILENAME_NEW_PRODUCTS:
		$metaInfo['title'] = __('New Arrivals');
	break;
	case FILENAME_PAGE_NOT_FOUND:
		$metaInfo['title'] = __('Page Not Found');
	break;
	case FILENAME_PRODUCT:
		$metaInfo['title'] = $productInfo['meta_title'];
		$metaInfo['keywords'] = $productInfo['meta_keywords'];
		$metaInfo['description'] = $productInfo['meta_description'];
		$metaInfo['canonical'] = href_link(FILENAME_PRODUCT, 'pID=' . $productInfo['product_id']);
	break;
	case FILENAME_SEARCH:
		$metaInfo['title'] = __('Search results for: "%s"', $_GET['q']);
		$metaInfo['canonical'] = href_link(FILENAME_SEARCH, 'q=' . $_GET['q']);
	break;
	case FILENAME_SHOPPING_CART:
		$metaInfo['title'] = __('Shopping Cart');
		$metaInfo['canonical'] = href_link(FILENAME_SHOPPING_CART, '', 'SSL');
	break;
	case FILENAME_SITE_MAP:
		$metaInfo['title'] = __('Site Map');
	break;
}

if (!isset($metaInfo['title'])||!not_null($metaInfo['title'])) $metaInfo['title'] = (IS_ZP == '0') ? HEAD_DEFAULT_TITLE : HEAD_DEFAULT_TITLE_ZP;
if (!isset($metaInfo['keywords'])||!not_null($metaInfo['keywords'])) $metaInfo['keywords'] = (IS_ZP == '0') ? HEAD_DEFAULT_KEYWORDS : HEAD_DEFAULT_KEYWORDS_ZP;
if (!isset($metaInfo['description'])||!not_null($metaInfo['description'])) $metaInfo['description'] = (IS_ZP == '0') ? HEAD_DEFAULT_DESCRIPTION : HEAD_DEFAULT_DESCRIPTION_ZP;
if (!isset($metaInfo['canonical'])||!not_null($metaInfo['canonical'])) $metaInfo['canonical'] = href_link($current_page);
$metaInfo['title'] = trim(HEAD_TITLE_PREFIX . ' ' . $metaInfo['title'] . ' ' . HEAD_TITLE_SUFFIX);
