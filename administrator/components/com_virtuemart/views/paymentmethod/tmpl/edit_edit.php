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
<table class="adminform">
    <tr class="row0">
      <td class="labelcell"><?php echo JText::_('VM_FIELDMANAGER_PUBLISHED') ?>?:</td>
      <td><?php echo JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $this->paym->published); ?></td>
    </tr>
    <tr class="row1"> 
      <td class="labelcell"><?php echo JText::_('VM_PAYMENT_METHOD_FORM_NAME') ?>:</td>
      <td width="69%" > 
        <input type="text" class="inputbox" name="name" value="<?php $this->paym->paym_name ?>" size="32" />
      </td>
    </tr>
    <tr class="row1">
      <td class="labelcell"><?php
          echo JText::_('VM_PAYMENT_CLASS_NAME');
          ?>
      </td>
      <td width="69%">
      	<?php
      	echo FileUtilities::list_available_classes( 'element', $this->paym->paym_element ? $this->paym->paym_element : 'payment' );
//     	echo vmPaymentMethod::list_available_classes( 'element', $db->sf("element") ? $db->sf("element") : 'payment' );
      	echo JHTML::tooltip( JText::_('VM_PAYMENT_CLASS_NAME_TIP') ); ?>
      </td>
    </tr>
    <tr class="row0"> 
      <td class="labelcell"><?php echo JText::_('VM_PAYMENT_METHOD_FORM_ENABLE_PROCESSOR') ?>:</td>
      <td width="69%" >
		<?php echo $this->PaymentTypeList ?>
      </td>
    </tr>
    <tr class="row1">
      <td colspan="2"><hr /></td>
    </tr>
    <tr class="row0">
      <td class="labelcell"><div id="accepted_creditcards1"></div></td>
      <td width="69%"><div id="accepted_creditcards2"></div></td>
    </tr>
    <div id="accepted_creditcards_store"></div>
    <tr class="row1">
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr class="row0"> 
      <td class="labelcell"><?php echo JText::_('VM_PAYMENT_METHOD_FORM_SHOPPER_GROUP') ?>:</td>
      <td width="69%" > 
      </td>
    </tr>
    
    <tr class="row0"> 
      <td class="labelcell"><?php echo JText::_('VM_PAYMENT_METHOD_FORM_LIST_ORDER') ?>:</td>
      <td width="69%" > 
        <input type="text" class="inputbox" name="list_order" size="4" maxlength="4" value="<?php echo $this->paym->ordering; ?>" />
      </td>
    </tr>
    <tr class="row0"> 
      <td colspan="2">&nbsp;</td>
    </tr>
  </table>  
            
