<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: order.order_print.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
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
global $ps_order_status;

require_once(CLASSPATH.'ps_product.php');
require_once(CLASSPATH.'ps_order_status.php');
require_once(CLASSPATH.'ps_checkout.php');
require_once(CLASSPATH.'ps_order_change.php');
require_once(CLASSPATH.'ps_order_change_html.php');

$ps_product =& new ps_product;
$order_id = vmRequest::getInt('order_id');
$ps_order_change_html =& new ps_order_change_html($order_id);

if (!is_numeric($order_id)){
    echo "<h2>The Order ID $order_id is not valid.</h2>";
    echo JText::_('VM_ORDER_NOTFOUND');
}else {
    $dbc = new ps_DB;
	$q = "SELECT * FROM #__{vm}_orders WHERE order_id='$order_id'";
	$db->query($q);
	if( $db->next_record() ) {
	
	  // Print View Icon
	  $print_url = $_SERVER['PHP_SELF']."?page=order.order_printdetails&amp;order_id=$order_id&amp;no_menu=1&pop=1";
//	  if( vmIsJoomla( '1.5', '>=' ) ) {
	  	$print_url .= "&amp;tmpl=component";
//	  }
	  
	  $print_url = $sess->url( $print_url );
	  $print_url = defined( '_VM_IS_BACKEND' ) ? str_replace( "index2.php", "index3.php", $print_url ) : str_replace( "index.php", "index2.php", $print_url );
?>
	  <div style="float: right;">
	  	<span class="pagenav" style="font-weight: bold;">
	  		<a href="javascript:void window.open('<?php echo $print_url ?>', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');">
	  			<img src="<?php echo $mosConfig_live_site ?>/images/M_images/printButton.png" align="ABSMIDDLE" height="16" width="16" border="0" />
	  			<?php echo JText::_('VM_CHECK_OUT_THANK_YOU_PRINT_VIEW') ?>
	  		</a>
	  	</span>
	  </div>

<?php

	  // Navigation
	  echo ps_order::order_print_navigation( $order_id );
	
	  $q = "SELECT * FROM #__{vm}_order_history WHERE order_id='$order_id' ORDER BY order_status_history_id ASC";
	  $dbc->query( $q );
	  $order_events = $dbc->record;
	  ?>
	  <br />
	  <table class="adminlist" style="table-layout: fixed;">
		<tr> 
		  <td valign="top"> 
			<table border="0" cellspacing="0" cellpadding="1">
			  <tr class="sectiontableheader"> 
				  <th colspan="2"><?php echo JText::_('VM_ORDER_PRINT_PO_LBL') ?></th>
			  </tr>
			  <tr> 
				  <td><strong><?php echo JText::_('VM_ORDER_PRINT_PO_NUMBER') ?>:</strong></td>
				<td><?php printf("%08d", $db->f("order_id"));?></td>
			  </tr>
			  <tr> 
				  <td><strong><?php echo JText::_('VM_ORDER_PRINT_PO_DATE') ?>:</strong></td>
				<td><?php echo vmFormatDate( $db->f("cdate")+$mosConfig_offset);?></td>
			  </tr>
			  <tr> 
				  <td><strong><?php echo JText::_('VM_ORDER_PRINT_PO_STATUS') ?>:</strong></td>
				<td><?php echo ps_order_status::getOrderStatusName($db->f("order_status")) ?></td>
			  </tr>
			  <tr>
		      <td><strong><?php echo JText::_('VM_ORDER_PRINT_PO_IPADDRESS') ?>:</strong></td>
			    <td><?php $db->p("ip_address"); ?></td>
			  </tr>
		  <?php 
		  if( PSHOP_COUPONS_ENABLE == '1') { ?>
		  <tr>
			  <td><strong><?php echo JText::_('VM_COUPON_COUPON_HEADER') ?>:</strong></td>
			  <td><?php if( $db->f("coupon_code") ) $db->p("coupon_code"); else echo '-'; ?></td>
		  </tr>
		  <?php 
			} ?>
			</table>
		  </td>
		  <td valign="top">
			<?php
		$tab = new vmTabPanel( 1, 1, "orderstatuspanel");
		$tab->startPane( "order_change_pane" );
		$tab->startTab(  JText::_('VM_ORDER_STATUS_CHANGE'), "order_change_page" );
			?>
			<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
			<table class="adminform">
			 <tr>
			  <th colspan="2"><?php echo JText::_('VM_ORDER_STATUS_CHANGE') ?></th>
			 </tr>
			 <tr>
			  <td class="labelcell"><?php echo JText::_('VM_ORDER_PRINT_PO_STATUS') .":"; ?>
			  </td>
			  <td><?php $ps_order_status->list_order_status($db->f("order_status")); ?>
				  <input type="submit" class="button" name="Submit" value="<?php echo JText::_('VM_UPDATE') ?>" />
					<input type="hidden" name="page" value="order.order_print" />
					<input type="hidden" name="func" value="orderStatusSet" />
					<input type="hidden" name="vmtoken" value="<?php echo vmSpoofValue($sess->getSessionId()) ?>" />
					<input type="hidden" name="option" value="com_virtuemart" />
					<input type="hidden" name="current_order_status" value="<?php $db->p("order_status") ?>" />
					<input type="hidden" name="order_id" value="<?php echo $order_id ?>" />
			  </td>
			 </tr>
			 <tr>
			  <td class="labelcell" valign="top"><?php echo JText::_('VM_COMMENT') .":"; ?>
			  </td>
			  <td>
				<textarea name="order_comment" rows="5" cols="25"></textarea>
			  </td>
			  <tr>
			  <tr>
			  <td class="labelcell"><label for="notify_customer"><?php echo JText::_('VM_ORDER_LIST_NOTIFY') ?></label></td>
			  <td><input type="checkbox" name="notify_customer" id="notify_customer" checked="checked" value="Y" /></td> 
				  </tr>
				  <tr>
				<td class="labelcell"><label for="include_comment"><?php echo JText::_('VM_ORDER_HISTORY_INCLUDE_COMMENT') ?></label>
			  </td>
			  <td>
			  <input type="checkbox" name="include_comment" id="include_comment" checked="checked" value="Y" /> 
				  </td>
			 </tr>
			</table>
			</form>
				<?php
				$tab->endTab();
				$tab->startTab( JText::_('VM_ORDER_HISTORY'), "order_history_page" );
				?>
			<table class="adminlist">
			 <tr >
			  <th><?php echo JText::_('VM_ORDER_HISTORY_DATE_ADDED') ?></th>
			  <th><?php echo JText::_('VM_ORDER_HISTORY_CUSTOMER_NOTIFIED') ?></th>
			  <th><?php echo JText::_('VM_ORDER_LIST_STATUS') ?></th>
			  <th><?php echo JText::_('VM_COMMENT') ?></th>
			 </tr>
			 <?php 
			 foreach( $order_events as $order_event ) {
			  echo "<tr>";
			  echo "<td>".$order_event->date_added."</td>\n";
			  echo "<td align=\"center\"><img alt=\"" . JText::_('VM_ORDER_STATUS_ICON_ALT') ."\" src=\"$mosConfig_live_site/administrator/images/";
			  echo $order_event->customer_notified == 1 ? 'tick.png' : 'publish_x.png';

			  echo "\" border=\"0\" align=\"absmiddle\" /></td>\n";
			  echo "<td>".$order_event->order_status_code."</td>\n";
			  echo "<td>".$order_event->comments."</td>\n";
			  echo "</tr>\n";
			 }
			 ?>
			</table>
			<?php
			$tab->endTab();
			$tab->endPane();
			?>
		  </td>
		</tr>
	  </table>
	  &nbsp;
	  <table class="adminlist" width="100%" >
      <?php
		  $user_id = $db->f("user_id");
		  $dbt = new ps_DB;
		  $qt = "SELECT * from #__{vm}_order_user_info WHERE user_id='$user_id' AND order_id='$order_id' ORDER BY address_type ASC"; 
		  $dbt->query($qt);
		  $dbt->next_record();
    	require_once( CLASSPATH . 'ps_userfield.php' );
    	$userfields = ps_userfield::getUserFields('registration', false, '', true, true );
    	$shippingfields = ps_userfield::getUserFields('shipping', false, '', true, true );
	   ?> 
		<tr> 
		  <th width="50%"  valign="top"><?php echo JText::_('VM_ORDER_PRINT_BILL_TO_LBL') ?></th>
		  <th width="50%" valign="top"><?php echo JText::_('VM_ORDER_PRINT_SHIP_TO_LBL') ?></th>
		</tr>
		<tr> 
		  <td valign="top"> 
		<table width="100%" border="0" cellspacing="0" cellpadding="1">
		<?php 
		foreach( $userfields as $field ) {
			if( $field->name == 'email') $field->name = 'email';
			if($field->type == 'captcha') continue;
			?>
		  <tr> 
			<td width="35%" align="right">&nbsp;<?php echo JText::_($field->title) ? JText::_($field->title) : $field->title ?>:</td>
			<td width="65%" align="left"><?php
				switch($field->name) {
		          	case 'country':
		          		require_once(CLASSPATH.'ps_country.php');
		          		$country = new ps_country();
		          		$dbc = $country->get_country_by_code($dbt->f($field->name));
	          			if( $dbc !== false ) echo $dbc->f('country_name');
		          		break;
		          	default:
		          	  $fieldvalue = $dbt->f($field->name);
		          		if ( is_null($fieldvalue) OR $fieldvalue == "" ) {
		          		  echo "&nbsp;";
		          		} else {
                    echo $fieldvalue;
                  }
		          		break;
		          }
		          ?>
			</td>
		  </tr>
		  <?php
			}
		   ?>
        <?php $ps_order_change_html->html_change_bill_to($user_id) ?>  
			</table>
		  </td>
		  <td valign="top">
	  <?php
	  // Get Ship To Address
	  $dbt->next_record();
	  ?> 
		<table width="100%" border="0" cellspacing="0" cellpadding="1">
		<?php 
		foreach( $shippingfields as $field ) {
			if( $field->name == 'email') $field->name = 'email';
			?>
		  <tr> 
			<td width="35%" align="right">&nbsp;<?php echo JText::_($field->title) ? JText::_($field->title) : $field->title ?>:</td>
			<td width="65%" align="left"><?php
				switch($field->name) {
		          	case 'country':
		          		require_once(CLASSPATH.'ps_country.php');
		          		$country = new ps_country();
		          		$dbc = $country->get_country_by_code($dbt->f($field->name));
		          		if( $dbc !== false ) echo $dbc->f('country_name');
		          		break;
		          	default: 
		          	  $fieldvalue = $dbt->f($field->name);
		          		if ( is_null($fieldvalue) OR $fieldvalue == "" ) {
		          		  echo "&nbsp;";
		          		} else {
                    echo $fieldvalue;
                  }
		          		break;
		          }
		          ?>
			</td>
		  </tr>
		  <?php
			}
		   ?>
        <?php $ps_order_change_html->html_change_ship_to($user_id) ?>  
			</table>
		  </td>
		</tr>
	</table>
	&nbsp;
	<table  class="adminlist">
		<tr> 
		  <td colspan="2"> 
			<table  class="adminlist">
			  <tr > 
				<th class="title" width="5%" align="left"><?php echo JText::_('VM_ORDER_EDIT_ACTIONS') ?></th>
				<th class="title" width="50" align="left"><?php echo JText::_('VM_ORDER_PRINT_QUANTITY') ?></th>
				<th class="title" width="*" align="left"><?php echo JText::_('VM_ORDER_PRINT_NAME') ?></th>
				<th class="title" width="10%" align="left"><?php echo JText::_('VM_ORDER_PRINT_SKU') ?></th>
				<th class="title" width="10%"><?php echo JText::_('VM_ORDER_PRINT_PO_STATUS') ?></th>
				<th class="title" width="50"><?php echo JText::_('VM_PRODUCT_FORM_PRICE_NET') ?></th>
				<th class="title" width="50"><?php echo JText::_('VM_PRODUCT_FORM_PRICE_GROSS') ?></th>
				<th class="title" width="5%"><?php echo JText::_('VM_ORDER_PRINT_TOTAL') ?></th>
				<th class="title" width="22%"><?php echo JText::_('VM_ORDER_PRINT_INTNOTES') ?></th>
			  </tr>
			  <?php
			  $dbt = new ps_DB;
			  $qt  = "SELECT order_item_id,product_quantity,order_item_name,order_item_sku,product_id,product_item_price,product_final_price, product_attribute, order_status
						FROM `#__{vm}_order_item`
						WHERE #__{vm}_order_item.order_id='$order_id' ";
			  $dbt->query($qt);
			  $i = 0;
			  while ($dbt->next_record()){
				$dbp = new ps_DB;
				
				$qr  = "SELECT intnotes FROM `#__{vm}_product` WHERE product_id='".$dbt->f("product_id")."'";
				$dbp->query($qr);
				
				$dbd = new ps_DB();
				
				if ($i++ % 2) {
				   $bgcolor='row0';
				} else {
				  $bgcolor='row1';
				}
			}
			$t = $dbt->f("product_quantity") * $dbt->f("product_final_price");
			// Check if it's a downloadable product
  			$downloadable = false;
  			$files = array();
  			$dbd->query('SELECT product_id, attribute_name
  							FROM `#__{vm}_product_attribute`
  							WHERE product_id='.$dbt->f('product_id').' AND attribute_name=\'download\'' );
  			if( $dbd->next_record() ) {
  				$downloadable = true;
  				$dbd->query('SELECT product_id, end_date, download_max, download_id, file_name
  							FROM `#__{vm}_product_download`
  							WHERE product_id='.$dbt->f('product_id').' AND order_id=\''.$order_id .'\'' );
  				while( $dbd->next_record() ) {
  					$files[] = $dbd->get_row();
  				}
  			}
			  ?>
			  <tr class="<?php echo $bgcolor; ?>" valign="top">
          <?php $ps_order_change_html->html_change_delete_item($dbt->f("order_item_id")) ?>
          <?php $ps_order_change_html->html_change_item_quantity($dbt->f("order_item_id"), $dbt->f("product_quantity")) ?>
        <td width="30%" align="left">
				<?php $dbt->p("order_item_name"); 
  			  echo "<br /><span style=\"font-size: smaller;\">" . ps_product::getDescriptionWithTax($dbt->f("product_attribute")) . "</span>"; 
  			  if( $downloadable ) {
  			  	echo '<br /><br />
  			  			<div style="font-weight:bold;">'.JText::_('VM_DOWNLOAD_STATS') .'</div>';
  			  	if( empty( $files )) {
  			  		echo '<em>- '.JText::_('VM_DOWNLOAD_NOTHING_LEFT') .' -</em>';
  			  		$enable_download_function = $ps_function->get_function('insertDownloadsForProduct');
  			  		if( $perm->check( $enable_download_function['perms'] ) ) {
  			  			echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">				  		
  			  			<input type="hidden" name="page" value="'.$page.'" />
  			  			<input type="hidden" name="order_id" value="'.$order_id.'" />
  			  			<input type="hidden" name="product_id" value="'.$dbt->f('product_id').'" />
  			  			<input type="hidden" name="user_id" value="'.$db->f('user_id').'" />
  			  			<input type="hidden" name="func" value="insertDownloadsForProduct" />
  						  <input type="hidden" name="vmtoken" value="'. vmSpoofValue($sess->getSessionId()) .'" />
  			  			<input type="hidden" name="option" value="'.$option.'" />
  			  			<input class="button" type="submit" name="submit" value="'.JText::_('VM_DOWNLOAD_REENABLE').'" />
  			  			</form>';
  			  		}
  			  	} else {
  			  		foreach( $files as $file ) {
  			  			echo '<em>'
  			  					.'<a href="'.$sess->url( $_SERVER['PHP_SELF'].'?page=product.file_form&amp;product_id='.$dbt->f('product_id').'&amp;file_id='.$db->f("file_id")).'&amp;no_menu='.@$_REQUEST['no_menu'].'" title="'.JText::_('VM_MANUFACTURER_LIST_ADMIN').'">'
  			  					.$file->file_name.'</a></em><br />';
  			  			echo '<ul>';
  			  			echo '<li>'.JText::_('VM_DOWNLOAD_REMAINING_DOWNLOADS') .': '.$file->download_max.'</li>';
  			  			if( $file->end_date > 0 ) {
  			  				echo '<li>'.JText::_('VM_EXPIRY').': '.vmFormatDate( $file->end_date + $mosConfig_offset ).'</li>';
  			  			}
  			  			echo '</ul>';
  			  			echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">				  		
  			  			<input type="hidden" name="order_id" value="'.$order_id.'" />
  			  			<input type="hidden" name="page" value="'.$page.'" />
  			  			<input type="hidden" name="func" value="mailDownloadId" />
  						  <input type="hidden" name="vmtoken" value="'. vmSpoofValue($sess->getSessionId()) .'" />
  			  			<input type="hidden" name="option" value="'.$option.'" />
  			  			<input class="button" type="submit" name="submit" value="'.JText::_('VM_DOWNLOAD_RESEND_ID').'" />
  			  			</form>';
  			  		}
  			  		
  			  	}
  			  }
				  ?>
				</td>
				<td width="10%" align="left"><?php  $dbt->p("order_item_sku") ?></td>
  			<td width="10%">
  				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
  				<?php echo "<strong>".JText::_('VM_ORDER_PRINT_PO_STATUS') .": </strong>";
  			 	 $ps_order_status->list_order_status($dbt->f("order_status")); ?>
  				<input type="submit" class="button" name="Submit" value="<?php echo JText::_('VM_UPDATE') ?>" />
  				<input type="hidden" name="page" value="order.order_print" />
  				<input type="hidden" name="func" value="orderStatusSet" />
  				<input type="hidden" name="vmtoken" value="<?php echo vmSpoofValue($sess->getSessionId()) ?>" />
  				<input type="hidden" name="option" value="com_virtuemart" />
  				<input type="hidden" name="current_order_status" value="<?php $dbt->p("order_status") ?>" />
  				<input type="hidden" name="order_id" value="<?php echo $order_id ?>" />
  				<input type="hidden" name="order_item_id" value="<?php $dbt->p("order_item_id") ?>" />
  				</form>
  			</td>
				<td align="right">
          <?php $ps_order_change_html->html_change_product_item_price($dbt->f("order_item_id"), $dbt->f("product_item_price")) ?>
        </td>				
				<td align="right">
          <?php $ps_order_change_html->html_change_product_final_price($dbt->f("order_item_id"), $dbt->f("product_final_price")) ?>
        </td>
				<td width="5%" align="right" style="padding-right: 5px;"><?php echo $GLOBALS['CURRENCY_DISPLAY']->getFullValue($t, '', $db->f('order_currency')); ?></td>
				<td width="22%"><div align="right"><?php $dbp->p("intnotes") ?></div></td>
			  </tr>
			  <?php 
			  } 
			  ?> 
			  
			  </table>
			  <table  class="adminlist">
			  <tr> 
				  <td align="right" colspan="7"><div align="right"><strong> <?php echo JText::_('VM_ORDER_PRINT_SUBTOTAL') ?>: </strong></div></td>
				  <td width="5%" align="right" style="padding-right: 5px;"><?php echo $GLOBALS['CURRENCY_DISPLAY']->getFullValue($db->f("order_subtotal"), '', $db->f('order_currency')); ?></td>
			  </tr>
	  <?php
			  /* COUPON DISCOUNT */
			$coupon_discount = $db->f("coupon_discount");
			
	  
			if( PAYMENT_DISCOUNT_BEFORE == '1') {
			  if ($db->f("order_discount") != 0) {
	  ?>
			  <tr>
				<td align="right" colspan="7"><strong><?php 
				  if( $db->f("order_discount") > 0)
					 echo JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT');
				  else
					 echo JText::_('VM_FEE');
					?>:</strong></td>
				<td width="5%" align="right" style="padding-right: 5px;"><?php
					  if ($db->f("order_discount") > 0 )
					 echo "-" . $GLOBALS['CURRENCY_DISPLAY']->getFullValue(abs($db->f("order_discount")), '', $db->f('order_currency'));
				elseif ($db->f("order_discount") < 0 )
					 echo "+" . $GLOBALS['CURRENCY_DISPLAY']->getFullValue(abs($db->f("order_discount")), '', $db->f('order_currency')); ?>
				  </td>
			  </tr>
			  
			  <?php 
			  } 
			  if( $coupon_discount > 0 || $coupon_discount < 0) {
	  ?>
			  <tr>
				<td align="right" colspan="7"><strong><?php echo JText::_('VM_COUPON_DISCOUNT') ?>:</strong></td> 
				<td  width="5%" align="right" style="padding-right: 5px;"><?php
				  echo "- ".$GLOBALS['CURRENCY_DISPLAY']->getFullValue( $coupon_discount, '', $db->f('order_currency') ); ?>
				</td>
			  </tr>
			  <?php
			  }
			}
	  ?>
			  
			  <tr> 
  				<td align="right" colspan="7"><strong><?php echo JText::_('VM_ORDER_PRINT_TOTAL_TAX') ?>:</strong></td>
  				<td width="5%" align="right" style="padding-right: 5px;"><?php echo $GLOBALS['CURRENCY_DISPLAY']->getFullValue($db->f("order_tax"), '', $db->f('order_currency')) ?></td>
			  </tr>
			  <tr> 
				<td align="right" colspan="7"><strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING') ?>:</strong></td>
				<td width="5%" align="right" style="padding-right: 5px;"><?php echo $GLOBALS['CURRENCY_DISPLAY']->getFullValue($db->f("order_shipping"), '', $db->f('order_currency')) ?></td>
			  </tr>
			  <tr> 
				<td align="right" colspan="7"><strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING_TAX') ?>:</strong></td>
				<td width="5%" align="right" style="padding-right: 5px;"><?php echo $GLOBALS['CURRENCY_DISPLAY']->getFullValue($db->f("order_shipping_tax"), '', $db->f('order_currency')) ?></td>
			  </tr>
	  <?php
			if( PAYMENT_DISCOUNT_BEFORE != '1') {
			  if ($db->f("order_discount") != 0) {
	  ?>
			  <tr> 
				<td align="right" colspan="7"><strong><?php 
				  if( $db->f("order_discount") > 0)
					echo JText::_('VM_PAYMENT_METHOD_LIST_DISCOUNT');
				  else
					echo JText::_('VM_FEE');
					?>:</strong></td>
				<td width="5%" align="right" style="padding-right: 5px;"><?php
					  if ($db->f("order_discount") > 0 )
					 echo "-" . $GLOBALS['CURRENCY_DISPLAY']->getFullValue(abs($db->f("order_discount")), '', $db->f('order_currency'));
				elseif ($db->f("order_discount") < 0 )
					 echo "+" . $GLOBALS['CURRENCY_DISPLAY']->getFullValue(abs($db->f("order_discount")), '', $db->f('order_currency')); ?>
				  </td>
			  </tr>
			  
			  <?php 
			  } 
			  if( $coupon_discount > 0 || $coupon_discount < 0) {
	  ?>
			  <tr> 
  				<td align="right" colspan="7"><strong><?php echo JText::_('VM_COUPON_DISCOUNT') ?>:</strong></td> 
  				<td width="5%" align="right" style="padding-right: 5px;"><?php echo "- ".$GLOBALS['CURRENCY_DISPLAY']->getFullValue( $coupon_discount, '', $db->f('order_currency') ); ?></td>
			  </tr>
			  <?php
			  }
			}
	  ?>
			  <tr>
				<td align="right" colspan="7"><strong><?php echo JText::_('VM_CART_TOTAL') ?>:</strong></td>
				<td width="5%" align="right" style="padding-right: 5px;"><strong><?php echo $GLOBALS['CURRENCY_DISPLAY']->getFullValue($db->f("order_total"), '', $db->f('order_currency')); ?></strong>
				  </td>
			  </tr>
			  <?php
				  // Get the tax details, if any
				  $tax_details = ps_checkout::show_tax_details( $db->f('order_tax_details'), $db->f('order_currency') );
			  ?>
			  <?php if( !empty( $tax_details ) ) : ?>
			  <tr>
				<td colspan="8" align="right"><?php echo $tax_details; ?></td>
			  </tr>
			  <?php endif; ?>
			  </table>
        <?php $ps_order_change_html->html_change_add_item() ?>  
		  </td>
		</tr>
	</table>
	&nbsp;
	<table class="adminlist">
		<tr>
  		<td valign="top" width="300">
    				<table class="adminlist">
    				  <tr>
      					<th ><?php 
      							echo JText::_('VM_ORDER_PRINT_SHIPPING_LBL') ?>
      					</th>
    				  </tr>
    				  <tr> 
    					  <td align="left">
        					<?php
                    if( $db->f("ship_method_id") ) { 
                      $details = explode( "|", $db->f("ship_method_id"));
                    }
                  ?>
    					    <strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING_CARRIER_LBL') ?>: </strong>
    						  <?php  echo $details[1]; ?>&nbsp;
                </td>
    	        </tr>
    				  <tr>
    					  <td align="left">
    					    <strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING_MODE_LBL') ?>: </strong>
    					    <?php echo $details[2]; ?>
                </td>
    				  </tr>
    				  <tr>
    					  <td align="left">
    					    <strong><?php echo JText::_('VM_ORDER_PRINT_SHIPPING_PRICE_LBL') ?>: </strong>
    					    <?php echo $GLOBALS['CURRENCY_DISPLAY']->getFullValue($details[3], '', $db->f('order_currency')); ?>
    					  </td>
    				  </tr>
              <?php $ps_order_change_html->html_change_shipping() ?>  
    				</table>
  			  </td>

  			  <?php
  			    $dbpm =& new ps_DB;
  				$q  = "SELECT * FROM #__{vm}_payment_method, #__{vm}_order_payment WHERE #__{vm}_order_payment.order_id='$order_id' ";
  				$q .= "AND #__{vm}_payment_method.payment_method_id=#__{vm}_order_payment.payment_method_id";
  				$dbpm->query($q);
  				$dbpm->next_record();
  			   
  				// DECODE Account Number
  				$dbaccount =& new ps_DB;
  			    $q = "SELECT ".VM_DECRYPT_FUNCTION."(order_payment_number,'".ENCODE_KEY."')
  					AS account_number, order_payment_code FROM #__{vm}_order_payment  
  					WHERE order_id='".$order_id."'";
  				$dbaccount->query($q);
  				$dbaccount->next_record();
  				?>
  			  <!-- Payment Information -->
  			  <td valign="top" width="*">
    				<table class="adminlist">
    				  <tr class="sectiontableheader"> 
      					<th width="13%"><?php echo JText::_('VM_ORDER_PRINT_PAYMENT_LBL') ?></th>
      					<th width="40%"><?php echo JText::_('VM_ORDER_PRINT_ACCOUNT_NAME') ?></th>
      					<th width="30%"><?php echo JText::_('VM_ORDER_PRINT_ACCOUNT_NUMBER'); ?></th>
      					<th width="17%"><?php echo JText::_('VM_ORDER_PRINT_EXPIRE_DATE') ?></th>
    				  </tr>
    				  <tr> 
      					<td width="13%">
                  <?php $ps_order_change_html->html_change_payment($dbpm->f("id")) ?>
                </td>
      					<td width="40%"><?php $dbpm->p("order_payment_name");?></td>
      					<td width="30%"><?php 
        					echo ps_checkout::asterisk_pad( $dbaccount->f("account_number"), 4, true );
        					if( $dbaccount->f('order_payment_code')) {
        						echo '<br/>(' . JText::_('VM_ORDER_PAYMENT_CCV_CODE') . ': '.$dbaccount->f('order_payment_code').') ';
        					}
        					?>
                </td>
      					<td width="17%"><?php echo vmFormatDate( $dbpm->f("order_payment_expire"), '%b-%Y'); ?></td>
    				  </tr> 
    				  <tr class="sectiontableheader"> 
      					<th colspan="4"><?php echo JText::_('VM_ORDER_PRINT_PAYMENT_LOG_LBL') ?></th>
    				  </tr>
    				  <tr> 
      					<td colspan="4"><?php if($dbpm->f("order_payment_log")) echo $dbpm->f("order_payment_log"); else echo "./."; ?></td>
    				  </tr>
    				  <tr>
    				    <td colspan="2" align="center">
                  <?php $ps_order_change_html->html_change_discount() ?>
                </td>
                <td colspan="2" align="center">
                  <?php $ps_order_change_html->html_change_coupon_discount() ?>
                </td>
              </tr>
    				</table>
  		  </td>
  		</tr>
  		<tr>
  			  <!-- Customer Note -->
  			  <td valign="top" width="30%" colspan="2">
    				<table class="adminlist">
    				  <tr>
    					  <th><?php echo JText::_('VM_ORDER_PRINT_CUSTOMER_NOTE') ?></th>
    				  </tr>
    				  <tr>
    				    <td valign="top" align="center" width="50%">
                  <?php $ps_order_change_html->html_change_customer_note() ?>  
    					  </td>
    				  </tr>
    				</table>
  			  </td>
			  </tr>
	  </table>
<?php
}
?>