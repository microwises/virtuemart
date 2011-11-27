<?php
defined('_JEXEC') or 	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
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

if (!class_exists('vmPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmcustomplugin.php');

class plgVmCustomTextinput extends vmCustomPlugin {


	// instance of class
	public static $_this = false;

	function __construct(& $subject, $config) {
		if(self::$_this) return self::$_this;
		parent::__construct($subject, $config);

// 		$this->_loggable = true;
// 		$this->tableFields = array('id','virtuemart_order_id','order_number','virtuemart_paymentmethod_id',
// 							'payment_name','cost','cost','tax_id');//,'created_on','created_by','modified_on','modified_by','locked_on');


		$varsToPush = array(
						    		'custom_size'=>array(0.0,'int'),
						    		'custom_price_by_letter'=>array(0.0,'bool')
		);

		$this->setConfigParameterable('custom_params',$varsToPush);

		self::$_this = $this;
	}

	// get product param for this plugin on edit
	function onProductEdit($field,$param,$row, $product_id) {
		if ($field->custom_value != $this->_name) return '';
// 		$plgParam = $this->getVmCustomParams($field->virtuemart_custom_id);

		$data = $this->getPlugin($field->virtuemart_custom_id);
		//print_r($plgParam);
// 		if (empty($param)) {
// 			$param['custom_name']= $plgParam->get('custom_name');
// 			$param['custom_size']= $plgParam->get('custom_size');
// 		}
// 		$html  ='<input type="text" value="'.$param['custom_name'].'" size="10" name="custom_param['.$row.'][custom_name]"> ';
// 		$html .='<input type="text" value="'.$param['custom_size'].'" size="10" name="custom_param['.$row.'][custom_size]">';
		$html  ='<input type="text" value="'.$data['custom_name'].'" size="10" name="custom_name['.$row.']"> ';
		$html .='<input type="text" value="'.$data['custom_size'].'" size="10" name="custom_size['.$row.']">';
		$html .=JTEXT::_('VMCUSTOM_TEXTINPUT_NO_CHANGES_BE');

		return $html  ;
	}
	/**
	 * @ idx plugin index
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::onDisplayProductFE()
	 * @author Patrick Kohl
	 * eg. name="customPlugin['.$idx.'][comment] save the comment in the cart & order
	 */
	function onDisplayProductFE($field, $param,$product,$idx) {
		// default return if it's not this plugin
		if ($field->custom_value != $this->_name) return '';
		if (!$param) {
			$param['custom_name']='' ;
			$param['custom_size']='10';
		}


		//echo $plgParam->get('custom_info');
		// Here the plugin values
		$html =JTEXT::_($param['custom_name']) ;
		$html.=': <input class="vmcustom-textinput" type="text" value="" size="'.$param['custom_size'].'" name="customPlugin['.$idx.'][comment]"><br />';
		static $textinputjs;
		// preventing 2 x load javascript
		if ($textinputjs) return $html;
		$textinputjs = true ;
		//javascript to update price
		$document = JFactory::getDocument();
		$document->addScriptDeclaration('
	jQuery( function($) {
		jQuery(".vmcustom-textinput").keyup(function() {
				formProduct = $(".productdetails-view").find(".product");
				virtuemart_product_id = formProduct.find(\'input[name="virtuemart_product_id[]"]\').val();
			$.setproducttype(formProduct,virtuemart_product_id);
			});
	});
		');
        return $html;
    }

	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::onViewCartModule()
	 * @author Patrick Kohl
	 */
	function onViewCartModule( $product,$param,$productCustom, $row) {
		if ($param->comment) return 'commented';
		return 'not commented';
    }

	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::onViewCart()
	 * @author Patrick Kohl
	 */
	function onViewCart($product, $param,$productCustom, $row) {
		$html  = '<div>';
		$html .='<span>'.$param->comment.'</span>';
		// $html .='<span>'.$param->Morecomment.'</span>';
		return $html.'</div>';
    }
	/**
	 * Add param as product_attributes
	 * from cart >>> to order
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::onViewCart()
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
	 * vendor order display BE
	 */
	function onViewOrderBE($item, $param,$productCustom, $row) {
		$html  = '<div>';
		$html .='<span>'.$param->comment.'</span>';
		// $html .='<span>'.$param->Morecomment.'</span>';

		return $html.'</div>';
    }

	/**
	 *
	 * shopper order display FE
	 */
	function onViewOrderFE($item, $param,$productCustom, $row) {
		$html  = '<div>';
		// if ($item->order_status == 'S' or $item->order_status == 'C' ) {
			// $html .=' Link to media';
		// } else {
			// $html .=' Paiment not confiremed, PLz come back later ';
		// }
		$html .='<span>'.$param->comment.'</span>';
		// $html .='<span>'.$param->Morecomment.'</span>';

		return $html.'</div>';
    }
	public function modifyPrice( $product, $field,$param,$selected,$row ) {
		if (!empty($field->custom_price)) {
			//TODO adding % and more We should use here $this->interpreteMathOp
			// eg. to calculate the price * comment text length
			$plgParam = $this->getVmCustomParams($field->virtuemart_custom_id);
			if ($plgParam->get('custom_price_by_letter') ==1) {
			$pluginFields = JRequest::getVar('customPlugin',null );
			if ($pluginFields ==  null) $pluginFields = json_decode( $product->customPlugin, true);
				if ($textinput = $pluginFields[$row]['comment']) {
					$field->custom_price = strlen ($textinput) *  $field->custom_price ;
				}
			}
			return $field->custom_price;
		}
	}
	function plgVmOnOrder($product) {

		$dbValues['virtuemart_product_id'] = $product->virtuemart_product_id;
		$dbValues['textinput'] = $this->_virtuemart_paymentmethod_id;
		$this->writeCustomData($dbValues, '#__virtuemart_product_custom_' . $this->_name);
	}


	/**
	 * (depredicate)
	 */
	function plgVmOnOrderShowFE($product,$order_item_id) {
		//$dbValues['virtuemart_product_id'] = $product->virtuemart_product_id;
		//$dbValues['textinput'] = $this->_virtuemart_paymentmethod_id;
		//$this->writePaymentData($dbValues, '#__virtuemart_product_custom_' . $this->_name);
				$db = JFactory::getDBO();
		$q = 'SELECT * FROM `#__virtuemart_product_custom_' . $this->_name . '` '
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