<?php
/**
 * sideber account.php
 */
$accountSidebarList = array();
if (substr($current_page, 0, 7) == 'account'
	|| substr($current_page, 0, 7) == 'address') {
	if ($current_page == FILENAME_ACCOUNT) {
		$accountSidebarList[] = '<strong>' . __('Account Dashboard') . '</strong>';
	} else {
		$accountSidebarList[] = '<a href="'.href_link(FILENAME_ACCOUNT, '', 'SSL').'">' . __('Account Dashboard') . '</a>';
	}
	if ($current_page == FILENAME_ACCOUNT_EDIT) {
		$accountSidebarList[] = '<strong>' . __('Account Information') . '</strong>';
	} else {
		$accountSidebarList[] = '<a href="'.href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL').'">' . __('Account Information') . '</a>';
	}
	if ($current_page == FILENAME_ADDRESS
		|| $current_page == FILENAME_ADDRESS_EDIT
		|| $current_page == FILENAME_ADDRESS_NEW) {
		$accountSidebarList[] = '<strong>' . __('Address Book') . '</strong>';
	} else {
		$accountSidebarList[] = '<a href="'.href_link(FILENAME_ADDRESS, '', 'SSL').'">' . __('Address Book') . '</a>';
	}
	if ($current_page == FILENAME_ACCOUNT_HISTORY
		|| $current_page == FILENAME_ACCOUNT_HISTORY_INFO) {
		$accountSidebarList[] = '<strong>' . __('My Orders') . '</strong>';
	} else {
		$accountSidebarList[] = '<a href="'.href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL').'">' . __('My Orders') . '</a>';
	}
	if ($current_page == FILENAME_ACCOUNT_REVIEW) {
		$accountSidebarList[] = '<strong>' . __('My Product Reviews') . '</strong>';
	} else {
		$accountSidebarList[] = '<a href="'.href_link(FILENAME_ACCOUNT_REVIEW, '', 'SSL').'">' . __('My Product Reviews') . '</a>';
	}
	if ($current_page == FILENAME_ACCOUNT_NEWSLETTER) {
		$accountSidebarList[] = '<strong>' . __('Newsletter Subscriptions') . '</strong>';
	} else {
		$accountSidebarList[] = '<a href="'.href_link(FILENAME_ACCOUNT_NEWSLETTER, '', 'SSL').'">' . __('Newsletter Subscriptions') . '</a>';
	}
}