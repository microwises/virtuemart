<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: list_shipping_methods.tpl.php 1755 2009-05-01 22:45:17Z rolandd $
* @package VirtueMart
* @subpackage templates
* @copyright Copyright (C) 2007-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

// Import all published Shipping Plugins
vmPluginHelper::importPlugin('shipping');
// Then call the method "get_shipping_rate_list" on all shipping plugins
$result = $vm_mainframe->triggerEvent('get_shipping_rate_list', array( $vars ));
if( !empty( $result )) {
	$i = 0;
	// Loop through each plugin's rate list
	foreach( $result as $shipping_module ) {
		$carrier = '';
		if( !empty( $shipping_module[$i]['carrier'])) {
			$carrier = $shipping_module[$i++]['carrier'];
			echo '<fieldset><legend>'.$carrier."</legend>\n";
		}
		// Loop through each rate of this shipping plugin
		foreach( $shipping_module as $rate ) {
			if( $rate['carrier'] != $carrier ) {
				$carrier = $rate['carrier'];
				echo "\n</fieldset>";
				echo '<fieldset><legend>'.$carrier."</legend>\n";
				$fieldSetOpened = true;
			}
			$id = uniqid($carrier);
			$rate_name_display = $rate['rate_name'];
			if(!empty($rate['delivery_date'])) {
				$rate_name_display .= ', Delivery: '.$rate['delivery_date'];
			}
			$rate_name_display .= ' - <strong>'.$CURRENCY_DISPLAY->getfullvalue($rate['rate']).'</strong>';
			if( !empty($rate['rate_tip'])) {
				$rate_name_display .= vmToolTip($rate['rate_tip'] );
			}
			$checked = $rate['shipping_rate_id'] == urlencode($vars['shipping_rate_id']) ? ' checked="checked"' : '';
			echo '<input type="radio" name="shipping_rate_id" value="'.$rate['shipping_rate_id'].'"'.$checked.' id="'.$id.'" />';
			echo '<label for="'.$id.'">'.$rate_name_display."</label><br />\n";
		}
	
	}
	echo "\n</fieldset>";
}

?>