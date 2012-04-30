<?php
/**
*
* Handle the waitinglist
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
defined('_JEXEC') or die('Restricted access'); ?>
<table class="adminlist" cellspacing="0" cellpadding="0">



<?php
if (!empty($this->product_emails)) { ?>
	<tr><td>
			<table class="adminlist" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th><?php echo JText::_('COM_VIRTUEMART_PRODUCT_EMAIL_DATE') ?></th>
					<th><?php echo JText::_('COM_VIRTUEMART_PRODUCT_EMAIL_CONTENT') ?></th>
				</tr>
			</thead>
			<?php

			foreach ($this->product_emails as $this->product_email ) {
				echo "<tr>";
				echo "<td>". vmJsApi::date($this->product_email->created_on,'LC2',true) ."</td>\n";

				echo "<td>".$this->product_email->email_content."</td>\n";
				echo "</tr>\n";
			}
			?>




		</table>
	    </td></tr>
	<?php }?>
	<?php if ($this->product_nbshoppers > 0 ) { ?>
	<tr><td>
	<table class="adminlist" cellspacing="0" cellpadding="0">
		<td width="100%" valign="top" colspan="2">
			<fieldset>
				<legend>
				<?php echo JText::sprintf('COM_VIRTUEMART_PRODUCT_EMAIL_SENT_TO_SHOPPER', $this->product_nbshoppers); ?></legend>
			<?php $link=JROUTE::_('index.php?option=com_virtuemart&view=product&task=sentproductemailtoshoppers&virtuemart_product_id='.$this->product->virtuemart_product_id.'&token='.JUtility::getToken() ); ?>
						<div class="button2-left">
							<div class="blank">
								<a onclick="Joomla.submitbutton('sentproductemailtoshoppers')" href="#">
								<span class="icon-nofloat vmicon icon-16-messages"></span><?php echo Jtext::_('COM_VIRTUEMART_PRODUCT_EMAIL_SEND'); ?>
								</a>
							</div>
						</div>


				<textarea
					style="width: 100%;" class="inputbox" name="product_email_shoppers"
					id="product_email_shoppers" cols="35" rows="6"> </textarea>
			</fieldset>

		</td>
	</tr>

	<?php } else { ?>
	<tr>
		<td width="100%" valign="top" colspan="2">
		    <fieldset>
				<legend>
				    <?php echo JText::sprintf('COM_VIRTUEMART_PRODUCT_EMAIL_SENT_TO_SHOPPER', $this->product_nbshoppers); ?></legend>
	<?php echo JText::_('COM_VIRTUEMART_PRODUCT_EMAIL_NO_SHOPPER' ); ?>

		    </fieldset>
</td>
	</tr>
	</tbody>
</table>
<?php }?>
	</tbody>
</table>