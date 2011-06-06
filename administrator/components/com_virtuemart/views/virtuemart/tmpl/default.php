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

AdminMenuHelper::startAdminArea(); 

JToolBarHelper::title(JText::_('COM_VIRTUEMART')." ".JText::_('COM_VIRTUEMART_CONTROL_PANEL'), 'vm_store_48');

$pane = JPane::getInstance('tabs', array('startOffset'=>0)); 
echo $pane->startPane( 'pane' );
echo $pane->startPanel(JText::_('COM_VIRTUEMART_CONTROL_PANEL'), 'control_panel');
?>
<br />
<div id="cpanel">
	<div class="icon"><?php VmImage::displayImageButton(JROUTE::_('index.php?option=com_virtuemart&view=product'), 'vm_shop_products_48.png', JText::_('COM_VIRTUEMART_PRODUCT_S')); ?></div>
	<div class="icon"><?php VmImage::displayImageButton(JROUTE::_('index.php?option=com_virtuemart&view=category'), 'vm_shop_categories_48.png', JText::_('COM_VIRTUEMART_CATEGORY_S')); ?></div>
	<div class="icon"><?php VmImage::displayImageButton(JROUTE::_('index.php?option=com_virtuemart&view=orders'), 'vm_shop_orders_48.png', JText::_('COM_VIRTUEMART_ORDER_S')); ?></div>
	<div class="icon"><?php VmImage::displayImageButton(JROUTE::_('index.php?option=com_virtuemart&view=paymentmethod'), 'vm_shop_payment_48.png', JText::_('COM_VIRTUEMART_PAYMENTMETHOD_S')); ?></div>
	<div class="icon"><?php VmImage::displayImageButton(JROUTE::_('index.php?option=com_virtuemart&view=user'), 'vm_shop_users_48.png', JText::_('COM_VIRTUEMART_USER_S')); ?></div>
	<div class="icon"><?php VmImage::displayImageButton(JROUTE::_('index.php?option=com_virtuemart&view=config'), 'vm_shop_configuration_48.png', JText::_('COM_VIRTUEMART_CONFIG')); ?></div>
	<div class="icon"><?php VmImage::displayImageButton(JROUTE::_('index.php?option=com_virtuemart&view=user&task=editshop'), 'vm_shop_mart_48.png', JText::_('COM_VIRTUEMART_STORE')); ?></div>
	<div class="icon"><?php VmImage::displayImageButton(JROUTE::_('http://virtuemart.org/index.php?option=com_content&amp;task=view&amp;id=248&amp;Itemid=125'), 'vm_shop_help_48.png', JText::_('COM_VIRTUEMART_DOCUMENTATION')); ?></div>
</div>
<?php
echo $pane->endPanel();
echo $pane->startPanel(JText::_('COM_VIRTUEMART_STATISTIC_STATISTICS'), 'statistics_page');
?>
<br />
	<table class="adminlist">
		<tr>
			<th colspan="2" class="title"><?php echo JText::_('COM_VIRTUEMART_STATISTIC_STATISTICS') ?></th>
		</tr>
		<tr> 
		  	<td width="50%">
		  		<a href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=user');?>">
					<?php echo JText::_('COM_VIRTUEMART_STATISTIC_CUSTOMERS') ?>
				</a>
			</td>			
		  	<td width="50%"> <?php echo $this->nbrCustomers ?></td>
		</tr>
		<tr> 
		  	<td width="50%">
		  		<a href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=product');?>">
					<?php echo JText::_('COM_VIRTUEMART_STATISTIC_ACTIVE_PRODUCTS') ?>
				</a>
			</td>
		  <td width="50%"> <?php echo $this->nbrActiveProducts ?> </td>
		</tr>
		<tr> 
		  <td width="50%"><?php echo JText::_('COM_VIRTUEMART_STATISTIC_INACTIVE_PRODUCTS') ?>:</td>
		  <td width="50%"> <?php  echo $this->nbrInActiveProducts ?></td>
		</tr>
		<tr> 
			<td width="50%">
		  		<a href="<?php echo JROUTE::_('index.php?option=com_virtuemart&page=product.specialprod&filter=featured');?>">
					<?php echo JText::_('COM_VIRTUEMART_SHOW_FEATURED') ?>
				</a>
			</td>
		  <td width="50%"><?php echo $this->nbrFeaturedProducts ?></td>
		</tr>
		<tr>
			<th colspan="2" class="title">
		  		<a href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=orders');?>">
					<?php echo JText::_('COM_VIRTUEMART_ORDER_MOD') ?>
				</a>
			</th>
		</tr>
		<?php 
		$sum = 0;
		for ($i=0, $n=count( $this->ordersByStatus ); $i < $n; $i++) {
			$row = $this->ordersByStatus[$i]; 
			$link = JROUTE::_('index.php?option=com_virtuemart&view=orders&show='.$row->order_status_code);
			?>
			<tr>
		  		<td width="50%">
		  			<a href="<?php echo $link; ?>"><?php echo $row->order_status_name; ?></a>
				</td>
		  		<td width="50%">
		  			<?php echo $row->order_count; ?>
		  		</td>
			</tr>
		<?php 
			$sum = $sum + $row->order_count;
		} ?>
		<tr> 
		  <td width="50%"><strong><?php echo JText::_('COM_VIRTUEMART_STATISTIC_SUM') ?>:</strong></td>
		  <td width="50%"><strong><?php echo $sum ?></strong></td>
		</tr>
		<tr>
			<th colspan="2" class="title"><?php echo JText::_('COM_VIRTUEMART_STATISTIC_NEW_ORDERS') ?></th>
		</tr>
		<?php 
		for ($i=0, $n=count($this->recentOrders); $i < $n; $i++) {
			$row = $this->recentOrders[$i];
			$link = JROUTE::_('index.php?option=com_virtuemart&page=order.order_print&virtuemart_order_id='.$row->virtuemart_order_id);
			?> 
		  	<tr>
				<td width="50%">
					<a href="<?php echo $link; ?>"><?php echo $row->virtuemart_order_id; ?></a>
			  	</td>
				<td width="50%">
					(<?php echo 'Here was some strange total and vendor currency' ?>)
				</td>
			</tr>
			<?php 
		} ?>
		<tr> 
		  <th colspan="2" class="title"><?php echo JText::_('COM_VIRTUEMART_STATISTIC_NEW_CUSTOMERS') ?></th>
		</tr>
		<?php 
		for ($i=0, $n=count($this->recentCustomers); $i < $n; $i++) {
			$row = $this->recentCustomers[$i];
			$link = JROUTE::_('index.php?option=com_virtuemart&view=user&virtuemart_user_id='.$row->virtuemart_user_id);
			?>
			<tr>
		  		<td colspan="2">
		  			<a href="<?php echo $link; ?>">
		  				<?php echo '(' . $row->virtuemart_order_id . ') ' . $row->first_name . ' ' . $row->last_name; ?>
		  			</a>
		  		</td>
			</tr>
		<?php 
		}?>	
	</table>
<?php
echo $pane->endPanel();
echo $pane->endPane();

AdminMenuHelper::endAdminArea();
?>