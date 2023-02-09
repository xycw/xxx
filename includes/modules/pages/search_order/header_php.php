<?php
/**
 * search_order header_php.php
 */

if (isset($_POST['email_address']) && !empty($_POST['email_address']) &&
    isset($_POST['order_id']) && !empty($_POST['order_id'])) {
    $email = db_prepare_input($_POST['email_address']);
    $order_id = get_orderNO(db_prepare_input($_POST['order_id']));

    if (validate_email($email)) {
        $sql = "SELECT order_id, customer_id
                FROM   orders
                WHERE  customer_email_address = :emailAddress
                AND    order_id = :orderID
                LIMIT  1";
        $sql = $db->bindVars($sql, ':emailAddress', $email, 'string');
        $sql = $db->bindVars($sql, ':orderID', $order_id, 'string');
        $result = $db->Execute($sql);
        if ($result->RecordCount() > 0) {
            if ($result->fields['customer_id'] > 0) {
                $message_stack->add_session('login', __('Please Sign In'));
                redirect(href_link(FILENAME_LOGIN, '', 'SSL'));
            } else {
                $_SESSION['old_order_id'] = $result->fields['order_id'];
                redirect(href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL'));
            }
        } else {
            $message_stack->add('search_order', __('No Matched Order'));
        }
    } else {
        $message_stack->add('search_order', __('Incorrect Email'));
    }
}