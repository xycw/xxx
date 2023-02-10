<?php
/**
 * init_includes init_shopping_cart.php
 */
if (!isset($_SESSION['shopping_cart'])) $_SESSION['shopping_cart'] = new shopping_cart();
//提交购物车操作
if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		case 'update_product' :
			$_SESSION['shopping_cart']->actionUpdateProduct();
		break;
		case 'add_product' :
			$_SESSION['shopping_cart']->actionAddProduct();
		break;
		case 'buy_now' :
			$_SESSION['shopping_cart']->actionBuyNow();
		break;
		case 'remove_product' :
			$_SESSION['shopping_cart']->actionRemoveProduct();
		break;
		case 'empty_cart' :
			$_SESSION['shopping_cart']->reset(true);
		break;
		case 'add_coupon' :
			$_SESSION['shopping_cart']->actionAddCoupon();
		break;
		case 'remove_coupon' :
			$_SESSION['shopping_cart']->actionRemoveCoupon();
		break;
	}
}
