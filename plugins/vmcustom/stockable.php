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


	// instance of class
	public static $_this = false;

	function __construct(& $subject, $config) {
		if(self::$_this) return self::$_this;
		parent::__construct($subject, $config);

		$varsToPush = array(
			'selectname1'=>array('','char'),'selectname2'=>array('','char'),'selectname3'=>array('','char'),'selectname4'=>array('','char'),
			'selectoptions1'=>array('','char'),'selectoptions2'=>array('','char'),'selectoptions3'=>array('','char'),'selectoptions4'=>array('','char')
		);

		$this->setConfigParameterable('custom_params',$varsToPush);

		self::$_this = $this;
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

		$html .='<span style="width:20px; display: inline-block;"> </span>';

		for ($i = 1; $i<5 ;$i++) {
			$selectname = 'selectname'.$i ;
			$listname = $field->$selectname;
			$html .=' <span style="width:98px; display: inline-block;color:#000;overflow:hidden;">'.JTEXT::_($listname).'</span>';
		}
		$html .=' <span style="width:98px; display: inline-block;color:#000;">'. JText::_('COM_VIRTUEMART_CART_PRICE') .'</span>';

		$childList = (array)$field->child;
		//vmdebug('field',$field);
		foreach ($childs as $child ) {
			$checked ='';

			if(!empty($childList)) {
				if (!array_key_exists($child->id, $childList) ) $childList[$child->id]['is_variant'] = 1;
				if ($childList[$child->id]['is_variant'] ) $checked='checked';
				else $checked ='';
			}
			//$html .= JHTML::_('select.genericlist', $childlist, 'custom_param['.$row.'][child_id]','','virtuemart_product_id','product_name',$param['child_id'],false,true);

			$html .='<div class="stockable">' ;
			$html .='	<input type="hidden"  value="0" name="custom_param['.$row.'][child]['.$child->id.'][is_variant]">';
			$html .='	<input type="checkbox" '.$checked.' title="use it as variant" value="1" name="custom_param['.$row.'][child]['.$child->id.'][is_variant]">';
			for ($i = 1; $i<5 ;$i++) {
				$selectoptions = 'selectoptions'.$i ;
				$attributes = 'attribute'.$i ;
				if (isset($field->$selectoptions)) $selectoption = (string)$field->$selectoptions;
				else  $selectoption = "" ;
				$option = array();
				$tmpOptions = str_replace( "\r", "" ,$selectoption);

				if ($listoptions = explode("\n",$tmpOptions ) ) {
					foreach ($listoptions as $key => $val) $option[] = JHTML::_('select.option',JText::_( $val ) , $val  );
					if (empty($childList[$child->id][$attributes])) {
						$childList[$child->id][$attributes] ='';
					}
					if ($listoptions[0] == '') $html .= ' <span style="width:98px; display: inline-block;color:#000;">'.JText::_('VMCUSTOM_STOCKABLE_NO_OPTION') .'</span>';
					else $html .= JHTML::_('select.genericlist', $option, 'custom_param['.$row.'][child]['.$child->id.'][attribute'.$i.']','style="width:100px !important;"','text','value',$childList[$child->id][$attributes],false,true)."\n";
				}

			}
			$html .='<input  type="hidden" name="field[c'.$child->id.'][custom_value]" value="'.$child->id.'">';
			// if (!$customfield = $this->getFieldId($product_id, $child->id) ) $price ='' ;
			// else
			$price = $childList[$child->id]['custom_price'] ;
			$html .='<input style="width:98px; display: inline-block;" type="text" name="custom_param['.$row.'][child]['.$child->id.'][custom_price]" value="'.$price.'">';
			// $html .='<input type="hidden" name="custom_param[c'.$child->id.'][field_type]" value="G">';
			// $html .='<input type="hidden" name="field[c'.$child->id.'][virtuemart_custom_id]" value="'.$group_custom_id.'">';

			$html .= ' '.$child->product_name.' Stock '.$child->stock.'</div>' ;

		}
		$html .='
				<fieldset style="background-color:#F9F9F9;">
					<legend>'. JText::_('COM_VIRTUEMART_PRODUCT_FORM_NEW_PRODUCT_LBL').'</legend>
					<div id="new_stockable">
						<span>'. JText::_('COM_VIRTUEMART_PRODUCT_SKU').' : <input value="" name="stockable[product_sku]" type="text"></span>
						<span>'. JText::_('COM_VIRTUEMART_PRODUCT_NAME').' : <input value="" name="stockable[product_name]" type="text"></span>
						<span>'. JText::_('COM_VIRTUEMART_PRODUCT_PRICE').' : <input value="" name="stockable[product_price]" type="text"></span>
						<span>'. JText::_('COM_VIRTUEMART_PRODUCT_IN_STOCK').' : <input value="" name="stockable[product_in_stock]" type="text"></span>

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
		$this->parseCustomParams($field);
		//if (!$childs = $this->getChilds($product_id) ) return ;

		$html='<br>';
		$customfield_id = array();
		$selects = array();
		$js = array();
		//vmdebug('$field',$field,$product);
		// generate option with valid child results
		foreach($field->child as $child_id => &$attribut) {
			if ($attribut['is_variant']==1) {
				unset ($attribut['is_variant']);
				if ($this->getValideId( $child_id)) {

					$js[]= '"'.$child_id.'" :'.$attribut['custom_price'];
					unset ($attribut['custom_price']);

					//vmdebug('attribut',$attribut);
					foreach ($attribut as $key => $list) {
						if (empty ($selects[$key])) {
							$selects[$key][] = $list ;
						} else {
							if (!in_array($list , $selects[$key]) ) {
								$selects[$key][$list] = $list ;
							}
						}
					}
				}
			} else unset ($attribut);
		}
		$i = 1;
		$selectname = 'selectname'.$i ;
		$listname = $field->$selectname;

		foreach ($selects as $keys =>$options) {
			if (!empty($listname)) {
				$optionName = 'customPlugin['.$field->virtuemart_custom_id.']['.$this->_name.']['.$key.']';
				$option = array();
				foreach ($options as $key => $val) if (!empty($val)) $option[] = JHTML::_('select.option',JText::_( $val ) , $val  );
				if (!empty($option)) {
					$html .='<div style="width:200px;"><span style="vertical-align: top;width:98px; display: inline-block;color:#000;">'.JTEXT::_($listname).'</span>';
					$html .= JHTML::_('select.genericlist', $option,$optionName ,'class="attribute_list" style="width:100px !important;"','text','value',$options[0],false,true)."</div>\n";
				} else $html .='<input id="'.$keys.'" class="attribute_list" type="hidden" value="'.$val.'" name="'.$optionName.'">' ;
			}
			$i++;
		}
		static $stockablejs;

		$field->display = $html;
		// preventing 2 x load javascript

		if ($stockablejs) return $row++;
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
			//console.log(stockable) ;
			if ( $("#attribute1").length ) recalculate($("#attribute1"));
			$(".attribute_list").change(function(){
				recalculate($(this));

			});
			function recalculate(Opt){
				var listIndex = $(".attribute_list").index(Opt) +2 ;
				choix = Opt.attr("id") ; valeur = Opt.val() ;
				//console.log (choix , valeur);
				var selection = new Object() ;
				for(var i=listIndex; i<totalattribut; i++){ selection["attribute"+i] =[] ; }
				var j=0;

				// set the option to show
				$.each(stockable, function(child_id, child_attrib) {
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
				// regenerate the option by selected val() after last index attribute
				for(var i=listIndex; i<totalattribut; i++){
					selectlist = $("#attribute"+i) ;
					orgOptions = original["attribute"+i];
					selectedOptions =$.unique(selection["attribute"+i]) ;
					var auxArr = [];
					$.each(selectedOptions, function( index, orgtext){ auxArr[index] = "<option value=\'" + orgtext+ "\'>" + orgtext + "</option>"; });
					selectlist.empty().append(auxArr.join(\'\'))
					selectlist.find("option:first").attr("selected","selected");
				}
				// get the selected valid values
				for(var i=1 ; i<totalattribut; i++){
					selecteds["attribute"+i] = $("#attribute"+i).val();
				}
				// find the product child id
				 $.each(stockable, function(child_id, attribut) {
					 atrID = (listIndex-1) ;
					if (attribut[ "attribute"+ atrID  ] == valeur ) {
						var i=j=1;
						for(i ; i<totalattribut; i++){
							if (attribut["attribute"+i] != selecteds["attribute"+i]){
								break;
							}
							j++;
							//console.log(selecteds["attribute"+i],attribut["attribute"+i]);
						}
						if (j>totalattribut-2) { found_id = child_id; return } // we have found the selected child
					}
				   if (found_id >0 ) return;
				 });
				// recalculate the price by found product child id;
				formProduct = Opt.parents(".productdetails-view").find(".product");
				virtuemart_product_id = formProduct.find(\'input[name="virtuemart_product_id[]"]\').val();
				//formProduct.find("#stockableChild").remove();
				//formProduct.append(\'<input id="stockableChild" type="hidden" value="\'+customfield_id[found_id]+\'" name="customPrice['.$row.'][\'+found_id+\']">\');
				$.setproducttype(formProduct,virtuemart_product_id);
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

		$row++;
		return true;
	}

	function plgVmOnDisplayProductFE( $product, &$idx,&$group){}
	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnViewCartModule()
	 * @author Patrick Kohl
	 */
	function plgVmOnViewCartModule( $product,$productCustom, $row,&$html) {
		if (!$plgParam = $this->GetPluginInCart($product)) return false ;
// 		$html  = '';
		foreach ($plgParam as $attributes) $html .='<span>'.$attributes.'</span>';
		// $html .='<span>'.$param->Morecomment.'</span>';

		return true;
	}

	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnViewCart()
	 * @author Patrick Kohl
	 */
	function plgVmOnViewCart($product,$productCustom, $row,&$html) {
		if (!$plgParam = $this->GetPluginInCart($product)) return false ;
		$html  .= '<div>';
		foreach ($plgParam as $attributes) $html .='<span>'.$attributes.'</span>';
		// $html .='<span>'.$param->Morecomment.'</span>';
		$html.='</div>';
		return true;
		//vmdebug('stockable attributs',$plgParam);
	}

	/**
	 *
	 * vendor order display BE
	 */
	function plgVmDisplayInOrderBE($item,$productCustom, $row, $plgParam) {
		if ($productCustom->custom_value != $this->_name) return null;
		$html  = '<div>';
		foreach ($plgParam as $attributes) $html .='<span>'.$attributes.'</span>';
		// $html .='<span>'.$param->Morecomment.'</span>';
		return $html.'</div>';
	}

	/**
	 *
	 * shopper order display FE
	 */
	function plgVmDisplayInOrderFE($item,$productCustom, $row, $plgParam) {
		if ($productCustom->custom_value != $this->_name) return null;
		$html  = '<div>';
		foreach ($plgParam as $attributes) $html .='<span>'.$attributes.'</span>';
		// $html .='<span>'.$param->Morecomment.'</span>';
		return $html.'</div>';
	}

	function getChilds($child_id = null) {

		$db = JFactory::getDBO();
		$q = 'SELECT CONCAT_WS(" : ",`product_name`,`product_sku`) as product_name,`virtuemart_product_id` as id, `product_in_stock` as stock FROM `#__virtuemart_products_'.VMLANG.'` as l '
		. ' JOIN `#__virtuemart_products` AS p using (`virtuemart_product_id`)'
		. 'WHERE `product_parent_id` ='.(int)$child_id ;
		$db->setQuery($q);

		$result = $db->loadObjectList();

		if (!($result)) {
			//JError::raiseWarning(500, $db->getErrorMsg());
			return array();
		} else return $result ;
	}
	function getFieldId($virtuemart_product_id, $chil_id ) {

		$db = JFactory::getDBO();
		$q = 'SELECT cf.* FROM `#__virtuemart_product_customfields` as cf JOIN `#__virtuemart_customs` as c ON `c`.`virtuemart_custom_id` = cf.`virtuemart_custom_id` AND c.`field_type`="G"
			WHERE cf.`virtuemart_product_id` ='.(int)$virtuemart_product_id.' and cf.custom_value='.(int)$chil_id ;
		$db->setQuery($q);
		$result = $db->loadObject();
		if (!($result)) {
			//JError::raiseWarning(500, $db->getErrorMsg());
			return false;
		} else return $result ;
	}
	function getValideId($chil_id ) {

		$db = JFactory::getDBO();
		$q = 'SELECT `product_in_stock` FROM `#__virtuemart_products` WHERE `published`=1 and `virtuemart_product_id` ='.(int)$chil_id ;
		$db->setQuery($q);
		$stock = $db->loadResult();
		if (VmConfig::get('show_out_of_stock_products',0) and $stock >0 ) return true ;
		else if ($stock !==NULL) return true ;
		return false ;
	}

	public function plgVmGetProductStockToUpdateByCustom($item, $pluginParam, $productCustom) {

		if ($productCustom->custom_element !== $this->_name) return $item ;
		if ( !empty($productCustom) ) {

			$fields = json_decode($productCustom->custom_param,true);

			// find the selected child
			foreach ( $fields['child'] as $childId => $child ) {

				$count = 0;
				$total = count($pluginParam);
				foreach ( $pluginParam['stockable'] as $key => $attribute ) {
					if  ($child[$key] !== $attribute) {

						break;
					} else {
					    $count++;
					}

				}
				// child found
				if ($total == $count) {
					$item->virtuemart_product_id = $childId;
					vmdebug ('$childId',$childId);
					return $item ;

				}
			}
		}
		return $item ;
		// echo $item[0]->virtuemart_product_id;jexit();
	}

	/**
	 * We must reimplement this triggers for joomla 1.7
	 * vmplugin triggers note by Max Milbers
	 */
	protected function plgVmOnStoreInstallPluginTable($psType) {

// 		return $this->onStoreInstallPluginTable($psType);
	}

	function plgVmDeclarePluginParamsCustom($psType,$name,$id, &$data){
		return $this->declarePluginParams($psType, $name, $id, $data);
	}

	function plgVmSetOnTablePluginParamsCustom($name, $id, &$table){
		return $this->setOnTablePluginParams($table);
	}

	/**
	 * Custom triggers note by Max Milbers
	 */
	function plgVmOnDisplayEdit($virtuemart_custom_id,&$customPlugin){
		return $this->onDisplayEditBECustom($virtuemart_custom_id,$customPlugin);
	}

	public function plgVmCalculateCustomVariant($product, &$productCustomsPrice,$selected,$row){

		$customVariant = $this->getCustomVariant($product, $productCustomsPrice,$selected,$row);

		if ( !empty($customVariant) ) {

			$fields = json_decode($productCustomsPrice->custom_param,true);
			// find the selected child
			foreach ( $fields['child'] as $childId => $child ) {
				$count = 0;
				$total = count($customVariant);
				foreach ( $customVariant as $key => $attribute ) {
					if  ($child[$key] !== $attribute) {

						break;
					} else {$count++;
					}

				}
				// child found
				if ($total == $count) {
					$field->custom_price = $child['custom_price'];
					break;
				}
			}
		}
	}

	public function plgVmDisplayInOrderCustom(&$html,$item, $param,$productCustom, $row ,$view='FE'){
		$this->plgVmDisplayInOrderCustom($html,$item, $param,$productCustom, $row ,$view);
	}

	public function plgVmCreateOrderLinesCustom(&$html,$item,$productCustom, $row ){
		$this->createOrderLinesCustom($html,$item,$productCustom, $row );
	}


}

// No closing tag