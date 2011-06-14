<?php
/**
 * abstract class for payment plugins
 *
 * @package	VirtueMart
 * @subpackage Plugins
 * @author Max Milbers
 * @author Oscar van Eijk
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */

// Load the helper functions that are needed by all plugins
if(!class_exists('ShopFunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'shopfunctions.php');
if(!class_exists('DbScheme')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'dbscheme.php');

// Get the plugin library
jimport('joomla.plugin.plugin');

abstract class vmPaymentPlugin extends JPlugin
{
    private $_virtuemart_paymentmethod_id = 0;
    private $_paym_name = '';

    /** var Must be overriden in every plugin file by adding this code to the constructor: $this->_pelement = basename(__FILE, '.php'); */
    var $_pelement = '';

    /** var Must be overriden in every plugin file */
	var $_pcode = '' ;

    /**
     * Constructor
     *
     * @param object $subject The object to observe
     * @param array  $config  An array that holds the plugin configuration
     * @since 1.5
     */
	function __construct(& $subject, $config)
	{
        parent::__construct($subject, $config);
    }

    /**
     * Method to create te plugin specific table; must be reimplemented.
     * @example
     * 	$_scheme = DbScheme::get_instance();
     * 	$_scheme->create_scheme('#__vm_order_payment_'.$this->_pelement);
     * 	$_schemeCols = array(
     * 		 'id' => array (
     * 				 'type' => 'int'
     * 				,'length' => 11
     * 				,'auto_inc' => true
     * 				,'null' => false
     * 		)
     * 		,'virtuemart_order_id' => array (
     * 				 'type' => 'int'
     * 				,'length' => 11
     * 				,'null' => false
     * 		)
     * 		,'payment_method_id' => array (
     * 				 'type' => 'text'
     * 				,'null' => false
     * 		)
     * 	);
     * 	$_schemeIdx = array(
     * 		 'idx_order_payment' => array(
     * 				 'columns' => array ('virtuemart_order_id')
     * 				,'primary' => false
     * 				,'unique' => false
     * 				,'type' => null
     * 		)
     * 	);
     * 	$_scheme->define_scheme($_schemeCols);
     * 	$_scheme->define_index($_schemeIdx);
     * 	if (!$_scheme->scheme()) {
     * 		JError::raiseWarning(500, $_scheme->get_db_error());
     * 	}
     * 	$_scheme->reset();
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
    protected function setVmPaymentParams($vendorId=0, $jplugin_id=0) {

        if (!$vendorId)   $vendorId = 1;
        $db = &JFactory::getDBO();
        if (!$jplugin_id) {
            if (VmConfig::isJ15()) {
                $q = 'SELECT `id` FROM #__plugins WHERE `element` = "' . $this->_pelement . '"';
            } else {
                $q = 'SELECT `extension_id` FROM #__extensions  WHERE `element` = "' . $this->_pelement . '"';
            }
            $db->setQuery($q);
            $this->_jplugin_id = $db->loadResult();
            if (!$this->_jplugin_id) {
                $mainframe = &JFactory::getApplication();
                $mainframe->enqueueMessage(JText::_('COM_VIRTUEMART_NO_PAYMENT_PLUGIN'));
                return false;
            }
        } else {
            $this->_jplugin_id = $jplugin_id;
        }

        $q = 'SELECT `virtuemart_paymentmethod_id`,`paym_name` FROM #__virtuemart_paymentmethods WHERE `paym_jplugin_id` = "' . $this->_jplugin_id . '" AND `virtuemart_vendor_id` = "' . $vendorId . '" AND `published`="1" ';
        $db->setQuery($q);
        $result = $db->loadAssoc();

		if(!empty($result)){
			if(!class_exists('VirtueMartModelPaymentmethod')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'paymentmethod.php');
                       
			if(!class_exists('vmParameters')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'parameterparser.php');
            $this->paymentModel = new VirtueMartModelPaymentmethod();
            $this->paymentModel->setId($result['virtuemart_paymentmethod_id']);
            $this->paymentMethod = $this->paymentModel->getPaym();
            $this->params->_raw=$this->paymentMethod->paym_params; // valerie */
       
            return true;
        } else {
            //			$mainframe = &JFactory::getApplication();
            //			$mainframe->enqueueMessage( 'The Paymentmethod '.$this->_paym_name.' with element '.$this->_pelement.' didnt found used and published payment plugin by vendor','error' );
            return false;
        }
    }

    /**
     * This event is fired during the checkout process. It allows the shopper to select
     * one of the available payment methods.
     * It should display a radio button (name: virtuemart_paymentmethod_id) to select the payment method. Other
     * information (like credit card info) might be selected as well.
     *
     * @param object $cart The cart object
     * @param integer $checkedPaymId ID of an already selected payment method ID, if any
     * @author Max Milbers
     */
    public function plgVmOnSelectPayment(VirtueMartCart $cart, $checkedPaymId=0) {

        if (!$this->setVmPaymentParams($cart->vendorId)) {
            return;
        }

        if ($checkedPaymId == $this->paymentMethod->virtuemart_paymentmethod_id) {
            $checked = '"checked"';
        } else {
            $checked = '';
        }

        $html = '<fieldset>';
        $html .= '<input type="radio" name="virtuemart_paymentmethod_id" value="' . $this->paymentMethod->virtuemart_paymentmethod_id . '" ' . $checked . '>' . $this->paymentMethod->paym_name . ' ';
        $html .= '</fieldset> ';

        return $html;
    }

    /**
     * This event is fired after the payment method has been selected. It can be used to store
     * additional payment info in the cart.
     *
     * @author Max Milbers
     */
    public function plgVmOnPaymentSelectCheck($cart) {
        return null;
    }

    /**
     * This event is fired during the checkout process. It can be used to validate the
     * payment data as entered by the user.
     *
     * @return boolean True when the data was valid, false otherwise. If the plugin is not activated, it should return null.
     * @author Max Milbers
     */
    public function plgVmOnCheckoutCheckPaymentData() {
        return null;
    }

    //abstract function plgVmOnPaymentResponseReceived();

    /**
     * This event is fired after the payment has been processed; it stores the payment method-
     * specific data.
     * All plugins *must* reimplement this method.
     * NOTE for Plugin developers:
     *  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL
     *  If this plugin IS executed, it MUST return the order status code that the order should get. This triggers the stock updates if required
     *
     * @param int $_orderNr The ordernumber being processed
     * @param object $_orderData Data from the cart
     * @param array $_priceData Price information for this order
     * @return mixed Null when this method was not selected, otherwise the new order status
     * @author Max Milbers
     * @author Oscar van Eijk
     */
    abstract function plgVmOnConfirmedOrderStorePaymentData($_orderNr, $_orderData, $_priceData);

    /**
     * This method is fired when showing the order details in the backend.
     * It displays the the payment method-specific data.
     * All plugins *must* reimplement this method.
     *
     * @param integer $_virtuemart_order_id The order ID
     * @param integer $_paymethod_id Payment method used for this order
     * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
     * @author Max Milbers
     * @author Oscar van Eijk
     */
    abstract function plgVmOnShowOrderPaymentBE($_virtuemart_order_id, $_paymethod_id);

    /**
     * This event is fired each time the status of an order is changed to Cancelled.
     * It can be used to refund payments, void authorization etc.
     * Return values are ignored.
     *
     * Note for plugin developers: you are not required to reimplement this method, but if you
     * do so, it MUST start with this code:
     *
     * 	$_paymethodID = $this->getPaymentMethodForOrder($_orderID);
     * 	if (!$this->selectedThisMethod($this->_pelement, $_paymethodID)) {
     * 		return;
     * 	}
     *
     * @author Oscar van Eijk
     * @param int $_orderID
     * @param char $_oldStat Previous order status
     * @param char $_newStat New order status
     */
    /*
    function plgVmOnCancelPayment($_orderID, $_oldStat, $_newStat) {
        return;
    }
*/
    /**
     * This event is fired when the status of an order is changed to Shipped.
     * It can be used to confirm or capture payments
     *
     * Note for plugin developers: you are not required to reimplement this method, but if you
     * do so, it MUST start with this code:
     *
     * 	$_paymethodID = $this->getPaymentMethodForOrder($_orderID);
     * 	if (!$this->selectedThisMethod($this->_pelement, $_paymethodID)) {
     * 		return null;
     * 	}
     *
     * @author Oscar van Eijk
     * @param int $_orderID Order ID
     * @return mixed True on success, False on failure, Null if this plugin was not activated
     */
    public function plgVmOnShipOrderPayment($_orderID) {
        return null;
    }

    /**
     * Get the order payment ID for a given order number
     * @access protected
     * @author Oscar van Eijk
     * @param int $_id The order ID
     * @return int The payment method ID, or -1 when not found
     */
    protected function getPaymentMethodForOrder($_id) {
        $_db = &JFactory::getDBO();
        $_q = 'SELECT `payment_method_id` '
                . 'FROM #__virtuemart_orders '
                . "WHERE virtuemart_order_id = $_id";
        $_db->setQuery($_q);
        if (!($_r = $_db->loadAssoc())) {
            return -1;
        }
        return $_r['payment_method_id'];
    }

    /**
     * Retrieve the payment method-specific encryption key
     *
     * @author Oscar van Eijk
     * @return mixed
     */
    function get_passkey() {
        $_db = &JFactory::getDBO();
        $_q = 'SELECT ' . VM_DECRYPT_FUNCTION . "(secret_key, '" . ENCODE_KEY . "') as passkey "
                . 'FROM #__virtuemart_paymentmethods '
                . "WHERE virtuemart_paymentmethod_id='" . $this->_virtuemart_paymentmethod_id . "'";
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
    final protected function selectedThisMethod($_pelement, $_pid) {
        $_db = &JFactory::getDBO();

        if (VmConfig::isJ15()) {
            $_q = 'SELECT COUNT(*) AS c '
                    . 'FROM #__virtuemart_paymentmethods AS vm '
                    . ',    #__plugins AS j '
                    . "WHERE vm.virtuemart_paymentmethod_id='$_pid' "
                    . 'AND   vm.paym_jplugin_id = j.id '
                    . "AND   j.element = '$_pelement'";
        } else {
            $_q = 'SELECT COUNT(*) AS c '
                    . 'FROM #__virtuemart_paymentmethods AS vm '
                    . ',    #__extensions AS j '
                    . "WHERE vm.virtuemart_paymentmethod_id='$_pid' "
                    . 'AND   vm.paym_jplugin_id = j.extension_id '
                    . "AND   j.element = '$_pelement'";
        }

        $_db->setQuery($_q);
        $_r = $_db->loadAssoc(); // TODO Error check
        return ($_r['c'] == 1);

    }

    /**
     * Get the name of the payment method
     * @param int $_pid The payment method ID
     * @author Oscar van Eijk
     * @return string Paymenent method name
     */
    final protected function getThisMethodName($_pid) {
        $_db = &JFactory::getDBO();


        $_q = 'SELECT `paym_name` '
                . 'FROM #__virtuemart_paymentmethods '
                . "WHERE virtuemart_paymentmethod_id='$_pid' ";



        $_db->setQuery($_q);
        $_r = $_db->loadAssoc(); // TODO Error check
        return $_r['paym_name'];
    }

    /**
     * This method writes all payment plugin specific data to the plugin's table
     *
     * @param array $_values Indexed array in the format 'column_name' => 'value'
     * @param string $_table Table name
     * @author Oscar van Eijk
     */
    protected function writePaymentData($_values, $_table) {
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
        if (!$_db->query()) {
            JError::raiseWarning(500, $_db->getErrorMsg());
        }
    }

    /**
     * This method updates all payment plugin specific data to the plugin's table
     *
     * @param array $_values Indexed array in the format 'column_name' => 'value'
     * @param string $_table Table name
     * @author Valerie Isaksen
     *
     */
    protected function updatePaymentData($values, $table, $where_key, $where_value) {
        if (count($values) == 0) {
            JError::raiseWarning(500, 'updatePaymentData got no data to update to ' . $table);
            return;
        }
        $cols = array();
        $vals = array();
        foreach ($values as $col => $val) {
            $fields[] = "`$col`" . "=" . "'$val'";
        }
        $db = JFactory::getDBO();
        $q = 'UPDATE `' . $table . '` SET '
                . implode(',', $fields)
                . ' WHERE `' . $where_key . '` =' . $where_value
        ;

        $db->setQuery($q);
        if (!$db->query()) {
            JError::raiseWarning(500, $db->getErrorMsg());
        }
    }

}
