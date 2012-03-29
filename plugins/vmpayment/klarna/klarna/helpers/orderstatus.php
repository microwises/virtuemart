<?php
defined('_JEXEC') or die('Restricted access');

$order_id = $_GET['order_id'];
$db = JFactory::getDbo();
$q = "SELECT order_number FROM #__{vm}_orders WHERE order_id='". mysql_real_escape_string($order_id) ."'";
$odb->query($q);
$order_long = $odb->loadResult();

if( isset($_GET['checkOrderStatus']) && ($_GET['checkOrderStatus'] == 'true')) {
    $order_status = KlarnaHandler::checkOrderStatus($order_id, $order_long);
    unset($_GET['checkOrderStatus']);
} else {

    $q = "SELECT order_title FROM klarna_orderstatus WHERE order_id = '" . mysql_real_escape_string($order_long) . "'";
   $odb->setQuery( $query);
    $order_status = $odb->loadResult();
}
if ( isset( $order_status) ) {
?>
<style type="text/css">
.check_orderstatus {
    background-color: #EEEEEE;
    border-color: #CCCCCC #333333 #333333 #CCCCCC;
    border-style: solid;
    border-width: 1px;
    font: bold 11px Arial;
    margin-left: 5px;
    padding: 2px 6px;
    position: relative;
    top: -5px;
}
.order_status {
    float: left;
    font-size: 15px;
    font-weight: bold;
    font-style: italic;
    position: relative;
}
.accepted {
    color: #03AF30;
}
.denied {
    color: #AF0330;
}
.pending {
    color: #0330AF;
}
</style>
<tr>
    <td>Klarna Order status: </td>
    <td>
        <div style="width: 250px;">
            <span class="order_status <?php echo strtolower($order_status);?>"><?php echo $order_status; ?></span>
            <span style="float: right;">
                <img src="<?php echo KLARNA_IMG_PATH;?>images/tulip.png" /><a class="check_orderstatus" href="<?php echo $_SERVER['REQUEST_URI']."&checkOrderStatus=true";?>">Check Orderstatus</a>
            </span>
        </div>
    </td>

</td>
<?php
}
