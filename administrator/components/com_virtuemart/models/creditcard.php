<?php
/**
*
* Data module for shop credit cards
*
* @package	VirtueMart
* @subpackage CreditCard
* @author RickG
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the model framework
jimport( 'joomla.application.component.model');

if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model class for shop credit cards
 *
 * @package	VirtueMart
 * @subpackage CreditCard
 * @author RickG
 */
class VirtueMartModelCreditcard extends VmModel {


	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('creditcards');
	}



    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author RickG
     */
	function getCreditCard($id=0)
	{
		if(!empty($id)) self::setId($id);
		$db = JFactory::getDBO();

  		if (empty($this->_data)) {
   			$this->_data = $this->getTable('creditcards');
   			$this->_data->load((int)$this->_id);
  		}

  		if(empty($this->_data->virtuemart_vendor_id)){
  			if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
  			$this->_data->virtuemart_vendor_id = VirtueMartModelVendor::getLoggedVendor();
  		}


  		return $this->_data;
	}


//	/**
//	 * Bind the post data to the credit card table and save it
//     *
//     * @author RickG
//     * @return boolean True is the save was successful, false otherwise.
//	 */
//    function store()
//	{
//		$table = $this->getTable('creditcards');
//
//		$data = JRequest::get( 'post' );
//		// Bind the form fields to the credit card table
//		if (!$table->bind($data)) {
//			$this->setError($table->getError());
//			return false;
//		}
//
//		// Make sure the credit card record is valid
//		if (!$table->check()) {
//			$this->setError($table->getError());
//			return false;
//		}
//
//		// Save the credit card record to the database
//		if (!$table->store()) {
//			$this->setError($table->getError());
//			return false;
//		}
//
//		return $table->virtuemart_creditcard_id;
//	}


	/**
	 * Retireve a list of credit cards from the database.
	 *
     * @author RickG, Max Milbers
	 * @return object List of credit card objects
	 */
	function getCreditCards($published=1)
	{
		$query = 'SELECT * FROM `#__virtuemart_creditcards` ';
		if($published) $query .= 'WHERE `published`= "'.$published.'" ';
		$query .= 'ORDER BY `#__virtuemart_creditcards`.`virtuemart_creditcard_id`';
		$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		// set total for pagination
		$this->_total = $this->_getListCount($query);
		return $this->_data;
	}

			/**
	 * Validates the Payment Method (Credit Card Number)
	 * Adapted From CreditCard Class
	 * Copyright (C) 2002 Daniel Frï¿½z Costa
	 *
	 * Documentation:
	 *
	 * Card Type                   Prefix           Length     Check digit
	 * - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	 * MasterCard                  51-55            16         mod 10
	 * Visa                        4                13, 16     mod 10
	 * AMEX                        34, 37           15         mod 10
	 * Dinners Club/Carte Blanche  300-305, 36, 38  14         mod 10
	 * Discover                    6011             16         mod 10
	 * enRoute                     2014, 2149       15         any
	 * JCB                         3                16         mod 10
	 * JCB                         2131, 1800       15         mod 10
	 *
	 * More references:
	 * http://www.beachnet.com/~hstiles/cardtype.hthml.
	  *
	  * @param string $creditcard_code
	  * @param string $cardnum
	  * @return boolean
	 */
	function validate_creditcard_data($creditcard_code, $cardnum) {

		$this->number = self::_strtonum($cardnum);
		/*
		if(!$this->detectType($this->number))
		{
		$this->errno = CC_ETYPE;
		$d['error'] = $this->errno;
		return false;
		}*/

		if(empty($this->number) || !self::mod10($this->number)){
			JError::raiseWarning('', JText::_('COM_VIRTUEMART_CC_ENUMBER'));
//			$this->errno = CC_ENUMBER;
//			$d['error'] = $this->errno;
			return false;
		}

		return true;
	}

	/*
	* _strtonum private method
	*   return formated string - only digits
	*/
	function _strtonum($string) {
		$nstr = "";
		for($i=0; $i< strlen($string); $i++) {
			if(!is_numeric($string{$i}))
			continue;
			$nstr = "$nstr".$string{$i};
		}
		return $nstr;
	}

		/*
	* mod10 method - Luhn check digit algorithm
	*   return 0 if true and !0 if false
	*/
	function mod10( $card_number ){

		$digit_array = array ();
		$cnt = 0;

		//Reverse the card number
		$card_temp = strrev ( $card_number );

		//Multiple every other number by 2 then ( even placement )
		//Add the digits and place in an array
		for ( $i = 1; $i <= strlen ( $card_temp ) - 1; $i = $i + 2 ) {
			//multiply every other digit by 2
			$t = substr ( $card_temp, $i, 1 );
			$t = $t * 2;
			//if there are more than one digit in the
			//result of multipling by two ex: 7 * 2 = 14
			//then add the two digits together ex: 1 + 4 = 5
			if ( strlen ( $t ) > 1 ) {
				//add the digits together
				$tmp = 0;
				//loop through the digits that resulted of
				//the multiplication by two above and add them
				//together
				for ( $s = 0; $s < strlen ( $t ); $s++ ) {
					$tmp = substr ( $t, $s, 1 ) + $tmp;
				}
			}
			else{  // result of (* 2) is only one digit long
				$tmp = $t;
			}
			//place the result in an array for later
			//adding to the odd digits in the credit card number
			$digit_array [ $cnt++ ] = $tmp;
		}
		$tmp = 0;

		//Add the numbers not doubled earlier ( odd placement )
		for ( $i = 0; $i <= strlen ( $card_temp ); $i = $i + 2 ) {
			$tmp = substr ( $card_temp, $i, 1 ) + $tmp;
		}

		//Add the earlier doubled and digit-added numbers to the result
		$result = $tmp + array_sum ( $digit_array );

		//Check to make sure that the remainder
		//of dividing by 10 is 0 by using the modulas
		//operator
		return ( $result % 10 == 0 );

	}

}
// pure php no closing tag