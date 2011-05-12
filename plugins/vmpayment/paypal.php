<?php
if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) )
	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
/**
 * @version $Id: ps_cashondelpay.php,v 1.4 2005/05/27 19:33:57 ei
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

class plgVMPaymentPaypal extends vmPaymentPlugin {
	
	var $_pelement;
	var $_pcode = 'PP_API' ;

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
	function plgVMPaymentPaypal(& $subject, $config) {
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
		$_scheme->create_scheme('#__vm_order_payment_'.$this->_pelement);
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
	/* this add the paiement on the select list choice*/
	public function plgVmOnSelectPayment($cart, $checkedPaymId=0)
	{

		if (!$this->setVmParams($cart->vendorId)) {
			return;
		}

		if ($checkedPaymId==$this->paymentMethod->paym_id) {
			$checked = '"checked"';
		} else {
			$checked = '';
		}

		$html  = '<fieldset>Hi i\'m PAYPAL SELECT TEST';
		$html .= '<input type="radio" name="paym_id" value="'.$this->paymentMethod->paym_id.'" '.$checked.'>'.$this->paymentMethod->paym_name.' ';
		$html .= '</fieldset> ';

		return $html;
	}
	/**
	 * Reimplementation of vmPaymentPlugin::plgVmOnCheckoutCheckPaymentData()
	 *	Here have to give all value for the BANK
	 * @see components/com_virtuemart/helpers/vmPaymentPlugin::plgVmOnConfirmedOrderStorePaymentData()
	 * @author Oscar van Eijk
	 */
	function plgVmOnConfirmedOrderStorePaymentData($_orderNr, $_orderData, $_priceData)
	{
		if (!$this->selectedThisMethod($this->_pelement, $_orderData->paym_id)) {
			return null; // Another method was selected, do nothing
		}
		$_returnValue = 'P'; // TODO Read the status from the parameters

		// Load the required helpers
		if(!class_exists('VmConnector')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'connection.php');
		
		if (!class_exists('VirtueMartModelOrders'))	require( JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'orders.php' );
		
		$_usr =& JFactory::getUser();

		$_usrBT = $_orderData->BT;
		$_usrST = (($_orderData->ST === null) ? $_orderData->BT : $_orderData->ST);

		$database = JFactory::getDBO();

		$_vendorID = 1 ; //$_orderData->virtuemart_vendor_id; TODO
		$_vendorCurrency = VirtueMartModelVendor::getVendorCurrency($_vendorID);

		// Option to send email to merchant from gateway
		if ($this->params->get('AN_EMAIL_MERCHANT') == '0') {
				$vendor_mail = "";
 		}
		if ($this->params->get('AN_EMAIL_CUSTOMER') == '1') {
			$email_customer = 'TRUE';
		} else {
			$email_customer = 'FALSE';
 		}
 		$_testReq = $this->params->get('DEBUG') == 1 ? 'YES' : 'NO';
		dump($_orderData,'info commande');
		dump($_priceData,'info prix');
		
		$post_variables = Array( 
			'cmd' => '_ext-enter' , 
			'redirect_cmd' => '_xclick' , 
			'upload' => '1' , 
			'business' => $this->params->get('PAYPAL_EMAIL') , 
			'receiver_email' => $this->params->get('PAYPAL_EMAIL') , 
			'item_name' => JText::_( 'VM_ORDER_PRINT_PO_NUMBER' ) . ': ' . $_orderNr , 
			'order_number' => VirtueMartModelOrders::getOrderNumber($_orderNr),
			"virtuemart_order_id" => $_orderNr,
			"invoice" => $_orderNr , 
			"amount" => $_priceData['billTotal'] , 
			"shipping" => $_priceData['order_shipping'], 
			"currency_code" => $_vendorCurrency , 
			"address_override" => "1" , 
			"first_name" => $_usrBT[ 'first_name' ] , 
			"last_name" => $_usrBT[ 'last_name' ] , 
			"address1" => $_usrBT[ 'address_1' ] , 
			"address2" => $_usrBT[ 'address_2' ] , 
			"zip" => $_usrBT[ 'zip' ] , 
			"city" => $_usrBT[ 'city' ] , 
			"state" => $_usrBT[ 'state' ] , 
			"country" => ShopFunctions::getCountryByID($_usrST['virtuemart_country_id'],'country_3_code') , 
			"email" => $_usrBT[ 'email' ] , 
			"night_phone_b" => $_usrBT[ 'phone_1' ] ,
			"return" =>  JROUTE::_(JURI::root().'index.php?option=com_virtuemart&view=orders&task=details&virtuemart_order_id=' . $_orderNr ), // TO VERIFY
			"notify_url" => JROUTE::_(JURI::root().'index.php?option=com_virtuemart&view=orders&task=details&virtuemart_order_id=' . $_orderNr ), // TO VERIFY send the bank payment statut
			"cancel_return" => JROUTE::_(JURI::root().'index.php?option=com_virtuemart') , // TO VERIFY
			"undefined_quantity" => "0" , 

			"test_ipn" => $this->params->get('DEBUG') , 
			"pal" => "NRUBJXESJTY24" , 
			"no_shipping" => "1" , 
			"no_note" => "1" ) ;
	//warning HTPPS 		"cpp_header_image" => $vendor_image_url , 

		$_qstring = '';
		foreach($post_variables AS $_k => $_v){
			$_qstring .= (empty($_qstring) ? '' : '&')
					. urlencode($_k) . '=' . urlencode($_v);
		}

		// Prepare data that should be stored in the database
		$_dbValues['virtuemart_order_id'] = $_orderNr;
		$_dbValues['payment_method_id'] = $this->_paym_id;
		// TODO wait for PAYPAL return ???
//		$this->writePaymentData($_dbValues, '#__vm_order_payment_' . $this->_pelement);
		// Send to PAYPAL TODO Sandbox choice ???
		$_host = $this->params->get('SANDBOX') == 1 ? 'www.sandbox.paypal.com' : 'www.paypal.com';
		$_port = ''; // ':443';
		$_uri = 'cgi-bin/webscr?';
			$mainframe = JFactory::getApplication();
//		$mainframe->redirect("https://$_host/test/$_uri".$_qstring);
		$_result = VmConnector::handleCommunication( "https://$_host/$_uri", $_qstring );

		if(!$_result) {
			//JError::raiseError(500, JText::_('The transaction could not be completed.'));
			$_dbValues['order_payment_status'] = -1;
		} else {
			$_response = explode("|", $_result);
			$_response[0] = str_replace( '"', '', $_response[0] ); // Strip quotes

			$_dbValues['order_payment_status'] = $_response[0];

			if ($_response[0] == '1') { // Succeeded
				$_dbValues['order_payment_log'] = JText::_('VM_PAYMENT_TRANSACTION_SUCCESS').': '
					. $_response[3]; // Transaction log
				$_dbValues['order_payment_trans_id'] = $_response[6]; // Transaction ID
			} else { // 2 (Declined) or 3 (Transaction error)
				if ($this->params->get('AN_SHOW_ERROR_CODE') == '1') {
					$_log = $_response[0] . '-'
						. $_response[1] . '-'
						. $_response[2] . '-'
						. $_response[5] . '-'
						. $_response[38] . '-'
						. $_response[39] . '-'
						. $_response[3];
				} else {
					$_log = $_response[3];
				}
				JError::raiseWarning(500, $_log);
				$_dbValues['order_payment_log'] = $_log; // Transaction log
				$_dbValues['order_payment_trans_id'] = $_response[6]; // Transaction ID
				$_returnValue = 'X';
			}
			$_dbValues['order_payment_log'] = $_response[3]; // Transaction log
			$_dbValues['order_payment_trans_id'] = $_response[6]; // Transaction ID
		}

		return 'P'; // Set order status to Pending.  TODO Must be a plugin parameter
	}
	
	/**
	 * Display stored payment data for an order
	 * @see components/com_virtuemart/helpers/vmPaymentPlugin::plgVmOnShowOrderPaymentBE()
	 */
	function plgVmOnShowOrderPaymentBE($_virtuemart_order_id, $_paymethod_id)
	{
		
		if (!$this->selectedThisMethod($this->_pelement, $_paymethod_id)) {
			return null; // Another method was selected, do nothing
		}
		$_db = JFactory::getDBO();
		$_q = 'SELECT * FROM `#__vm_order_payment_' . $this->_pelement . '` '
			. 'WHERE `virtuemart_order_id` = ' . $_virtuemart_order_id;
		$_db->setQuery($_q);
		if (!($payment = $_db->loadObject())) {
			JError::raiseWarning(500, $_db->getErrorMsg());
			return '';
		}
		
		$_html = '<table class="adminlist">'."\n";
		$_html .= '	<thead>'."\n";
		$_html .= '		<tr>'."\n";
		$_html .= '			<th>'.JText::_('VM_ORDER_PRINT_PAYMENT_LBL').'</th>'."\n";
//		$_html .= '			<th width="40%">'.JText::_('VM_ORDER_PRINT_ACCOUNT_NAME').'</th>'."\n";
//		$_html .= '			<th width="30%">'.JText::_('VM_ORDER_PRINT_ACCOUNT_NUMBER').'</th>'."\n";
//		$_html .= '			<th width="17%">'.JText::_('VM_ORDER_PRINT_EXPIRE_DATE').'</th>'."\n";
		$_html .= '		</tr>'."\n";
		$_html .= '	</thead>'."\n";
		$_html .= '	<tr>'."\n";
		$_html .= '		<td>'.$this->getThisMethodName($_paymethod_id).'</td>'."\n";
//		$_html .= '		<td></td>'."\n";
//		$_html .= '		<td></td>'."\n";
//		$_html .= '		<td></td>'."\n";
		$_html .= '	<tr>'."\n";
		$_html .= '</table>'."\n";
		return $_html;
	}

/*	function get_payment_rate( $sum ) {
		
		if( $sum < 5000 )
			return - ($this->params->get( 'CASH_ON_DEL_5000' )) ;
		elseif( $sum < 10000 )
			return - ($this->params->get( 'CASH_ON_DEL_10000' )) ;
		elseif( $sum < 20000 )
			return - ($this->params->get( 'CASH_ON_DEL_20000' )) ;
		elseif( $sum < 30000 )
			return - ($this->params->get( 'CASH_ON_DEL_30000' )) ;
		elseif( $sum < 40000 )
			return - ($this->params->get( 'CASH_ON_DEL_40000' )) ;
		elseif( $sum < 50000 )
			return - ($this->params->get( 'CASH_ON_DEL_50000' )) ;
		elseif( $sum < 100000 )
			return - ($this->params->get( 'CASH_ON_DEL_100000' )) ;
		else
			return - ($this->params->get( 'CASH_ON_DEL_100000' )) ;
		
	//	return -($sum * 0.10);
	}
*/
}

// No closing tag