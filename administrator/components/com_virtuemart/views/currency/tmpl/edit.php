<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Currency
* @author Max Milbers, RickG
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
?>

<form action="index.php" method="post" name="adminForm">


<div class="col50">
	<fieldset class="adminform">
	<legend><?php echo JText::_('VM_CURRENCY_LIST_DETAILS'); ?></legend>
	<table class="admintable">			
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_( 'VM_CURRENCY_LIST_NAME' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="currency_name" id="currency_name" size="50" value="<?php echo $this->currency->currency_name; ?>" />				
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('PUBLISHED'); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $this->currency->published); ?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_( 'VM_CURRENCY_LIST_EXCHANGE_RATE' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="exchange_rate" id="exchange_rate" size="6" value="<?php echo $this->currency->exchange_rate; ?>" />				
			</td>
		</tr>			
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_( 'VM_CURRENCY_LIST_CODE_2' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="currency_code_2" id="currency_code_2" size="2" value="<?php echo $this->currency->currency_code_2; ?>" />				
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_( 'VM_CURRENCY_LIST_CODE_3' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="currency_code" id="currency_code" size="3" value="<?php echo $this->currency->currency_code; ?>" />				
			</td>
		</tr>
<?php /*		<tr>
		<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('start Date'); ?>:
				</label>
			</td>
			<td>
				<?php
					$startDate = JFactory::getDate($this->currency->publish_up,$this->tzoffset);
					echo JHTML::_('calendar', $startDate->toFormat($this->dateformat), "publish_up", "publish_up", $this->dateformat);
 				?>
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_('end Date'); ?>:
				</label>
			</td>
			<td>
				<?php $endDate;
				if (empty($this->currency->publish_down) || !strcmp($this->currency->publish_down,'0000-00-00 00:00:00')  ) {
					$endDate = JText::_('VM_NEVER');
				} else {
					$date = JFactory::getDate($this->currency->publish_down,$this->tzoffset);
					$endDate = $date->toFormat($this->dateformat);
				}
				echo JHTML::_('calendar', $endDate, "publish_down", "publish_down", $this->dateformat,array('class'=>'inputbox', 'size'=>'25',  'maxlength'=>'19')); ?>
			</td>
		</tr> */ ?>
	<tr> 
		<td class="key">
			<?php echo JText::_( 'VM_CURRENCY_SYMBOL' ); ?>:
		</td>
		<td>
			<input type="hidden" name="currency_display_style[0]" value="<?php echo $this->currency->vendor_id; ?>" />
			<input class="inputbox" type="text" name="currency_display_style[1]" id="currency_symbol" size="10" value="<?php echo $this->currencyDisplay->getSymbol(); ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<?php echo JText::_( 'VM_CURRENCY_DECIMALS' ); ?>:
		</td>
		<td>
			<input class="inputbox" type="text" name="currency_display_style[2]" id="currency_nbr_decimals" size="10" value="<?php echo $this->currencyDisplay->getNbrDecimals(); ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<?php echo JText::_( 'VM_CURRENCY_DECIMALSYMBOL' ); ?>:
		</td>
		<td>
			<input class="inputbox" type="text" name="currency_display_style[3]" id="currency_decimal_symbol" size="10" value="<?php echo $this->currencyDisplay->getDecimalSymbol(); ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<?php echo JText::_( 'VM_CURRENCY_THOUSANDS' ); ?>:
		</td>
		<td>
			<input class="inputbox" type="text" name="currency_display_style[4]" id="currency_thousands_seperator" size="10" value="<?php echo $this->currencyDisplay->getThousandsSeperator(); ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<?php echo JText::_( 'VM_CURRENCY_POSITIVE_DISPLAY' ); ?>:
		</td>
		<td>
			<?php
				$options = array();
				$options[] = JHTML::_('select.option', '0', JText::_('00Symb') );
				$options[] = JHTML::_('select.option', '1', JText::_('00 Symb'));
				$options[] = JHTML::_('select.option', '2', JText::_('Symb00'));
				$options[] = JHTML::_('select.option', '3', JText::_('Symb 00'));
				echo JHTML::_('Select.genericlist', $options, 'currency_display_style[5]', 'size=1', 'value', 'text', $this->currencyDisplay->getPositiveFormat());
			?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<?php echo JText::_( 'VM_CURRENCY_NEGATIVE_DISPLAY' ); ?>:
		</td>
		<td>
			<?php
				$options = array();
				$options[] = JHTML::_('select.option', '0', JText::_('(Symb00)') );
				$options[] = JHTML::_('select.option', '1', JText::_('-Symb00'));
				$options[] = JHTML::_('select.option', '2', JText::_('Symb00-'));
				$options[] = JHTML::_('select.option', '3', JText::_('(00Symb)'));
				$options[] = JHTML::_('select.option', '4', JText::_('-00Symb') );
				$options[] = JHTML::_('select.option', '5', JText::_('00-Symb'));
				$options[] = JHTML::_('select.option', '6', JText::_('00Symb-'));
				$options[] = JHTML::_('select.option', '7', JText::_('-00 Symb'));
				$options[] = JHTML::_('select.option', '8', JText::_('-Symb 00'));
				$options[] = JHTML::_('select.option', '9', JText::_('00 Symb-') );
				$options[] = JHTML::_('select.option', '10', JText::_('Symb 00-'));
				$options[] = JHTML::_('select.option', '11', JText::_('Symb -00'));
				$options[] = JHTML::_('select.option', '12', JText::_('(Symb 00)'));
				$options[] = JHTML::_('select.option', '13', JText::_('(00 Symb)'));
				echo JHTML::_('Select.genericlist', $options, 'currency_display_style[6]', 'size=1', 'value', 'text', $this->currencyDisplay->getNegativeFormat());
			?>
		</td>
	</tr>
	</table>
	</fieldset>
</div>
	<input type="hidden" name="vendor_id" value="<?php echo $this->currency->vendor_id; ?>" />
	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="currency_id" value="<?php echo $this->currency->currency_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="currency" />
</form>


<?php AdminMenuHelper::endAdminArea(); ?> 