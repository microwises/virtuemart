<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Paymentmethod
* @author Max Milbers
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
//if($vendor_id==1 || $perm->check( 'admin' )){

?>
      	
<form action="index.php" method="post" name="adminForm">
	<div id="editcell">
		<table class="adminlist">
		<thead>
		<tr>
			<th>
				<?php echo JText::_('#'); ?>
			</th>		            
			<th width="10">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->payms); ?>);" />
			</th>			
			<th width="60">
				<?php echo JText::_( 'COM_VIRTUEMART_PAYM_LIST_NAME' ); ?>
			</th>
			<?php if($this->perms->check( 'admin' )){ ?>
			<th width="20">
				<?php echo JText::_( 'COM_VIRTUEMART_CALC_VENDOR' );  ?>
			</th><?php }?>
			<th width="20">
				<?php echo JText::_( 'COM_VIRTUEMART_PAYM_ELEMENT' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'COM_VIRTUEMART_PAYM_SHOPPERGROUPS' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'COM_VIRTUEMART_PAYM_DISCOUNT' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'COM_VIRTUEMART_PAYM_IS_PERCENTAGE' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'COM_VIRTUEMART_PAYM_MIN_DISCOUNT' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'COM_VIRTUEMART_PAYM_MAX_DISCOUNT' ); ?>
			</th>
<?php /*			<th width="20">
				<?php echo JText::_( 'COM_VIRTUEMART_PAYM_TYPE' ); ?>
			</th>  */?>
			<th width="10">
				<?php echo JText::_( 'COM_VIRTUEMART_PUBLISHED' ); ?>
			</th>
			<th width="10">
				<?php echo JText::_( 'COM_VIRTUEMART_CALC_SHARED' ); ?>
			</th>
		</tr>
		</thead>
		<?php
		$k = 0;

		for ($i=0, $n=count( $this->payms ); $i < $n; $i++) {
			
			$row = $this->payms[$i];
			$checked = JHTML::_('grid.id', $i, $row->paym_id);
			$published = JHTML::_('grid.published', $row, $i);
			$editlink = JROUTE::_('index.php?option=com_virtuemart&controller=paymentmethod&task=edit&cid[]=' . $row->paym_id);
			?>
			<tr class="<?php echo "row".$k; ?>">
				<td width="10" align="right">
					<?php echo $row->paym_id; ?>
				</td>			            
				<td width="10">
					<?php echo $checked; ?>
				</td>
				<td align="left">
					<a href="<?php echo $editlink; ?>"><?php echo $row->paym_name; ?></a>
				</td>
				<?php if($this->perms->check( 'admin' )){?>				
				<td align="left">
					<?php echo JText::_($row->paym_vendor_id); ?>
				</td>
				<?php } ?>
				<td>
					<?php echo $row->paym_element; ?>
				</td>
				<td>
					<?php echo $row->paymShoppersList; ?>
				</td>
				<td>
					<?php echo $row->discount; ?>
				</td>
				<td>
					<?php echo $row->discount_is_percentage; ?>
				</td>
				<td>
					<?php echo $row->discount_min_amount; ?>
				</td>
				<td>
					<?php echo $row->discount_max_amount; ?>
				</td>
<?php /*				<td>
					<?php 
					switch($row->paym_type) { 
						case "Y": 
							$tmp_cell = JText::_('COM_VIRTUEMART_PAYMENT_FORM_USE_PP');
							break;
						case "N":
							$tmp_cell = JText::_('COM_VIRTUEMART_PAYMENT_FORM_AO');
							break;
						case "B":
							$tmp_cell = JText::_('COM_VIRTUEMART_PAYMENT_FORM_BANK_DEBIT');
							break;
						case "P":
							$tmp_cell = JText::_('COM_VIRTUEMART_PAYMENT_FORM_FORMBASED');
							break;
						default:
							$tmp_cell = JText::_('COM_VIRTUEMART_PAYMENT_FORM_CC');
							break;
					}
					echo $tmp_cell; ?>
				</td> */ ?>
				<td align="center">
					<?php echo $published; ?>
				</td>
				<td align="center">
					<?php echo $row->shared; ?>
				</td>				        																														
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		<tfoot>
			<tr>
				<td colspan="21">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>		
	</table>	
</div>
	        
	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="controller" value="paymentmethod" />
	<input type="hidden" name="view" value="paymentmethod" />	
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
            
            
<?php AdminMenuHelper::endAdminArea(); ?> 