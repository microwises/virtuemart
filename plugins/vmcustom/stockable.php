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

class plgVmCustomStockable extends vmCustomPlugin {

	var $_pname;


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
	function plgVmCustomStockable() {
		$this->_pname = basename(__FILE__, '.php');
		$this->_createTable();
		parent::__construct();
	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 * @author Patrick Kohl
	 */
	protected function _createTable()
	{
		$scheme = DbScheme::get_instance();
		$scheme->create_scheme('#__virtuemart_product_custom_'.$this->_pname);
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
			,'stockable' => array (
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
		if ($field->custom_value != $this->_pname) return '';
		$html ='';
		if (!$childs = $this->getChilds($product_id) ) $html .='<DIV>'.JTEXT::_('VMCUSTOM_STOCKABLE_NO_CHILD').'</DIV>';
		$db = JFactory::getDBO();
		$db->setQuery('SELECT `virtuemart_custom_id` FROM `#__virtuemart_customs` WHERE field_type="G" ');
		$group_custom_id = $db->loadResult();
		$plgParam = $this->getVmCustomParams($field->virtuemart_custom_id);
		//print_r($plgParam);
		$html .='<span style="width:20px; display: inline-block;"> </span>';

		for ($i = 1; $i<5 ;$i++) { 
			$listname = $plgParam->get('selectname'.$i,'');
			$html .=' <span style="width:98px; display: inline-block;color:#000;overflow:hidden;">'.JTEXT::_($listname).'</span>';
		}
		$html .=' <span style="width:98px; display: inline-block;color:#000;">'. JText::_('COM_VIRTUEMART_CART_PRICE') .'</span>';
		foreach ($childs as $child ) { 
			if (!array_key_exists($child->id, (array)$param)) $param[$child->id]['is_variant'] = 1;
			if ($param[$child->id]['is_variant'] ) $checked='checked';
			else $checked ='';
		//$html .= JHTML::_('select.genericlist', $childlist, 'custom_param['.$row.'][child_id]','','virtuemart_product_id','product_name',$param['child_id'],false,true);

			$html .='<div class="stockable">' ;
			$html .='	<input type="hidden"  value="0" name="custom_param['.$row.']['.$child->id.'][is_variant]">';
			$html .='	<input type="checkbox" '.$checked.' title="use it as variant" value="1" name="custom_param['.$row.']['.$child->id.'][is_variant]">';
			for ($i = 1; $i<5 ;$i++) { 
				$option = array();
				$tmpOptions = str_replace( "\r", "" ,$plgParam->get('selectoptions'.$i,'')); 
				if ($listoptions = explode("\n",$tmpOptions ) ) {
					foreach ($listoptions as $key => $val) $option[] = JHTML::_('select.option',JText::_( $val ) , $val  );
					if (empty($param[$child->id]['attribute'.$i])) {
						$param[$child->id]['attribute'.$i] ='';
					}
					if ($listoptions[0] == '') $html .= ' <span style="width:98px; display: inline-block;color:#000;">'.JText::_('VMCUSTOM_STOCKABLE_NO_OPTION') .'</span>';
					else $html .= JHTML::_('select.genericlist', $option, 'custom_param['.$row.']['.$child->id.'][attribute'.$i.']','style="width:100px !important;"','text','value',$param[$child->id]['attribute'.$i],false,true)."\n";
				}

			}
			$html .='<input  type="hidden" name="field[c'.$child->id.'][custom_value]" value="'.$child->id.'">';
			if (!$customfield = $this->getFieldId($product_id, $child->id) ) $price ='' ;
			else $price = $customfield->custom_price ;
			$html .='<input style="width:98px; display: inline-block;" type="text" name="field[c'.$child->id.'][custom_price]" value="'.$price.'">';
			$html .='<input type="hidden" name="field[c'.$child->id.'][field_type]" value="G">';
			$html .='<input type="hidden" name="field[c'.$child->id.'][virtuemart_custom_id]" value="'.$group_custom_id.'">';
			
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
		// $html  ='<input type="text" value="'.$param['custom_name'].'" size="10" name="custom_param['.$row.'][custom_name]"> ';
		// $html .='<input type="text" value="'.$param['custom_size'].'" size="10" name="custom_param['.$row.'][custom_size]">';
		//$html .=JTEXT::_('VMCUSTOM_TEXTINPUT_NO_CHANGES_BE');

		return $html ;
	}
	/**
	 * @ idx plugin index
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::onDisplayProductFE()
	 * @author Patrick Kohl
	 */
	function onDisplayProductFE($field, $param,$product,$idx) {
		// default return if it's not this plugin
		if ($field->custom_value != $this->_pname) return '';
		//if (!$childs = $this->getChilds($product_id) ) return ; 

		$plgParam = $this->getVmCustomParams($field->virtuemart_custom_id);
		$html='<br>';
		$customfield_id = array();
		$selects = array();
		$js = array();
	
		foreach($param as $child_id => $attribut) {
			if ($customfield_id = $this->getFieldId($product->virtuemart_product_id, $child_id)) {
				$js[]= '"'.$child_id.'" :'.$customfield_id->virtuemart_customfield_id;
				if ($attribut['is_variant']) { 
					unset ($attribut['is_variant']);
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
			}
		}
		$i = 1;
		foreach ($selects as $keys =>$options) {
			if (($listname = $plgParam->get('selectname'.$i,'')) ) { 
				$option = array();
				foreach ($options as $key => $val) if (!empty($val)) $option[] = JHTML::_('select.option',JText::_( $val ) , $val  );
				if (!empty($option)) {
					$html .='<div style="width:200px;"><span style="vertical-align: top;width:98px; display: inline-block;color:#000;">'.JTEXT::_($listname).'</span>';
					$html .= JHTML::_('select.genericlist', $option,$keys ,'class="attribute_list" style="width:100px !important;"','text','value',$options[0],false,true)."</div>\n";
				} else $html .='<input id="'.$keys.'" class="attribute_list" type="hidden" value="'.$val.'" name="'.$keys.'">' ;
			}
			$i++;
		}
		static $stockablejs;
		// preventing 2 x load javascript
		if ($stockablejs) return $html;
		$stockablejs = true ;
		// TODO ONE PARAM IS MISSING
		$document = JFactory::getDocument();
		$document->addScriptDeclaration('
		jQuery( function($) {
			var customfield_id = {'. implode(',' , $js ) .'};
			var stockable =$.parseJSON(\'' .$field->custom_param. '\') ;
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
					if (child_attrib[choix] == valeur && child_attrib["is_variant"] == 1) { 
						$.each(child_attrib, function(index, value) {
							if (index != "is_variant" && index > choix)
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
					if (attribut.is_variant == 1 && attribut[ "attribute"+ atrID  ] == valeur ) {
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
				formProduct.find("#stockableChild").remove();
				formProduct.append(\'<input id="stockableChild" type="hidden" value="\'+customfield_id[found_id]+\'" name="customPrice['.$idx.'][\'+found_id+\']">\');
				$.setproducttype(formProduct,virtuemart_product_id);
			}
		}); 
		');

		// 'custom_param['.$keys.']'
		//print_r($param);
		//dump($param);
//"is_variant":"1","attribute1":"Red","attribute2":"20 cm","attribute3":"10","attribute4":"10"

		//echo $plgParam->get('custom_info');
		// Here the plugin values
		//$html =JTEXT::_($param['custom_name']) ;
		//$html.=': <input type="text" value="" size="'.$param['custom_name'].'" name="customPlugin['.$idx.'][comment]"><br />';
        return $html;
    }

	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::onViewCartModule()
	 * @author Patrick Kohl
	 */
	function onViewCartModule( $product,$param,$productCustom, $row) {
		// if ($param->comment) return 'commented';
		// return 'not commented';
		return '';
    }

	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::onViewCart()
	 * @author Patrick Kohl
	 */
	function onViewCart($product, $param,$productCustom, $row) {
		// $html  = '<div>';
		// $html .='<span>'.$param->comment.'</span>';
		//$html .='<span>'.$param->Morecomment.'</span>';
		// return $html.'</div>';
		return '';
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
		return '';//$param;
    }
	
	/**
	 *
	 * vendor order display BE
	 */
	function onViewOrderBE($item, $param,$productCustom, $row) {
		// $html  = '<div>';
		// $html .='<span>'.$param->comment.'</span>';
		//$html .='<span>'.$param->Morecomment.'</span>';

		// return $html.'</div>';
		return '';
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
	
	function plgVmOnOrder($product) {

		$dbValues['virtuemart_product_id'] = $product->virtuemart_product_id;
		$dbValues['stockable'] = $this->_virtuemart_paymentmethod_id;
		$this->writeCustomData($dbValues, '#__virtuemart_product_custom_' . $this->_pname);
	}

	
	/**
	 * (depredicate)
	 */
	function plgVmOnOrderShowFE($product,$order_item_id) {
		//$dbValues['virtuemart_product_id'] = $product->virtuemart_product_id;
		//$dbValues['stockable'] = $this->_virtuemart_paymentmethod_id;
		//$this->writePaymentData($dbValues, '#__virtuemart_product_custom_' . $this->_pname);
				$db = JFactory::getDBO();
		$q = 'SELECT * FROM `#__virtuemart_product_custom_' . $this->_pname . '` '
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
	function getChilds($child_id = null) {

		$db = JFactory::getDBO();
		$q = 'SELECT CONCAT_WS(" : ",`product_name`,`product_sku`) as product_name,`virtuemart_product_id` as id, `product_in_stock` as stock FROM `#__virtuemart_products` '
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

}

// No closing tag