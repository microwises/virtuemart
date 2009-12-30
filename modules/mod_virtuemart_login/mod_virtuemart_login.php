<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* @version		$Id$
* @package		VirtueMart
* @subpackage modules
* @copyright	Copyright (C) 2007 Greg Perkins. All rights reserved.
* @license		GNU/GPL, http://www.gnu.org/copyleft/gpl.html
* 
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/

// TODO: Joomla! 1.5 compatibility - do these global vars depend on the legacy plugin?
global $mosConfig_absolute_path, $mosConfig_allowUserRegistration;

// Load the virtuemart main parse code
if( file_exists(dirname(__FILE__).'/../../components/com_virtuemart/virtuemart_parser.php' )) {
	require_once( dirname(__FILE__).'/../../components/com_virtuemart/virtuemart_parser.php' );
} else {
	require_once( dirname(__FILE__).'/../components/com_virtuemart/virtuemart_parser.php' );
}

global $mm_action_url, $sess, $VM_LANG;

//	Show login or logout?
if( vmIsJoomla(1.5) ) {
	$user = & JFactory::getUser();
	$type = (!$user->get('guest')) ? 'logout' : 'login';
} else {
	$type = ($my->id) ? 'logout' : 'login';
}

// Determine settings based on CMS version
if( vmIsJoomla('1.5') ) {
// Joomla 1.5

	if( $type == 'login' ) {
		// Redirect type
		$redirect = $params->get('login');
		
		// Lost password
		$reset_url = JRoute::_( 'index.php?option=com_user&view=reset' );
		
		// User name reminder (Joomla 1.5 only)
		$remind_url = JRoute::_( 'index.php?option=com_user&view=remind' );
		
		// Set the validation value
		$validate = JUtility::getToken();
	} else {
		// Redirect type
		$redirect = $params->get('logout');
		
		// Return URL
		$uri = JFactory::getURI();
		$url = $uri->toString();
		$return_url = base64_encode( $url );
	
		// Set the greeting name
		$user =& JFactory::getUser();
		$name = ( $params->get( 'name') ) ? $user->name : $user->username;
	}
	
	// Post action
	$action =  $mm_action_url. 'index.php?option=com_user&amp;task='.$type;

	// Set the redirection URL
	if( $redirect == 'home' ) {
		// The Joomla! home page
		$menu = &JSite::getMenu();
		$default = $menu->getDefault();
		$uri = JFactory::getURI( $default->link );
		$url = $uri->toString();
	} elseif( $redirect == 'vmhome' ) {
		// The VirtueMart home page
		$url = JRoute::_( 'index.php?option=com_virtuemart&page='.HOMEPAGE.'&Itemid='.$sess->getShopItemid(), false );
	} else {
		// The same page
		$uri = JFactory::getURI();
		$url = $uri->toString();
	}
	
	$return_url = base64_encode( $url );

} else {
// Not Joomla 1.5

	if( $type == 'login' ) {
		// Redirect type
		$redirect = $params->get('login');
		
		// Lost password url
		$reset_url = sefRelToAbs( 'index.php?option=com_registration&amp;task=lostPassword&amp;Itemid='.(int)mosGetParam($_REQUEST, 'Itemid', 0) );
		
		// Set user name reminder to nothing
		$remind_url = '';

		// Set the validation value
		if( function_exists( 'josspoofvalue' ) ) {
			$validate = josSpoofValue(1);
		} else {
			$validate = vmSpoofValue(1);
		}
	} else {
		// Redirect type
		$redirect = $params->get('logout');
		
		// Set the greeting name
		$name = ( $params->get( 'name') ) ? $my->name : $my->username;
	}

	// Post action
	$action = sefRelToAbs( $mm_action_url . 'index.php?option='.$type );

	// Set the redirection URL
	if( $redirect == 'home' ) {
		$url = sefRelToAbs( 'index.php' );
	} elseif( $redirect == 'vmhome' ) {
		// The VirtueMart home page
		$url = $sess->url( URL.'index.php?option=com_virtuemart&amp;page='.HOMEPAGE );
	} else {
		// The same page
		$url = $sess->url( basename($_SERVER['PHP_SELF']).'?'.mosGetParam($_SERVER,'QUERY_STRING'), true, false );
	}
	
	$return_url = sefRelToAbs( $url );

}

