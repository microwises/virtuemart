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

?>
<table class="admintable">
    <tr  >
      <td class="key"><?php echo JText::_('COM_VIRTUEMART_PUBLISHED') ?>?</td>
      <td><fieldset class="radio"><?php echo JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $this->paym->published); ?></fieldset></td>
    </tr>
    <tr  class="key">
      <td class="key"><?php echo JText::_('COM_VIRTUEMART_PAYMENTMETHOD_FORM_NAME') ?></td>
      <td width="69%" >
        <input type="text" class="inputbox" name="payment_name" value="<?php echo $this->paym->payment_name; ?>" size="32" />
      </td>
    </tr>
    <tr  >
      <td class="key"><span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_PAYMENT_CLASS_NAME_TIP') ?>"><?php echo JText::_('COM_VIRTUEMART_PAYMENT_CLASS_NAME'); ?></span></td>
      <td width="69%"><?php
      	echo $this->vmPPaymentList;
      	//echo FileUtilities::list_available_classes( 'payment_element', $this->paym->payment_element ? $this->paym->payment_element : 'payment' );
       ?>
      </td>
    </tr>
    <tr   id=creditcardlist style="display : none;" >
      <td class="key"><?php echo JText::_('COM_VIRTUEMART_PAYMENTMETHOD_FORM_CREDITCARD_LIST') ?></td>
      <td width="69%" ><?php
		echo $this->creditCardList ?>
      </td>
    </tr>

    <tr >
      <td class="key"><?php echo JText::_('COM_VIRTUEMART_PAYMENTMETHOD_FORM_SHOPPER_GROUP') ?></td>
      <td width="69%" ><?php
		echo $this->shopperGroupList ?>
      </td>
    </tr>
    <tr >
      <td class="key"><span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_PAYMENTMETHOD_DISCOUNT_TIP') ?>"><?php echo JText::_('COM_VIRTUEMART_PAYMENTMETHOD_FORM_DISCOUNT') ?></span></td>
      <td width="69%" >
      <input type="text" class="inputbox" name="discount" value="<?php	echo $this->paym->discount;?>" size="4" />

      </td>
    </tr>
	<tr  >
      <td class="key"><span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_DISCOUNT_ISPERCENT_TIP') ?>"><?php echo JText::_('COM_VIRTUEMART_DISCOUNT_AMOUNTTYPE') ?></span></td>
      <td width="69%" >
		<fieldset class="radio">
		<?php
		echo JHTML::_('select.booleanlist',  'discount_is_percentage', 'class="inputbox"', $this->paym->discount_is_percentage, 'COM_VIRTUEMART_DISCOUNT_ISPERCENT', 'COM_VIRTUEMART_DISCOUNT_ISTOTAL');
		  ?>
		</fieldset>
      </td>
    </tr>
	<tr  >
      	<td class="key"><span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_PAYMENTMETHOD_DISCOUNT_MAX_AMOUNT_TIP') ?>"><?php echo JText::_('COM_VIRTUEMART_PAYMENTMETHOD_DISCOUNT_MAX_AMOUNT') ?></span></td>
      	<td width="69%" >
      	<input type="text" class="inputbox" name="discount_max_amount" value="<?php	echo $this->paym->discount_max_amount;?>" size="4" />
	<?php echo $this->vendor_currency;   ?>
      </td>
    </tr>
        <tr  >
      	<td class="key"><span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_PAYMENTMETHOD_DISCOUNT_TIP') ?>"><?php echo JText::_('COM_VIRTUEMART_PAYMENTMETHOD_DISCOUNT_MIN_AMOUNT') ?></td>
      	<td width="69%" >
      	<input type="text" class="inputbox" name="discount_min_amount" value="<?php	echo $this->paym->discount_min_amount;?>" size="4" />
	<?php echo $this->vendor_currency;   ?>
      </td>
    </tr>
    <tr  >
      <td class="key"><?php echo JText::_('COM_VIRTUEMART_LIST_ORDER') ?></td>
      <td width="69%" >
        <input type="text" class="inputbox" name="ordering" size="4" maxlength="4" value="<?php echo $this->paym->ordering; ?>" />
      </td>
    </tr>
    <?php if(Vmconfig::get('multix','none')!=='none'){ ?>
    	<tr >
    	<td class="key"><?php echo JText::_('COM_VIRTUEMART_VENDOR') ?></td>
    	<td width="69%" ><?php
			echo $this->vendorList ?>
		</td>
	</tr>

    <?php } ?>
  </table>

