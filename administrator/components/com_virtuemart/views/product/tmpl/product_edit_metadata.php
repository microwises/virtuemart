<?php
/**
*
* Set the meta data for a product
*
* @package	VirtueMart
* @subpackage Product
* @author RolandD
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
defined('_JEXEC') or die('Restricted access');?>
<fieldset>
	<legend><?php echo JText::_('COM_VIRTUEMART_META_INFORMATION') ?></legend>		
	<table valign="top">
		<tr>
			<td valign="top"><div style="text-align:right;font-weight:bold;"><?php echo JText::_('COM_VIRTUEMART_METADESC'); ?>: </div></td>
			<td valign="top">
				<textarea class="inputbox" name="metadesc" id="meta_desc" cols="60" rows="6"><?php echo $this->product->metadesc; ?></textarea>
			</td>
		</tr>
		<tr>
			<td >
				<div style="text-align:right;font-weight:bold;"><?php echo JText::_('COM_VIRTUEMART_METAKEYS'); ?>: </div>
			</td>
			<td valign="top">
				<textarea class="inputbox" name="metakey" id="meta_keyword" cols="60" rows="6"><?php echo $this->product->metakey; ?></textarea>
			</td>
		</tr>
		<tr>
			<td >
				<div style="text-align:right;font-weight:bold;"><?php echo JText::_('COM_VIRTUEMART_METAROBOT'); ?>: </div>
			</td>
			<td valign="top">
				<input type="text" class="inputbox" size="60" name="metarobot" value="<?php echo $this->product->metarobot ?>" />
			</td>
		</tr>
		<tr>
			<td >
				<div style="text-align:right;font-weight:bold;"><?php echo JText::_('COM_VIRTUEMART_METAAUTHOR'); ?>: </div>
			</td>
			<td valign="top">
				<input type="text" class="inputbox" size="60" name="metaauthor" value="<?php echo $this->product->metaauthor ?>" />
			</td>
		</tr>
	</table>
</fieldset>
