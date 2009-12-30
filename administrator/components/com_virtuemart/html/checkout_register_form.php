<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: checkout_register_form.php 1768 2009-05-11 22:24:39Z macallf $
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
mm_showMyFileName( __FILE__ );
global $mosConfig_allowUserRegistration, $mosConfig_useractivation;
require_once( CLASSPATH . "ps_userfield.php" );
require_once( CLASSPATH . "htmlTools.class.php" );

$missing = JRequest::getVar(  "missing", "" );

if (!empty( $missing )) {
	echo "<script type=\"text/javascript\">alert('".JText::_('CONTACT_FORM_NC',false)."'); </script>\n";
}

// If not using NO_REGISTRATION, redirect with a warning when Joomla doesn't allow user registration
if ($mosConfig_allowUserRegistration == "0" && VM_REGISTRATION_TYPE != 'NO_REGISTRATION' ) {
	$msg = JText::_('USER_REGISTRATION_DISABLED');
	vmRedirect( $sess->url( 'index.php?page='.HOMEPAGE, true, false ), $msg );
	return;
}

//if( vmIsJoomla( '1.5' ) ) {
	// Set the validation value
	$validate = JUtility::getToken();
//} else {
//	$validate =  function_exists( 'josspoofvalue' ) ? josSpoofValue(1) : vmSpoofValue(1);
//}

$fields = ps_userfield::getUserFields('registration', false, '', false );
// Read-only fields on registration don't make sense.
foreach( $fields as $field ) $field->readonly = 0;
$skip_fields = array();

if ( $my->id > 0 || (VM_REGISTRATION_TYPE != 'NORMAL_REGISTRATION' && VM_REGISTRATION_TYPE != 'OPTIONAL_REGISTRATION' 
								&& ( $page == 'checkout.index' || $page == 'shop.registration' ) ) ) {
	// A listing of fields that are NOT shown
	$skip_fields = array( 'username', 'password', 'password2' );
	if( $my->id ) {
		$skip_fields[] = 'email';
	}
}

// This is the part that prints out ALL registration fields!
ps_userfield::listUserFields( $fields, $skip_fields );

echo '
<div align="center">';
    
	if( !$mosConfig_useractivation && @VM_SHOW_REMEMBER_ME_BOX && VM_REGISTRATION_TYPE == 'NORMAL_REGISTRATION' ) {
		echo '<input type="checkbox" name="remember" value="yes" id="remember_login2" checked="checked" />
		<label for="remember_login2">'. JText::_('REMEMBER_ME') .'</label><br /><br />';
	}
	else {
		if( VM_REGISTRATION_TYPE == 'NO_REGISTRATION' ) {
			$rmbr = '';
		} else {
			$rmbr = 'yes';
		}
		echo '<input type="hidden" name="remember" value="'.$rmbr.'" />';
	}
	echo '
		<input type="submit" value="'. JText::_('BUTTON_SEND_REG') . '" class="button" onclick="return( submitregistration());" />
	</div>
	<input type="hidden" name="Itemid" value="'. $sess->getShopItemid() .'" />
	<input type="hidden" name="gid" value="'. $my->gid .'" />
	<input type="hidden" name="id" value="'. $my->id .'" />
	<input type="hidden" name="user_id" value="'. $my->id .'" />
	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="' . $validate . '" value="1" />
	<input type="hidden" name="useractivation" value="'. $mosConfig_useractivation .'" />
	<input type="hidden" name="func" value="shopperadd" />
	<input type="hidden" name="page" value="'.$page.'" />
	</form>';
?>
