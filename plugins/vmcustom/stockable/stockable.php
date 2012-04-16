<?php
defined('_JEXEC') or 	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
/**
 * @version $Id: standard.php,v 1.4 2005/05/27 19:33:57 ei
 *
 * a special type of 'cash on delivey':
 * its fee depend on total sum
 * @author Max Milbers
 * @version $Id: stockable.php 3681 2011-07-08 12:27:36Z alatak $
 * @package VirtueMart
 * @subpackage vmcustom
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

class plgVmCustomStockable extends vmCustomPlugin {

	var $verifyStock =0;
	// instance of class
// 	public static $_this = false;

	function __construct(& $subject, $config) {
// 		if(self::$_this) return self::$_this;
		parent::__construct($subject, $config);

		$varsToPush = array(
			'selectname1'=>array('','char'),'selectname2'=>array('','char'),'selectname3'=>array('','char'),'selectname4'=>array('','char'),
			'selectoptions1'=>array('','char'),'selectoptions2'=>array('','char'),'selectoptions3'=>array('','char'),'selectoptions4'=>array('','char')
		);

		$this->setConfigParameterable('custom_params',$varsToPush);

// 		self::$_this = $this;
	}

	// function plgVmOnOrder($product) {

		// $dbValues['virtuemart_product_id'] = $product->virtuemart_product_id;
		// $dbValues['stockable'] = $this->_virtuemart_paymentmethod_id;
		// $this->writeCustomData($dbValues, '#__virtuemart_product_custom_' . $this->_name);
	// }




	// get product param for this plugin on edit
	function plgVmOnProductEdit($field, $product_id, &$row,&$retValue) {

		if ($field->custom_element != $this->_name) return '';

		$this->parseCustomParams($field);
		$html ='';
		if (!$childs = $this->getChilds($product_id) ) $html .='<DIV>'.JTEXT::_('VMCUSTOM_STOCKABLE_NO_CHILD').'</DIV>';
		$db = JFactory::getDBO();
		$db->setQuery('SELECT `virtuemart_custom_id` FROM `#__virtuemart_customs` WHERE field_type="G" ');
		$group_custom_id = $db->loadResult();
		// $plgParam = $this->getVmCustomParams($field->virtuemart_custom_id);

		$html .='<span style="width:50px; display: inline-block;">'.JText::_('VMCUSTOM_STOCKABLE_IS_VARIANT').'</span>';

		for ($i = 1; $i<5 ;$i++) {
			$selectname = 'selectname'.$i ;
			$listname = $field->$selectname;
			if (!empty($listname)) {
			$html .=' <span style="width:98px; display: inline-block;color:#000;overflow:hidden;">'.JTEXT::_($listname).'</span>';
			}
		}
		$html .=' <span style="width:98px; display: inline-block;color:#000;">'. JText::_('VMCUSTOM_STOCKABLE_PRICE') .'</span>';
		// $param = json_decode($field->custom_param,true);
		if (isset($field->child)) $childList = $field->child;
		else $childList = array();
		foreach ($childs as $child ) {
			$checked ='';
			$price = null;
			if(!empty($childList)) {
				if (!array_key_exists($child->id, $childList) ) $childList[$child->id]['is_variant'] = 1;
				if ($childList[$child->id]['is_variant'] ) $checked='checked';
				if (array_key_exists('custom_price', $childList[$child->id] ) )
					$price = $childList[$child->id]['custom_price'] ;
			}
			//$html .= JHTML::_('select.genericlist', $childlist, 'custom_param['.$row.'][child_id]','','virtuemart_product_id','product_name',$param['child_id'],false,true);
			$name='custom_param['.$row.'][child]['.$child->id.']';
			$html .='<div class="stockable">' ;
			$html .='	<input type="hidden"  value="0" name="'.$name.'[is_variant]">';
			$html .='	<span style="width:50px; display: inline-block;"><input type="checkbox" '.$checked.'  value="1" name="'.$name.'[is_variant]"></span>';
			for ($i = 1; $i<5 ;$i++) {
				$selectoptions = 'selectoptions'.$i ;
				$attributes = 'attribute'.$i ;
				if (isset($field->$selectoptions)) $selectoption = (string)$field->$selectoptions;
				else  $selectoption = "" ;
				$option = array();
				$tmpOptions = str_replace( "\r", "" ,$selectoption);

				if ($listoptions = explode("\n",$tmpOptions ) ) {
					foreach ($listoptions as $key => $val) $option[] = JHTML::_('select.option',JText::_( $val ) , $val  );
					if (empty($childList[$child->id][$selectoptions])) {
						$childList[$child->id][$selectoptions] ='';
					}
					if ($listoptions[0] == '') $html .= '';// <span style="width:98px; display: inline-block;color:#000;">'.JText::_('VMCUSTOM_STOCKABLE_NO_OPTION') .'</span>';
					else $html .= JHTML::_('select.genericlist', $option, $name.'['.$selectoptions.']','style="width:100px !important;"','text','value',$childList[$child->id][$selectoptions],false,true)."\n";
				}

			}
			//$html .='<input  type="hidden" name="'.$name.'[child_id]" value="'.$child->id.'">';
			// if (!$customfield = $this->getFieldId($product_id, $child->id) ) $price ='' ;
			// else

			$html .='<input style="width:98px; display: inline-block;" type="text" name="'.$name.'[custom_price]" value="'.$price.'">';
			// $html .='<input type="hidden" name="custom_param[c'.$child->id.'][field_type]" value="G">';
			// $html .='<input type="hidden" name="field[c'.$child->id.'][virtuemart_custom_id]" value="'.$group_custom_id.'">';

			$html .= ' '.$child->product_name.' ['.JText::_('COM_VIRTUEMART_PRODUCT_IN_STOCK').' : '.$child->stock.']</div>' ;

		}
		$html .='
				<fieldset style="background-color:#F9F9F9;">
					<legend>'. JText::_('COM_VIRTUEMART_PRODUCT_FORM_NEW_PRODUCT_LBL').'</legend>
					<div id="new_stockable">
						<span>'. JText::_('COM_VIRTUEMART_PRODUCT_SKU').'</span> <span><input value="" name="stockable[product_sku]" type="text"></span>
						<span>'. JText::_('COM_VIRTUEMART_PRODUCT_NAME').'</span> <span><input value="" name="stockable[product_name]" type="text"></span>
						<span>'. JText::_('VMCUSTOM_STOCKABLE_PRICE').'</span> <span><input value="" name="stockable[product_price]" type="text"></span>
						<span>'. JText::_('COM_VIRTUEMART_PRODUCT_IN_STOCK').'</span> <span><input value="" name="stockable[product_in_stock]" type="text"></span>

						<span id="new_stockable_product"><span class="icon-nofloat vmicon vmicon-16-new"></span>'. JText::_('COM_VIRTUEMART_ADD').'</span>
					</div>
				</fieldset>';

		$script = "
	jQuery( function($) {
		$('#new_stockable_product').click(function() {
			var Prod = $('#new_stockable');// input[name^=\"stockable\"]').serialize();

			$.getJSON('index.php?option=com_virtuemart&view=product&task=saveJS&token=".JUtility::getToken()."' ,
				{
					product_sku: Prod.find('input[name*=\"product_sku\"]').val(),
					product_name: Prod.find('input[name*=\"product_name\"]').val(),
					product_price: Prod.find('input[name*=\"product_price\"]').val(),
					product_in_stock: Prod.find('input[name*=\"product_in_stock\"]').val(),
					product_parent_id: ".$product_id.",
					published: 1,
					format: \"json\"
				},
				function(data) {
					//console.log (data);
					//$.each(data.msg, function(index, value){
						$(\"#new_stockable\").append(data.msg);
					//});
				});
		});
	});
	";
		$document = JFactory::getDocument();
		$document->addScriptDeclaration($script);
		// $html  ='<input type="text" value="'.$field['custom_name'].'" size="10" name="custom_param['.$row.'][custom_name]"> ';
		// $html .='<input type="text" value="'.$field['custom_size'].'" size="10" name="custom_param['.$row.'][custom_size]">';
		//$html .=JTEXT::_('VMCUSTOM_TEXTINPUT_NO_CHANGES_BE');
		$retValue .= $html;
		return true ;
	}

	/**
	 * @ idx plugin index
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::onDisplayProductFE()
	 * @author Patrick Kohl
	 */
	function plgVmOnDisplayProductVariantFE($field,&$row,&$group) {
		// default return if it's not this plugin
		if ($field->custom_element != $this->_name) return '';
		$row++;
		$this->parseCustomParams($field);
		//if (!$childs = $this->getChilds($product_id) ) return ;
		$stockhandle = VmConfig::get('stockhandle','none');
		$this->verifyStock = ($stockhandle=='disableit' || $stockhandle=='disableadd');
		$html='<br>';
		$customfield_id = array();
		$selects = array();
		$js = array();
		// generate option with valid child results
		foreach($field->child as $child_id => &$attribut) {

			if ($attribut['is_variant']==1) {
				unset ($attribut['is_variant']);
				if ($this->getValideChild( $child_id)) {
					if ($attribut['custom_price'])
						$js[]= '"'.$child_id.'" :'.$attribut['custom_price'];
					unset ($attribut['custom_price']);


					foreach ($attribut as $key => $list) {
						// if (!in_array($key,$selects)) {
							// $selects[$key] = array() ;
						// }
						// if (!in_array($list , $selects[$key]) ) {
							$selects[$key][$list] = $list ;
						// }

					}
				}
			} else unset ($attribut);
		}

		$i = 1;

		foreach ($selects as $keys =>$options) {
			$selectname = 'selectname'.$i;
			$listname = $field->$selectname;
			if (!empty($listname)) {
				$optionName = 'customPlugin['.$field->virtuemart_custom_id.']['.$this->_name.']['.$keys.']';
				$option = array();
				foreach ($options as $key => $val) if (!empty($val)) $option[] = JHTML::_('select.option',JText::_( $val ) , $val  );
				if (!empty($option)) {
					$html .='<div style="width:200px;"><span style="vertical-align: top;width:98px; display: inline-block;color:#000;">'.JTEXT::_($listname).'</span>';
					$html .= JHTML::_('select.genericlist', $option,$optionName ,'class="attribute_list" style="width:100px !important;"','text','value',reset($options),'selectoptions'.$i,true)."</div>\n";
				} else $html .='<input id="'.$keys.'" class="attribute_list" type="hidden" value="'.$val.'" name="'.$optionName.'">' ;
			}
			$i++;
		}
		static $stockablejs;

		$group->display = $html.'
				<input type="hidden" value="'.$child_id.'" name="customPlugin['.$field->virtuemart_custom_id.']['.$this->_name.'][child_id]">';
		// preventing 2 x load javascript

		if ($stockablejs) return;
		$stockablejs = true ;
		// TODO ONE PARAM IS MISSING
		$document = JFactory::getDocument();
		$document->addScriptDeclaration('
		jQuery( function($) {
			var customfield_id = {'. implode(',' , $js ) .'};
			var stockable =$.parseJSON(\'' .json_encode($field->child). '\') ;
			var selecteds = [];//all selected options
			var found_id=0;//found child id
			var original=[];
			var totalattribut = $(".attribute_list").length+1;
			// get all initial select list values
			$.each($(".attribute_list"), function(idx,selec) {
				original[selec.name] = $.map($(this).find("option"), function(idx, opt) {
						return [[ idx.value ,idx.text ]];
					});
			});

			if ( $("#selectoptions1").length ) recalculate($("#selectoptions1"));
			$(".attribute_list").unbind("change");
			$(".attribute_list").change(function(){
				recalculate($(this));

			});
			function recalculate(Opt){
				var listIndex = $(".attribute_list").index(Opt) +2 ;
				choix = Opt.attr("id") ; valeur = Opt.val() ;
				 // console.log (choix , valeur);
				var selection = new Object() ;
				for(var i=listIndex; i<totalattribut; i++){ selection["selectoptions"+i] =[] ; }
				var j=0;

				// set the option to show
				$.each(stockable, function(child_id, child_attrib) {
					// console.log(child_attrib,choix,valeur) ;
					// find all  matrix with an invalid "stockable" child
					if (child_attrib[choix] == valeur ) {
						$.each(child_attrib, function(index, value) {
							if (index > choix)
							selection[index][j] = value ;

						});
					j++;
					}
				});

				// unset invalid option
				// regenerate the option by selected val() after last index selectoptions
				for(var i=listIndex; i<totalattribut; i++){
					selectlist = $("#selectoptions"+i) ;
					orgOptions = original["selectoptions"+i];
					selectedOptions =$.unique(selection["selectoptions"+i]) ;
					var auxArr = [];
					$.each(selectedOptions, function( index, orgtext){ auxArr[index] = "<option value=\'" + orgtext+ "\'>" + orgtext + "</option>"; });
					selectlist.empty().append(auxArr.join(\'\'))
					selectlist.find("option:first").attr("selected","selected");
				}
				// get the selected valid values
				for(var i=1 ; i<totalattribut; i++){
					selecteds["selectoptions"+i] = $("#selectoptions"+i).val();
				}
				// find the product child id
				 $.each(stockable, function(child_id, attribut) {
					 atrID = (listIndex-1) ;
					if (attribut[ "selectoptions"+ atrID  ] == valeur ) {
						var i=j=1;
						for(i ; i<totalattribut; i++){
							if (attribut["selectoptions"+i] != selecteds["selectoptions"+i]){
								break;
							}
							j++;
							//console.log(selecteds["selectoptions"+i],attribut["selectoptions"+i]);
						}
						if (j>totalattribut-2) { found_id = child_id; return } // we have found the selected child
					}
				   if (found_id >0 ) return;
				 });
				// recalculate the price by found product child id;
				formProduct = Opt.parents(".productdetails-view").find(".product");
				virtuemart_product_id = formProduct.find(\'input[name="virtuemart_product_id[]"]\').val();
				//formProduct.find("#selectedStockable").remove();
				//formProduct.append(\'<input id="stockableChild" type="hidden" value="\'+customfield_id[found_id]+\'" name="customPrice['.$row.'][\'+found_id+\']">\');
				formProduct.find(\'input[name*="customPlugin['.$field->virtuemart_custom_id.']['.$this->_name.'][child_id]"]\').val(found_id);

				//(\'<input id="stockableChild" type="hidden" value="\'+customfield_id[found_id]+\'" name="customPrice['.$row.'][\'+found_id+\']">\');
				Virtuemart.setproducttype(formProduct,virtuemart_product_id);
			}
		});
		');

		// 'custom_param['.$keys.']'

		//dump($param);
		//"is_variant":"1","attribute1":"Red","attribute2":"20 cm","attribute3":"10","attribute4":"10"

		//echo $plgParam->get('custom_info');
		// Here the plugin values
		//$html =JTEXT::_($param['custom_name']) ;
		//$html.=': <input type="text" value="" size="'.$param['custom_name'].'" name="customPlugin['.$row.'][comment]"><br />';


		return true;
	}

	function plgVmOnDisplayProductFE( $product, &$idx,&$group){}
	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnViewCartModule()
	 * @author Patrick Kohl
	 */
	function plgVmOnViewCartModule( $product, $row,&$html) {
		if (!$plgParam = $this->GetPluginInCart($product)) return false ;
		foreach ($plgParam as $k => $attributes) {
			foreach ($attributes as $k => $attribute) {
				if ($k =='child_id') continue;
				$html .='<span> '.$attribute.' </span>';
			}
		}
		return true;
	}

	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnViewCart()
	 * @author Patrick Kohl
	 */
	function plgVmOnViewCart($product, $row,&$html) {
		if (!$plgParam = $this->GetPluginInCart($product)) return false ;
		$html  .= '<div>';
		foreach ($plgParam as $attributes) {
			foreach ($attributes as $k => $attribute) {
				if ($k =='child_id') continue;
				$html .='<span> '.$attribute.' </span>';
			}
		}		// $html .='<span>'.$param->Morecomment.'</span>';
		$html.='</div>';
		return true;
		//vmdebug('stockable attributs',$plgParam);
	}

	/**
	 *
	 * vendor order display BE
	 */
	function plgVmDisplayInOrderBE($item, $row,&$html) {
		if (empty($item->productCustom->custom_element) or $item->productCustom->custom_element != $this->_name) return '';
		return $this->plgVmOnViewCart($item, $row,$html);
	}

	/**
	 *
	 * shopper order display FE
	 */
	function plgVmDisplayInOrderFE($item, $row,&$html) {
		if (empty($item->productCustom->custom_element) or $item->productCustom->custom_element != $this->_name) return '';
		return $this->plgVmOnViewCart($item, $row,$html);
	}

	function getChilds($child_id = null) {

		$db = JFactory::getDBO();
		$q = 'SELECT CONCAT( `product_name`, " [' .JText::_('COM_VIRTUEMART_PRODUCT_SKU').'"," : ",`product_sku`,"]") as product_name,`virtuemart_product_id` as id, `product_in_stock` as stock FROM `#__virtuemart_products_'.VMLANG.'` as l '
		. ' JOIN `#__virtuemart_products` AS p using (`virtuemart_product_id`)'
		. 'WHERE `product_parent_id` ='.(int)$child_id ;
		$db->setQuery($q);

		$result = $db->loadObjectList();

		if (!($result)) {
			//JError::raiseWarning(500, $db->getErrorMsg());
			return array();
		} else return $result ;
	}

	function getFieldId($virtuemart_product_id, $child_id ) {

		$db = JFactory::getDBO();
		$q = 'SELECT cf.* FROM `#__virtuemart_product_customfields` as cf JOIN `#__virtuemart_customs` as c ON `c`.`virtuemart_custom_id` = cf.`virtuemart_custom_id` AND c.`field_type`="G"
			WHERE cf.`virtuemart_product_id` ='.(int)$virtuemart_product_id.' and cf.custom_value='.(int)$child_id ;
		$db->setQuery($q);
		$result = $db->loadObject();
		if (!($result)) {
			//JError::raiseWarning(500, $db->getErrorMsg());
			return false;
		} else return $result ;
	}

	function getValideChild($child_id ) {
		$db = JFactory::getDBO();
		$q = 'SELECT `product_sku`,`product_name`,`product_in_stock`,`product_ordered` FROM `#__virtuemart_products` JOIN `#__virtuemart_products_'.VMLANG.'` as l using (`virtuemart_product_id`) WHERE `published`=1 and `virtuemart_product_id` ='.(int)$child_id ;
		$db->setQuery($q);
		$child = $db->loadObject();
		if ($child) {
			if ($this->verifyStock ) {
				$stock = $child->product_in_stock - $child->product_ordered ;
				if ($stock>0)return $child ;
				else return false ;
			}
			else return $child ;
		}
		return false ;
	}

	public function plgVmGetProductStockToUpdateByCustom(&$item, $pluginParam, $productCustom) {

		if ($productCustom->custom_element !== $this->_name) return false ;
//vmdebug('$pluginParam',$pluginParam[$this->_name]);
		$item->virtuemart_product_id = (int)$pluginParam[$this->_name]['child_id'];
		return true ;
		// echo $item[0]->virtuemart_product_id;jexit();
	}

	/**
	 * We must reimplement this triggers for joomla 1.7
	 * vmplugin triggers note by Max Milbers
	 */
	public function plgVmOnStoreInstallPluginTable($psType) {

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

	public function plgVmCalculateCustomVariant(&$product, &$productCustomsPrice,$selected){

		if ($productCustomsPrice->custom_element != $this->_name) return false;

		if (!$customPlugin = JRequest::getVar('customPlugin',0)) {
			$customPlugin = json_decode($product->customPlugin,true);
		}
		$selected = $customPlugin[$productCustomsPrice->virtuemart_custom_id]['stockable']['child_id'];

		$param = json_decode($productCustomsPrice->custom_param,true);
		if ($child = $this->getValideChild($selected)) {
			if ($param['child'][$selected]['custom_price'] !=='') {
				$productCustomsPrice->custom_price = (float)$param['child'][$selected]['custom_price'];
			} else {
				$db = JFactory::getDBO();
				$db->setQuery('SELECT `product_price` FROM `#__virtuemart_product_prices`  WHERE `virtuemart_product_id`="' . (int)$selected . '" ');
				if ($price = $db->loadResult()) $product->product_price = (float)$price;
			}
			return $child;
		}
		else return false;
		// find the selected child

	}
	public function plgVmOnAddToCart(&$product){
		$customPlugin = JRequest::getVar('customPlugin',0);

		if ($customPlugin) {
			$db = JFactory::getDBO();
			$query = 'SELECT  C.* , field.*
				FROM `#__virtuemart_customs` AS C
				LEFT JOIN `#__virtuemart_product_customfields` AS field ON C.`virtuemart_custom_id` = field.`virtuemart_custom_id`
				WHERE `virtuemart_product_id` =' . $product->virtuemart_product_id.' and `custom_element`="'.$this->_name.'"';
			$query .=' and is_cart_attribute = 1';
			$db->setQuery($query);
			$productCustomsPrice = $db->loadObject();
			if (!$productCustomsPrice) return null;
			// if ( !in_array($this->_name,$customPlugin[$productCustomsPrice->virtuemart_custom_id]) ) return false;
			$selected = $customPlugin[$productCustomsPrice->virtuemart_custom_id]['stockable']['child_id'];

			if (!$child = $this->plgVmCalculateCustomVariant($product, $productCustomsPrice,$selected) ) return false;
			if (!empty($productCustomsPrice->custom_price)) {
				//TODO adding % and more We should use here $this->interpreteMathOp
				$product->product_price +=(float)$productCustomsPrice->custom_price;

			}
			if ($child->product_sku)
				$product->product_sku = $child->product_sku;
			if ($child->product_name)
				$product->product_name = $child->product_name;
		}
	}

	public function plgVmDisplayInOrderCustom(&$html,$item, $param,$productCustom, $row ,$view='FE'){
		$this->plgVmDisplayInOrderCustom($html,$item, $param,$productCustom, $row ,$view);
	}

	public function plgVmCreateOrderLinesCustom(&$html,$item,$productCustom, $row ){
// 		$this->createOrderLinesCustom($html,$item,$productCustom, $row );
	}


}

// No closing tag