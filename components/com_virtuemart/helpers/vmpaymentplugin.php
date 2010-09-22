<?php
/**
 * abstract class for payment plugins
 *
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
 * @version $Id: user_info.php 2494 2010-07-19 20:50:08Z milbo $
 */
 
// Load the shopfunctions helper that's needed by all plugins
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'shopfunctions.php');

// Get the plugin library
jimport('joomla.plugin.plugin');

abstract class vmPaymentPlugin extends JPlugin  {
	
	private $_paym_id = 0;
	private $_paym_name = '';
	
	/** var Must be overriden in every plugin file by adding this code to the constructor: $this->_pelement = basename(__FILE, '.php'); */
	var $_pelement = '';
	
	/** var Must be overriden in every plugin file */
	var $_pcode = '' ;

	/** var Must be overriden in every plugin file  atm without use, must be choosen while configuration
	 * 
	 *  C = Creditcart
	 *  Y = Payment processor
	 *  B = Bank debit
	 *  N = Address only (Cash on delivery)
	 *  P = HTML form based (paypal)
	 * 
	 * ATTENTION: Is now saved in the params !
	 * */
	var $paym_type = '' ;

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgVmPaymentPlugin(& $subject, $config) {
		parent::__construct($subject, $config);
		
//		dump($config,'plgPaymentCashondel Constructor $config');
//		dump($this->params,'plgPaymentCashondel Constructor $this->params');		
	}
	
	/**
	 * Method to create te plugin specific table; must be reimplemented.
	 * @example 
	 * 	$_db = JFactory::getDBO();
	 *.	$_q = 'CREATE TABLE IF NOT EXISTS `#__vm_order_payment_' . $this->_pelement . '` ('
	 *.	. ' `id` INT(11) NOT NULL AUTO_INCREMENT'
	.*.	. ',`order_id` INT(11) NOT NULL' // REQUIRED!
	.*.	. ',`payment_method_id` INT(11) NOT NULL' // REQUIRED!
	.*.	. ',`status` INT(11) NOT NULL DEFAULT 1'
	.*.	. ',`data` BLOB'
	.*.	. ',`account` INT(11) DEFAULT NULL'
	.*.	. ',`log` TEXT'
	.*.	. ',PRIMARY KEY (`id`)'
	.*.	. ',KEY `idx_order_payment_' . $this->_pelement . '_order_id` (`order_id`)'
	.*.	. ") ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Data for the " . $this->_pelement . " payment plugin.'";
	.*.	$_db->setQuery($_q);
	.*.	if (!$_db->query()) {
	.*.		JError::raiseWarning(500, $_db->getErrorMsg());
	.*.	}
	 * @author Oscar van Eijk
	 */
	abstract protected function _createTable();

