<?php
include_once('DbObject.php');
/**
 * Support a SKU object as stored in the DB
 *
 * @author dthomas
 */
class Order extends DbObject {
	const OrderID_Undefined = DbObject::DbID_Undefined;
	const OrderItemArraySize_New = 3;
	const OrderItemArraySize_Edit = 4;
	const OrderItemArrayIndex_SKU = 0;
	const OrderItemArrayIndex_Amount = 1;
	const OrderItemArrayIndex_Fee = 2;
	const OrderItemArrayIndex_Orderitemid = 3;

	function __construct( $session, $id= DbObject::DbID_Undefined) {
		parent::__construct($session);
		if ($id != DbObject::DbID_Undefined) {
		}
	}

}
?>
