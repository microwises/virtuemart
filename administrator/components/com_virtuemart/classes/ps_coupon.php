<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage classes
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
 * Class Code for coupon codes
 * The author would like to thank Digitally Imported (www.di.fm) for good music to code to
 *
 *
 * CHANGELOG:
 *
 * v 1.0: Initial Release (28-NOV-2004) - Erich
*/

class ps_coupon {

    function validate_add( &$d ) {
        global  $vmLogger;
        /* init the database */
        $coupon_db =& new ps_DB;
        $valid = true;

        /* make sure the coupon_code does not exist */
        $q = "SELECT coupon_code FROM #__{vm}_coupons WHERE coupon_code = '".$coupon_db->getEscaped($d['coupon_code'])."' ";
        $coupon_db->query($q);
        if ($coupon_db->next_record()) {
            $vmLogger->err( JText::_('VM_COUPON_CODE_EXISTS',false) );
            $valid = false;
        }
        if( empty( $d['coupon_value'] ) || empty( $d['coupon_code'] )) {
            $vmLogger->warning( JText::_('VM_COUPON_COMPLETE_ALL_FIELDS',false) );
            $valid = false;
        }
        if( !is_numeric( $d['coupon_value'] )) {
            $vmLogger->err( JText::_('VM_COUPON_VALUE_NOT_NUMBER',false) );
            $valid = false;
        }
		if( !is_numeric( $d['coupon_value_valid'] )) {
            $vmLogger->err( JText::_('VM_COUPON_VALUE_VALID_AT_NOT_NUMBER',false) );
            $valid = false;
        }
		if (!$d["coupon_start_date"]) {
			$vmLogger->err( JText::_('VM_COUPON_START_INVALID',false) );
			$valid = false;
		}

		if (!$d["coupon_expiry_date"]) {
			$vmLogger->err( JText::_('VM_COUPON_EXPIRY_INVALID',false) );
			$valid = false;
		}
        return $valid;

    }
    function validate_update( &$d ) {
        global  $vmLogger;
        /* init the database */
        $coupon_db =& new ps_DB;
        $valid = true;

        /* make sure the coupon_code does not exist */
        $q = "SELECT coupon_code FROM #__{vm}_coupons WHERE coupon_code = '".$coupon_db->getEscaped($d['coupon_code'])."' AND coupon_id <> '".$d['coupon_id']."'";
        $coupon_db->query($q);
        if ($coupon_db->next_record()) {
            $vmLogger->err( JText::_('VM_COUPON_CODE_EXISTS',false) );
            $valid = false;
        }
        if( empty( $d['coupon_value'] ) || empty( $d['coupon_code'] )) {
            $vmLogger->err( JText::_('VM_COUPON_COMPLETE_ALL_FIELDS',false) );
            $valid = false;
        }
        if( !is_numeric( $d['coupon_value'] )) {
            $vmLogger->err( JText::_('VM_COUPON_VALUE_NOT_NUMBER',false) );
            $valid = false;
        }
		if( !is_numeric( $d['coupon_value_valid'] )) {
            $vmLogger->err( JText::_('VM_COUPON_VALUE_VALID_AT_NOT_NUMBER',false) );
            $valid = false;
        }
        if (!$d["coupon_start_date"]) {
			$vmLogger->err( JText::_('VM_COUPON_START_INVALID',false) );
			$valid = false;
		}

		if (!$d["coupon_expiry_date"]) {
			$vmLogger->err( JText::_('VM_COUPON_EXPIRY_INVALID',false) );
			$valid = false;
		}
        return $valid;

    }
    /* function to add a coupon coupon_code to the database */
    function add_coupon_code( &$d ) {
     	global $vmLogger;
        $coupon_db =& new ps_DB;

        if( !$this->validate_add( $d ) ) {
            return false;
        }
        $fields = array(
					        'coupon_code' => vmGet($d,'coupon_code'),
					        'percent_or_total' => strtolower($d['percent_or_total']) == 'percent' ? 'percent' : 'total',
					        'coupon_type' => strtolower($d['coupon_type']) == 'gift' ? 'gift' : 'permanent',
					        'coupon_value' => (float)$d['coupon_value'],
					        'coupon_value_valid' => (float)$d['coupon_value_valid'],
					        'coupon_start_date' => vmGet($d,'coupon_start_date'),
					        'coupon_expiry_date' => vmGet($d,'coupon_expiry_date')
				        );
        $coupon_db->buildQuery( 'INSERT', '#__{vm}_coupons', $fields );
        if( $coupon_db->query() ) {
	        $_REQUEST['coupon_id'] = $coupon_db->last_insert_id();
	        $vmLogger->info(JText::_('VM_COUPON_ADDED'));
	        return true;
        }
        return false;

    }


