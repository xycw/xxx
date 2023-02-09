<?php require('includes/application_top.php'); ?>
<?php
unset($_SESSION['admin']);
redirect(href_link(FILENAME_LOGIN));
?>
<?php require('includes/application_bottom.php'); ?>