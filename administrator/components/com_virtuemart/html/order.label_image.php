<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
 * @version $Id$
 * @package VirtueMart
 * @subpackage html
 */
mm_showMyFileName(__FILE__);

$order_id = JRequest::getVar( 'order_id', null);
if (!is_numeric($order_id))
	die(str_replace('order_id',$order_id,JText::_('VM_ORDER_LABEL_ORDERID_NOTVALID')));

$db =& new ps_DB;

$q = "SELECT shipper_class, label_is_generated, label_image ";
$q .= "FROM #__{vm}_shipping_label ";
$q .= "WHERE order_id='" . $order_id . "'";
$db->query($q);
if (!$db->next_record())
	die(JText::_('VM_ORDER_LABEL_NOTFOUND'));

if (!$db->f('label_is_generated'))
	die(JText::_('VM_ORDER_LABEL_NEVERGENERATED'));

include_once(CLASSPATH . "shipping/" . $db->f("shipper_class") . ".php");
eval("\$ship_class =& new " . $db->f("shipper_class") . "();");
if (!is_callable(array($ship_class, 'get_label_image')))
	die(str_replace('{ship_class}',$ship_class,JText::_('VM_ORDER_LABEL_CLASSCANNOT')));

echo $ship_class->get_label_image($order_id);
?>
