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
	function plgVmCustomTextinput() {
		$this->_pelement = basename(__FILE__, '.php');
		$this->_createTable();
		//parent::__construct($subject, $config);
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
	function onProductEdit($field,$param,$row, $product_id) {
		if ($field->custom_value != $this->_pelement) return '';

		//print_r($value);
		if (!$param) {
			$param['comment']='' ;
			$param['Morecomment']='' ;
		}
		$html  ='<input type="text" value="'.$param['comment'].'" size="10" name="custom_param['.$row.'][comment]"> ';
		$html .='<input type="text" value="'.$param['Morecomment'].'" size="10" name="custom_param['.$row.'][Morecomment]">';
		$html .='Plz fill the Text ';

		return $html  ;
	}
	/**
	 * @ idx plugin index
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::onDisplayProductFE()
	 * @author Patrick Kohl
	 */
	function onDisplayProductFE($field, $param,$product,$idx) {
		// default return if it's not this plugin
		if ($field->custom_value != $this->_pelement) return '';
		if (!$param) {
			$param['comment']='' ;
			$param['Morecomment']='10';
		}
		
		$plgParam = $this->getVmCustomParams($field->virtuemart_custom_id);

		//echo $plgParam->get('custom_info');
		// Here the plugin values
		$html ='Text inputs ';
		$html.='<input type="text" value="'.$param['comment'].'" size="10" name="customPlugin['.$idx.'][comment]"><br />';
		$html.='<input type="text" value="'.$param['Morecomment'].'" size="10" name="customPlugin['.$idx.'][Morecomment]">';
        return $html;
    }

	/**
	 * all param are in session
	 * *** Can only set in table at order then put it in session ***
	 * *** Have to add it in VIrtuemart cart ? ***
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnSaveProductFE()
	 * @author Patrick Kohl
	 */
	function onViewCartModule( $product,$param,$productCustom, $row) {
		if ($param->comment) return 'commented';
		return 'not commented';
    }

	/**
	 * TODO Add all param to session
	 * *** Can only set in table at order then put it in session ***
	 * *** Have to add it in VIrtuemart cart ? ***
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnSaveProductFE()
	 * @author Patrick Kohl
	 */
	function onViewCart($product, $param,$productCustom, $row) {
		$html  = '<div>';
		$html .='<span>'.$param->comment.'</span>';
		$html .='<span>'.$param->Morecomment.'</span>';
		return $html.'</div>';
    }
	/**
	 * Add param as product_attributes 
	 * from cart to order
	 * @author Patrick Kohl
	 */
	function onViewCartOrder($product, $param,$productCustom, $row) {
		// $html  = '<div>';
		// $html .='<span>'.$param->comment.'</span>';
		// $html .='<span>'.$param->Morecomment.'</span>';
		// $html .='</div>';
		// return $html;
		return $param;
    }
	
	/**
	 *
	 * venodr order display
	 */
	function onViewOrderBE($item, $param,$productCustom, $row) {
		$html  = '<div>';
		$html .='<span>'.$param->comment.'</span>';
		$html .='<span>'.$param->Morecomment.'</span>';

		return $html.'</div>';
    }
	
	/**
	 *
	 * shopper order display
	 */
	function onViewOrderFE($item, $param,$productCustom, $row) {
		$html  = '<div>';
		if ($item->order_status == 'S' or $item->order_status == 'C' ) {
			$html .=' Link to media';
		} else {
			$html .=' Paiment not confiremed, PLz come back later ';
		}
		// $html .='<span>'.$param->comment.'</span>';
		// $html .='<span>'.$param->Morecomment.'</span>';

		return $html.'</div>';
    }
	
	function plgVmOnOrder($product) {

		$dbValues['virtuemart_product_id'] = $product->virtuemart_product_id;
		$dbValues['textinput'] = $this->_virtuemart_paymentmethod_id;
		$this->writeCustomData($dbValues, '#__virtuemart_product_custom_' . $this->_pelement);
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

}

// No closing tag