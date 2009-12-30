<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: shop.ask.tpl.php 1755 2009-05-01 22:45:17Z rolandd $
* @package VirtueMart
* @subpackage themes
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
mm_showMyFileName( __FILE__ );

if ( $set == 0 ) { // This is the enquiry form!

	$validate = vmIsJoomla( '1.5' ) ? JUtility::getHash( $mainframe->getCfg( 'db' ) ) : mosHash( $mainframe->getCfg( 'db' ) );
	?>
	<br />
	<a class="button" href="<?php echo $product_link ?>"><?php echo JText::_('VM_RETURN_TO_PRODUCT') ?></a>
	<br /><br />
	
	<form action="<?php echo $mm_action_url ?>index.php" method="post" name="emailForm" id="emailForm">
	<label for="contact_name"><?php echo JText::_('NAME_PROMPT') ?></label>
	<br /><input type="text" name="name" id="contact_name" size="80" class="inputbox" value="<?php echo $name ?>"><br /><br />
	<label for="contact_mail"><?php echo JText::_('EMAIL_PROMPT') ?></label>
	<br /><input type="text" id="contact_mail" name="email" size="80" label="Your email" class="inputbox" value="<?php echo $email ?>"><br /><br />
	<label for="contact_text"><?php echo JText::_('MESSAGE_PROMPT') ?></label><br />
	<textarea rows="10" cols="60" name="text" id="contact_text" class="inputbox"><?php echo $subject ?></textarea><br />
	
	<input type="button" name="send" value="<?php echo JText::_('SEND_BUTTON') ?>" class="button" onclick="validateEnquiryForm()" />	
	
	<input type="hidden" name="product_id" value="<?php echo  $db_product->f("product_id")  ?>" />
	<input type="hidden" name="product_sku" value="<?php echo  $db_product->f("product_sku")  ?>" />
	<input type="hidden" name="set" value="1" />	
	<input type="hidden" name="func" value="productAsk" />
	<input type="hidden" name="page" value="shop.ask" />
	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="flypage" value="<?php echo $flypage ?>" />
	<input type="hidden" name="Itemid" value="<?php echo $Itemid ?>" />
	
	<input type="hidden" name="<?php echo $validate ?>" value="1" />
	</form>
	<script type="text/javascript"><!--
	function validateEnquiryForm() {
		if ( ( document.emailForm.text.value == "" ) || ( document.emailForm.email.value.search("@") == -1 ) || ( document.emailForm.email.value.search("[.*]" ) == -1 ) ) {
			alert( "<?php echo JText::_('CONTACT_FORM_NC',false); ?>" );
		} else if ( ( document.emailForm.email.value.search(";") != -1 ) || ( document.emailForm.email.value.search(",") != -1 ) || ( document.emailForm.email.value.search(" ") != -1 ) ) {
			alert( "You cannot enter more than one email address" );
		} else {
			document.emailForm.action = "<?php echo sefRelToAbs("index.php"); ?>"
			document.emailForm.submit();
		}
	}
	--></script>
	
	<?php
}
else { // if set==1 then we have sent the email to the vendor and say thank you here.
  ?>
   <img src="<?php echo VM_THEMEURL ?>images/button_ok.png" height="48" width="48" align="center" alt="Success" border="0" />
   <?php echo JText::_('THANK_MESSAGE') ?>
 
  <br /><br />
  
  <a class="button" href="<?php echo $product_link ?>"><?php echo JText::_('VM_RETURN_TO_PRODUCT') ?></a>
  
  <?php 
}
?>