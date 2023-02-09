<?php
class shopping_cart {
	private $_contents = array();
	private $_couponCode = '';
	private $_productAttributeArr = array();

	public function __construct()
	{
		$this->reset();
	}
	
	public function reset()
	{
		$this->_contents = array();
	}
	
	private function addCart($cartpID, $qty=1, $attribute='')
	{
		$pID = pid($cartpID);
		if ($product = get_product($pID)) {
			$this->_contents[$cartpID]['product_id'] = $product['product_id'];
			$this->_contents[$cartpID]['sku']        = $product['sku'];
			$this->_contents[$cartpID]['name']       = $product['name'];
			$this->_contents[$cartpID]['image']      = $product['image'];
			$this->_contents[$cartpID]['qty']        = $qty;
			$this->_contents[$cartpID]['price']      = $product['price'];
			if (is_array($attribute)) {
				foreach ($attribute as $option_id => $option_value_id) {
					$option = $this->_productAttributeArr[$option_id];
					switch ($option['type']) {
						case 'select':
						case 'radio':
						case 'list':
						case 'wholesale':
							if (!$option['required']
								&& !isset($option['value'][$option_value_id])) {
								continue;
							}
							$option_value = $option['value'][$option_value_id];
							eval('$this->_contents[$cartpID][\'price\'] ' . $option_value['price_prefix'] . '=' . $option_value['price'] . ';');
							$option_value_name = $option_value['name'];
						break;
						case 'text':
							$option_value_id = trim($option_value_id);
							if (!$option['required']
								&& empty($option_value_id)) {
								$option_value_name = '';
								continue;
							}
							$option_value = $option['value'][0];
							eval('$this->_contents[$cartpID][\'price\'] ' . $option_value['price_prefix'] . '=' . $option_value['price'] . ';');
							$option_value_name = $option_value_id;
						break;
						case 'checkbox':
							if (!$option['required']
								&& !is_array($option_value_id)) {
								continue;
							}
							$option_value_name = '';
							foreach ($option_value_id as $val) {
								if (!$option['required']
									&& !isset($option['value'][$val])) {
									continue;
								}
								$option_value = $option['value'][$val];
								eval('$this->_contents[$cartpID][\'price\'] ' . $option_value['price_prefix'] . '=' . $option_value['price'] . ';');
								$option_value_name .= (strlen($option_value_name) > 0 ? ';' : '') . $option_value['name'];
							}
						break;
					}
					$this->_contents[$cartpID]['attribute'][$option['name']] = $option_value_name;
				}
			} else {
				$this->_contents[$cartpID]['attribute'] = array();
			}
		}
		$this->cleanup();
	}

	private function updateCart($cartpID, $qty=0)
	{
		$this->_contents[$cartpID]['qty'] = $qty;
		$this->cleanup();
	}
	
