<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: shippingMethod.class.php 1755 2009-05-01 22:45:17Z rolandd $
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

class vmShippingMethod {

	/**
	 * Saves the configuration of a Shipping Module
	 *
	 * @param array $d
	 * @return boolean
	 */
	function save(&$d) {
		global  $vmLogger;
		
		$db = new ps_DB;
		$shipping_class = basename(vmGet($d,'shipping_class'));
		
		if( file_exists(ADMINPATH . "plugins/shipping/$shipping_class.php" )) {
			include( ADMINPATH . "plugins/shipping/$shipping_class.php" );
			$_SHIPPING = new $shipping_class();
			
			if( $_SHIPPING->configfile_writeable() ) {
				$_SHIPPING->write_configuration( $d );
				$vmLogger->info( JText::_('VM_CONFIGURATION_CHANGE_SUCCESS',false) );
				return True;
			}
			else {
		    	$vmLogger->err( sprintf(JText::_('VM_CONFIGURATION_CHANGE_FAILURE',false) , CLASSPATH."payment/".$_SHIPPING->classname.".cfg.php" ) );
		    	return false;
		    }
			
		}
		else {
			$vmLogger->err( 'The shipping class file '.ADMINPATH . "plugins/shipping/$shipping_class.php could not be found." );
			return false;
		}
	}

	/**************************************************************************
	** name: method_list()
	** created by: soeren
	** description:
	** parameters:
	** returns:
	***************************************************************************/
	/**
	 *  list all available shipping methods.
	 *
	 * @param int $shipping_method_id
	 * @return mixed
	 */
	function method_list( $shipping_method_id="" ) {

		$db = new ps_DB;

		$rows = Array();
		$files = vmReadDirectory( ADMINPATH . 'plugins/shipping/', '.xml$', false, true );
		if( $files ) {
			require_once( CLASSPATH. 'installer.class.php' );
			foreach( $files as $file ) {
				$rows[] = vmInstaller::getInfo($file);				
			}
		}
		return $rows;

	}
	/**
	 * Retrieve the weight of a product (specified by $pid). If necessary, the weight is converted to the correct unit of measure.
	 *
	 * @param int $pid
	 * @return double
	 */
	function get_weight( $pid ) {
		global $vendor_country_2_code;
		if( empty($GLOBALS['product_info'][$pid]['weight'] )) {
			if( $vendor_country_2_code=="US"
			|| $vendor_country_2_code=="PR"
			|| $vendor_country_2_code=="DO"
			) {
				defined( 'WEIGHT_UOM' ) or define('WEIGHT_UOM', "LB" );
				$GLOBALS['product_info'][$pid]['weight'] = vmShippingMethod::get_weight_LB( $pid );
				return $GLOBALS['product_info'][$pid]['weight'];
			}

			else {
				defined( 'WEIGHT_UOM' ) or define('WEIGHT_UOM', "KG" );
				$GLOBALS['product_info'][$pid]['weight'] = vmShippingMethod::get_weight_KG( $pid );
				return $GLOBALS['product_info'][$pid]['weight'];
			}
		}
		else {
			return $GLOBALS['product_info'][$pid]['weight'];
		}
	}

	/**
	 * Calculate product weight in ounces
	 * @author Matt Oberpriller
	 * @param int $pid
	 * @return double
	 */
	function get_weight_OZ($pid) {
		global $vars, $vmLogger;
		$db = new ps_DB;

		$q  = "SELECT product_sku,product_parent_id,product_weight,product_weight_uom FROM #__{vm}_product ";
		$q .= "WHERE product_id='$pid' ";

		$db->query($q);
		$db->next_record();

		if ($db->f("product_weight") == 0 && $db->f("product_parent_id")) {
			$q = "SELECT * from #__{vm}_product WHERE product_id='".$db->f("product_parent_id")."'";
			$db->query($q);
			$db->next_record();
		}

		if ($db->f("product_weight") > 0) {
			if (eregi("LB",$db->f("product_weight_uom")) || eregi("PO",$db->f("product_weight_uom"))) {
				$weight = $db->f("product_weight") * 16;
			}
			elseif (eregi("KG",$db->f("product_weight_uom")) || eregi("KILO",$db->f("product_weight_uom"))) {
				$weight = $db->f("product_weight") * 35.27396194958041;
			}
			elseif (eregi("G",$db->f("product_weight_uom"))) {
				$weight = $db->f("product_weight") * 0.035273961949580414;
			}
			elseif (eregi("OZ",$db->f("product_weight_uom")) || eregi("OU",$db->f("product_weight_uom"))) {
				$weight = $db->f("product_weight");
			}
			else {
				$vmLogger->debug( "Unknown weight UOM in product " . $db->f("product_sku") );
			}
		} 
		else {
			$vmLogger->debug( "Weight not specified for product " . $db->f("product_sku"));
		}

		if (!empty($weight)) {
			return($weight);
		}
		else {
			return 0;
		}
	}