    /* $$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$ */

    /* function to update a coupon */
    function update_coupon( &$d ) {

      	global $vmLogger;
        if( !$this->validate_update( $d ) ) {
            return false;
        }
        /* init the database */
        $coupon_db = new ps_DB;

        $fields = array(
					        'coupon_code' => vmGet($d,'coupon_code'),
					        'percent_or_total' => strtolower($d['percent_or_total']) == 'percent' ? 'percent' : 'total',
					        'coupon_type' => strtolower($d['coupon_type']) == 'gift' ? 'gift' : 'permanent',
					        'coupon_value' => (float)$d['coupon_value'],
					        'coupon_value_valid' => (float)$d['coupon_value_valid'],
					        'coupon_start_date' => vmGet($d,'coupon_start_date'),
					        'coupon_expiry_date' => vmGet($d,'coupon_expiry_date')
				        );
        $coupon_db->buildQuery( 'UPDATE', '#__{vm}_coupons', $fields, 'WHERE coupon_id = '.(int)$d['coupon_id'] );
        if( $coupon_db->query() ) {
	        $_REQUEST['coupon_id'] = $coupon_db->last_insert_id();
	        $vmLogger->info(JText::_('VM_COUPON_UPDATED'));
	        return true;
        }
        return false;
    }


    /* $$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$ */

    /* function to remove coupon coupon_code from the database */
    function remove_coupon_code( &$d ) {

        /* remove the coupon coupon_code */
        /* init the database */
        $coupon_db = new ps_DB;
		if( is_array($d['coupon_id'] )) {
			foreach( $d['coupon_id'] as $coupon ) {
				$q = 'DELETE FROM #__{vm}_coupons WHERE coupon_id = '.(int)$coupon;
				$coupon_db->query($q);
			}
		}
		else {
			$q = 'DELETE FROM #__{vm}_coupons WHERE coupon_id = '.(int)$d['coupon_id'];
			$coupon_db->query($q);
		}
        $_SESSION['coupon_discount'] =    0;
        $_SESSION['coupon_redeemed']   = false;

        return true;
    }


