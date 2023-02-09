<?php
/**
 * init_includes init_currency.php
 */
if (!isset($_SESSION['currency']) && !isset($_GET['currency'])) $_SESSION['currency'] = STORE_CURRENCY;

$new_currency = (isset($_GET['currency'])) ? currency_exists($_GET['currency']) : currency_exists($_SESSION['currency']);

if ($new_currency==false) $new_currency = currency_exists(STORE_CURRENCY, true);

if (isset($_GET['currency'])) {
	$_SESSION['currency'] = $new_currency;
	if ($current_page != FILENAME_CHECKOUT_RESULT) redirect(href_link($current_page, get_all_get_params(array('currency'))));
}

require(DIR_FS_CATALOG_CLASSES . 'currencies.php');
$currencies = new currencies($_SESSION['currency']);
