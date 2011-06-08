<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Config
* @author RickG
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default_shopfront.php 3437 2011-06-06 14:47:08Z impleri $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
/*$orderByFieldsArray = $this->config->get('browse_orderby_fields');
$orderByFields = array();
$orderByFields[] = JHTML::_('select.option', 'ordering', JText::_('COM_VIRTUEMART_DEFAULT'));
$orderByFields[] = JHTML::_('select.option', 'product_name', JText::_('COM_VIRTUEMART_PRODUCT_NAME'));
$orderByFields[] = JHTML::_('select.option', 'product_price', JText::_('COM_VIRTUEMART_PRODUCT_PRICE'));
$orderByFields[] = JHTML::_('select.option', 'product_sku', JText::_('COM_VIRTUEMART_CART_SKU'));
$orderByFields[] = JHTML::_('select.option', 'product_cdate', JText::_('COM_VIRTUEMART_LATEST'));
$orderByFields[] = JHTML::_('select.option', 'product_sales', JText::_('COM_VIRTUEMART_SALES'));*/
?>
<br />

<table>
    <tr><td valign="top">
		<fieldset class="adminform">
		<legend><?php echo JText::_('COM_VIRTUEMART_BROWSE_ORDERBY_DEFAULT_FIELD_TITLE') ?></legend>
		<table class="admintable">
			<tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_BROWSE_ORDERBY_DEFAULT_FIELD_LBL_TIP'); ?>" >
			    <?php echo JText::_('COM_VIRTUEMART_BROWSE_ORDERBY_DEFAULT_FIELD_LBL') ?>
			    </span>
			</td>
			<td>

			    <?php echo JHTML::_('Select.genericlist', $this->orderByFields->select, 'browse_orderby_field', 'size=1', 'value', 'text', $this->config->get('browse_orderby_field'),'virtuemart_product_id'); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_BROWSE_ORDERBY_FIELDS_LBL_TIP'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_BROWSE_ORDERBY_FIELDS_LBL') ?>
			    </span>
			</td>
			<td><fieldset class="checkbox">
			    <?php echo $this->orderByFields->checkbox ; ?>
			</fieldset></td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_BROWSE_SEARCH_FIELDS_LBL_TIP'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_BROWSE_SEARCH_FIELDS_LBL') ?>
			    </span>
			</td>
			<td><fieldset class="checkbox">
			    <?php echo $this->searchFields ; ?>
			</fieldset></td>
		    </tr>
		    <tr>
		</table>
		</fieldset>
	</td></tr>
</table>