    /* function to process a coupon_code entered by a user */
    function process_coupon_code( $d ) {
        global  $vmLogger;
        /* init the database */
        $coupon_db =& new ps_DB;

        /* we need some functions from the checkout module */
        require_once( CLASSPATH . "ps_checkout.php" );
        $checkout =& new ps_checkout();
        if( empty( $d['total'])) {
        	$totals = $checkout->calc_order_totals($d);
        	$d['total'] = 	$totals['order_subtotal']
							+ $totals['order_tax']
							+ $totals['order_shipping']
							+ $totals['order_shipping_tax']
							- $totals['payment_discount'];
        }
        $d['coupon_code'] = trim(JRequest::getVar(  'coupon_code' ));
        $coupon_id = vmGet( $_SESSION, 'coupon_id', null );

        $q = 'SELECT coupon_id, coupon_code, percent_or_total, coupon_value, coupon_value_valid, coupon_type, coupon_start_date, coupon_expiry_date FROM #__{vm}_coupons WHERE ';
        //q = 'SELECT coupon_id, coupon_code, percent_or_total, coupon_value, coupon_type, coupon_start_date, coupon_expiry_date FROM #__{vm}_coupons WHERE ';
        if( $coupon_id ) {
            /* the query to select the coupon coupon_code */
            $q .= 'coupon_id = '.intval($coupon_id);
        }
        else {
            /* the query to select the coupon coupon_code */
            $q .= 'coupon_code = \''.$coupon_db->getEscaped( $d['coupon_code'] ).'\'';
        }
        /* make the query */
        $coupon_db->query($q);

        /* see if we have any fields returned */
        if ($coupon_db->num_rows() < 1)
        {

        	/* no record, so coupon_code entered was not valid */
            $GLOBALS['coupon_error'] = JText::_('VM_COUPON_CODE_INVALID');
            return false;

        }else{

        	/* we have a record */
            if ($coupon_db->f("coupon_value_valid") <= $d['total']){

	        	/* AG check coupon start and expiry dates */
	            $todays_date = date("Y-m-d");
	            $today = strtotime($todays_date);
	            /* Assume validity */
	            $valid = "yes";

	            /* For a valid coupon, start_date < today and expiry_date > today */

	            $start_date = strtotime ($coupon_db->f("coupon_start_date"));
	            $expiration_date = strtotime($coupon_db->f("coupon_expiry_date"));

	            /*only run coupon processing if coupon valid */
	            if ($start_date <= $today and $expiration_date >= $today){
	            	$valid = "yes";
	            } else { $valid = "no"; }

	             /* Ony run coupon processing if coupon is vallid */
	            if ($valid =="yes") {

	            	/* end AG check coupon start and expiry dates */

	            	/* see if we are calculating percent or dollar discount */
	            	if ($coupon_db->f("percent_or_total") == "percent")
	            	{
	            	    /* percent */
	            	    //$subtotal = $checkout->calc_order_subtotal( $d );

	            	    /* take the subtotal for calculation of the discount */
	            	    //$_SESSION['coupon_discount'] = round( ($subtotal * $coupon_db->f("coupon_value") / 100), 2);
	            	     $coupon_value = round( ($d["total"] * $coupon_db->f("coupon_value") / 100), 2);

	            	    if( $d["total"] < $coupon_value ) {
	            	      	$coupon_value = (float)$d['total'];
	            	      	$vmLogger->info( str_replace('{value}',$GLOBALS['CURRENCY_DISPLAY']->getFullValue( $coupon_value ),JText::_('VM_COUPON_GREATER_TOTAL_SETTO')) );
	            		}

	                 	$_SESSION['coupon_discount'] = $coupon_value;
	            	}
	            	else
	            	{

	            		$coupon_value = $coupon_db->f("coupon_value");

	            	    /* Total Amount */
	            	    if( $d["total"] < $coupon_value ) {
	            	      	$coupon_value = (float)$d['total'];
	            	      	$vmLogger->info( str_replace('{value}',$GLOBALS['CURRENCY_DISPLAY']->getFullValue( $coupon_value ),JText::_('VM_COUPON_GREATER_TOTAL_SETTO')) );
	            	    }
	            	    $_SESSION['coupon_discount'] = $GLOBALS['CURRENCY']->convert( $coupon_value );

	            	}

	            	/* mark this order as having used a coupon so people cant go and use coupons over and over */
	            	$_SESSION['coupon_redeemed'] = true;
	            	$_SESSION['coupon_id'] = $coupon_db->f("coupon_id");
	            	$_SESSION['coupon_code'] = $coupon_db->f("coupon_code");
	            	$_SESSION['coupon_type'] = $coupon_db->f("coupon_type");
		        }

		        else {
		            /*Coupon not valid */
		            $GLOBALS['coupon_error'] = JText::_('VM_COUPON_CODE_INVALID');
		            /*echo "STARTDATE:".$start_date."<br/>";
		            echo "ENDDATE:".$expiration_date."<br/>";
					echo "TODAYDATE:".$today."<br/>";*/
		            return false;
	            }
			}else{

			    $GLOBALS['coupon_error'] = "Coupon only valid if you spend ".$coupon_db->f("coupon_value_valid");
			    return false;

			}

	    }//end elseif numrows<1

	}	//end function
}
?>
