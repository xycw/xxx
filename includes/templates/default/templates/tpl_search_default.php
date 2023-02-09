<div class="page-title">
    <h1><?php echo __('Search results for: "%s"', $_GET['q']); ?></h1>
</div>
<?php require($template->get_template_dir('tpl_modules_product_list.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_product_list.php'); ?>
