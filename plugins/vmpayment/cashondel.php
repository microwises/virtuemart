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

jimport('joomla.plugin.plugin');

class plgVmPaymentCashondel extends vmPaymentPlugin {
	
	var $_pelement = 'cashondel';
	var $payment_code = 'PU' ;

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
	function plgVmPaymentCashondel(& $subject, $config) {
		parent::__construct($subject, $config);
	}

	function plgVmOnConfirmedOrderStorePaymentData($_orderNr, $_orderData, $_priceData, &$_returnValues)
	{
		return null;
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