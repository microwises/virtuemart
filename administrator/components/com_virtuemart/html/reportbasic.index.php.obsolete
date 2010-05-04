<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
mm_showMyFileName( __FILE__ );
$nh_report = new nh_report();
$show_products = JRequest::getVar(  "show_products" );
$interval = JRequest::getVar(  "interval", "byMonth" );

foreach (array ('thisMonth', 'lastMonth', 'last60', 'last90', 'sbmt') as $button_name) {
	$$button_name = JRequest::getVar(  $button_name );
}

$selected_begin["day"] = $sday = JRequest::getVar(  "sday", 1 );
$selected_begin["month"] = $smonth = JRequest::getVar(  "smonth", date("m"));
$selected_begin["year"] = $syear = JRequest::getVar(  "syear", date("Y"));

$selected_end["day"] = $eday = JRequest::getVar(  "eday", date("d") );
$selected_end["month"] = $emonth = JRequest::getVar(  "emonth", date("m"));
$selected_end["year"] = $eyear = JRequest::getVar(  "eyear", date("Y"));

$i=0;

?>
<!-- BEGIN body -->
&nbsp;&nbsp;&nbsp;<img src="<?php echo VM_ADMIN_ICON_URL ?>icon_48/vm_report_48.png" border="0" />&nbsp;&nbsp;&nbsp;
<span class="sectionname"><?php echo JText::_('VM_REPORTBASIC_MOD') ?></span><br /><br />
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
    <input type="hidden" name="page" value="reportbasic.index" />
    <input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="pshop_mode" value="admin" />
    <table class="adminform" width="100%" border="0" cellspacing="0" cellpadding="1">
        <tr>
          <td><?php echo JText::_('VM_VIEW') ?></td>
          <td><input type="checkbox" name="show_products" id="show_products" value="show_products"<?php
          if (!empty($show_products)) { echo ' checked="checked"'; } ?> />
          <label for="show_products"><?php echo JText::_('VM_RB_INDIVIDUAL') ?></label> &nbsp; &nbsp; 
          </td>
        </tr>

        <tr>
          <td colspan="2">
            <hr noshade="noshade" size="2" color="#000000" />
          </td>
        </tr>
        <tr>
          <td><?php echo JText::_('VM_RB_INTERVAL_TITLE'); ?></td>

          <td><input type="radio" id="byMonth" name="interval" value="byMonth" <?php if($interval=="byMonth") echo "checked='checked'" ?> />
          <label for="byMonth"><?php echo JText::_('VM_RB_INTERVAL_MONTHLY_TITLE') ?></label> &nbsp; &nbsp; 
          <input type="radio" name="interval" id="byWeek" value="byWeek" <?php if($interval=="byWeek") echo "checked='checked'" ?> />
          <label for="byWeek"><?php echo JText::_('VM_RB_INTERVAL_WEEKLY_TITLE'); ?></label> &nbsp; &nbsp;
          <input type="radio" name="interval" id="byDay" value="byDay" <?php if($interval=="byDay") echo "checked='checked'" ?> />
          <label for="byDay"><?php echo JText::_('VM_RB_INTERVAL_DAILY_TITLE'); ?></label></td>
        </tr>

        <tr>
          <td colspan="2">
            <hr noshade="noshade" size="2" color="#000000" />
          </td>
        </tr>

        <tr>
          <td><?php echo JText::_('VM_SHOW') ?></td>

          <td>
          <input type="submit" class="button" name="thisMonth" value="<?php echo JText::_('VM_RB_THISMONTH_BUTTON'); ?>" /> &nbsp; 
          <input type="submit" class="button" name="lastMonth" value="<?php echo JText::_('VM_RB_LASTMONTH_BUTTON'); ?>" /> &nbsp; 
          <input type="submit" class="button" name="last60" value="<?php echo JText::_('VM_RB_LAST60_BUTTON'); ?>" /> &nbsp;
          <input type="submit" class="button" name="last90" value="<?php echo JText::_('VM_RB_LAST90_BUTTON'); ?>" />
          </td>
        </tr>

        <tr>
          <td colspan="2">
            <hr noshade="noshade" size="2" color="#000000" />
          </td>
        </tr>

        <tr valign="top">
          <td width="100"><?php echo JText::_('VM_RB_START_DATE_TITLE'); ?></td>

          <td><?php
          $nh_report->make_date_popups("s", $selected_begin );
          ?></td>
        </tr>

        <tr>
          <td width="100"><?php echo JText::_('VM_RB_END_DATE_TITLE'); ?></td>

          <td><?php $nh_report->make_date_popups("e", $selected_end ); ?></td>
        </tr>

        <tr>
          <td>&nbsp;</td>

          <td><input type="submit" class="button" name="sbmt" value="<?php echo JText::_('VM_RB_SHOW_SEL_RANGE') ?>" /> </td>
        </tr>
      </table>
    </form>
