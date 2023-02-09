<?php
function validate_customer($customer_id)
{
	global $db;
	$sql = "SELECT count(*) AS total
			FROM   " . TABLE_CUSTOMER . "
			WHERE  status = 1
			AND    customer_id = :customerID";
	$sql = $db->bindVars($sql, ':customerID', $customer_id, 'integer');
	$result = $db->Execute($sql);
	if ($result->fields['total'] > 0) {
		return true;
	}
	
	return false;
}

function validate_password($plain, $encrypted)
{
	if (not_null($plain) && not_null($encrypted)) {
		$stack = explode(':', $encrypted);
		if (sizeof($stack) != 2) return false;
		if (md5($stack[1] . $plain) == $stack[0]) {
			return true;
		}
	}
	
	return false;
}

function encrypt_password($plain)
{
	$password = '';
	for ($i=0; $i<10; $i++) {
		$password .= es_rand();
	}
	$salt = substr(md5($password), 0, 2);
	$password = md5($salt . $plain) . ':' . $salt;
	
	return $password;
}

function get_address($address_id, $customer_id)
{
	global $db;
	$sql = "SELECT address_id, firstname, lastname, company,
				   street_address, suburb, city, region_id, region,
				   postcode, country_id, country, telephone, fax
			FROM   " . TABLE_ADDRESS . "
			WHERE  address_id = :addressID
			AND    customer_id = :customerID
			LIMIT  1";
	$sql = $db->bindVars($sql, ':addressID', $address_id, 'integer');
	$sql = $db->bindVars($sql, ':customerID', $customer_id, 'integer');
	$result = $db->Execute($sql);
	if ($result->RecordCount()) {
		return array(
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
	}
	
	return '';
}

function address_format($address, $type='html')
{
	if (!is_array($address)||!not_null($address)) return '';
	$br = '<br />';
	if ($type=='text') $br = ' ';
	$output = $address['firstname'] . ' ' . $address['lastname'] . $br;
	if ($address['company']!='') $output .= $address['company'] . $br;
	$output .= $address['street_address'] . $br;
	if ($address['suburb']!='') $output .= $address['suburb'] . $br;
	$output .= $address['city'] . ', ';
	if ($address['region']!='') $output .= $address['region'] . ', ';
	$output .= $address['postcode'] . $br;
	$output .= $address['country'] . $br;
	$output .= 'T: ' . $address['telephone'] . $br;
	if ($address['fax']!='') $output .= 'F: ' . $address['fax'];
	
	return $output;
}
