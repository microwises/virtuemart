<?php
if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) )
	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
/**
 * @version $Id: standard.php,v 1.4 2005/05/27 19:33:57 ei
 *
 * a special type of 'cash on delivey':
 * its fee depend on total sum
 * @author Max Milbers
 * @version $Id$
 * @package VirtueMart
 * @subpackage payment
 * @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.org
 */

class plgVmPaymentStandard extends vmPaymentPlugin {

	var $_pelement;


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
	function plgVmPaymentStandard(& $subject, $config) {
		$this->_pelement = basename(__FILE__, '.php');
		$this->_createTable();
		parent::__construct($subject, $config);
	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 * @author Oscar van Eijk
	 */
	protected function _createTable()
	{
		$_scheme = DbScheme::get_instance();
		$_scheme->create_scheme('#__virtuemart_order_payment_'.$this->_pelement);
		$_schemeCols = array(
			 'id' => array (
					 'type' => 'int'
					,'length' => 11
					,'auto_inc' => true
					,'null' => false
			)
			,'virtuemart_order_id' => array (
					 'type' => 'int'
					,'length' => 11
					,'null' => false
			)
			,'payment_method_id' => array (
					 'type' => 'text'
					,'null' => false
			)
		);
		$_schemeIdx = array(
			 'idx_order_payment' => array(
					 'columns' => array ('virtuemart_order_id')
					,'primary' => false
					,'unique' => false
					,'type' => null
			)
		);
		$_scheme->define_scheme($_schemeCols);
		$_scheme->define_index($_schemeIdx);
		if (!$_scheme->scheme(true)) {
			JError::raiseWarning(500, $_scheme->get_db_error());
		}
		$_scheme->reset();
	}

	/**
	 * Reimplementation of vmPaymentPlugin::plgVmOnCheckoutCheckPaymentData()
	 *
	 * @see components/com_virtuemart/helpers/vmPaymentPlugin::plgVmOnConfirmedOrderStorePaymentData()
	 * @author Oscar van Eijk
	 */
	   function plgVmOnConfirmedOrderStorePaymentData($virtuemart_order_id, $orderData, $priceData) {
        return false;
    }

    function plgVmAfterCheckoutDoPayment($virtuemart_order_id, $orderData) {

		if (!$this->selectedThisPayment($this->_pelement, $orderData->virtuemart_paymentmethod_id)) {
			return null; // Another method was selected, do nothing
		}

            $paramstring = $this->getVmPaymentParams($vendorId = 0, $orderData->virtuemart_paymentmethod_id);
                $params = new JParameter($paramstring);
                $payment_info = $params->get('payment_info');
	    /**
	     * CODE from VM1
	     */

        if (!empty($payment_info) ){
// // Here's the place where the Payment Extra Form Code is included
	    // Thanks to Steve for this solution (why make it complicated...?)
	    if( eval('?>' . $payment_info . '<?php ') === false ) {
                 JError::raiseWarning(500, 'Error: The code of the payment method contains a Parse Error!<br />Please correct that first');

	    }
        }

	    // END printing out HTML Form code (Payment Extra Info)




		$this->_virtuemart_paymentmethod_id = $orderData->virtuemart_paymentmethod_id;
		$_dbValues['virtuemart_order_id'] = $virtuemart_order_id;
		$_dbValues['payment_method_id'] = $this->_virtuemart_paymentmethod_id;
		$this->writePaymentData($_dbValues, '#__virtuemart_order_payment_' . $this->_pelement);
		return 'P'; // Set order status to Pending.  TODO Must be a plugin parameter
	}
        /*
        function plgVmOnPaymentResponseReceived( $pelement)  {
           return null;
       }
*/
	/**
	 * Display stored payment data for an order
	 * @see components/com_virtuemart/helpers/vmPaymentPlugin::plgVmOnShowOrderPaymentBE()
	 */
	function plgVmOnShowOrderPaymentBE($_virtuemart_order_id, $_paymethod_id)
	{

		if (!$this->selectedThisPayment($this->_pelement, $_paymethod_id)) {
			return null; // Another method was selected, do nothing
		}
		$_db = JFactory::getDBO();
		$_q = 'SELECT * FROM `#__virtuemart_order_payment_' . $this->_pelement . '` '
			. 'WHERE `virtuemart_order_id` = ' . $_virtuemart_order_id;
		$_db->setQuery($_q);
		if (!($payment = $_db->loadObject())) {
			JError::raiseWarning(500, $_db->getErrorMsg());
			return '';
		}

		$_html = '<table class="adminlist">'."\n";
		$_html .= '	<thead>'."\n";
		$_html .= '		<tr>'."\n";
		$_html .= '			<th>'.JText::_('COM_VIRTUEMART_ORDER_PRINT_PAYMENT_LBL').'</th>'."\n";
//		$_html .= '			<th width="40%">'.JText::_('VM_ORDER_PRINT_ACCOUNT_NAME').'</th>'."\n";
//		$_html .= '			<th width="30%">'.JText::_('VM_ORDER_PRINT_ACCOUNT_NUMBER').'</th>'."\n";
//		$_html .= '			<th width="17%">'.JText::_('VM_ORDER_PRINT_EXPIRE_DATE').'</th>'."\n";
		$_html .= '		</tr>'."\n";
		$_html .= '	</thead>'."\n";
		$_html .= '	<tr>'."\n";
		$_html .= '		<td>'.$this->getThisPaymentName($_paymethod_id).'</td>'."\n";
//		$_html .= '		<td></td>'."\n";
//		$_html .= '		<td></td>'."\n";
//		$_html .= '		<td></td>'."\n";
		$_html .= '	<tr>'."\n";
		$_html .= '</table>'."\n";
		return $_html;
	}

 
}

// No closing tag