<!-- begin output of report -->
<?php 
 /* assemble start date */
 if (isset($thisMonth)) {
   $start_date = mktime(0,0,0,date("n"),1,date("Y"));
   $end_date = mktime(23,59,59,date("n")+1,0,date("Y"));
 }
 else if (isset($lastMonth)) {
   $start_date = mktime(0,0,0,date("n")-1,1,date("Y"));
   $end_date = mktime(23,59,59,date("n"),0,date("Y"));
 }
 else if (isset($last60)) {
   $start_date = mktime(0,0,0,date("n"),date("j")-60,date("Y"));
   $end_date = mktime(23,59,59,date("n"),date("j"),date("Y"));
 }
 else if(isset ($last90)) {
   $start_date = mktime(0,0,0,date("n"),date("j")-90,date("Y"));
   $end_date = mktime(23,59,59,date("n"),date("j"),date("Y"));
 }
 elseif (isset($sbmt)) {
   /* start and end dates should have been given, assign accordingly */
   $start_max_day = date("j",mktime(0,0,0,$smonth+1,0,$syear));
   if (! (intval($sday) <= $start_max_day)) {
     $sday = $start_max_day;
   }
   $start_date = mktime(0,0,0,intval($smonth),intval($sday),$syear);

   $end_max_day = date("j",mktime(0,0,0,intval($smonth)+1,0,$syear));
   if (! (intval($eday) <= $end_max_day)) {
     $eday = $end_max_day;
   }
   $end_date = mktime(23,59,59,intval($emonth),intval($eday),$eyear);
 }
 else {
 /* nothing was sent to the page, so create default inputs */
   $start_date = mktime(0,0,0,date("n"),1,date("Y"));
   $end_date = mktime(23,59,59,date("n")+1,0,date("Y"));
   $interval = "byMonth";
 }
$query_date_line = "";
 /* get the interval and set the date line for the query */
 switch ($interval) {
    case 'byMonth':
     $query_date_line = "FROM_UNIXTIME(cdate, '%M, %Y') as order_date, ";
     $query_group_line = "GROUP BY order_date";
     break;
   case 'byWeek':
     $query_date_line .= "WEEK(FROM_UNIXTIME(cdate, '%Y-%m-%d')) as week_number, ";
     $query_date_line .= "FROM_UNIXTIME(cdate, '%M %d, %Y') as order_date, ";
     $query_group_line = "GROUP BY week_number";
     break;
   case 'byDay':
   /* query for days */
     $query_date_line = "FROM_UNIXTIME(cdate, '%M %d, %Y') as order_date, ";
     $query_group_line = "GROUP BY order_date";
     break;
   default:
     $query_date_line = '';
     $query_group_line = '';
    break;
  }
  /* better way of setting up query */
  $q  = "SELECT ";
  $r  = $q;
  $u  = $q;

  $query_between_line = "WHERE cdate BETWEEN '" . $start_date . "' AND '" . $end_date . "' ";
  if ($query_date_line) {
    $q .= $query_date_line;
  }
  $q .= "FROM_UNIXTIME(cdate, '%Y%m%d') as date_num, ";
  $q .= "COUNT(order_id) as number_of_orders, ";
  $q .= "SUM(order_subtotal) as revenue ";
  $q .= "FROM #__{vm}_orders ";
  $q .= $query_between_line;
  if ($query_group_line) {
    $q .= $query_group_line;
  }
  $q .= " ORDER BY date_num ASC";

  /** setup items sold query */
  if ($query_date_line) {
    $r .= $query_date_line;
  }
  $r .= "FROM_UNIXTIME(cdate, '%Y%m%d') as date_num, ";
  $r .= "SUM(product_quantity) as items_sold ";
  $r .= "FROM #__{vm}_order_item ";
  $r .= $query_between_line;
  if ($query_group_line) {
    $r .= $query_group_line;
  }
  $r .= " ORDER BY date_num ASC";

