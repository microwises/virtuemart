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

AdminMenuHelper::startAdminArea(); 
//if($vendor_id==1 || $perm->check( 'admin' )){

?>
      	
<form action="index.php" method="post" name="adminForm">
	<div id="editcell">
		<table class="adminlist">
		<thead>
		<tr>
			<th>
				<?php echo JText::_( '#' ); ?>
			</th>		            
			<th width="10">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->calcs); ?>);" />
			</th>			
			<th width="60">
				<?php echo JText::_( 'VM_CALC_LIST_NAME' ); ?>
			</th>
			<?php if(Permissions::check( 'admin' )){ ?>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_VENDOR' );  ?>
			</th><?php }?>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_DESCR' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_ORDERING' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_KIND' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_VALUE_MATHOP' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_VALUE' ); ?>
			</th>
			<th width="10">
				<?php echo JText::_( 'VM_CALC_CURRENCY' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_CATEGORY' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_SHOPPER_GROUPS' ); ?>
			</th>
			<th width="10">
				<?php echo JText::_( 'VM_CALC_VIS_SHOPPER' ); ?>
			</th>
			<th width="10">
				<?php echo JText::_( 'VM_CALC_VIS_VENDOR' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_START_DATE' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_END_DATE' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_AMOUNT_COND' ); ?>
			</th>
			<th width="10">
				<?php echo JText::_( 'VM_CALC_AMOUNT_DIMUNIT' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_COUNTRIES' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'VM_CALC_STATES' ); ?>
			</th>
			<th width="10">
				<?php echo JText::_( 'PUBLISHED' ); ?>
			</th>
			<th width="10">
				<?php echo JText::_( 'VM_CALC_SHARED' ); ?>
			</th>
		</tr>
		</thead>
		<?php
		$k = 0;

		for ($i=0, $n=count( $this->calcs ); $i < $n; $i++) {
			
			$row = $this->calcs[$i];
			$checked = JHTML::_('grid.id', $i, $row->calc_id);
			$published = JHTML::_('grid.published', $row, $i);
			$editlink = JROUTE::_('index.php?option=com_virtuemart&controller=calc&task=edit&cid[]=' . $row->calc_id);
			?>
			<tr class="<?php echo "row".$k; ?>">
				<td width="10" align="right">
					<?php echo $row->calc_id; ?>
				</td>			            
				<td width="10">
					<?php echo $checked; ?>
				</td>
				<td align="left">
					<a href="<?php echo $editlink; ?>"><?php echo $row->calc_name; ?></a>
				</td>
				<?php if(Permissions::check( 'admin' )){?>				
				<td align="left">
					<?php echo JText::_($row->calc_vendor_id); ?>
				</td>
				<?php } ?>
				<td>
					<?php echo JText::_($row->calc_descr); ?>
				</td>
				<td>
					<?php echo JText::_($row->ordering); ?>
				</td>				
				<td>
					<?php echo JText::_($row->calc_kind); ?>
				</td>
				<td>
					<?php echo JText::_($row->calc_value_mathop); ?>
				</td>
				<td>
					<?php echo JText::_($row->calc_value); ?>
				</td>
				<td>
					<?php echo JText::_($row->calc_currency); ?>
				</td>				
				<td>
					<?php echo JText::_($row->calcCategoriesList); ?>
				</td>
				<td>
					<?php echo JText::_($row->calcShoppersList); ?>
				</td>
				<td align="center">
					<a href="#" onclick="return listItemTask('cb<?php echo $i;?>', 'toggleShopper')" title="<?php echo ( $row->calc_shopper_published == '1' ) ? JText::_( 'Yes' ) : JText::_( 'No' );?>">
						<img src="images/<?php echo ( $row->calc_shopper_published) ? 'tick.png' : 'publish_x.png';?>" width="16" height="16" border="0" alt="<?php echo ( $row->calc_shopper_published == '1' ) ? JText::_( 'Yes' ) : JText::_( 'No' );?>" />
					</a>
				</td>
				<td align="center">
					<a href="#" onclick="return listItemTask('cb<?php echo $i;?>', 'toggleVendor')" title="<?php echo ( $row->calc_vendor_published == '1' ) ? JText::_( 'Yes' ) : JText::_( 'No' );?>">
						<img src="images/<?php echo ( $row->calc_vendor_published) ? 'tick.png' : 'publish_x.png';?>" width="16" height="16" border="0" alt="<?php echo ( $row->calc_vendor_published == '1' ) ? JText::_( 'Yes' ) : JText::_( 'No' );?>" />
					</a>
				</td>
				<td>
					<?php 
					$publish_up ='';
					if(strcmp($row->publish_up,'0000-00-00 00:00:00')){
						$date = JFactory::getDate($row->publish_up, $this->tzoffset);
						$publish_up = $date->toFormat(VM_DATE_FORMAT);
					}
					echo $publish_up?>
				</td>
				<td>
					<?php 
						if (!strcmp($row->publish_down,'0000-00-00 00:00:00')) {
							$endDate = JText::_('Never');
						} else {
							$date = JFactory::getDate($row->publish_down,$this->tzoffset);
							$endDate = $date->toFormat(VM_DATE_FORMAT);
						}
//						echo JHTML::_('calendar', $endDate->toFormat(VM_DATE_FORMAT), "publish_down", "publish_down", VM_DATE_FORMAT);
//					$publish_down ='';
//					if(strcmp($row->publish_down,'0000-00-00 00:00:00')){
//						$date = JFactory::getDate($row->publish_down, $row->tzoffset);
//						$publish_down = $date->toMySQL();
//					}
					echo $endDate?>
				</td>
				<td>
					<?php echo JText::_($row->calc_amount_cond); ?>
				</td>
				<td>
					<?php echo JText::_($row->calc_amount_dimunit); ?>
				</td>
				<td>
					<?php echo JText::_($row->calcCountriesList); ?>
				</td>
				<td>
					<?php echo JText::_($row->calcStatesList); ?>
				</td>
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
	<input type="hidden" name="controller" value="calc" />
	<input type="hidden" name="view" value="calc" />	
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
            
            
<?php AdminMenuHelper::endAdminArea(); ?> 