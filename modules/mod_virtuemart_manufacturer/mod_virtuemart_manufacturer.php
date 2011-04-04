<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
* manufacturer Module
*
* @package VirtueMart
* @subpackage modules
*
* 	@copyright (C) 2010 - Patrick Kohl
// W: demo.st42.fr
// E: cyber__fr|at|hotmail.com
*
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*/
/* Load  VM fonction */ 
require('helper.php');

/* Setting */
$vendorId = JRequest::getInt('vendorid', 1);
$model = new VirtueMartModelManufacturer();

$display_style = 	$params->get( 'display_style', "div" ); // Display Style
$manufacturers_per_row = $params->get( 'manufacturers_per_row', 1 ); // Display X manufacturers per Row
$headerText = 		$params->get( 'headerText', '' ); // Display a Header Text
$footerText = 		$params->get( 'footerText', ''); // Display a footerText
$show = 			$params->get( 'show', 'all'); // Display a footerText
$manufacturers = $model->getManufacturers(true, true);
$model->addImagesToManufacturer($manufacturers);
if(empty($manufacturers)) return false;
/* load the template */
require(JModuleHelper::getLayoutPath('mod_virtuemart_manufacturer'));
?>