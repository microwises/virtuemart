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

if (!class_exists('vmCustomPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmcustomplugin.php');

class plgVmCustomTextinput extends vmCustomPlugin {


	// instance of class
// 	public static $_this = false;

	function __construct(& $subject, $config) {
// 		if(self::$_this) return self::$_this;
		parent::__construct($subject, $config);

		$varsToPush = array(	'custom_size'=>array(0.0,'int'),
						    		'custom_price_by_letter'=>array(0.0,'bool')
		);

		$this->setConfigParameterable('custom_params',$varsToPush);

// 		self::$_this = $this;
	}

	// function plgVmOnOrder($product) {

		// $dbValues['virtuemart_product_id'] = $product->virtuemart_product_id;
		// $dbValues['textinput'] = $this->_virtuemart_paymentmethod_id;
		// $this->writeCustomData($dbValues, '#__virtuemart_product_custom_' . $this->_name);
	// }



	// get product param for this plugin on edit
	function plgVmOnProductEdit($field, $product_id, &$row,&$retValue) {
		if ($field->custom_element != $this->_name) return '';
		// $html .='<input type="text" value="'.$field->custom_size.'" size="10" name="custom_param['.$row.'][custom_size]">';
		$this->parseCustomParams($field);

		$html ='
			<fieldset>
				<legend>'. JText::_('VMCUSTOM_TEXTINPUT') .'</legend>
				<table class="admintable">
					'.VmHTML::row('input','VMCUSTOM_TEXTINPUT_SIZE','custom_param['.$row.'][custom_size]',$field->custom_size).'
				</table>
			</fieldset>';
		$retValue .= $html;
		$row++;
		return true ;
	}

	/**
	 * @ idx plugin index
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::onDisplayProductFE()
	 * @author Patrick Kohl
	 * eg. name="customPlugin['.$idx.'][comment] save the comment in the cart & order
	 */
	function plgVmOnDisplayProductVariantFE($field,&$idx,&$group) {
		// default return if it's not this plugin
		 if ($field->custom_element != $this->_name) return '';
		$this->getCustomParams($field);
			// ob_start();
			// require($this->getLayoutPath('default'));
			// $html = ob_get_clean();

		$group->display .= $this->renderByLayout('default',array($field,&$idx,&$group ) );


		return true;
//         return $html;
    }
	//function plgVmOnDisplayProductFE( $product, &$idx,&$group){}
	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnViewCartModule()
	 * @author Patrick Kohl
	 */
	function plgVmOnViewCartModule( $product,$row,&$html) {
		if (!$plgParam = $this->GetPluginInCart($product)) return false ;
		foreach($plgParam as $k => $item){
			$this->getVmPluginMethod($k);
			if(!empty($item['comment']) ){
				$html .='<span>'.$this->_vmpCtable->custom_title.' '.$item['comment'].'</span>';
			}
		 }
		return true;
    }

	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnViewCart()
	 * @author Patrick Kohl
	 */
	function plgVmOnViewCart($product,$row,&$html) {
		if (!$plgParam = $this->GetPluginInCart($product)) return '' ;

// 		$html  .= '<div>';
		foreach($plgParam as $k => $item){
			$this->getVmPluginMethod($k);
			if(!empty($item['comment']) ){
				$html .='<span>'.$this->_vmpCtable->custom_title.' '.$item['comment'].'</span>';
			}
		 }
// 		$html .='</div>';

		return true;
    }


	/**
	 *
	 * vendor order display BE
	 */
	function plgVmDisplayInOrderBE($item, $row, &$html) {
		if (empty($item->productCustom->custom_element) or $item->productCustom->custom_element != $this->_name) return '';
		$this->plgVmOnViewCart($item,$row,$html); //same render as cart
    }

	/**
	 *
	 * shopper order display FE
	 */
	function plgVmDisplayInOrderFE($item, $row, &$html) {
		if (empty($item->productCustom->custom_element) or $item->productCustom->custom_element != $this->_name) return '';
		$this->plgVmOnViewCart($item,$row,$html); //same render as cart
    }

	/**
	 * We must reimplement this triggers for joomla 1.7
	 * vmplugin triggers note by Max Milbers
	 */
	public function plgVmOnStoreInstallPluginTable($psType) {
		//Should the textinput use an own internal variable or store it in the params?
		//Here is no getVmPluginCreateTableSQL defined
// 		return $this->onStoreInstallPluginTable($psType);
	}


	function plgVmDeclarePluginParamsCustom($psType,$name,$id, &$data){
		return $this->declarePluginParams($psType, $name, $id, $data);
	}

	function plgVmSetOnTablePluginParamsCustom($name, $id, &$table){
		return $this->setOnTablePluginParams($name, $id, $table);
	}

	/**
	 * Custom triggers note by Max Milbers
	 */
	function plgVmOnDisplayEdit($virtuemart_custom_id,&$customPlugin){
		return $this->onDisplayEditBECustom($virtuemart_custom_id,$customPlugin);
	}

	public function plgVmCalculateCustomVariant($product, &$productCustomsPrice,$selected){
		if ($productCustomsPrice->custom_element !==$this->_name) return ;
		$customVariant = $this->getCustomVariant($product, $productCustomsPrice,$selected);
		if (!empty($productCustomsPrice->custom_price)) {
			//TODO adding % and more We should use here $this->interpreteMathOp
			// eg. to calculate the price * comment text length
			if ($productCustomsPrice->custom_price_by_letter ==1) {
				if (!empty($customVariant['comment'])) {
					$charcount = strlen ($customVariant['comment']);
					$productCustomsPrice->custom_price = $charcount * $productCustomsPrice->custom_price ;
				} else {
					$productCustomsPrice->custom_price = 0.0;
				}
			}
		}
		return true;
	}

	public function plgVmDisplayInOrderCustom(&$html,$item, $param,$productCustom, $row ,$view='FE'){
		$this->plgVmDisplayInOrderCustom($html,$item, $param,$productCustom, $row ,$view);
	}

	public function plgVmCreateOrderLinesCustom(&$html,$item,$productCustom, $row ){
// 		$this->createOrderLinesCustom($html,$item,$productCustom, $row );
	}
	function plgVmOnSelfCallFE($type,$name,&$render) {
		$render->html = 'test';
	}

}

// No closing tag