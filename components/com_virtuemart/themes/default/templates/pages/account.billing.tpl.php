<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2007 Soeren Eberhardt. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

?>
<div class="pathway"><?php echo $vmPathway; ?></div>
<div style="float:left;width:90%;text-align:right;"> 
    <span>
    	<a href="#" onclick="if( submitregistration() ) { document.adminForm.submit(); return false;}">
    		<img border="0" src="administrator/images/save_f2.png" name="submit" alt="<?php echo JText::_('CMN_SAVE') ?>" />
    	</a>
    </span>
    <span style="margin-left:10px;">
    	<a href="<?php $sess->purl( SECUREURL."index.php?page=$next_page") ?>">
    		<img src="administrator/images/back_f2.png" alt="<?php echo JText::_('BACK') ?>" border="0" />
    	</a>
    </span>
</div>

<?php
ps_userfield::listUserFields( $fields, $skip_fields, $db );
?>

<div align="center">	
	<input type="submit" value="<?php echo JText::_('CMN_SAVE') ?>" class="button" onclick="return( submitregistration());" />
</div>
  <input type="hidden" name="option" value="<?php echo $option ?>" />
  <input type="hidden" name="page" value="<?php echo $next_page; ?>" />
  <input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
  <input type="hidden" name="func" value="shopperupdate" />
  <input type="hidden" name="user_info_id" value="<?php $db->p("user_info_id"); ?>" />
  <input type="hidden" name="id" value="<?php echo $auth["user_id"] ?>" />
  <input type="hidden" name="user_id" value="<?php echo $auth["user_id"] ?>" />
  <input type="hidden" name="address_type" value="BT" />
  <noscript>
  <input type="submit" class="inputbox" value="<?php echo JText::_('CMN_SAVE') ?>" />
  </noscript>
</form>
