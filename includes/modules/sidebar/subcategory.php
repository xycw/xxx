<?php
/**
 * sidebar subcategory.php
 */
$sideberCategoryID = 0;
if (isset($_GET['cID'])) {
	if (count($category_tree->getSubcategories('', $_GET['cID'])) > 0) {
		$sideberCategoryID = $_GET['cID'];
	} else {
		$sideberCategoryID = $category_tree->getParcategory($_GET['cID']);
	}
}