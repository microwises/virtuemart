<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Edit
* @author Max Milbers
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
AdminUIHelper::startAdminArea();
?>
<form action="index.php" method="post" name="adminForm">

<?php // Loading Templates in Tabs
$tabarray = array();
$tabarray['edit'] = 'COM_VIRTUEMART_ADMIN_PAYMENT_FORM';
$tabarray['config'] = 'COM_VIRTUEMART_ADMIN_PAYMENT_CONFIGURATION';

AdminUIHelper::buildTabs ( $tabarray,$this->paym->virtuemart_paymentmethod_id );
// Loading Templates in Tabs END ?>

<!-- Hidden Fields -->
<input type="hidden" name="task" value="" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="view" value="paymentmethod" />
<input type="hidden" name="virtuemart_paymentmethod_id" value="<?php echo $this->paym->virtuemart_paymentmethod_id; ?>" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php AdminUIHelper::endAdminArea(); ?>

<script language="javascript">

jQuery(document).ready(function() {

	var toggler = document.getElements('#pam_type_CC_on');
	for (var i = 0; i < toggler.length; i++) {
		toggler[i].type='radio';
		jQuery(toggler[i]).bind('click', show);
	}
	var toggler = document.getElements('#pam_type_CC_off');
	for (var i = 0; i < toggler.length; i++) {
		toggler[i].type='radio';
		jQuery(toggler[i]).bind('click', hide);
	}
});

function show(){
	div = jQuery("#creditcardlist");
	div.show();
	//alert('show '+div);
}

function hide(){
	div = jQuery("#creditcardlist");
	div.hide();
}


</script>