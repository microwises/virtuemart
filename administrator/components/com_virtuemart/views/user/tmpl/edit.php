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
* @version $Id: edit.php 2302 2010-02-07 19:57:37Z rolandd $
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

AdminMenuHelper::startAdminArea(); 
?>

<form method="post" name="adminForm" action="index.php" enctype="multipart/form-data">
<?php
	echo $this->pane->startPane("user-pane");

	echo $this->pane->startPanel( JText::_('VM_PRODUCT_FORM_PRODUCT_INFO_LBL'), 'edit_user' );
	echo $this->loadTemplate('user');
	echo $this->pane->endPanel();

	echo $this->pane->startPanel( JText::_('VM_SHOPPER_FORM_LBL'), 'edit_shopper' );
	echo $this->loadTemplate('shopper');
	echo $this->pane->endPanel();

	if($this->vendor->isVendor($this->userDetails->JUser->get('id'))){
		echo $this->pane->startPanel( JText::_('VM_STORE_MOD'), 'edit_store' );
		echo $this->loadTemplate('store');
		echo $this->pane->endPanel();
	}

	echo $this->pane->endPane();
?>
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="user" />
</form>

<?php AdminMenuHelper::endAdminArea(); ?>
