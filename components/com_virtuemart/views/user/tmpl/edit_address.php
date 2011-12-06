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

if ($this->fTask === 'savecartuser') {
	$rtask = 'registercartuser';
} else {
	$rtask = 'registercheckoutuser';
}

echo shopFunctionsF::getLoginForm(false);


?>
<script language="javascript">
    function myValidator(f, t)
    {
        f.task.value=t; //I understand this as method to set the task of the form on the fTask. This is not longer needed, because we use another js method for the cancel button than before.
        if (document.formvalidator.isValid(f)) {
            f.submit();
            return true;
        } else {
            var msg = '<?php echo addslashes( JText::_('COM_VIRTUEMART_USER_FORM_MISSING_REQUIRED_JS') ); ?>';
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

		return myValidator(f, '<?php echo $rtask ?>');

   }


</script>

 <fieldset>
	 <legend><?php
	 if($this->address_type=='BT'){
	 	echo JText::_('COM_VIRTUEMART_USER_FORM_EDIT_BILLTO_LBL');
	 } else {
	 	echo JText::_('COM_VIRTUEMART_USER_FORM_ADD_SHIPTO_LBL');
	 } ?>
	</legend>


<form method="post" id="adminForm" name="userForm" class="form-validate">
<!--<form method="post" id="userForm" name="userForm" action="<?php echo JRoute::_('index.php'); ?>" class="form-validate">-->
<div class="control-buttons">
<?php

if( strpos($this->fTask,'cart') || strpos($this->fTask,'checkout') ){
	$rview = 'cart';
} else {
	$rview = 'user';
}

if( strpos($this->fTask,'checkout') || $this->address_type=='ST' ){
	$buttonclass = 'default';
} else {
	$buttonclass = 'button vm-button-correct';
}


if (VmConfig::get('oncheckout_show_register', 1) && $this->userDetails->JUser->id === 0 && !VmConfig::get('oncheckout_only_registered',0) && $this->address_type=='BT') {
	echo JText::sprintf('COM_VIRTUEMART_ONCHECKOUT_DEFAULT_TEXT_REGISTER', JText::_('COM_VIRTUEMART_REGISTER_AND_CHECKOUT'), JText::_('COM_VIRTUEMART_CHECKOUT_AS_GUEST'));
}
if (VmConfig::get('oncheckout_show_register', 1) && $this->userDetails->JUser->id === 0 && $this->address_type=='BT') {
?>

<button class="<?php echo $buttonclass ?>" type="submit" onclick="javascript:return callValidatorForRegister(userForm);" title="<?php echo JText::_('COM_VIRTUEMART_REGISTER_AND_CHECKOUT'); ?>"><?php echo JText::_('COM_VIRTUEMART_REGISTER_AND_CHECKOUT'); ?></button>
<?php if(!VmConfig::get('oncheckout_only_registered',0)) { ?>
	<button class="<?php echo $buttonclass ?>" title="<?php echo JText::_('COM_VIRTUEMART_CHECKOUT_AS_GUEST'); ?>" type="submit" onclick="javascript:return myValidator(userForm, '<?php echo $this->fTask; ?>');" ><?php echo JText::_('COM_VIRTUEMART_CHECKOUT_AS_GUEST'); ?></button>
<?php } ?>
<button class="default" type="reset" onclick="window.location.href='<?php echo JRoute::_('index.php?option=com_virtuemart&view='.$rview); ?>'" ><?php echo JText::_('COM_VIRTUEMART_CANCEL'); ?></button>


<?php } else { ?>

	<button class="<?php echo $buttonclass ?>" type="submit" onclick="javascript:return myValidator(userForm, '<?php echo $this->fTask; ?>');" ><?php echo JText::_('COM_VIRTUEMART_SAVE'); ?></button>
	<button class="default" type="reset" onclick="window.location.href='<?php echo JRoute::_('index.php?option=com_virtuemart&view='.$rview); ?>'" ><?php echo JText::_('COM_VIRTUEMART_CANCEL'); ?></button>

<?php } ?>
    </div>


        <span class="bold">
        <?php //echo JText::_('COM_VIRTUEMART_USERFIELDS_FORM_LBL'); ?>
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
                echo '				<label class="' . $_field['name'] . '" for="' . $_field['name'] . '_field">' . "\n";
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
    <?php echo $this->lists['shipTo']; ?>

                </fieldset>
<?php } ?>
            <input type="hidden" name="option" value="com_virtuemart" />
            <input type="hidden" name="view" value="user" />
            <input type="hidden" name="controller" value="user" />
            <input type="hidden" name="task" value="<?php echo $this->fTask; // I remember, we removed that, but why?  ?>" />
            <input type="hidden" name="address_type" value="<?php echo $this->address_type; ?>" />
            <input type="hidden" name="virtuemart_userinfo_id" value="<?php echo $this->virtuemart_userinfo_id ; ?>" />
<?php echo JHTML::_('form.token');

?>
</form>