// Registration URL
$registration_url = $sess->url( SECUREURL.'index.php?option=com_virtuemart&amp;page=shop.registration' );

?>
<?php if( $type == 'logout' ) : ?>
<div>
	<form action="<?php echo $action ?>" method="post" name="login" id="login">
		<?php if( $params->get('greeting') ) : ?>
		<div><?php echo $VM_LANG->_('HI') . ' ' . $name ?></div>
		<?php endif; ?>
		<?php if( $params->get('accountlink') || ENABLE_DOWNLOADS == '1' ) : ?>
		<ul>
			<?php if( $params->get('accountlink') ) : ?>
			<li><a href="<?php echo $sess->url(SECUREURL . "index.php?page=account.index");?>"><?php echo $VM_LANG->_('PHPSHOP_ACCOUNT_TITLE') ?></a></li>
			<?php endif; ?>
			<?php if( ENABLE_DOWNLOADS == '1' ) : ?>
        	<li><a href="<?php $sess->purl(SECUREURL . "index.php?page=shop.downloads");?>"><?php echo $VM_LANG->_('PHPSHOP_DOWNLOADS_TITLE') ?></a></li>
			<?php endif; ?>
		</ul>
		<?php endif; ?>
		<input type="submit" name="Submit" class="button" value="<?php echo $VM_LANG->_('BUTTON_LOGOUT') ?>" />
		<br /><br />
		<input type="hidden" name="op2" value="logout" />
		<input type="hidden" name="return" value="<?php echo $return_url ?>" />
		<input type="hidden" name="lang" value="english" />
		<input type="hidden" name="message" value="0" />
	</form>
</div>
<?php else : ?> 
<div>
	<form action="<?php echo $action ?>" method="post" name="login" id="login">
		<?php if( $params->get('pretext') ) : ?>
		<?php echo $params->get('pretext'); ?>
		<br />
		<?php endif; ?>
		<label for="username_field"><?php echo $VM_LANG->_('USERNAME') ?></label><br/>
		<input class="inputbox" type="text" id="username_field" size="12" name="username" />
		<br />
		<label for="password_field"><?php echo $VM_LANG->_('PASSWORD') ?></label><br/>
		<input type="password" class="inputbox" id="password_field" size="12" name="passwd" />
		<?php if( @VM_SHOW_REMEMBER_ME_BOX == '1' ) : ?>
		<br />
		<label for="remember_login"><?php echo $VM_LANG->_('REMEMBER_ME') ?></label>
		<input type="checkbox" name="remember" id="remember_login" value="yes" checked="checked" />
		<?php else : ?>
		<br />
		<input type="hidden" name="remember" value="yes" />
		<?php endif; ?>
		<br />
		<input type="submit" value="<?php echo $VM_LANG->_('BUTTON_LOGIN') ?>" class="button" name="Login" />
		<ul>
			<li><a href="<?php echo $reset_url ?>"><?php echo $VM_LANG->_('LOST_PASSWORD') ?></a></li>
			<?php if( $remind_url ) : ?>
			<li><a href="<?php echo $remind_url ?>"><?php echo $VM_LANG->_('FORGOT_YOUR_USERNAME') ?></a></li>
			<?php endif; ?>
			<?php if( $mosConfig_allowUserRegistration == '1' ) : ?>
			<li><?php echo $VM_LANG->_('NO_ACCOUNT') ?> <a href="<?php echo $registration_url ?>"><?php echo $VM_LANG->_('CREATE_ACCOUNT') ?></a></li>
			<?php endif; ?>
		</ul>
		<input type="hidden" value="login" name="op2" />
		<input type="hidden" value="<?php echo $return_url ?>" name="return" />
		<input type="hidden" name="<?php echo $validate; ?>" value="1" />
		<?php echo $params->get('posttext'); ?>
	</form>
</div>
<?php endif; ?>
