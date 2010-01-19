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

class plgPaymentNochex extends vmPaymentPlugin {
	
	var $payment_code = "NOCHEX" ;
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
	function plgPaymentNochex( & $subject, $config ) {
		parent::__construct( $subject, $config ) ;
	}
    
	/**
	 * Shows the HTML Form Code to redirect the customer to Nochex
	 *
	 * @param ps_DB $db
	 * @param stdCls $user
	 * @param ps_DB $dbbt
	 */
	function showPaymentForm( &$db, $user, $dbbt ) {
		global $vendor_image_url;
        ?>
        <form action="https://www.nochex.com/nochex.dll/checkout" method=post target="_blank"> 
                   <input type="hidden" name="email" value="<?php echo $this->params->get('NOCHEX_EMAIL') ?>" />
                   <input type="hidden" name="amount" value="<?php printf("%.2f", $db->f("order_total"))?>" />
               <input type="hidden" name="ordernumber" value="<?php $db->p("order_id") ?>" />
                    <input type="hidden" name="logo" value="<?php echo $vendor_image_url ?>" />
               <input type="hidden" name="returnurl" value="<?php echo SECUREURL ."index.php?option=com_virtuemart&amp;page=checkout.result&amp;order_id=".$db->f("order_id") ?>" />
             <input type="image" name="submit" src="http://www.nochex.com/web/images/paymeanimated.gif" /> 
              </form>
              <?php
    }
   
}
?>