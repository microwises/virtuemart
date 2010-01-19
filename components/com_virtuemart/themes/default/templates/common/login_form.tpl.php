<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage templates
* @copyright Copyright (C) 2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

if(JRequest::getVar('tmpl') == 'component') {
	$lostPwUrl =  JRoute::_( 'index.php?option=com_user&view=reset&tmpl=component' );
} else {
	$lostPwUrl =  JRoute::_( 'index.php?option=com_user&view=reset' );
}

?>
<form action="index.php" method="post" name="login" style="margin-left:20px;">
	<label for="username_login"><?php echo JText::_('USERNAME') ?>:</label>
	<input type="text" id="username_login" name="username" class="inputbox" size="20" />
	<br />
	<br />
	<label for="passwd_login"><?php echo JText::_('PASSWORD') ?>:</label> 
	<input type="password" id="passwd_login" name="passwd" class="inputbox" size="20" />
	(<a title="<?php echo JText::_('LOST_PASSWORD'); ?>" href="<?php echo $lostPwUrl; ?>"><?php echo JText::_('LOST_PASSWORD'); ?></a>)
	<br />
	<br />
	<input type="submit" name="Submit" class="button" value="<?php echo JText::_('BUTTON_LOGIN') ?>" />
	<?php if( @VM_SHOW_REMEMBER_ME_BOX == '1' ) : ?>
	<br />
	<input type="checkbox" name="remember" id="remember_login" value="yes" checked="checked" />
	<label for="remember_login"><?php echo JText::_('REMEMBER_ME') ?></label>
	<?php else : ?>
	<input type="hidden" name="remember" value="yes" />
	<?php endif; ?>
	<input type="hidden" name="option" value="com_user" />
	<input type="hidden" name="task" value="login" />
	<input type="hidden" name="lang" value="<?php echo vmIsJoomla() ? $mosConfig_lang : $GLOBALS['mosConfig_locale'] ?>" />
	<input type="hidden" name="return" value="<?php echo $return_url ?>" />
	<input type="hidden" name="<?php echo $validate; ?>" value="1" />
</form>