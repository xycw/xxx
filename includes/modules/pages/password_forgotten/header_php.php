<?php
/**
 * password_forgotten header_php.php
 */
if (isset($_POST['email_address']) && !empty($_POST['email_address'])) {
	$toMail = db_prepare_input($_POST['email_address']);
	if (validate_email($toMail)) {
		$sql = "SELECT *
                FROM   " . TABLE_CUSTOMER . "
                WHERE  email_address = :emailAddress
                LIMIT  1";
		$sql = $db->bindVars($sql, ':emailAddress', $toMail, 'string');
		$result = $db->Execute($sql);
		if ($result->RecordCount() > 0) {
			$toName = $result->fields['firstname'] . ' ' . $result->fields['lastname'];;
			$randomPassword = substr(md5(microtime()), 0, 7);
			$storeWebsite = STORE_WEBSITE;
			$sendSubject = 'Password Forgotten (' . $storeWebsite . ')';
			$sendBody = <<<HTML
<div style="width: 560px; margin: 0 auto;">
<p><strong>Your $storeWebsite Password</strong></p>
<p>$toName</p><br>
<p>A new password was requested for your $storeWebsite account.<br><br>
Your new password is: $randomPassword<br><br>
After you have logged in using the new password, we recommend you go to My
Orders and enter Account Settings to change it.<br><br>
Sincerely,<br>
$storeWebsite</p>
</div>
HTML;
			if(send_email($toMail, $toName, STORE_EMAIL, STORE_NAME, $sendSubject, $sendBody)){
				$temp = encrypt_password($randomPassword);
				$sql = "UPDATE " . TABLE_CUSTOMER . " SET password = '{$temp}', last_modified = NOW() WHERE customer_id = '". $result->fields['customer_id'] ."'";
				$db->Execute($sql);
				$message_stack->add_session('login', __('A new password has been sent to your email address.'), 'success');
				redirect(href_link(FILENAME_LOGIN, '', 'SSL'));
			} else {
				$message_stack->add('password_forgotten', __('The Email sending failed; please try again.'));
			}
		} else {
			$message_stack->add('password_forgotten', __('The Email Address was not found in our records; please try again.'));
		}
	} else {
		$message_stack->add('password_forgotten', __('Email format error.'));
	}
}
//Breadcrumb
$breadcrumb->add(__('Password Forgotten'), 'root');
