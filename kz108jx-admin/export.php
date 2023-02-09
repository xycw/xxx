<?php require('includes/application_top.php'); ?>
<?php
$action = isset($_GET['action'])?$_GET['action']:'';
require(DIR_FS_ADMIN_CLASSES . 'seo_url.php');
$seo_url = new seo_url();
$data = '';
switch ($action) {
  case 'categorylinks':
		$file_name = 'categorylinks' . date('YmdHis') . '.csv';
		$sql = "SELECT category_id, name FROM " . TABLE_CATEGORY . " WHERE status = 1";
		$result = $db->Execute($sql);
		while (!$result->EOF) {
			$data .= '"' . $seo_url->href_link(FILENAME_CATEGORY, 'cID=' . $result->fields['category_id']) . '","' . str_replace('"', '""', $result->fields['name']) . '"' . "\n";
			$result->MoveNext();
		}
	break;
  case 'productlinks':
		$file_name = 'productlinks' . date('YmdHis') . '.csv';
		$sql = "SELECT product_id, name FROM " . TABLE_PRODUCT . " WHERE status = 1";
		$result = $db->Execute($sql);
		while (!$result->EOF) {
			$data .= '"' . $seo_url->href_link(FILENAME_PRODUCT, 'pID=' . $result->fields['product_id']) . '","' . str_replace('"', '""', $result->fields['name']) . '"' . "\n";
			$result->MoveNext();
		}
	break;
  case 'category':
		$data = '"分类名称","分类图片","父分类","排序","状态","meta标题","meta关键字","meta描述","url","分类描述"' . "\n";
		$file_name = 'category' . date('YmdHis') . '.csv';
		$sql = "SELECT c1.sku, c1.name, c1.description, c1.image, c1.url, c2.sku parent_sku, c1.date_added,
                       c1.status, c1.sort_order, c1.meta_title, c1.meta_keywords, c1.meta_description
                FROM " . TABLE_CATEGORY . " c1
                LEFT JOIN " . TABLE_CATEGORY . " c2
                ON c2.category_id = c1.parent_id
                WHERE 1";
		$result = $db->Execute($sql);
		while (!$result->EOF) {
			$data .= '"' . str_replace('"', '""', $result->fields['name']) . '","' . $result->fields['image'] . '","' . $result->fields['parent_sku'] . '","' . $result->fields['sort_order'] . '","' . $result->fields['status'] . '","' . $result->fields['meta_title'] . '","' . $result->fields['meta_keywords'] . '","' . str_replace('"', '""', $result->fields['meta_description']) . '","' . $result->fields['url'] . '","' . str_replace('"', '""', $result->fields['description']) . '"' . "\n";
			$result->MoveNext();
		}
	break;
  case 'category_old':
		$data = '"v_sku","v_name","v_description","v_image","v_url","v_parent_sku","v_date_added","v_status","v_sort_order","v_meta_title","v_meta_keywords","v_meta_description"' . "\n";
		$file_name = 'category_old' . date('YmdHis') . '.csv';
		$sql = "SELECT c1.sku, c1.name, c1.description, c1.image, c1.url, c2.sku parent_sku, c1.date_added,
                       c1.status, c1.sort_order, c1.meta_title, c1.meta_keywords, c1.meta_description
                FROM " . TABLE_CATEGORY . " c1
                LEFT JOIN " . TABLE_CATEGORY . " c2
                ON c2.category_id = c1.parent_id
                WHERE 1";
		$result = $db->Execute($sql);
		while (!$result->EOF) {
			$data .= '"' . $result->fields['sku'] . '","' . str_replace('"', '""', $result->fields['name']) . '","' . str_replace('"', '""', $result->fields['description']) . '","' . $result->fields['image'] . '","' . $result->fields['url'] . '","' . $result->fields['parent_sku'] . '","' . $result->fields['date_added'] . '","' . $result->fields['status'] . '","' . $result->fields['sort_order'] . '","' . $result->fields['meta_title'] . '","' . $result->fields['meta_keywords'] . '","' . str_replace('"', '""', $result->fields['meta_description']) . '"' . "\n";
			$result->MoveNext();
		}
	break;
  case 'product':
		$data = '"产品型号","分类名称","产品名称","产品图片","价格","特价","选项","排序","状态","meta标题","meta关键字","meta描述","url","产品描述","短描述","品牌","类型","颜色","性别","球队","球员名","联赛","系列","号码"' . "\n";
		$op_name = $db->Execute('select * from product_option');
		$opn_array = array();
		while (!$op_name->EOF) {
			$opn_array[$op_name->fields['product_option_id']]=$op_name->fields['name'];
			$op_name->MoveNext();
		}
		$op_val = $db->Execute('select * from product_option_value');
		$opv_array = array();
		while (!$op_val->EOF) {
			$opv_array[$op_val->fields['product_option_value_id']]=$op_val->fields['name'];
			$op_val->MoveNext();
		}
		$file_name = 'product' . date('YmdHis') . '.csv';
		$sql = "SELECT p.product_id,p.sku, p.name, p.short_description, p.description, p.image, p.url, p.price, p.specials_price,
                       p.specials_expire_date, p.date_added, p.in_stock, p.status, c.sku category_sku, p.sort_order, p.meta_title,
                       p.meta_keywords, p.meta_description, p.filter_1, p.filter_2, p.filter_3, p.filter_4, p.filter_5,
                       p.filter_6, p.filter_7, p.filter_8, p.filter_9
                FROM " . TABLE_PRODUCT . " p
                LEFT JOIN " . TABLE_CATEGORY . " c
                ON c.category_id = p.master_category_id
                WHERE 1";
		$result = $db->Execute($sql);
		while (!$result->EOF) {
			$p_attr = $db->Execute('select * from product_attribute where product_id='.$result->fields['product_id']);
			$csv_attr = '';
			$k=0;
			while (!$p_attr->EOF) {
				if($k==0){
					$csv_attr.=$opn_array[$p_attr->fields['product_option_id']].'#';
				}
				$csv_attr.=':'.$opv_array[$p_attr->fields['product_option_value_id']];
				$k++;
				$p_attr->MoveNext();
			}
			$data .= '"' . $result->fields['sku'] . '","' . $result->fields['category_sku'] . '","' . str_replace('"', '""', $result->fields['name']) . '","' . $result->fields['image'] . '","' . $result->fields['price'] . '","' . $result->fields['specials_price'] . '","' . $csv_attr . '","' . $result->fields['sort_order'] . '","' . $result->fields['status'] . '","' . $result->fields['meta_title'] . '","' . $result->fields['meta_keywords'] . '","' . str_replace('"', '""', $result->fields['meta_description']) . '","' . $result->fields['url'] . '","' . str_replace('"', '""', $result->fields['description']) . '","' . str_replace('"', '""', $result->fields['short_description']) . '","' . str_replace('"', '""', $result->fields['filter_1']) . '","' . str_replace('"', '""',$result->fields['filter_2']) . '","' . str_replace('"', '""', $result->fields['filter_3']) . '","' . str_replace('"', '""', $result->fields['filter_4']) . '","' . str_replace('"', '""', $result->fields['filter_5']) . '","' . str_replace('"', '""', $result->fields['filter_6']) . '","' . str_replace('"', '""', $result->fields['filter_7']) . '","' . str_replace('"', '""', $result->fields['filter_8']) . '","' . str_replace('"', '""', $result->fields['filter_9']) . '"' . "\n";
			$result->MoveNext();
		}
	break;
  case 'product_old':
		$op_name = $db->Execute('select * from product_option');
		$opn_array = array();
		while (!$op_name->EOF) {
			$opn_array[$op_name->fields['product_option_id']]=$op_name->fields['name'];
			$op_name->MoveNext();
		}
		
		$op_val = $db->Execute('select * from product_option_value');
		$opv_array = array();
		while (!$op_val->EOF) {
			$opv_array[$op_val->fields['product_option_value_id']]=$op_val->fields['name'];
			$op_val->MoveNext();
		}
		$data = '"v_sku","v_name","v_short_description","v_description","v_image","v_url","v_attribute","v_price","v_specials_price","v_specials_expire_date","v_date_added","v_in_stock","v_status","v_viewed","v_ordered","v_category_sku","v_sort_order","v_meta_title","v_meta_keywords","v_meta_description","v_brand_filter","v_class_filter","v_color_filter","v_gender_filter","v_material_filter","v_origin_filter","v_series_filter","v_spec_filter","v_year_filter"' . "\n";
		$file_name = 'product_old' . date('YmdHis') . '.csv';
		$sql = "SELECT p.product_id,p.sku, p.name, p.short_description, p.description, p.image, p.url, p.price, p.specials_price,
                       p.specials_expire_date, p.date_added, p.in_stock, p.status, c.sku category_sku, p.sort_order, p.meta_title,
                       p.meta_keywords, p.meta_description, p.filter_1, p.filter_2, p.filter_3, p.filter_4, p.filter_5,
                       p.filter_6, p.filter_7, p.filter_8, p.filter_9
                FROM " . TABLE_PRODUCT . " p
                LEFT JOIN " . TABLE_CATEGORY . " c
                ON c.category_id = p.master_category_id
                WHERE 1";
		$result = $db->Execute($sql);
		while (!$result->EOF) {
			$p_attr = $db->Execute('select * from product_attribute where product_id='.$result->fields['product_id']);
			$csv_attr = '';
			$k=0;
			while (!$p_attr->EOF) {
				if($k==0){
					$csv_attr.=$opn_array[$p_attr->fields['product_option_id']].'#';
				}
				$csv_attr.=':'.$opv_array[$p_attr->fields['product_option_value_id']];
				$k++;
				$p_attr->MoveNext();
			}
			$data .= '"' . $result->fields['sku'] . '","' . str_replace('"', '""', $result->fields['name']) . '","' . str_replace('"', '""', $result->fields['short_description']) . '","' . str_replace('"', '""', $result->fields['description']) . '","' . $result->fields['image'] . '","' . $result->fields['url'] . '","' . $csv_attr . '","' . $result->fields['price'] . '","' . $result->fields['specials_price'] . '","' . $result->fields['specials_expire_date'] . '","' . $result->fields['date_added'] . '","' . $result->fields['in_stock'] . '","' . $result->fields['status'] . '","","","' . $result->fields['category_sku'] . '","' . $result->fields['sort_order'] . '","' . $result->fields['meta_title'] . '","' . $result->fields['meta_keywords'] . '","' . str_replace('"', '""', $result->fields['meta_description']) . '","' . str_replace('"', '""', $result->fields['filter_1']) . '","' . str_replace('"', '""',$result->fields['filter_2']) . '","' . str_replace('"', '""', $result->fields['filter_3']) . '","' . str_replace('"', '""', $result->fields['filter_4']) . '","' . str_replace('"', '""', $result->fields['filter_5']) . '","' . str_replace('"', '""', $result->fields['filter_6']) . '","' . str_replace('"', '""', $result->fields['filter_7']) . '","' . str_replace('"', '""', $result->fields['filter_8']) . '","' . str_replace('"', '""', $result->fields['filter_9']) . '"' . "\n";
			$result->MoveNext();
		}
	break;
}
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=".$file_name);
header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
header('Expires:0');
header('Pragma:public');
echo $data;
?>
<?php require('includes/application_bottom.php'); ?>