<?php
/**
 * Renders the email for the user send in the registration process
 * @package	VirtueMart
 * @subpackage User
 * @author Max Milbers
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.html.php 2459 2010-07-02 17:30:23Z milbo $
 */

if(VmConfig::get('html_email',true)){
	$li = '<br />';
}
else {
	$li = "\n";
}
?>

<?php echo JText::_('COM_VIRTUEMART_WELCOME_USER')." ".$this->vendor->vendor_store_name . $li;

if(!empty($this->activationLink)) echo  JText::_('COM_VIRTUEMART_LINK_ACTIVATE_ACCOUNT')." ".JURI::root().$this->activationLink .$li; ?>

Your Registration data <?php echo $li ?>

<?php echo  JText::_('COM_VIRTUEMART_YOUR_LOGINAME').": ".$this->_models['user']->_data->JUser->username .$li; ?>
<?php echo  JText::_('COM_VIRTUEMART_YOUR_DISPLAYED_NAME').": ".$this->_models['user']->_data->JUser->name .$li;  ?>
<?php echo  JText::_('COM_VIRTUEMART_YOUR_PASSWORD').": ".$this->password .$li; ?>

<?php  echo JText::_('COM_VIRTUEMART_YOUR_ADDRESS').": ".  $li ?>
<?php foreach($this->userFields['fields'] as $userField){
	if(!empty($userField['value']) && $userField['name']!='user_is_vendor') echo $userField['title'].' '.$userField['value'].$li ;
}
echo $li;
echo JURI::root().JRoute::_('index.php?option=com_virtuemart&view=user',$this->useXHTML,$this->useSSL).$li;
//Multi-X
//echo JURI::root().JRoute::_('index.php?option=com_virtuemart&view=vendor&virtuemart_vendor_id='.$this->vendor->virtuemart_vendor_id).$li;
