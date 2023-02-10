<body class="<?php echo str_replace('_', '', $current_page) . 'Body'; ?>">
<div class="wrapper">
	<?php require($template->get_template_dir('tpl_noscript.php', DIR_WS_TEMPLATE, $current_page, 'common') . 'tpl_noscript.php'); ?>
	<?php require($template->get_template_dir('tpl_header.php', DIR_WS_TEMPLATE, $current_page, 'common') . 'tpl_header.php'); ?>
	<div class="main-container">
		<?php require($template->get_template_dir('tpl_account.php', DIR_WS_TEMPLATE, $current_page, 'sidebar') . 'tpl_account.php'); ?>
		<?php require($template->get_template_dir('tpl_' . $current_page . '_default.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_' . $current_page . '_default.php'); ?>
	</div>
	<?php require($template->get_template_dir('tpl_footer.php', DIR_WS_TEMPLATE, $current_page, 'common') . 'tpl_footer.php'); ?>
</div>
</body>
