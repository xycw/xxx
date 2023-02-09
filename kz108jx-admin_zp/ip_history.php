<?php require('includes/application_top.php'); ?>
<?php
$action             = isset($_GET['action']) ? $_GET['action'] : '';
$ipHistoryFilterSql = '';
if (isset($_GET['filter_date_added']) && not_null($_GET['filter_date_added'])) {
    $sql                = " AND date_added BETWEEN  ':date_before 00:00:00' AND ':date_added 23:59:59'";
    $tmpSql             = $db->bindVars($sql, ':date_before', trim($_GET['filter_date_before']), 'noquotestring');
    $ipHistoryFilterSql .= $db->bindVars($tmpSql, ':date_added', trim($_GET['filter_date_added']), 'noquotestring');
} else {
    $sql                = " AND date_added LIKE ':date_added%'";
    $ipHistoryFilterSql .= $db->bindVars($sql, ':date_added', trim(date('Y-m-d', time())), 'noquotestring');
}
if (isset($_GET['filter_ip_address']) && not_null($_GET['filter_ip_address'])) {
    $sql                = " AND ip_address LIKE ':ip_address%'";
    $ipHistoryFilterSql .= $db->bindVars($sql, ':ip_address', trim($_GET['filter_ip_address']), 'noquotestring');
}
if (isset($_GET['filter_is_cloak']) && not_null($_GET['filter_is_cloak'])) {
    $sql                = " AND is_cloak = ':is_cloak'";
    $ipHistoryFilterSql .= $db->bindVars($sql, ':is_cloak', trim($_GET['filter_is_cloak']), 'integer');
}
if (isset($_GET['filter_continent_code']) && not_null($_GET['filter_continent_code'])) {
    $sql                = " AND continent_code LIKE '%:continent_code%'";
    $ipHistoryFilterSql .= $db->bindVars($sql, ':continent_code', trim($_GET['filter_continent_code']), 'noquotestring');
}
if (isset($_GET['filter_country_code']) && not_null($_GET['filter_country_code'])) {
    $sql                = " AND country_code LIKE '%:country_code%'";
    $ipHistoryFilterSql .= $db->bindVars($sql, ':country_code', trim($_GET['filter_country_code']), 'noquotestring');
}
if (isset($_GET['filter_currency_code']) && not_null($_GET['filter_currency_code'])) {
    $sql                = " AND currency_code LIKE '%:currency_code%'";
    $ipHistoryFilterSql .= $db->bindVars($sql, ':currency_code', trim($_GET['filter_currency_code']), 'noquotestring');
}
if (isset($_GET['filter_http_request']) && not_null($_GET['filter_http_request'])) {
    $sql                = " AND http_request LIKE '%:http_request%'";
    $ipHistoryFilterSql .= $db->bindVars($sql, ':http_request', trim($_GET['filter_http_request']), 'noquotestring');
}
if (isset($_GET['filter_http_referer']) && not_null($_GET['filter_http_referer'])) {
    $sql                = " AND http_referer LIKE '%:http_referer%'";
    $ipHistoryFilterSql .= $db->bindVars($sql, ':http_referer', trim($_GET['filter_http_referer']), 'noquotestring');
}
if (isset($_GET['filter_http_user_agent']) && not_null($_GET['filter_http_user_agent'])) {
    $sql                = " AND http_user_agent LIKE '%:http_user_agent%'";
    $ipHistoryFilterSql .= $db->bindVars($sql, ':http_user_agent', trim($_GET['filter_http_user_agent']), 'noquotestring');
}
if (isset($_GET['filter_is_zp']) && not_null($_GET['filter_is_zp'])) {
    $sql                = " AND is_zp = ':is_zp'";
    $ipHistoryFilterSql .= $db->bindVars($sql, ':is_zp', trim($_GET['filter_is_zp']), 'integer');
}

