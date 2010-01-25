<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage 
* @author
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'image.php');

$iCol = 1;
//Number of featured products to show per row
$featured_per_row = 2;
//Set the cell width
$cellwidth = intval( (100 / $featured_per_row) - 2 );

echo "<h3>".JText::_('VM_FEATURED_PRODUCT')."</h3>";
foreach ($this->featuredProducts as $featured) {
	?>
	<div style="float:left;width:<?php echo $cellwidth ?>%;text-align:top;padding:0px;" >
		<?php
		echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&flypage='.$featured->flypage.'&product_id='.$featured->product_id), $featured->product_name);
		?>
			<h4><?php echo $featured->product_name; ?></h4></a>
			<?php echo JRequest::getVar('currencyDisplay')->getFullValue($featured->product_price['salesPrice']); ?><br />
			<span class="product-Old-Price">
			<?php echo JRequest::getVar('currencyDisplay')->getFullValue($featured->product_price['basePriceWithTax']); ?></span>
            <?php
			if ($featured->product_thumb_image) {
				echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&flypage='.$featured->flypage.'&product_id='.$featured->product_id), 
					ImageHelper::displayShopImage($featured->product_thumb_image, 'product', 'class="browseProductImage" border="0" alt="'.$featured->product_name.'"'));
			?>
			<br /><br/>
            <?php } ?>
            <?php echo $featured->product_s_desc; ?><br />
            
            <?php 
				JRequest::setVar('product', $featured);
            	echo $this->loadTemplate('addtocart_form'); 
            ?>
	</div>
	<?php
	// Do we need to close the current row now?
	if ($iCol == $featured_per_row) { // If the number of products per row has been reached
		echo "<br style=\"clear:both;\" />\n";
		$iCol = 1;
	}
	else {
		$iCol++;
	}
}
?>
<br style="clear:both;" />