	/**
	 * Calculate product weight in Kilograms
	 * @author Matt Oberpriller
	 * @author soeren
	 * @param int $pid
	 * @return double
	 */
	function get_weight_KG( $pid ) {
		global $vars, $vmLogger;
		$db = new ps_DB;

		$q  = "SELECT product_sku,product_parent_id,product_weight,product_weight_uom FROM #__{vm}_product ";
		$q .= "WHERE product_id='$pid' ";

		$db->query($q);
		$db->next_record();

		if ($db->f("product_weight") == 0 && $db->f("product_parent_id")) {
			$q = "SELECT * from #__{vm}_product WHERE product_id='".$db->f("product_parent_id")."'";
			$db->query($q);
			$db->next_record();
		}

		if ($db->f("product_weight") > 0) {
			if (eregi("LB",$db->f("product_weight_uom")) || eregi("PO",$db->f("product_weight_uom"))) {
				$weight = $db->f("product_weight") * 0.4536;
			}
			elseif (eregi("KG",$db->f("product_weight_uom")) || eregi("KILO",$db->f("product_weight_uom"))) {
				$weight = $db->f("product_weight");
			}
			elseif (eregi("G",$db->f("product_weight_uom"))) {
				$weight = $db->f("product_weight") * 0.001;
			}
			elseif (eregi("OZ",$db->f("product_weight_uom")) || eregi("OU",$db->f("product_weight_uom"))) {
				$weight = $db->f("product_weight") * 0.02835;
			}
			else {
				$vmLogger->debug( "Unknown weight UOM in product " . $db->f("product_sku"));
			}
		} 
		else {
			$vmLogger->debug( "Weight not specified for product " . $db->f("product_sku"));
		}

		if (!empty($weight)) {
			return($weight);
		}
		else {
			return 0;
		}
	}

	/**
	 * Calculate product weight in Pounds
	 * @author Matt Oberpriller
	 * @author soeren
	 * @param int $pid
	 * @return double
	 */
	function get_weight_LB( $pid ) {
		global $vars, $vmLogger;
		$db = new ps_DB;

		$q  = "SELECT product_sku,product_parent_id,product_weight,product_weight_uom FROM #__{vm}_product ";
		$q .= "WHERE product_id='$pid' ";

		$db->query($q);
		$db->next_record();

		if ($db->f("product_weight") == 0 && $db->f("product_parent_id")) {
			$q = "SELECT * from #__{vm}_product WHERE product_id='".$db->f("product_parent_id")."'";
			$db->query($q);
			$db->next_record();
		}

		if ($db->f("product_weight") > 0) {
			if (eregi("LB",$db->f("product_weight_uom")) || eregi("PO",$db->f("product_weight_uom"))) {
				$weight = $db->f("product_weight");
			}
			elseif (eregi("KG",$db->f("product_weight_uom")) || eregi("KILO",$db->f("product_weight_uom"))) {
				$weight = $db->f("product_weight") * 2.20459;
			}
			elseif (eregi("G",$db->f("product_weight_uom"))) {
				$weight = $db->f("product_weight") * 0.00220459;
			}
			elseif (eregi("OZ",$db->f("product_weight_uom")) || eregi("OU",$db->f("product_weight_uom"))) {
				$weight = $db->f("product_weight") * 0.0625;
			}
			else {
				$vmLogger->debug( "Unknown weight UOM in product " . $db->f("product_sku"));
			}
		} 
		else {
			$vmLogger->debug( "Weight not specified for product " . $db->f("product_sku"));
		}

		if (!empty($weight)) {
			return($weight);
		}
		else {
			return 0;
		}
	}
}

/**
*
* @abstract 
*/
class vmShippingPlugin extends vmPlugin {

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
	function vmShippingPlugin(& $subject, $config) {
		parent::__construct($subject, $config);
	}

	/**
	 * Retrieve a list of available Shipping Rates
	 *
	 * @param string $order_number
	 * @param double $order_total
	 * @param array $d
	 * @return mixed - false if retrieving the rate list failed, an array if success
	 */
   function get_shipping_rate_list(&$d) {
        return array();
    }
    /**
     * Get the rate for a specific Shipping Rate ID ($d['shipping_rate_id'])
     *
     * @param array $d
     * @return double
     */
    function get_shipping_rate( &$d ) {
    	return 0.00;
    }
    /**
     * Should display some HTML, example: a Form to redirect the customer to the payment gateway
     *
	 * @return double
     */
    function get_shippingtax_rate() {
    	return 0.00;
    }
    /**
     * Validates the Shipping Method
     *
     * @return boolean
     */
	function validate( &$d ) {
		  $shipping_rate_id = JRequest::getVar("shipping_rate_id");
	  
		  if( array_key_exists( $shipping_rate_id, $_SESSION )) {
		  	return true;
		  }
		  else {
		  	return false;
		  }
	}
}

?>