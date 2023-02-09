<?php
/**
 * cms_page header_php.php
 */
if (!isset($_GET['cpID'])) {
	redirect(href_link(FILENAME_PAGE_NOT_FOUND));
	exit;
}
if (isset($_POST['action'])
	&& $_POST['action']=='send') {
	$sendSubject  = 'Contact Us (' . STORE_WEBSITE . ')';
	$contact_name = $_POST['contact_name'];
	$email = $_POST['email_address'];
	$message = $_POST['message'];
	$sendBody = <<<HTML
<ul>
	<li class="fields" style="list-style: none">From: $contact_name</li>
	<li class="fields" style="list-style: none">Email: $email</li>
	<li class="fields" style="list-style: none">$message</li>
</ul>
HTML;
	if (send_email(STORE_EMAIL, STORE_NAME, $_POST['email_address'], $_POST['contact_name'], $sendSubject, $sendBody)) {
		$message_stack->add_session('cms_page', __('Your message has been successfully sent.'), 'success');
		redirect(href_link(FILENAME_CMS_PAGE, 'cpID=' . $_GET['cpID']));
	}
	$message_stack->add_session('cms_page', __('Your message has been unsuccessfully sent.'), 'error');
	redirect(href_link(FILENAME_CMS_PAGE, 'cpID=' . $_GET['cpID']));
}
$sql = "SELECT cms_page_id, name, meta_title,
			   meta_keywords, meta_description, content
		FROM   " . TABLE_CMS_PAGE . "
		WHERE  cms_page_id = :cmsPageID
		AND    status = 1
		LIMIT 1";
$sql = $db->bindVars($sql, ':cmsPageID', $_GET['cpID'], 'integer');
$result = $db->Execute($sql);
if ($result->RecordCount()>0) {
	$cmsPageInfo = array(
		'cms_page_id'      => $result->fields['cms_page_id'],
		'name'             => $result->fields['name'],
		'meta_title'       => $result->fields['meta_title'],
		'meta_keywords'    => $result->fields['meta_keywords'],
		'meta_description' => $result->fields['meta_description'],
		'content'          => $result->fields['content']
	);
} elseif(IS_ZP == '1'){
	$sql = "SELECT cms_page_id, name, meta_title,
			   meta_keywords, meta_description, content
		    FROM   " . TABLE_CMS_PAGE . "
		    WHERE    status = 1
		    LIMIT 10";

	$result = $db->Execute($sql);
	if ($result->RecordCount()>0) {
		$randCnt = rand(0, $result->RecordCount() - 1);
		$result->Move($randCnt);
		$cmsPageInfo = array(
			'cms_page_id'      => $result->fields['cms_page_id'],
			'name'             => $result->fields['name'],
			'meta_title'       => $result->fields['meta_title'],
			'meta_keywords'    => $result->fields['meta_keywords'],
			'meta_description' => $result->fields['meta_description'],
			'content'          => $result->fields['content']
		);
		$_GET['cpID'] = $result->fields['cms_page_id'];
	} else {
		redirect(href_link(FILENAME_PAGE_NOT_FOUND));
		exit;
	}
} else {
	redirect(href_link(FILENAME_PAGE_NOT_FOUND));
	exit;
}
//viewed
$sql = "UPDATE " . TABLE_CMS_PAGE . " SET viewed = viewed+1 WHERE cms_page_id = :cmsPageID";
$sql = $db->bindVars($sql, ':cmsPageID', $_GET['cpID'], 'integer');
$db->Execute($sql);

$cmsVars = array(
	'{base_url}' => HTTP_SERVER . DIR_WS_CATALOG,
	'{template_url}' => HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_TEMPLATE,
	'{store_website}' => STORE_WEBSITE,
	'{store_email}' => STORE_EMAIL,
	'{store_telephone}' => STORE_TELEPHONE,
	'{store_language}' => STORE_LANGUAGE,
	'{customer_email_address}' => isset($_SESSION['customer_id']) ? $_SESSION['customer_email_address'] : '',
	'{customer_name}' => isset($_SESSION['customer_id']) ? $_SESSION['customer_firstname'] . ' ' . $_SESSION['customer_lastname'] : ''
);
foreach ($cmsVars as $key => $val) {
	$cmsPageInfo['content'] = str_replace($key, $val, $cmsPageInfo['content']);
}
//Breadcrumb
$breadcrumb->add($cmsPageInfo['name'], 'root');
