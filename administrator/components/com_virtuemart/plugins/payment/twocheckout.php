<?php
if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) )
	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
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

/**
* The twocheckout class for transactions with 2Checkout 
 */
class plgPaymentTwoCheckout extends vmPaymentPlugin {
	
	var $payment_code = "TWOCO";
	
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
	function plgPaymentTwoCheckout( & $subject, $config ) {
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
	
    	$q  = "SELECT * FROM #__users WHERE user_info_id='".$db->f("user_info_id")."'"; 
    	$dbbt = new ps_DB;
    	$dbbt->setQuery($q);
  		$dbbt->query();
       	$dbbt->next_record(); 
        // Get ship_to information
    	if( $db->f("user_info_id") != $dbbt->f("user_info_id")) {
       		$q2  = "SELECT * FROM #__vm_user_info WHERE user_info_id='".$db->f("user_info_id")."'"; 
    		$dbst = new ps_DB;
    		$dbst->setQuery($q2);
         	$dbst->query();
       		$dbst->next_record();
       	}
     	else  {
        	$dbst = $dbbt;
      	}
                     
      	// vars to send
        $formdata = array (
    		'x_login' => $this->params->get('TWOCO_LOGIN'),
   			'x_email_merchant' => (($this->params->get('TWOCO_MERCHANT_EMAIL')) ? 'TRUE' : 'FALSE'),
                  
      		// Customer Name and Billing Address
  			'x_first_name' => $dbbt->f("first_name"),
   			'x_last_name' => $dbbt->f("last_name"),
     		'x_company' => $dbbt->f("company"),
         	'x_address' => $dbbt->f("address_1"),
       		'x_city' => $dbbt->f("city"),
       		'x_state' => $dbbt->f("state"),
     		'x_zip' => $dbbt->f("zip"),
         	'x_country' => $dbbt->f("country"),
         	'x_phone' => $dbbt->f("phone_1"),
   			'x_fax' => $dbbt->f("fax"),
         	'x_email' => $dbbt->f("email"),
    
	       // Customer Shipping Address
  			'x_ship_to_first_name' => $dbst->f("first_name"),
   			'x_ship_to_last_name' => $dbst->f("last_name"),
     		'x_ship_to_company' => $dbst->f("company"),
         	'x_ship_to_address' => $dbst->f("address_1"),
       		'x_ship_to_city' => $dbst->f("city"),
       		'x_ship_to_state' => $dbst->f("state"),
     		'x_ship_to_zip' => $dbst->f("zip"),
         	'x_ship_to_country' => $dbst->f("country"),
        
       		'x_invoice_num' => $db->f("order_number"),
  			'x_receipt_link_url' => SECUREURL."2checkout_notify.php"
    );
    
     if( $this->params->get('DEBUG')) {
     	$formdata['demo'] = "Y";
     }
  
       $version = "2";
       $url = "https://www2.2checkout.com/2co/buyer/purchase";
       $formdata['x_amount'] = number_format($db->f("order_total"), 2, '.', '');
     
       //build the post string
       $poststring = '';
   		foreach($formdata AS $key => $val){
     		$poststring .= "<input type='hidden' name='$key' value='$val' />
     	";
     	}
    
      ?>
	<form action="<?php echo $url ?>" method="post" target="_blank">
	      <?php echo $poststring ?>
	     <p>Click on the Image below to pay...</p>
	<input type="image" name="submit"
		src="https://www.2checkout.com/images/buy_logo.gif" border="0"
		alt="Make payments with 2Checkout, it's fast and secure!"
		title="Pay your Order with 2Checkout, it's fast and secure!" />
		</form>
	<?php
	}
}
?>