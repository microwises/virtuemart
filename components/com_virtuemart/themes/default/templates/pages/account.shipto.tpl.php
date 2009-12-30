<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: account.shipto.tpl.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage templates
* @copyright Copyright (C) 2007 Soeren Eberhardt. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
/*****************************
** Checkout Bar Feature
**/
?>
<div class="pathway"><?php echo $vmPathway; ?></div>
<?php
 if ( $next_page=="checkout.index") {
    
     echo "<h3>". JText::_('VM_CHECKOUT_TITLE') ."</h3>";    
	    
	include_class('checkout');
	ps_checkout::show_checkout_bar();
    
 }
/**
** End Checkout Bar Feature
*****************************/
?>
<fieldset>
        <legend><span class="sectiontableheader"><?php echo JText::_('VM_SHOPPER_FORM_SHIPTO_LBL') ?></span></legend>
        
<br />
<?php echo JText::_('VM_SHIPTO_TEXT') ?>
<br /><br /><br />

<div style="width:90%;">
<?php
ps_userfield::listUserFields( $fields, array(), $db );
?>

  <input type="hidden" name="option" value="com_virtuemart" />
  <input type="hidden" name="Itemid" value="<?php echo $Itemid ?>" />
  <input type="hidden" name="page" value="<?php echo $next_page ?>" />
  <input type="hidden" name="next_page" value="<?php echo $next_page ?>" />
  <input type="hidden" name="vmtoken" value="<?php echo vmspoofvalue( $sess->getSessionId() ) ?>" />
<?php
   if (!empty($user_info_id)) { ?>
      <input type="hidden" name="func" value="userAddressUpdate" />
      <input type="hidden" name="user_info_id" value="<?php echo $user_info_id ?>" />
<?php 
   }
   else { ?>
      <input type="hidden" name="func" value="userAddressAdd" />
<?php 
    } ?>
  <input type="hidden" name="user_id" value="<?php echo $auth["user_id"] ?>" />
  <input type="hidden" name="address_type" value="ST" />
  

    
  <br/>
  <div style="float:left;width:45%;text-align:right;" >
    <input type="submit" class="button" name="submit" value="<?php echo JText::_('CMN_SAVE') ?>" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="<?php $sess->purl( SECUREURL."index.php?page=$next_page") ?>" class="button"><?php echo JText::_('BACK') ?></a>
  </div>
  </form>
<?php
  if (!empty($user_info_id)) { ?>
    <div style="float:left;width:45%;text-align:center;"> 
      <form action="<?php echo SECUREURL ?>index.php" method="post">
        <input type="hidden" name="option" value="com_virtuemart" />
        <input type="hidden" name="page" value="<?php echo $next_page ?>" />
        <input type="hidden" name="next_page" value="<?php echo $next_page ?>" />
        <input type="hidden" name="func" value="useraddressdelete" />
        <input type="hidden" name="user_info_id" value="<?php echo $user_info_id ?>" />
        <input type="hidden" name="user_id" value="<?php echo $auth["user_id"] ?>" />
        <input type="submit" class="button" name="submit" value="<?php echo JText::_('E_REMOVE') ?>" />
      </form>
    </div>
<?php 
  } ?>
  </div>
  </fieldset>
