<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
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


class plgPaymentPaymate extends vmPaymentPlugin {
	
	var $payment_code = "PAYMATE";
	
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 * @since 1.2.0
	 */
	function plgPaymentPaymate( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}
	/**
	 * Shows the HTML Form Code to redirect the customer to PayPal
	 *
	 * @param ps_DB $db
	 * @param stdCls $user
	 * @param ps_DB $dbbt
	 */
	function showPaymentForm( &$db, $user, $dbbt ) {
	?>
		<script type="text/javascript">
	  function openExpress(){
	        var url = 'https://www.paymate.com.au/PayMate/ExpressPayment?mid=<?php echo $this->params->get('PAYMATE_USERNAME').
	          "&amt=".$db->f("order_total").
	        "&currency=".$_SESSION['vendor_currency'].
	          "&ref=".$db->f("order_id").
	   "&pmt_sender_email=".$user->email.
	    "&pmt_contact_firstname=".$user->first_name.
	          "&pmt_contact_surname=".$user->last_name.
	     "&regindi_address1=".$user->address_1.
	        "&regindi_address2=".$user->address_2.
	        "&regindi_sub=".$user->city.
	          "&regindi_pcode=".$user->zip;?>'
	   var newWin = window.open(url, 'wizard', 'height=640,width=500,scrollbars=0,toolbar=no');
	  self.name = 'parent';
	       newWin.focus();
	  }
	  </script>
	  <div align="center">
	  <p>
	  <a href="javascript:openExpress();">
	  <img src="https://www.paymate.com.au/homepage/images/butt_PayNow.gif" border="0" alt="Pay with Paymate Express">
	  <br />Click here to pay your account</a>
	  </p>
	  </div>
	  <?php
	}
}
?>