<?php 
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: shop.product_details.php,v 1.2 2006/03/30 11:23:24 M Exp $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2005 Soeren Eberhardt. All rights reserved.
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
/**
	 * Available indexes:
	 * 
	 * $product_id => The actual product id of the current product
	 * $product_type_name => The name of the product type
	 * $custom_parameters => array of the following items
	 * 		$custom_parameters["parameter_label"] => The lablel for the parameter
	 * 		$custom_parameters["parameter_description"] => The description of the parameter
	 * 		$custom_parameters["parameter_tooltip"] => The description of the parameter formed into a tooltip
	 * 		$custom_parameters["parameter_value"] => The value of the parameter
	 * 		$custom_parameters["parameter_unit"] => The unit value of the value
	 * 		$custom_parameters["parameter_name"] => name of the parameter
	 */
	
	$tpl = vmTemplate::getInstance();
	$product_types["product_type_name"] = $product_type_name;
	$product_types["parameters"] = $custom_parameters;
	$tpl->set( 'product_types', $product_types );
	$html = $tpl->fetch( 'common/product_type.tpl.php' );
	return $html;		
?>