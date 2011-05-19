<?php
/**
 * Renders the email for the vendor send in the registration process
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
} else {
	$li = "\n";
}
?>

A new shopper registered <?php echo $this->_models['user']->_data->JUser->name .$li; ?>

The Registration data <?php echo $li; ?>

loginname: <?php echo $this->_models['user']->_data->JUser->username .$li; ?>
displayed name: <?php echo $this->_models['user']->_data->JUser->name .$li; ?>


Entered adress <?php echo $li ?>
<?php foreach($this->userFields['fields'] as $userField){
	if(!empty($userField['value']) && $userField['name']!='user_is_vendor') echo $userField['title'].' '.$userField['value'].$li ;
}
echo $li;
echo JURI::root().'index.php?option=com_virtuemart&view=user&virtuemart_user_id='.$this->_models['user']->_id.' '.$li;
echo JURI::root().'index.php?option=com_virtuemart&view=vendor&virtuemart_vendor_id='.$this->vendor->virtuemart_vendor_id.' '.$li;
