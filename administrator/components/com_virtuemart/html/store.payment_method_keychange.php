<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: store.payment_method_keychange.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
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

$payment_method_id = JRequest::getVar(  'payment_method_id', null );
$passkey = vmGet( $_POST, 'passkey', null );
$Itemid = $sess->getShopItemid();
$task = vmGet( $_POST, 'task', null );

if ( $payment_method_id ) {
	echo '<table class="adminform"><tr><th>';
	echo "<h2>".JText::_('VM_CHANGE_PASSKEY_FORM')."</h2></th>";
	echo '</tr><tr><td>';
	// Get the Transaction Key securely from the database
	$db->query( "SELECT ".VM_DECRYPT_FUNCTION."(secret_key,'".ENCODE_KEY."') AS `passkey` FROM #__{vm}_payment_method WHERE payment_method_id='$payment_method_id'" );
	$db->next_record();
	
	if( !empty( $_POST['submit'] )) {
		$auth_result = vmCheckPass();
	} else {
		$auth_result = false;
	}
	// authenticated. Show "Change Key" and "Password" Form
	if( $auth_result && empty( $passkey ) ) {
		echo "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\n";
		echo "<table width=\"60%\">\n";
		echo "<tr><th style=\"text-align:right;\">".JText::_('VM_CURRENT_TRANSACTION_KEY').":</th>\n";
		echo "<th><input type=\"text\" name=\"passkey\" value=\"". $db->f('passkey') ."\" /></th></tr>\n";
		echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
		echo "<tr><td style=\"text-align:right;\">".JText::_('VM_TYPE_PASSWORD').":</td>\n";
		echo "<td><input type=\"password\" name=\"passwd\" value=\"\" /></td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td><input name=\"submit\" type=\"submit\" value=\"".JText::_('VM_SUBMIT')."\" /></td></tr>\n";
		echo "</table>\n";
		echo "<input type=\"hidden\" name=\"option\" value=\"com_virtuemart\" />\n";
		echo "<input type=\"hidden\" name=\"Itemid\" value=\"$Itemid\" />\n";
		echo "<input type=\"hidden\" name=\"payment_method_id\" value=\"$payment_method_id\" />\n";
		echo "<input type=\"hidden\" name=\"task\" value=\"changekey\" />\n";
		echo "<input type=\"hidden\" name=\"pshop_mode\" value=\"admin\" />\n";
		echo "<input type=\"hidden\" name=\"page\" value=\"store.payment_method_keychange\" />\n";
		echo "</form>\n";

	}
	// authenticated
	elseif ( $auth_result && !empty($passkey) && $task == "changekey") {

		$q = "UPDATE #__{vm}_payment_method ";
		$q .= "SET secret_key = ".VM_ENCRYPT_FUNCTION."('$passkey','" . ENCODE_KEY . "')\n";
		$q .= "WHERE payment_method_id='$payment_method_id';";
		$db->query( $q );
		vmRedirect( $sess->url($_SERVER['PHP_SELF']."?page=store.payment_method_form&payment_method_id=$payment_method_id", false, false), JText::_('VM_CHANGE_PASSKEY_SUCCESS'));
	}
	// not authenticated
	else {
		require_once( CLASSPATH. "ps_checkout.php" );
		echo "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\n";
		echo "<table class=\"adminForm\">\n";
		echo "<tr><td>".JText::_('VM_CURRENT_TRANSACTION_KEY').":</td><td>".( $db->f('passkey') ? ps_checkout::asterisk_pad( $db->f('passkey'), 4 ) : '<i>(empty!)</i>')."</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
		echo "<tr><td>".JText::_('VM_TYPE_PASSWORD').":</td>\n";
		echo "<td><input type=\"password\" name=\"passwd\" value=\"\" /></td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td><input name=\"submit\" type=\"submit\" value=\"".JText::_('VM_SUBMIT')."\" /></td></tr>\n";
		echo "</table>\n";
		echo "<input type=\"hidden\" name=\"option\" value=\"com_virtuemart\" />\n";
		echo "<input type=\"hidden\" name=\"Itemid\" value=\"$Itemid\" />\n";
		echo "<input type=\"hidden\" name=\"pshop_mode\" value=\"admin\" />\n";
		echo "<input type=\"hidden\" name=\"payment_method_id\" value=\"$payment_method_id\" />\n";
		echo "<input type=\"hidden\" name=\"page\" value=\"store.payment_method_keychange\" />\n";
		echo "</form>\n";

	}
	echo '</td></tr></table>';
}
else {
	echo "<script>alert(\"" . JText::_('VM_PAYMENT_METHOD_ID_NOT_PROVIDED') . "\"); window.history.go(-1); </script>\n";
}

?>