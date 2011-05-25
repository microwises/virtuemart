<?php
/**
*
* Modify user form view
*
* @package	VirtueMart
* @subpackage User
* @author Oscar van Eijk
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

AdminMenuHelper::startAdminArea();

// Implement Joomla's form validation
JHTML::_('behavior.formvalidation')
?>
<style type="text/css">
.invalid {
	border-color: #f00;
	background-color: #ffd;
	color: #000;
}
label.invalid {
	background-color: #fff;
	color: #f00;
}
</style>
<script language="javascript">
function myValidator(f, t)
{
	if (f.task.value=='cancel') {
		return true;
	}
	if (document.formvalidator.isValid(f)) {
		f.task.value=t;
		f.submit();
		return true;
	} else {
		var msg = '<?php echo JText::_('COM_VIRTUEMART_USER_FORM_MISSING_REQUIRED'); ?>';
		alert (msg);
	}
	return false;
}
</script>

<form method="post" id="adminForm" name="adminForm" action="index.php" enctype="multipart/form-data" class="form-validate" onSubmit="return myValidator(this);">
<?php
	echo $this->pane->startPane("user-pane");

        if (!empty($this->userDetails->user_is_vendor)) {
		echo $this->pane->startPanel( JText::_('COM_VIRTUEMART_VENDOR'), 'edit_vendor' );
		echo $this->loadTemplate('vendor');
		echo $this->pane->endPanel();
	}
	

	echo $this->pane->startPanel( JText::_('COM_VIRTUEMART_SHOPPER_FORM_LBL'), 'edit_shopper' );
	echo $this->loadTemplate('shopper');
	echo $this->pane->endPanel();
        
        echo $this->pane->startPanel( JText::_('COM_VIRTUEMART_USER_FORM_TAB_GENERALINFO'), 'edit_user' );
	echo $this->loadTemplate('user');
	echo $this->pane->endPanel();
	if ($this->shipto != 0) {
		// Note:
		// Of the order of the tabs change here, change the startOffset value for
		// JPane::getInstance() as well in view.html.php!
		echo $this->pane->startPanel( JText::_('COM_VIRTUEMART_USER_FORM_ADD_SHIPTO_LBL'), 'edit_shipto' );
		echo $this->loadTemplate('shipto');
		echo $this->pane->endPanel();
	}

	if (($_ordcnt = count($this->orderlist)) > 0) {
		echo $this->pane->startPanel( JText::_('COM_VIRTUEMART_ORDER_LIST_LBL') . ' (' . $_ordcnt . ')', 'edit_orderlist' );
		echo $this->loadTemplate('orderlist');
		echo $this->pane->endPanel();
	}

	

	echo $this->pane->endPane();
?>
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="user" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>

<?php AdminMenuHelper::endAdminArea(); ?>
