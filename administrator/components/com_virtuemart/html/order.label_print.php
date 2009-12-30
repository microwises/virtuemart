<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
 * @version $Id: order.label_print.php 1755 2009-05-01 22:45:17Z rolandd $
 * @package VirtueMart
 * @subpackage html
 */
mm_showMyFileName(__FILE__);

$order_id = JRequest::getVar('order_id', null);
if (!is_numeric($order_id))
	die(str_replace('order_id',$order_id,JText::_('VM_ORDER_LABEL_ORDERID_NOTVALID')));

$db =& new ps_DB;

$q = "SELECT shipper_class FROM #__{vm}_shipping_label ";
$q .= "WHERE order_id='" . $order_id . "'";
$db->query($q);
if (!$db->next_record())
	die(JText::_('VM_ORDER_LABEL_NOTFOUND'));

include_once(CLASSPATH . "shipping/" . $db->f("shipper_class") . ".php");
eval("\$ship_class =& new " . $db->f("shipper_class") . "();");
if (!is_callable(array($ship_class, 'generate_label')))
	die(str_replace('{ship_class}',$ship_class,JText::_('VM_ORDER_LABEL_CLASSCANNOT')));

$ship_class->generate_label($order_id);
$dim = $ship_class->get_label_dimensions($order_id);
$dim_arr = explode("x", $dim);
$dim_x = $dim_arr[0];
$dim_y = $dim_arr[1];

$image_type = $ship_class->get_label_image_type($order_id);

$image_url = $sess->url($_SERVER['PHP_SELF'] .
    "?page=order.label_image&order_id=" .
    $order_id .  "&no_menu=1&no_html=1");
$image_url = stristr($image_url, "index2.php") ?
    str_replace("index2.php", "index3.php", $image_url) :
    str_replace("index.php", "index2.php", $image_url);

echo "<h2>" . JText::_('VM_ORDER_LABEL_SHIPPINGLABEL_LBL') . ":</h2>\n";
echo '<img src="' . $image_url . '" height="' .  $dim_y . '" width="' . $dim_x . '" />' . "\n";

?>