switch ($action) {
    case 'export_ip_history':
        $data      = '"IP地址","是否屏蔽","大陆代码","国家代码","访问","来路","浏览器信息","目标网站","添加时间"' . "\n";
        $file_name = 'ip_history' . date('YmdHis') . '.csv';
        $sql       = "SELECT *
		FROM   " . TABLE_IP_HISTORY . "
		WHERE 1=1" . $ipHistoryFilterSql . "
		ORDER BY ip_history_id DESC";
        $result    = $db->Execute($sql);
        while (!$result->EOF) {
            $data .= '"' . str_replace('"', '""', $result->fields['ip_address']) . '","' . (($result->fields['is_cloak'] == 1) ? '是' : '否') . '","' . $result->fields['continent_code'] . '","' . $result->fields['country_code'] . '","' . $result->fields['http_request'] . '","' . $result->fields['http_referer'] . '","' . $result->fields['http_user_agent'] . '","' . (($result->fields['is_zp'] == 1) ? '审核站' : '产品站') . '","' . $result->fields['date_added'] . '"' . "\n";
            $result->MoveNext();
        }
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=" . $file_name);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $data;
        die;
        break;
    case 'clear_ip_history':
        $db->Execute("TRUNCATE " . TABLE_IP_HISTORY . ";");
        $message_stack->add_session('ip_history', 'IP历史数据清除成功。', 'success');
        redirect(href_link(FILENAME_IP_HISTORY));
        break;
    default:
        $sql         = "SELECT COUNT(*) AS total FROM " . TABLE_IP_HISTORY . " WHERE 1=1" . $ipHistoryFilterSql;
        $result      = $db->Execute($sql);
        $pagerConfig = array(
            'total'          => $result->fields['total'],
            'availableLimit' => array(50, 200, 500),
            'currentLimit'   => 50
        );
        require(DIR_FS_ADMIN_CLASSES . 'pager.php');
        $pager           = new pager($pagerConfig);
        $sql             = "SELECT *
		FROM   " . TABLE_IP_HISTORY . "
		WHERE 1=1" . $ipHistoryFilterSql . "
		ORDER BY ip_history_id DESC";
        $ipHistoryResult = $db->Execute($sql, $pager->getLimitSql());
        break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>IP历史</title>
    <meta name="robot" content="noindex, nofollow"/>
    <base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_ADMIN; ?>"/>
    <link href="css/styles.css" type="text/css" rel="stylesheet"/>
    <link href="css/styles-ie.css" type="text/css" rel="stylesheet"/>
    <link href="css/ui.custom.css" type="text/css" rel="stylesheet"/>
    <script src="js/jquery/jquery.js" type="text/javascript"></script>
    <script src="js/jquery/base.js" type="text/javascript"></script>
    <script src="js/jquery/ui.custom.min.js" type="text/javascript"></script>
</head>
<body>
<div class="wrapper">
    <?php require(DIR_FS_ADMIN_INCLUDES . 'noscript.php'); ?>
    <div class="page">
        <?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
        <div class="main-container">
            <div class="main">
                <?php if ($message_stack->size('ip_history') > 0) echo $message_stack->output('ip_history'); ?>
                <div class="page-title title-buttons">
                    <h1>IP历史列表</h1>
                    日期:
                    <input type="text" class="input-text date" id="filter_date_before"
                           value="<?php echo isset($_GET['filter_date_before']) ? $_GET['filter_date_before'] : date('Y-m-d', time()); ?>"/>
                    -
                    <input type="text" class="input-text date" id="filter_date_added"
                           value="<?php echo isset($_GET['filter_date_added']) ? $_GET['filter_date_added'] : date('Y-m-d', time()); ?>"/>
                    <button type="button" class="button button-new"
                            onclick="filter('<?php echo href_link(FILENAME_IP_HISTORY, 'action=export_ip_history'); ?>')">
                        <span><span>导出</span></span></button>
                    <button type="button" class="button button-new"
                            onclick="if(confirm('清除数据后您将不能恢复，请确定要这么做吗？')){setLocation('<?php echo href_link(FILENAME_IP_HISTORY, 'action=clear_ip_history'); ?>');}">
                        <span><span>清除所有数据</span></span></button>
                </div>
                <?php require(DIR_FS_ADMIN_INCLUDES . 'pager.php'); ?>
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>ID#</th>
                        <th>IP地址</th>
                        <th class="a-center">是否屏蔽</th>
						<th class="a-center">目标网站</th>
						<th>屏蔽原因</th>
						<th class="a-center">执行时间</th>
                        <th class="a-center">大陆代码</th>
                        <th class="a-center">国家代码</th>
						<th class="a-center">货币代码</th>
                        <th>访问</th>
                        <th>来路</th>
                        <th>浏览器用户</th>
						<th>浏览器语言</th>
                        <th>访问时间</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="filter">
                        <td></td>
                        <td class="value"><input type="text" class="input-text"
                                                 value="<?php echo isset($_GET['filter_ip_address']) ? $_GET['filter_ip_address'] : ''; ?>"
                                                 id="filter_ip_address"/></td>
                        <td class="value a-center">
                            <select id="filter_is_cloak">
                                <option value="">全部</option>
                                <option value="0"<?php echo isset($_GET['filter_is_cloak']) && $_GET['filter_is_cloak'] == 0 ? ' selected' : ''; ?>>
                                    否
                                </option>
                                <option value="1"<?php echo isset($_GET['filter_is_cloak']) && $_GET['filter_is_cloak'] == 1 ? ' selected' : ''; ?>>
                                    是
                                </option>
                            </select>
                        </td>
						<td class="value a-center">
                            <select id="filter_is_zp">
                                <option value="">全部</option>
                                <option value="0"<?php echo isset($_GET['filter_is_zp']) && $_GET['filter_is_zp'] == 0 ? ' selected' : ''; ?>>
                                    产品站
                                </option>
                                <option value="1"<?php echo isset($_GET['filter_is_zp']) && $_GET['filter_is_zp'] == 1 ? ' selected' : ''; ?>>
                                    审核站
                                </option>
                            </select>
                        </td>
						<td></td>
						<td></td>
                        <td class="value"><input type="text" class="input-text"
                                                 value="<?php echo isset($_GET['filter_continent_code']) ? $_GET['filter_continent_code'] : ''; ?>"
                                                 id="filter_continent_code"/></td>
                        <td class="value"><input type="text" class="input-text"
                                                 value="<?php echo isset($_GET['filter_country_code']) ? $_GET['filter_country_code'] : ''; ?>"
                                                 id="filter_country_code"/></td>
						<td class="value"><input type="text" class="input-text"
                                                 value="<?php echo isset($_GET['filter_currency_code']) ? $_GET['filter_currency_code'] : ''; ?>"
                                                 id="filter_currency_code"/></td>
                        <td class="value"><input type="text" class="input-text"
                                                 value="<?php echo isset($_GET['filter_http_request']) ? $_GET['filter_http_request'] : ''; ?>"
                                                 id="filter_http_request"/></td>
                        <td class="value"><input type="text" class="input-text"
                                                 value="<?php echo isset($_GET['filter_http_referer']) ? $_GET['filter_http_referer'] : ''; ?>"
                                                 id="filter_http_referer"/></td>
                        <td class="value"><input type="text" class="input-text"
                                                 value="<?php echo isset($_GET['filter_http_user_agent']) ? $_GET['filter_http_user_agent'] : ''; ?>"
                                                 id="filter_http_user_agent"/></td>
						<td></td>
                        <td></td>
                        <td class="a-center">
                            <button type="button" class="button"
                                    onclick="filter('<?php echo href_link(FILENAME_IP_HISTORY); ?>');">
                                <span><span>筛选</span></span></button>
                        </td>
                    </tr>
                    </tbody>
                    <?php if ($ipHistoryResult->RecordCount() > 0) { ?>
                        <tbody>
                        <?php while (!$ipHistoryResult->EOF) { ?>
                            <tr>
                                <td width="50"><?php echo $ipHistoryResult->fields['ip_history_id']; ?></td>
                                <td width="150"><?php echo $ipHistoryResult->fields['ip_address']; ?></td>
                                <td width="50" class="a-center"><?php echo $ipHistoryResult->fields['is_cloak'] == '0' ? '<span style="color:green;">否</span>' : '<span style="color:red;">是</span>'; ?></td>
								<td width="80"
                                    class="a-center"><?php echo $ipHistoryResult->fields['is_zp'] == '0' ? '<span style="color:green;">产品站</span>' : '<span style="color:red;">审核站</span>'; ?></td>
								<td><?php $cloakApiData = json_decode($ipHistoryResult->fields['cloak_api_json'], true);echo $cloakApiData['cloak']['reason']; ?></td>
                                <td width="80" class="a-center"><?php echo $ipHistoryResult->fields['cloak_runtime']; ?>ms</td>
								<td width="80"
                                    class="a-center"><?php echo $ipHistoryResult->fields['continent_code']; ?></td>
                                <td width="80"
                                    class="a-center"><?php echo $ipHistoryResult->fields['country_code']; ?></td>
								<td width="80"
                                    class="a-center"><?php echo $ipHistoryResult->fields['currency_code']; ?></td>
                                <td><?php echo $ipHistoryResult->fields['http_request']; ?></td>
                                <td><?php echo $ipHistoryResult->fields['http_referer']; ?></td>
                                <td><?php echo $ipHistoryResult->fields['http_user_agent']; ?></td>
								<td width="150"><?php echo $ipHistoryResult->fields['http_accept_language']; ?></td>
                                <td width="150"><?php echo $ipHistoryResult->fields['date_added']; ?></td>
                                <td></td>
                            </tr>
                            <?php $ipHistoryResult->MoveNext();
                        } ?>
                        </tbody>
                    <?php } else { ?>
                        <tbody>
                        <tr>
                            <td class="a-center" colspan="15">没有结果！</td>
                        </tr>
                        </tbody>
                    <?php } ?>
                </table>
                <?php require(DIR_FS_ADMIN_INCLUDES . 'pager.php'); ?>
            </div>
        </div>
        <?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        $('#filter_date_added').datepicker({
            dateFormat: 'yy-mm-dd',
            onSelect: function (datetimeText, datepickerInstance) {
                filter('<?php echo href_link(FILENAME_IP_HISTORY); ?>');
            }
        });
        $('#filter_date_before').datepicker({
            dateFormat: 'yy-mm-dd',
            onSelect: function (datetimeText, datepickerInstance) {
                filter('<?php echo href_link(FILENAME_IP_HISTORY); ?>');
            }
        });
        $(document).keydown(function (event) {
            if (event.keyCode == 13) {
                filter('<?php echo href_link(FILENAME_IP_HISTORY); ?>');
            }
        });
    });

    function filter(url) {
        var key = '';
        var val = '';
        $("[id^='filter_']").each(function () {
            key = $(this).attr('id');
            val = $(this).val();
            if (val) {
                if (url.indexOf('?') > 0) {
                    url += '&' + key + '=' + encodeURIComponent(val);
                } else {
                    url += '?' + key + '=' + encodeURIComponent(val);
                }
            }
        });
        setLocation(url);
    }
</script>
</body>
</html>
<?php require('includes/application_bottom.php'); ?>
