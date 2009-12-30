<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: admin.update_result.php 1755 2009-05-01 22:45:17Z rolandd $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*
*/



require_once( CLASSPATH.'update.class.php');
vmUpdate::stepBar(3);

if( !empty( $_SESSION['vmupdatemessage'] )) {
	echo '<div class="shop_info">'.shopMakeHtmlSafe($_SESSION['vmupdatemessage'])
			.'<br /><br /><br />'.JText::_('VM_UPDATE_RESULT_TITLE').':<br />';
	unset($_SESSION['vmupdatemessage']);
	require_once( ADMINPATH. 'version.php' );
	
	echo $myVersion.'<br /><br />
	<input class="vmicon vmicon32 vmicon-32-apply" type="button" onclick="document.location=\''.$sess->url($_SERVER['PHP_SELF'].'?page=store.index').'\';" value="' . JText::_('CMN_CONTINUE') . '" name="submitbutton" />';
	echo '</div>';
} 
else {
	vmRedirect($sess->url($_SERVER['PHP_SELF'].'?page=admin.update_check', false, false));
}
?>
