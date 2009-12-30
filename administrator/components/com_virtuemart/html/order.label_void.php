<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
 * @version $Id: order.label_void.php 1755 2009-05-01 22:45:17Z rolandd $
 * @package VirtueMart
 * @subpackage html
 */
mm_showMyFileName(__FILE__);

$order_id = JRequest::getVar('order_id', null);
if (!is_numeric($order_id))
	die(str_replace('order_id',$order_id,JText::_('VM_ORDER_LABEL_ORDERID_NOTVALID')));

$db =& new ps_DB;

$q = "SELECT shipper_class, label_is_generated, tracking_number ";
$q .= "FROM #__{vm}_shipping_label ";
$q .= "WHERE order_id='" . $order_id . "'";
$db->query($q);
if (!$db->next_record())
	die(JText::_('VM_ORDER_LABEL_NOTFOUND'));

include_once(CLASSPATH . "shipping/" . $db->f("shipper_class") . ".php");
eval("\$ship_class =& new " . $db->f("shipper_class") . "();");
if (!is_callable(array($ship_class, 'void_label')))
	die(str_replace('{ship_class}',$ship_class,JText::_('VM_ORDER_LABEL_CLASSCANNOT')));

if (!$db->f('label_is_generated'))
	die(JText::_('VM_ORDER_LABEL_NEVERGENERATED'));

$msg = $ship_class->void_label($order_id);
if ($msg == '') {
	$msg = str_replace('{tracking_number}',$db->f('tracking_number'),JText::_('VM_ORDER_LABEL_VOIDED_MSG'));
}

echo "<html>\n";
echo "<head><title>" . JText::_('VM_ORDER_LABEL_VOID_TITLE') . "</title></head>\n";
echo "<body>\n";
echo "<p>" . $msg . "\n";
echo "</body>\n";
echo "</html>\n";
?>
