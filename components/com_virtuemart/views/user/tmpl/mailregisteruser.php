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

dump($this,'Data in mail for user');
if(VmConfig::get('html_email',true)){
	$li = '<br />';
} else {
	$li = "\n";
}
?>

Welcome to <?php echo $this->vendor->vendor_store_name . $li;

if(!empty($this->activationLink)) echo 'Please use this link to activate your account: '.JURI::root().$this->activationLink .$li; ?>

Your Registration data <?php echo $li ?>

Your loginname: <?php echo $this->_models['user']->_data->JUser->username .$li; ?>
your displayed name: <?php echo $this->_models['user']->_data->JUser->name .$li;  ?>
your password: <?php echo $this->password .$li; ?>

Your entered adress <?php echo $li ?>
<?php foreach($this->userFields['fields'] as $userField){
	if(!empty($userField['value']) && $userField['name']!='user_is_vendor') echo $userField['title'].' '.$userField['value'].$li ;
}
echo $li;
echo JURI::root().JRoute::_('index.php?option=com_virtuemart&controller=user').$li;
echo JURI::root().JRoute::_('index.php?option=com_virtuemart&controller=vendor&vendor_id='.$this->vendor->vendor_id).$li;