	/**
	 * This functions gets the used and configured payment method
	 * pelement of this class determines the used jplugin.
	 * The right payment method is determined by the vendor and the jplugin id.
	 * 
	 * This function sets the used payment plugin as variable of this class
	 * @author Max Milbers
	 * 
	 */
	function setVmParams($vendorId=0,$jplugin_id=0){
		
		if(!$vendorId) $vendorId = 1;
	 	$db = &JFactory::getDBO();
	 	if(!$jplugin_id){
			$q = 'SELECT `id` FROM #__plugins WHERE `element` = "'.$this->_pelement.'"';
			$db->setQuery($q);
			$this->_jplugin_id = $db->loadResult();
			if(!$this->_jplugin_id){
				return false;	
			}		
	 	}else{
	 		$this->_jplugin_id = $jplugin_id;
	 	}

		$q = 'SELECT `paym_id`,`paym_name` FROM #__vm_payment_method WHERE `paym_jplugin_id` = "'.$this->_jplugin_id.'" AND `paym_vendor_id` = "'.$vendorId.'" AND `published`="1" ';
		$db->setQuery($q);
		$result =  $db->loadAssoc();
		
		if($result){
			require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'models'.DS.'paymentmethod.php');
			$this->paymentModel = new VirtueMartModelPaymentmethod();
			$this->paymentModel->setId($result['paym_id']);
			$this->paymentMethod = $this->paymentModel->getPaym();
			return true;	
		} else{
			return false;
		}

	}
		
	/**
	 * This shows the plugin for choosing in the payment list of the checkout process.
	 * 
	 * @author Max Milbers
	 */
	 
	function plgVmOnShowList($cart,$checkedPaymId=0){
		
		if(!$this -> setVmParams($cart['vendor_id'])) return ;
		
		if($checkedPaymId==$this->paymentMethod->paym_id) $checked = '"checked"'; else $checked = '';
		
		$html = '<fieldset>';
		$html .= '<input type="radio" name="paym_id" value="'.$this->paymentMethod->paym_id.'" '.$checked.'>'.$this->paymentMethod->paym_name.' ';
		$html .= ' </fieldset> ';
		return $html;
	}

	/**
	 * This is for setting the input data of the payment method, after selecting into the cart
	 * 
	 * @author Max Milbers
	 */
	function plgVmOnPaymentSelectCheck($cart){
		return null;	
	}
	
	/**
	 * This is for checking the input data of the payment method within the checkout
	 * 
	 * @author Max Milbers
	 */
	function plgVmOnCheckoutCheckPaymentData(){
		return null;
	}
	
	/**
	 * This method stores the data of the used payment method. The function is made abstract
	 * since all plugins *must* reimplement it.
	 * 
	 * @param int $_orderNr The ordernumber being processed
	 * @param array $_orderData Data from the cart
	 * @param array $_priceData Price information for this order
	 * @author Max Milbers
	 * @author Oscar van Eijk
	 */
	abstract function plgVmOnConfirmedOrderStorePaymentData($_orderNr, $_orderData, $_priceData);
	
	/**
	 * This method displays the stored data of the transaction
	 * 
	 * @author Max Milbers
	 */
	function plgVmOnShowStoredOrder(){
		return null;
	}

	/**
	 * Retrieve the payment method-specific encryption key
	 *
	 * @author Oscar van Eijk
	 * @return mixed
	 */
	function get_passkey()
	{
		$_db = &JFactory::getDBO();
		$_q = 'SELECT ' . VM_DECRYPT_FUNCTION . "(secret_key, '" . ENCODE_KEY . "') as passkey "
			. 'FROM #__vm_payment_method '
			. "WHERE paym_id='" . $this->_paym_id . "'";
		$_db->setQuery($_q);
		$_r = $_db->loadAssoc(); // TODO Error check
		return $_r['passkey'];
	}

	/**
	 * This method checks if the selected payment method matches the current plugin
	 * @param string $_pelement Element name, taken from the plugin filename
	 * @param int $_pid The payment method ID
	 * @author Oscar van Eijk
	 * @return True if the calling plugin has the given payment ID
	 */
	protected function selectedThisMethod($_pelement, $_pid)
	{
		$_db = &JFactory::getDBO();
		$_q = 'SELECT COUNT(*) AS c '
			. 'FROM #__vm_payment_method AS vm '
			. ',    #__plugins AS j '
			. "WHERE vm.paym_id='$_pid' "
			. 'AND   vm.paym_jplugin_id = j.id '
			. "AND   j.element = '$_pelement'";
		$_db->setQuery($_q);
		$_r = $_db->loadAssoc(); // TODO Error check
		return ($_r['c'] == 1);
		
	}

	/**
	 * This method writes all payment plugin specific data to the plugin's table
	 *
	 * @param array $_values Indexed array in the format 'column_name' => 'value'
	 * @param string $_table Table name
	 * @author Oscar van Eijk
	 */
	protected function writePaymentData($_values, $_table)
	{
		if (count($_values) == 0) {
			JError::raiseWarning(500, 'writePaymentData got no data to save to ' . $_table);
			return;
		}
		$_cols = array();
		$_vals = array();
		foreach ($_values as $_col => $_val) {
			$_cols[] = "`$_col`";
			$_vals[] = "'$_val'";
		}
		$_db = JFactory::getDBO();
		$_q = 'INSERT INTO `' . $_table . '` ('
			. implode(',', $_cols)
			. ') VALUES ('
			. implode(',', $_vals)
			. ')';
		$_db->setQuery($_q);
		dump ($_values,'ValuesToSave');dump($_q, 'SaveQuery');
		if (!$_db->query()) {
			JError::raiseWarning(500, $_db->getErrorMsg());
		}
	}
}
