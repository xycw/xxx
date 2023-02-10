<?php
/**
 * address header_php.php
 */
if (!isset($_SESSION['customer_id'])) {
	redirect(href_link(FILENAME_LOGIN, '', 'SSL'));
}
//Delete Address
if (isset($_GET['delete'])) {
	$sql = "DELETE a
			FROM   " . TABLE_ADDRESS . " a, " . TABLE_CUSTOMER . " c
			WHERE  a.customer_id = c.customer_id
			AND    a.address_id = :addressID
			AND    c.customer_id = :customerID
			AND    a.address_id <> c.billing_address_id
			AND    a.address_id <> c.shipping_address_id";
	$sql = $db->bindVars($sql, ':addressID', $_GET['delete'], 'integer');
	$sql = $db->bindVars($sql, ':customerID', $_SESSION['customer_id'], 'integer');
	$db->Execute($sql);
	$message_stack->add_session('address', 'The address has been deleted.', 'success');
	redirect(href_link(FILENAME_ADDRESS, '', 'SSL'));
}
//Default Address
$billingAddress = get_address($_SESSION['customer_billing_address_id'], $_SESSION['customer_id']);
$shippingAddress = get_address($_SESSION['customer_shipping_address_id'], $_SESSION['customer_id']);
//Additional Address
$sql = "SELECT a.address_id, a.firstname, a.lastname, a.company,
			   a.street_address, a.suburb, a.city, a.region_id, a.region,
			   a.postcode, a.country_id, a.country, a.telephone, a.fax
		FROM   " . TABLE_ADDRESS . " a, " . TABLE_CUSTOMER . " c
		WHERE  a.customer_id = c.customer_id
		AND    c.customer_id = :customerID
		AND    a.address_id <> c.billing_address_id
		AND    a.address_id <> c.shipping_address_id";
$sql = $db->bindVars($sql, ':customerID', $_SESSION['customer_id'], 'integer');
$result = $db->Execute($sql);
$additionalAddressList = array();
while (!$result->EOF) {
	$additionalAddressList[] = array(
		'address_id'     => $result->fields['address_id'],
		'firstname'      => $result->fields['firstname'],
		'lastname'       => $result->fields['lastname'],
		'company'        => $result->fields['company'],
		'street_address' => $result->fields['street_address'],
		'suburb'         => $result->fields['suburb'],
		'city'           => $result->fields['city'],
		'region_id'      => $result->fields['region_id'],
		'region'         => $result->fields['region'],
		'postcode'       => $result->fields['postcode'],
		'country_id'     => $result->fields['country_id'],
		'country'        => $result->fields['country'],
		'telephone'      => $result->fields['telephone'],
		'fax'            => $result->fields['fax']
	);
	$result->MoveNext();
}
//Breadcrumb
$breadcrumb->add(__('My Dashboard'), 'sub', href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(__('Address Book'), 'root');