	public function actionUpdateProduct()
	{
		global $message_stack;
		foreach ($_POST['cartQty'] as $cartpID => $cartQty) {
			$new_qty = $this->adjustQty($cartQty);
			$this->updateCart($cartpID, $new_qty);
		}
		$message_stack->add_session('shopping_cart', __('Shopping cart have been updated success.'), 'success');
		redirect(href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
	}
	
	public function actionAddProduct()
	{
		global $message_stack;
		if (isset($_POST['pID']) && validate_product($_POST['pID'])) {
			$this->_productAttributeArr = get_product_attribute($_POST['pID']);
			if (isset($_POST['qty']) && is_array($_POST['qty'])
				&& not_null($this->_productAttributeArr)) {
				foreach ($_POST['qty'] as $option_id => $val) {
					if (!isset($this->_productAttributeArr[$option_id])) {
						continue;
					}
					$option = $this->_productAttributeArr[$option_id];
					if ($option['type'] != 'wholesale') {
						continue;
					}
					foreach ($val as $option_value_id => $qty) {
						if (!is_numeric($qty) || $qty < 1
							|| !isset($option['value'][$option_value_id]) ) {
							continue;
						}
						$attribute = $_POST['attribute'];
						$attribute[$option_id] = $option_value_id;
						$cartpID = upid($_POST['pID'], $attribute);
						$new_qty = $this->adjustQty($qty);
						if ($this->inCart($cartpID)) {
							$new_qty = $new_qty + $this->getQty($cartpID);
							$this->updateCart($cartpID, $new_qty);
						} else {
							$this->addCart($cartpID, $new_qty, $attribute);
						}
					}
				}
			} elseif (not_null($this->_productAttributeArr)
				&& (isset($_POST['attribute']) && !$this->validateProductAttribute($_POST['attribute']))) {
				$message_stack->add_session('product', __('Please specify the product\'s required option(s).'), 'note');
				redirect(href_link(FILENAME_PRODUCT, 'pID=' . $_POST['pID']));
			} else {
				$attribute = isset($_POST['attribute']) ? $_POST['attribute'] : '';
				$cartpID = upid($_POST['pID'], $attribute);
				$new_qty = $this->adjustQty($_POST['qty']);
				if ($this->inCart($cartpID)) {
					$new_qty = $new_qty + $this->getQty($cartpID);
					$this->updateCart($cartpID, $new_qty);
				} else {
					$this->addCart($cartpID, $new_qty, $attribute);
				}
			}
		}
		$message_stack->add_session('shopping_cart', __('This good was added to your shopping cart.'), 'success');
		redirect(href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
	}
	
	public function actionBuyNow()
	{
		global $message_stack;
		if (isset($_GET['pID']) && validate_product($_GET['pID'])) {
			$this->_productAttributeArr = get_product_attribute($_GET['pID']);
			if (not_null($this->_productAttributeArr)) {
				$message_stack->add_session('product', __('Please specify the product\'s required option(s).'), 'note');
				redirect(href_link(FILENAME_PRODUCT, 'pID=' . $_GET['pID']));
			} else {
				$cartpID = upid($_GET['pID']);
				$new_qty = 1;
				if ($this->inCart($cartpID)) {
					$new_qty = $new_qty + $this->getQty($cartpID);
					$this->updateCart($cartpID, $new_qty);
				} else {
					$this->addCart($cartpID, $new_qty);
				}
			}
		}
		$message_stack->add_session('shopping_cart', __('This good was added to your shopping cart.'), 'success');
		redirect(href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
	}
	
	public function actionRemoveProduct()
	{
		if (isset($_GET['cartpID']) && $this->inCart($_GET['cartpID'])) {
			$this->remove($_GET['cartpID']);
		}
		redirect(href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
	}
	
	private function cleanup()
	{
		foreach ($this->_contents as $cartpID => $contents) {
			if (!isset($this->_contents[$cartpID]['qty'])
				|| $this->_contents[$cartpID]['qty'] <= 0) {
				unset($this->_contents[$cartpID]);
			} elseif ($this->_contents[$cartpID]['qty'] > 999) {
				$this->_contents[$cartpID]['qty'] = 999;
			}
		}
	}
	
	public function actionAddCoupon()
	{
		global $message_stack;
		$coupon_code = db_prepare_input($_POST['coupon_code']);
		$amount = get_customer_coupon($coupon_code, $this->getItems(), $this->getSubTotal());
		if ($amount > 0) {
			$this->_couponCode = $coupon_code;
			$message_stack->add_session('shopping_cart', __('Coupon code "%s" was applied.', $coupon_code), 'success');
		} else {
			$message_stack->add_session('shopping_cart', __('Coupon code "%s" is not valid.', $coupon_code));
		}
		redirect(href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
	}
	
	public function actionRemoveCoupon()
	{
		global $message_stack;
		$this->_couponCode = '';
		$message_stack->add_session('shopping_cart', __('Coupon code was canceled.'), 'success');
		redirect(href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
	}
	
	public function getSubTotal()
	{
		$sub_total = 0;
		if (is_array($this->_contents)) {
			reset($this->_contents);
			foreach ($this->_contents as $cartpID => $contents) {
				$sub_total += $this->_contents[$cartpID]['qty'] * $this->_contents[$cartpID]['price'];
			}
		}
		return $sub_total;
	}
	
	public function getDiscount()
	{
		$discount = get_shopping_cart_coupon($this->getItems(), $this->getSubTotal());
		return $discount;
	}
	
	public function getCouponCode ()
	{
		return $this->_couponCode;
	}
	
	public function getCouponDiscount()
	{
		$amount = 0;
		if ($this->_couponCode != '') {
			if (!($amount = get_customer_coupon($this->_couponCode, $this->getItems(), $this->getSubTotal()))) {
				$this->_couponCode = '';
			}
		}
		
		return $amount;
	}
	
	public function getItems()
	{
    	$items = 0;
    	if (is_array($this->_contents)) {
      		reset($this->_contents);
      		foreach ($this->_contents as $cartpID => $contents) {
        		$items += $this->getQty($cartpID);
			}
    	}
		return $items;
	}
	
	private function getQty($cartpID)
	{
		if (isset($this->_contents[$cartpID])) {
			return $this->_contents[$cartpID]['qty'];
		} else {
			return 0;
		}
  	}
  	
	private function inCart($cartpID)
	{
		if (isset($this->_contents[$cartpID])) {
			return true;
		} else {
			return false;
		}
		
	}
	
	private function remove($cartpID)
	{
		unset($this->_contents[$cartpID]);
	}
	
	private function adjustQty($old_qty)
	{
		if ($old_qty != round($old_qty)) {
			$new_qty = round($old_qty);
        } else {
			$new_qty = $old_qty;
        }
        return $new_qty;
	}

	private function validateProductAttribute($attribute)
	{
		foreach ($attribute as $option_id => $option_value_id) {
			if (!isset($this->_productAttributeArr[$option_id])) {
				return false;
			}
			$option = $this->_productAttributeArr[$option_id];
			switch ($option['type']) {
				case 'select':
				case 'radio':
				case 'list':
					// 提交的选项值不合法
					if ($option['required']
						&& !isset($option['value'][$option_value_id])) {
						return false;
					}
				break;
				case 'text':
					// 提交的选项值不合法
					$option_value_id = trim($option_value_id);
					if ($option['required']
						&& empty($option_value_id)) {
						return false;
					}
				break;
				case 'checkbox':
					// 提交的复选框的选项值不合法
					if ($option['required']
						&& !is_array($option_value_id)) {
						return false;
					}
					foreach ($option_value_id as $val) {
						// 提交的选项值不合法
						if ($option['required']
							&& !isset($option['value'][$val])) {
							return false;
						}
					}
				break;
			}
		}

		return true;
	}
	
	public function getProduct()
	{
		if (!is_array($this->_contents)) return false;
		return $this->_contents;
	}
}
