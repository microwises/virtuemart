<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Calculation tool
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

AdminUIHelper::startAdminArea();

$j15 = VmConfig::isJ15();
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<div id="header">
		<div id="filterbox">
		<table>
		  <tr>
			 <td align="left">
				<?php echo ShopFunctions::displayDefaultViewSearch() ?>
			 </td>
		  </tr>
		</table>
		</div>
		<div id="resultscounter" ><?php echo $this->pagination->getResultsCounter();?></div>
	</div>
	<br />
	<div id="editcell">
		<table class="admin-table" cellspacing="0" cellpadding="0">
		<thead>
		<tr>
			<th>
				<?php echo JText::_('COM_VIRTUEMART_#'); ?>
			</th>
			<th>
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->calcs); ?>);" />
			</th>
			<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_NAME', 'calc_name', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
			<?php /* if($this->perms->check( 'admin' )){ ?>
			<th width="20">
				<?php echo JText::_('COM_VIRTUEMART_CALC_VENDOR');  ?>
			</th><?php } */ ?>
			<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_DESCRIPTION', 'calc_descr', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
			<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_ORDERING', 'ordering', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
			<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_CALC_KIND', 'calc_kind', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
			<th><?php echo JText::_('COM_VIRTUEMART_CALC_VALUE_MATHOP'); ?></th>
			<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_VALUE', 'calc_value', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
			<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_CURRENCY', 'calc_currency', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
			<th><?php echo JText::_('COM_VIRTUEMART_CATEGORY_S'); ?></th>
			<th><?php echo JHTML::_('grid.sort', 'COM_VIRTUEMART_SHOPPERGROUP_IDS', 'calc_currency', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th>
			<th><?php echo JText::_('COM_VIRTUEMART_CALC_VIS_SHOPPER'); ?></th>
<?php /*	<th width="10"><?php echo JText::_('COM_VIRTUEMART_CALC_VIS_VENDOR'); ?></th> */  ?>
			<th><?php echo JText::_('COM_VIRTUEMART_START_DATE'); ?></th>
			<th><?php echo JText::_('COM_VIRTUEMART_END_DATE'); ?></th>
<?php /*	<th width="20"><?php echo JText::_('COM_VIRTUEMART_CALC_AMOUNT_COND'); ?></th>
			<th width="10"><?php echo JText::_('COM_VIRTUEMART_CALC_AMOUNT_DIMUNIT'); ?></th> */  ?>
			<th><?php echo JText::_('COM_VIRTUEMART_COUNTRY_S'); ?></th>
			<th><?php echo JText::_('COM_VIRTUEMART_STATE_IDS'); ?></th>
			<th><?php echo JText::_('COM_VIRTUEMART_PUBLISHED'); ?></th>
		<?php /*	<th width="10">
				<?php echo JText::_('COM_VIRTUEMART_CALC_SHARED'); ?>
			</th> */ ?>
		</tr>
		</thead>
		<?php
		$k = 0;

		for ($i=0, $n=count( $this->calcs ); $i < $n; $i++) {

			$row = $this->calcs[$i];
			$checked = JHTML::_('grid.id', $i, $row->virtuemart_calc_id);
			$published = JHTML::_('grid.published', $row, $i);
			$editlink = JROUTE::_('index.php?option=com_virtuemart&view=calc&task=edit&cid[]=' . $row->virtuemart_calc_id);
			?>
			<tr class="<?php echo "row".$k; ?>">
				<td align="right">
					<?php echo $row->virtuemart_calc_id; ?>
				</td>
				<td>
					<?php echo $checked; ?>
				</td>
				<td align="left">
					<a href="<?php echo $editlink; ?>"><?php echo $row->calc_name; ?></a>
				</td>
				<?php /* if($this->perms->check( 'admin' )){?>
				<td align="left">
					<?php echo $row->virtuemart_vendor_id; ?>
				</td>
				<?php } */ ?>
				<td>
					<?php echo $row->calc_descr; ?>
				</td>
				<td>
					<?php echo $row->ordering; ?>
				</td>
				<td>
					<?php echo $row->calc_kind; ?>
				</td>
				<td>
					<?php echo $row->calc_value_mathop; ?>
				</td>
				<td>
					<?php echo $row->calc_value; ?>
				</td>
				<td>
					<?php echo $row->currencyName; ?>
				</td>
				<td>
					<?php echo $row->calcCategoriesList; ?>
				</td>
				<td>
					<?php echo $row->calcShoppersList; ?>
				</td>
				<td align="center">
					<a href="#" onclick="return listItemTask('cb<?php echo $i;?>', 'toggle.calc_shopper_published')" title="<?php echo ( $row->calc_shopper_published == '1' ) ? JText::_('COM_VIRTUEMART_YES') : JText::_('COM_VIRTUEMART_NO');?>">
						<?php echo JHtml::_('image.administrator', ($j15 ? '' : 'admin/') . ($row->calc_shopper_published ? 'tick.png' : 'publish_x.png')); ?>
					</a>
				</td>
<?php /*				<td align="center">
					<a href="#" onclick="return listItemTask('cb<?php echo $i;?>', 'toggle.calc_vendor_published')" title="<?php echo ( $row->calc_vendor_published == '1' ) ? JText::_('COM_VIRTUEMART_YES') : JText::_('COM_VIRTUEMART_NO');?>">
						<?php echo JHtml::_('image.administrator', ($j15 ? '' : 'admin/') . ($row->calc_vendor_published ? 'tick.png' : 'publish_x.png')); ?>
					</a>
				</td> */  ?>
				<td>
					<?php
					$publish_up ='';
					if(strcmp($row->publish_up,'0000-00-00 00:00:00')){
						$date = JFactory::getDate($row->publish_up, $this->tzoffset);
						$publish_up = $date->toFormat($this->dateformat);
					}
					echo $publish_up = JText::_('COM_VIRTUEMART_NEVER');?>
				</td>
				<td>
					<?php
						if (!strcmp($row->publish_down,'0000-00-00 00:00:00')) {
							$endDate = JText::_('COM_VIRTUEMART_NEVER');
						} else {
							$date = JFactory::getDate($row->publish_down,$this->tzoffset);
							$endDate = $date->toFormat($this->dateformat);
						}
					echo $endDate?>
				</td>
<?php /*				<td>
					<?php echo $row->calc_amount_cond; ?>
				</td>
				<td>
					<?php echo JText::_($row->calc_amount_dimunit); ?> 
				</td> */  ?>
				<td>
					<?php echo JText::_($row->calcCountriesList); ?>
				</td>
				<td>
					<?php echo JText::_($row->calcStatesList); ?>
				</td>
				<td align="center">
					<?php echo $published; ?>
				</td>
				<?php /*
				<td align="center">
					<?php echo $row->shared; ?>
				</td>	*/?>
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
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="controller" value="calc" />
	<input type="hidden" name="view" value="calc" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>


<?php AdminUIHelper::endAdminArea(); ?>
