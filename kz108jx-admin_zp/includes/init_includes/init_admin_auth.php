<?php
if (!(basename($PHP_SELF) == FILENAME_LOGIN)) {
	if (!isset($_SESSION['admin'])) {
		if (!(basename($PHP_SELF) == FILENAME_PASSWORD_FORGOTTEN)
			&& !(basename($PHP_SELF) == FILENAME_CAPTCHA)
			&& !(basename($PHP_SELF) == FILENAME_GUEST_ORDER)) {
			redirect(href_link(FILENAME_LOGIN));
		}
	}
}
