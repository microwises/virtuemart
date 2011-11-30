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

		$varsToPush = array(	'custom_size'=>array(0.0,'int'),
						    		'custom_price_by_letter'=>array(0.0,'bool')
		);

		$this->setConfigParameterable('custom_params',$varsToPush);

		self::$_this = $this;
	}

	function plgVmOnOrder($product) {

		$dbValues['virtuemart_product_id'] = $product->virtuemart_product_id;
		$dbValues['textinput'] = $this->_virtuemart_paymentmethod_id;
		$this->writeCustomData($dbValues, '#__virtuemart_product_custom_' . $this->_name);
	}



	// get product param for this plugin on edit
	function onProductEdit($field, $product, $row) {
		if ($field->custom_element != $this->_name) return '';
// 		$data = $this->getVmPluginMethod($field->virtuemart_custom_id);
// 		VmTable::bindParameterable($field,$this->_xParams,$this->_varsToPushParam);

// 		$html  ='<input type="text" value="'.$field->custom_title.'" size="10" name="custom_param['.$row.'][custom_title]"> ';
		$html ='<input type="text" value="'.$field->custom_size.'" size="10" name="custom_param['.$row.'][custom_size]">';
		$html .=JTEXT::_('VMCUSTOM_TEXTINPUT_NO_CHANGES_BE');
// 		$field->display = $html;
		return $html  ;
	}

	/**
	 * @ idx plugin index
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::onDisplayProductFE()
	 * @author Patrick Kohl
	 * eg. name="customPlugin['.$idx.'][comment] save the comment in the cart & order
	 */
	function onDisplayProductFE(&$field, $product,$idx) {
		// default return if it's not this plugin
		if ($field->custom_value != $this->_name) return '';
// 		if (!$field->custom_name) {
// 			$param['custom_name']='' ;
// 			$param['custom_size']='10';
// 		}


		vmdebug('onDisplayProductFE');
		// Here the plugin values
		//$html =JTEXT::_($field->custom_title) ;
		$html=': <input class="vmcustom-textinput" type="text" value="" size="'.$field->custom_size.'" name="customPlugin['.$field->virtuemart_custom_id.']['.$this->_name.'][comment]"><br />';
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
		$field->display = $html;
        return $html;
    }

	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::onViewCartModule()
	 * @author Patrick Kohl
	 */
	function onViewCartModule( $product,$productCustom, $row,$plgParam) {
		if(!empty($plgParam['comment']) ){
			return ' = '.$plgParam['comment'];
		}
		return '';
    }

	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::onViewCart()
	 * @author Patrick Kohl
	 */
	function onViewCart($product,$productCustom, $row,$plgParam) {
		vmdebug('onViewCart',$plgParam);

		$comment ='';
		// foreach($plgParam as $k => $item){
			if(!empty($plgParam['comment']) ){
				$comment .= ' = '.$plgParam['comment'];
			}
		// }
// 		$comment = current($product->param);
		$html  = '<div>';
		$html .='<span>'.$comment.'</span>';
		// $html .='<span>'.$param->Morecomment.'</span>';
		return $html.'</div>';
    }


	/**
	 *
	 * vendor order display BE
	 */
	function onViewOrderBE($item,$productCustom, $row,$plgParam) {
		$comment ='';
			if(!empty($plgParam['comment']) ){
				$comment .= ' = '.$plgParam['comment'];
			}
		$html  = '<div>';
		$html .='<span>'.$comment.'</span>';
		return $html.'</div>';
    }

	/**
	 *
	 * shopper order display FE
	 */
	function onViewOrderFE($item,$productCustom, $row,$plgParam) {
		$comment ='';
			if(!empty($plgParam['comment']) ){
				$comment .= ' = '.$plgParam['comment'];
			}
		$html  = '<div>';
		$html .='<span>'.$comment.'</span>';
		return $html.'</div>';
    }

	public function modifyPrice( $product, &$field,$selected,$row ) {

		if (!empty($field->custom_price)) {
			//TODO adding % and more We should use here $this->interpreteMathOp
			// eg. to calculate the price * comment text length
// 			if ($field->custom_price_by_letter ==1) {
				$pluginFields = JRequest::getVar('customPlugin',null );
				if ($pluginFields ==  null) $pluginFields = json_decode( $product->customPlugin, true);
					if ($textinput = $pluginFields[$row]['comment']) {

						$field->custom_price = strlen ($textinput) *  $field->custom_price ;
					}
// 			}
// 			return $field->custom_price;
		}
	}

}

// No closing tag