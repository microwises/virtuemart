<?php
if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) )
	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
/**
 * @version $Id: standard.php,v 1.4 2005/05/27 19:33:57 ei
 *
 * a special type of 'cash on delivey':
 * its fee depend on total sum
 * @author Max Milbers
 * @version $Id: standard.php 3681 2011-07-08 12:27:36Z alatak $
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

class plgVmCustomTextinput extends vmCustomPlugin {

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
	function plgVmCustomTextinput(& $subject, $config) {
		$this->_pelement = basename(__FILE__, '.php');
		$this->_createTable();
		parent::__construct($subject, $config);
	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 * @author Patrick Kohl
	 */
	protected function _createTable()
	{
		$scheme = DbScheme::get_instance();
		$scheme->create_scheme('#__virtuemart_product_custom_'.$this->_pelement);
		$schemeCols = array(
			 'id' => array (
					 'type' => 'int'
					,'length' => 11
					,'auto_inc' => true
					,'null' => false
			)
			,'virtuemart_product_id' => array (
					 'type' => 'int'
					,'length' => 11
					,'null' => false
			)
			,'virtuemart_custom_id' => array (
					 'type' => 'text'
					,'null' => false
			)
			,'textinput' => array (
					 'type' => 'text'
					,'null' => false
			)
		);
		$schemeIdx = array(
			 'idx_order_custom' => array(
					 'columns' => array ('virtuemart_product_id')
					,'primary' => false
					,'unique' => false
					,'type' => null
			)
		);
		$scheme->define_scheme($schemeCols);
		$scheme->define_index($schemeIdx);
		if (!$scheme->scheme(true)) {
			JError::raiseWarning(500, $scheme->get_db_error());
		}
		$scheme->reset();
	}


	
	
	// get product param for this plugin on edit
	function plgVmOnProductEdit($value,$row, $product_id) {
	
	//print_r($value);
	$html='<input type="text" value="'.$value.'" name="field['.$row.'][custom_value]" />';

	//jExit();
	
		return $html  ;
	}
	/**
	 * @ idx to increment and return to next plugin
	 *	 TODO Get from table registred product
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnDisplayProductFE()
	 * @author Patrick Kohl
	 */
	function plgVmOnDisplayProductFE($field,$product,$idx) {
		// set value for price and custom array
		$html ='<input type="hidden" value="'.$this->_pelement.'" size="10" name="customPrice['.$idx.']['.$field->virtuemart_custom_id.']">';
		// Here the plugin values
		$html.='<input type="text" value="'.$this->_pelement.'" size="10" name="customPlugin['.$idx.']['.$field->virtuemart_custom_id.']">';
		$html.='<input type="text" value="" size="10" name="customPlugin['.$idx.'][Morecomment]">';
        return $html;
    }

	/**
	 * TODO Add all param to session
	 * *** Can only set in table at order then put it in session ***
	 * *** Have to add it in VIrtuemart cart ? ***
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnSaveProductFE()
	 * @author Patrick Kohl
	 */
	function plgVmOnViewCartFE($virtuemart_product_id) {
		
		if (!empty($textInputs)) {
        $session = JFactory::getSession();
		$sessionCustom = $session->get('vmcustom', 0, 'vm');
			if (!empty($sessionCustom)) {
				$custom = $sessionCustom ;
				if (!empty ($custom[$this->_pelement]) ) { $html = '';
					foreach ($custom[$this->_pelement] as $text) 
						 $html = '<div>'.$text.'<div>';
				}
			}
		}
    }
	
	function plgVmOnOrder($product) {

		$dbValues['virtuemart_product_id'] = $product->virtuemart_product_id;
		$dbValues['textinput'] = $this->_virtuemart_paymentmethod_id;
		$this->writeCustomData($dbValues, '#__virtuemart_product_custom_' . $this->_pelement);
	}
	/**
	 *
	 * Admin order display
	 */
	function plgVmOnOrderShowBE($virtuemart_product_id) {

		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `#__virtuemart_product_custom_' . $this->_pelement . '` '
			. 'WHERE `virtuemart_product_id` = ' . $virtuemart_product_id;
		$db->setQuery($q);
		if (!($customs = $db->loadObjectList())) {
			JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		$html = '';
		foreach ($customs as $custom) {
			$html .= '<div>'.$custom.'</div>';
		}
		return $html ;
	}
	
	/**
	 *
	 * User order display
	 */
	function plgVmOnOrderShowFE($product,$order_item_id) {
		//$dbValues['virtuemart_product_id'] = $product->virtuemart_product_id;
		//$dbValues['textinput'] = $this->_virtuemart_paymentmethod_id;
		//$this->writePaymentData($dbValues, '#__virtuemart_product_custom_' . $this->_pelement);
				$db = JFactory::getDBO();
		$q = 'SELECT * FROM `#__virtuemart_product_custom_' . $this->_pelement . '` '
			. 'WHERE `virtuemart_product_id` = ' . $virtuemart_product_id;
		$db->setQuery($q);
		if (!($customs = $db->loadObjectList())) {
			JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		$html = '';
		foreach ($customs as $custom) {
			$html .= '<div>'.$custom.'</div>';
		}
		return $html ;
	}
	
/*****************************Old Code*****************************************/
    function plgVmAfterCheckout($virtuemart_order_id, $orderData) {

		if (!$this->selectedThisPayment($this->_pelement, $orderData->virtuemart_paymentmethod_id)) {
			return null; // Another method was selected, do nothing
		}

            $paramstring = $this->getVmCustomParams($vendorId = 0, $orderData->virtuemart_paymentmethod_id);
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
		$dbValues['virtuemart_order_id'] = $virtuemart_order_id;
		$dbValues['payment_method_id'] = $this->_virtuemart_paymentmethod_id;
		$this->writePaymentData($dbValues, '#__virtuemart_order_payment_' . $this->_pelement);
		return 'P'; // Set order status to Pending.  TODO Must be a plugin parameter
	}
        /*
        function plgVmOnPaymentResponseReceived( $pelement)  {
           return null;
       }
*/
	/**
	 * Display stored payment data for an order
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnShowOrderPaymentBE()
	 */
	function plgVmOnShowOrderPaymentBE($virtuemart_order_id, $paymethod_id)
	{

		if (!$this->selectedThisPayment($this->_pelement, $paymethod_id)) {
			return null; // Another method was selected, do nothing
		}
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `#__virtuemart_order_payment_' . $this->_pelement . '` '
			. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
		$db->setQuery($q);
		if (!($payment = $db->loadObject())) {
			JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}

		$html = '<table class="adminlist">'."\n";
		$html .= '	<thead>'."\n";
		$html .= '		<tr>'."\n";
		$html .= '			<th>'.JText::_('COM_VIRTUEMART_ORDER_PRINT_PAYMENT_LBL').'</th>'."\n";
//		$html .= '			<th width="40%">'.JText::_('VM_ORDER_PRINT_ACCOUNT_NAME').'</th>'."\n";
//		$html .= '			<th width="30%">'.JText::_('VM_ORDER_PRINT_ACCOUNT_NUMBER').'</th>'."\n";
//		$html .= '			<th width="17%">'.JText::_('VM_ORDER_PRINT_EXPIRE_DATE').'</th>'."\n";
		$html .= '		</tr>'."\n";
		$html .= '	</thead>'."\n";
		$html .= '	<tr>'."\n";
		$html .= '		<td>'.$this->getThisPaymentName($paymethod_id).'</td>'."\n";
//		$html .= '		<td></td>'."\n";
//		$html .= '		<td></td>'."\n";
//		$html .= '		<td></td>'."\n";
		$html .= '	<tr>'."\n";
		$html .= '</table>'."\n";
		return $html;
	}

 
}

// No closing tag