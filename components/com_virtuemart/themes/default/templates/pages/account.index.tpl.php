<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

if ($perm->is_registered_customer($auth['user_id']) ) {

?>
  <strong><?php echo JText::_('VM_ACC_CUSTOMER_ACCOUNT') ?></strong>
  <?php  echo $auth["first_name"] . " " . $auth["last_name"] . "<br />";?>
  <br />
  <table border="0" cellspacing="0" cellpadding="10" width="100%" align="center">
  
<?php if( $my->id > 0)  { ?>
    <tr>
      <td>
      <strong><a href="<?php $sess->purl(SECUREURL . "index.php?page=account.billing") ?>">
          <?php 
          echo "<img src=\"".VM_THEMEURL."images/identity.png\" align=\"middle\" height=\"48\" width=\"48\" border=\"0\" alt=\"".JText::_('VM_ACCOUNT_TITLE')."\" />&nbsp;";
          echo JText::_('VM_ACC_ACCOUNT_INFO') ?></a></strong>
          <br /><?php echo JText::_('VM_ACC_UPD_BILL') ?>
      </td>
    </tr>
    <?php
    if(NO_SHIPTO != '1') {
	?>
		<tr><td>&nbsp;</td></tr>
		
		<tr>
		  <td><hr />
		  <strong><a href="<?php $sess->purl(SECUREURL . "index.php?page=account.shipping") ?>"><?php
                  echo "<img src=\"".VM_THEMEURL."images/web.png\" align=\"middle\" border=\"0\" height=\"32\" width=\"32\" alt=\"".JText::_('VM_ACC_SHIP_INFO')."\" />&nbsp;&nbsp;&nbsp;";
                  echo JText::_('VM_ACC_SHIP_INFO') ?></a></strong>
                        <br />
                        <?php echo JText::_('VM_ACC_UPD_SHIP') ?>
                  </td>
                </tr>
                <?php
	}
	?>
    <tr><td>&nbsp;</td></tr>
<?php } ?>
    <tr>
      <td>
      	<hr />
      	<strong><?php 
	      echo "<img src=\"".VM_THEMEURL."images/package.png\" align=\"middle\" height=\"32\" width=\"32\" border=\"0\" alt=\"".JText::_('VM_ACC_ORDER_INFO')."\" />&nbsp;&nbsp;&nbsp;";
	      echo JText::_('VM_ACC_ORDER_INFO') ?>
	    </strong>
        <?php $ps_order->list_order("A", "1" ); ?>
      </td>
    </tr>
    
</table>
<!-- Body ends here -->
<?php } 
else { 
	// You're not allowed... you need to login.
    echo JText::_('DO_LOGIN') .'<br/><br/><br/>';
    include(PAGEPATH.'checkout.login_form.php');
} ?>