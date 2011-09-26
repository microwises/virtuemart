<?php
/**
 *
 * Enter address data for the cart, when anonymous users checkout
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk, Max Milbers
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Implement Joomla's form validation
JHTML::_('behavior.formvalidation');
JHTML::stylesheet('vmpanels.css', JURI::root() . 'components/com_virtuemart/assets/css/');
?>
<script language="javascript">
    function myValidator(f, t)
    {
        f.task.value=t; //I understand this as method to set the task of the form on the fTask. This is not longer needed, because we use another js method for the cancel button than before.
        if (document.formvalidator.isValid(f)) {
            f.submit();
            return true;
        } else {
            var msg = '<?php echo JText::_('COM_VIRTUEMART_USER_FORM_MISSING_REQUIRED'); ?>';
            alert (msg+' ');
        }
        return false;
    }

    function callValidatorForRegister(f){

        var elem = jQuery('#username_field');
        elem.attr('class', "required");

        var elem = jQuery('#name_field');
        elem.attr('class', "required");

        var elem = jQuery('#password_field');
        elem.attr('class', "required");

        var elem = jQuery('#password2_field');
        elem.attr('class', "required");

        var elem = jQuery('#userForm');

<?php if ($this->fTask === 'savecartuser') $rtask = 'registercartuser'; else $rtask = 'registercheckoutuser'; ?>

                return myValidator(f, '<?php echo $rtask ?>');

            }


</script>
<?php
if (VmConfig::get('oncheckout_show_steps', 1)){
	echo '<div class="checkoutStep" id="checkoutStep1">'.JText::_('COM_VIRTUEMART_USER_FORM_CART_STEP1').'</div>';
}

if (VmConfig::get('oncheckout_show_register', 1) && $this->userDetails->JUser->id === 0) {

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

    $uri = JFactory::getURI();
    $url = $uri->toString(array('path', 'query', 'fragment'));
?>	<form action="<?php echo JRoute::_('index.php', true, 0); ?>" method="post" name="com-login" >

        <fieldset class="input">
            <legend><?php echo JText::_('COM_VIRTUEMART_ORDER_CONNECT_FORM') ?></legend>

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
            <a   href="<?php echo JRoute::_('index.php?option=com_user&view=reset'); ?>">
<?php echo JText::_('COM_VIRTUEMART_ORDER_FORGOT_YOUR_PASSWORD'); ?></a>
        </div>
        <div class="width30 floatleft">
            <a   href="<?php echo JRoute::_('index.php?option=com_user&view=remind'); ?>">
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
	<input type="hidden" name="option" value="com_user" />
	<input type="hidden" name="task" value="login" />
<?php } else { ?>
	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="user.login" />
<?php } ?>
    <input type="hidden" name="return" value="<?php echo base64_encode($url) ?>" />
    <?php echo JHTML::_('form.token'); ?>
    </form>


<?php
	if (VmConfig::get('oncheckout_show_register', 1)) {
       	echo JText::sprintf('COM_VIRTUEMART_ONCHECKOUT_DEFAULT_TEXT_REGISTER', JText::_('COM_VIRTUEMART_REGISTER_AND_CHECKOUT'), JText::_('COM_VIRTUEMART_CHECKOUT_AS_GUEST'));
//         	echo JText::_(VmConfig::get('oncheckout_show_register_text', $msg));
	}
}
?>

        <form method="post" id="userForm" name="userForm" class="form-validate">
        <!--<form method="post" id="userForm" name="userForm" action="<?php echo JRoute::_('index.php'); ?>" class="form-validate">-->
            <div class="control-buttons">
        <?php
        if (VmConfig::get('oncheckout_show_register', 1) && $this->userDetails->JUser->id === 0) {

            if ($this->fTask === 'savecartuser') {
                $rtask = 'registercartuser';
            } else {
                $rtask = 'registercheckoutuser';
            }

        ?>
         <button class="default" type="submit" onclick="javascript:return callValidatorForRegister(userForm);" ><?php echo JText::_('COM_VIRTUEMART_REGISTER_AND_CHECKOUT'); ?></button>
         <button class="default" type="submit" onclick="javascript:return myValidator(userForm, '<?php echo $this->fTask; ?>');" ><?php echo JText::_('COM_VIRTUEMART_CHECKOUT_AS_GUEST'); ?></button>
         <button class="default" type="reset" onclick="window.location.href='<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart'); ?>'" ><?php echo JText::_('COM_VIRTUEMART_CANCEL'); ?></button>


<?php } else { ?>

	<button class="default" type="submit" onclick="javascript:return myValidator(userForm, '<?php echo $this->fTask; ?>');" ><?php echo JText::_('COM_VIRTUEMART_SAVE'); ?></button>
	<button class="default" type="reset" onclick="window.location.href='<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart'); ?>'" ><?php echo JText::_('COM_VIRTUEMART_CANCEL'); ?></button>

<?php } ?>
    </div>

    <fieldset>
        <span class="bold">
        <?php echo JText::_('COM_VIRTUEMART_USERFIELDS_FORM_LBL'); ?>
        </span>
        <?php
            $_k = 0;
            $_set = false;
            $_table = false;
            $_hiddenFields = '';
            if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
//             $cart = VirtueMartCart::getCart();
//             $cart->prepareAddressDataInCart();
//             $this->assignRef('cart', $cart);
// 				vmdebug('my cart in edit_adress',$cart->BTaddress);


            if (count($this->userFields['functions']) > 0) {
                echo '<script language="javascript">' . "\n";
                echo join("\n", $this->userFields['functions']);
                echo '</script>' . "\n";
            }

            $corefields = VirtueMartModelUserfields::getCoreFields();
//             for ($_i = 0, $_n = count($this->userFields['fields']); $_i < $_n; $_i++) {
            for ($_i = 0, $_n = count($this->userFields['fields']); $_i < $_n; $_i++) {
            	// Do this at the start of the loop, since we're using 'continue' below!
                if ($_i == 0) {
                    $_field = current($this->userFields['fields']);
                } else {
                    $_field = next($this->userFields['fields']);
                }

                if ($_field['hidden'] == true) {
                    $_hiddenFields .= $_field['formcode'] . "\n";
                    continue;
                }
                if ($_field['type'] == 'delimiter') {
                    if ($_set) {
                        // We're in Fieldset. Close this one and start a new
                        if ($_table) {
                            echo '	</table>' . "\n";
                            $_table = false;
                        }
                        echo '</fieldset>' . "\n";
                    }
                    $_set = true;
                    echo '<fieldset>' . "\n";
                    echo '	<legend>' . "\n";
                    echo '		' . $_field['title'];
                    echo '	</legend>' . "\n";
                    continue;
                }

                if (!$_table) {
                    // A table hasn't been opened as well. We need one here,
                    echo '	<table class="adminform user-details">' . "\n";
                    $_table = true;
                }

                $class = '';

                if (in_array($_field['name'], $corefields)  && $_field['name'] != 'email') {
                    $class = 'class = "joomlaCoreField" ';
                }

                echo '		<tr ' . $class . ' >' . "\n";
                echo '			<td class="key">' . "\n";
                echo '				<label for="' . $_field['name'] . '_field">' . "\n";
                echo '					' . $_field['title'] . ($_field['required'] ? ' *' : '') . "\n";
                echo '				</label>' . "\n";
                echo '			</td>' . "\n";
                echo '			<td>' . "\n";
                echo '				' . $_field['formcode'] . "\n";
                echo '			</td>' . "\n";
                echo '		</tr>' . "\n";
            }

            if ($_table) {
                echo '	</table>' . "\n";
            }
            if ($_set) {
                echo '</fieldset>' . "\n";
            }
            echo $_hiddenFields;
        ?>

            </fieldset>
<?php // }
            if ($this->userDetails->JUser->get('id')) { ?>
        <fieldset>
            <legend>
<?php echo JText::_('COM_VIRTUEMART_USER_FORM_SHIPTO_LBL'); ?>
            </legend>
            <a class="vmicon vmicon-16-editadd" href="<?php echo JRoute::_('index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=ST&shipto=0&cid[]='.$this->userDetails->JUser->get('id'),$this->useXHTML,$this->useSSL) ?>">
<?php echo JText::_('COM_VIRTUEMART_USER_FORM_ADD_SHIPTO_LBL'); ?>
            </a>

            <table class="adminform user-details">
                <tr>
                    <td>
    <?php echo $this->lists['shipTo']; ?>
                            </td>
                        </tr>
                    </table>
                </fieldset>
<?php } ?>
            <input type="hidden" name="option" value="com_virtuemart" />
            <input type="hidden" name="view" value="user" />
            <input type="hidden" name="controller" value="user" />
            <input type="hidden" name="task" value="<?php echo $this->fTask; // I remember, we removed that, but why?  ?>" />
            <input type="hidden" name="address_type" value="<?php echo $this->address_type; ?>" />
            <input type="hidden" name="virtuemart_userinfo_id" value="<?php echo current($this->userDetails->userInfo)->virtuemart_userinfo_id; ?>" />
<?php echo JHTML::_('form.token');
// $userinfoid = current($this->userDetails->userInfo);
// vmdebug('hmm',$userinfoid->virtuemart_userinfo_id);
?>
</form>
