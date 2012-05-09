<?php defined('_JEXEC') or die('Restricted access');
/**
*
* @version $Id: virtuemart.php 5967 2012-04-29 23:17:14Z electrocity $
* @package VirtueMart
* @subpackage Klarna
* @author Valérie Isaksen
* @copyright Copyright (C) 2009-11 by the authors of the VirtueMart Team listed at /administrator/com_virtuemart/copyright.php - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/
?>
<?php
$js = '<script type="text/javascript">jQuery(document).find(".product_price").width("25%");</script>';
	$js .= '<style>';
	$js .= 'div.klarna_PPBox{z-index: 200 !important;}';
	$js .= 'div.cbContainer{z-index: 10000 !important;}';
	$js .= 'div.klarna_PPBox_bottomMid{overflow: visible !important;}';
	$js .= '</style>';
	//$html .= '<br>';
	if ($viewData['country'] == 'nl') {
	    $js .= '<style>.klarna_PPBox_topMid{width: 81%;}</style>';
	}
	$document = &JFactory::getDocument();
?>

<tr>
    <td style='text-align: left'>
        <?php echo $viewData['pp_title'] ?>
    </td>
    <td class='klarna_PPBox_pricetag'>
        <?php echo   $viewData['pp_price']   ?>
    </td>
</tr>
