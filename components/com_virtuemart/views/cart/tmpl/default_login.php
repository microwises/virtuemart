<?php
/**
*
* Layout for the shopping cart
*
* @package	VirtueMart
* @subpackage Cart
* @author Max Milbers
*
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: cart.php 2551 2010-09-30 18:52:40Z milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.formvalidation');
JHTML::_ ( 'behavior.modal' );
// vmdebug('cart',$this->cart);

if(!class_exists('shopFunctionsF')) require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
$comUserOption=shopfunctionsF::getComUserOption();
?>


<?php
$uri = JFactory::getURI();
    $url = $uri->toString(array('path', 'query', 'fragment'));
if (VmConfig::get('oncheckout_show_register', 1)  && $this->cart->userDetails->JUser->id === 0  ) {

    if (JPluginHelper::isEnabled('authentication', 'openid')) {
        $lang = &JFactory::getLanguage();
        $lang->load('plg_authentication_openid', JPATH_ADMINISTRATOR);
        $langScript = 'var JLanguage = {};' .
                ' JLanguage.WHAT_IS_OPENID = \'' . JText::_('WHAT_IS_OPENID') . '\';' .
                ' JLanguage.LOGIN_WITH_OPENID = \'' . JText::_('LOGIN_WITH_OPENID') . '\';' .
                ' JLanguage.NORMAL_LOGIN = \'' . JText::_('NORMAL_LOGIN') . '\';' .
                ' var comlogin = 1;';
        $document = &JFactory::getDocument();
        $document->addScriptDeclaration($langScript);
        JHTML::_('script', 'openid.js');
    }

?>	<form action="<?php echo JRoute::_('index.php', true, 0); ?>" method="post" name="com-login" >

         <p> <?php echo JText::_('COM_VIRTUEMART_ORDER_CONNECT_FORM'); ?></p>


            <p class="width30 floatleft" id="com-form-login-username">
                <input type="text" name="username" size="18" alt="<?php echo JText::_('COM_VIRTUEMART_USERNAME'); ?>" value="<?php echo JText::_('COM_VIRTUEMART_USERNAME'); ?>" onblur="if(this.value=='') this.value='<?php echo JText::_('COM_VIRTUEMART_USERNAME'); ?>';" onfocus="if(this.value=='<?php echo JText::_('COM_VIRTUEMART_USERNAME'); ?>') this.value='';" />
            </p>
            <p class="width30 floatleft" id="com-form-login-password">
				<?php if ( VmConfig::isJ15() ) { ?>
					<input type="password" id="passwd" name="passwd" class="inputbox" size="18" alt="password" />
				<?php } else { ?>
					<input id="modlgn-passwd" type="password" name="password" class="inputbox" size="18"  />
				<?php } ?>
			</p>
            <p class="width30 floatleft" id="com-form-login-remember">
                <input type="submit" name="Submit" class="default" value="<?php echo JText::_('COM_VIRTUEMART_LOGIN') ?>" />
<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
			<label for="remember"><?php echo $remember_me = VmConfig::isJ15()? JText::_('Remember me') : JText::_('JGLOBAL_REMEMBER_ME') ?></label>
            <input type="checkbox" id="remember" name="remember" class="inputbox" value="yes" alt="Remember Me" />
        </p>
<?php endif; ?>
        <div class="clr"></div>
        <div class="width30 floatleft">

            <a   href="<?php echo JRoute::_('index.php?option='.$comUserOption.'&view=reset'); ?>">
<?php echo JText::_('COM_VIRTUEMART_ORDER_FORGOT_YOUR_PASSWORD'); ?></a>
        </div>
        <div class="width30 floatleft">
            <a   href="<?php echo JRoute::_('index.php?option='.$comUserOption.'&view=remind'); ?>">
<?php echo JText::_('COM_VIRTUEMART_ORDER_FORGOT_YOUR_USERNAME'); ?></a>
        </div>
        <?php /*
          $usersConfig = &JComponentHelper::getParams( 'com_users' );
          if ($usersConfig->get('allowUserRegistration')) { ?>
          <div class="width30 floatleft">
          <a  class="details" href="<?php echo JRoute::_( 'index.php?option=com_virtuemart&view=user' ); ?>">
          <?php echo JText::_('COM_VIRTUEMART_ORDER_REGISTER'); ?></a>
          </div>
          <?php }
         */ ?>

        <div class="clr"></div>
    </fieldset>

<?php if ( VmConfig::isJ15() ) { ?>
	<input type="hidden" name="option" value="<?php echo $comUserOption ?>" />
	<input type="hidden" name="task" value="login" />
<?php } else { ?>
	<input type="hidden" name="option" value="<?php echo $comUserOption ?>" />
	<input type="hidden" name="task" value="user.login" />
<?php } ?>
    <input type="hidden" name="return" value="<?php echo base64_encode($url) ?>" />
    <?php echo JHTML::_('form.token'); ?>
    </form>


<?php }else {
   ?>
   <form action="index.php" method="post" name="login" id="form-login">
        <?php echo JText::sprintf( 'COM_VIRTUEMART_HINAME', $this->cart->userDetails->JUser->name ); ?>
	<input type="submit" name="Submit" class="button" value="<?php echo JText::_( 'COM_VIRTUEMART_BUTTON_LOGOUT'); ?>" />
        <input type="hidden" name="option" value="<?php echo $comUserOption ?>" />
        
        <?php if ( VmConfig::isJ15() ) { ?>
            <input type="hidden" name="task" value=logout" />
        <?php } else { ?>
            <input type="hidden" name="task" value=user.logout" />
            <?php echo JHtml::_('form.token'); ?>
        <?php } ?>        
	<input type="hidden" name="return" value="<?php echo base64_encode($url) ?>" />
    </form>
    <?php
}
?>

