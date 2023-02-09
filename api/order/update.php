<?php
/**
 * 同步订单状态
 */

function updateOrder() {
    global $db;
    $response    = array('error_code' => 0, 'reason' => '同步成功');
    $orderNumber = isset($_GET['orderNumber']) ? $_GET['orderNumber'] : 0;
    $orderStatus = isset($_GET['orderStatus']) ? $_GET['orderStatus'] : 0;
    $remarks     = isset($_GET['remarks'])     ? $_GET['remarks'] : '';

    if (empty($orderNumber)) {
        $response['error_code'] = 1;
        $response['reason']     = '订单号为空';
        echo json_encode($response);
        die;
    }

    // 验证订单状态
    if (empty($orderStatus) || empty(get_order_status_name($orderStatus))) {
        $response['error_code'] = 1;
        $response['reason']     = '订单状态错误';
        echo json_encode($response);
        die;
    }

    // 根据订单号查询orderId
    $orderId = get_orderNO($orderNumber);
    $where   = " where order_id = :order_id";
    $where   = $db->bindVars($where, ':order_id', $orderId, 'integer');
    $sql     = "SELECT order_id FROM " . TABLE_ORDERS . $where;
    $result  = $db->Execute($sql);

    if ($result->RecordCount() <= 0) {
        $response['error_code'] = 1;
        $response['reason']     = '不存在的订单号';
        echo json_encode($response);
        die;
    }

    // 修改订单状态
    $sql_data_array = array(array('fieldName'=>'order_status_id', 'value'=>$orderStatus, 'type'=>'string'));

    if ($db->perform(TABLE_ORDERS, $sql_data_array, 'UPDATE', 'order_id = ' . $orderId) == false){
        $response['error_code'] = 1;
        $response['reason']     = '订单状态同步失败';
        echo json_encode($response);
        die;
    }

    // 添加订单历史
    $sql_data_array = array(
        array('fieldName'=>'order_id',        'value'=>$result->fields['order_id'], 'type'=>'integer'),
        array('fieldName'=>'order_status_id', 'value'=>$orderStatus,                'type'=>'string'),
        array('fieldName'=>'remarks',         'value'=>$remarks,                    'type'=>'string'),
        array('fieldName'=>'date_added',      'value'=>'NOW()',                     'type'=>'noquotestring'),
    );

    if ($db->perform(TABLE_ORDER_STATUS_HISTORY, $sql_data_array, 'INSERT') == false){
        $response['error_code'] = 1;
        $response['reason']     = '订单历史添加失败';
        echo json_encode($response);
        die;
    }

    echo json_encode($response);
}

updateOrder();