// added for v0.2 PRODUCT LISTING QUERY!
if (!empty($show_products)) {
/* setup end of product listing query */
  $u .= "product_name, product_sku, ";
  if ($query_date_line) {
    $u .= str_replace ("cdate", "#__{vm}_order_item.cdate", $query_date_line);
  }
  $u .= "FROM_UNIXTIME(#__{vm}_order_item.cdate, '%Y%m%d') as date_num, ";
  $u .= "SUM(product_quantity) as items_sold ";
  $u .= "FROM #__{vm}_order_item, #__{vm}_orders, #__{vm}_product ";
  $u .= str_replace ("cdate", "#__{vm}_order_item.cdate", $query_between_line);
  $u .= "AND #__{vm}_orders.order_id=#__{vm}_order_item.order_id ";
  $u .= "AND #__{vm}_order_item.product_id=#__{vm}_product.product_id ";
  $u .= "GROUP BY product_sku, product_name, order_date ";
  $u .= " ORDER BY date_num, product_name ASC";
  $dbpl = new ps_DB;
  $dbpl->query($u);
}
/* setup the db and query */
  $db = new ps_DB;
  $dbis = new ps_DB;
  
  $db->query($q);
  $dbis->query($r);
 ?>
    <h4><?php 
    echo JText::_('VM_RB_REPORT_FOR') ." ";
    echo date("M j, Y", $start_date)." --&gt; ". date("M j, Y", $end_date); 
    ?></h4>

    <table class="adminlist">
      <tr>
        <th><?php echo JText::_('VM_RB_DATE') ?></th>

        <th><?php echo JText::_('VM_RB_ORDERS') ?></th>

        <th><?php echo JText::_('VM_RB_TOTAL_ITEMS') ?></th>

        <th><?php echo JText::_('VM_RB_REVENUE') ?></th>
      </tr>
<?php
  while ($db->next_record()) {
    $dbis->next_record();
    
    if ($i++ % 2) {
      $bgcolor='row0';
    }
    else {
      $bgcolor='row1';
    }
        ?> 
    <tr class="<?php echo $bgcolor ?>"> 
      <td><?php $db->p("order_date"); ?></td>
      <td><?php $db->p("number_of_orders"); ?></td>
      <td><?php $dbis->p("items_sold"); ?></td>
      <td><?php $db->p("revenue"); ?>&nbsp;</td>
    </tr>
  <?php
    // BEGIN product listing
    if (!empty($show_products)) {
    ?>
    <tr><td>&nbsp;</td><td colspan="2">
      <table class="adminlist">
        <tr>
          <td colspan="3" align="left"><h3><?php echo JText::_('VM_RB_PRODLIST') ?></h3></td>
        </tr>
        <tr bgcolor="#ffffff">
          <th>#</th>
          <th><?php echo JText::_('VM_PRODUCT_NAME_TITLE') ?></th>
          <th><?php echo JText::_('VM_CART_QUANTITY') ?></th>
        </tr>
      <?php
        $i = 1;
        $has_next = $dbpl->next_record();
        
        while ( $has_next) {
        	if( $dbpl->f("order_date") == $db->f("order_date")) {
	          echo "<tr bgcolor=\"#ffffff\">\n";
	          echo "<td>".$i++."</td>\n";
	          echo '<td align="left">' . $dbpl->f("product_name") . " (" . $dbpl->f("product_sku") . ")</td>\n";
	          echo '<td align="left">' . $dbpl->f("items_sold") . "</td>\n";
	          echo "</tr>\n";
        	}
         	$has_next = $dbpl->next_record();
        }
        $dbpl->reset();
      ?>
        <tr><td colspan="3"><hr width="85%"></td></tr>
      </table>
      </td><td>&nbsp;</td>
      </tr>
    <?php
    }
    // END product listing

  } ?>
  </table>
        
<!-- end output of report -->
<!-- END body -->
