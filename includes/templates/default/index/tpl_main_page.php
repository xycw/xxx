<body class="<?php echo str_replace('_', '', $current_page) . 'Body'; ?>">
<div class="wrapper">
	<?php require($template->get_template_dir('tpl_noscript.php', DIR_WS_TEMPLATE, $current_page, 'common') . 'tpl_noscript.php'); ?>
	<div class="page">
    	<?php require($template->get_template_dir('tpl_header.php', DIR_WS_TEMPLATE, $current_page, 'common') . 'tpl_header.php'); ?>
    	<?php require($template->get_template_dir('tpl_header_banner.php', DIR_WS_TEMPLATE, $current_page, 'common') . 'tpl_header_banner.php'); ?>
    	<div class="main-container col2-left-layout">
        	<div class="main">
                <div class="col-main">
					<?php require($template->get_template_dir('tpl_' . $current_page . '_default.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_' . $current_page . '_default.php'); ?>
                </div>
                <div class="col-left sidebar">
                	<?php require($template->get_template_dir('tpl_col_left.php', DIR_WS_TEMPLATE, $current_page, 'common') . 'tpl_col_left.php'); ?>
                </div>
            </div>
        </div>
        <?php require($template->get_template_dir('tpl_why_buy_from_us.php', DIR_WS_TEMPLATE, $current_page, 'common') . 'tpl_why_buy_from_us.php'); ?>
        <?php require($template->get_template_dir('tpl_footer.php', DIR_WS_TEMPLATE, $current_page, 'common') . 'tpl_footer.php'); ?>
    </div>
</div>
</body>
