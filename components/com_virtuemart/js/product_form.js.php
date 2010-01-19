<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/
mm_showMyFileName( __FILE__ );
?>
function toggleDisable( elementOnChecked, elementDisable, disableOnChecked ) {
	try {
		if( !disableOnChecked ) {
			if(elementOnChecked.checked==true) {
				elementDisable.disabled=false;
			}
			else {
				elementDisable.disabled=true;
			}
		}
		else {
			if(elementOnChecked.checked==true) {
				elementDisable.disabled=true;
			}
			else {
				elementDisable.disabled=false;
			}
		}
	}
	catch( e ) {}
}
// borrowed from OSCommerce with small modifications.
// All rights reserved.
var tax_rates = new Array();
<?php
foreach( $tax_rates as $tax_rate_id => $tax_rate ) {
	echo "tax_rates[\"$tax_rate_id\"] = $tax_rate;\n";
}
?>
function doRound(x, places) {
	return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
}

function getTaxRate() {
	var selected_value = document.adminForm.product_tax_id.selectedIndex;
	var parameterVal = document.adminForm.product_tax_id[selected_value].value;

	if ( (parameterVal > 0) && (tax_rates[parameterVal] > 0) ) {
		return tax_rates[parameterVal];
	} else {
		return 0;
	}
}

function updateGross() {
	if( document.adminForm.product_price.value != '' ) {
		var taxRate = getTaxRate();

		var r = new RegExp("\,", "i");
		document.adminForm.product_price.value = document.adminForm.product_price.value.replace( r, "." );

		var grossValue = document.adminForm.product_price.value;

		if (taxRate > 0) {
			grossValue = grossValue * (taxRate + 1);
		}

		document.adminForm.product_price_incl_tax.value = doRound(grossValue, 5);
	}
}

function updateNet() {
	if( document.adminForm.product_price_incl_tax.value != '' ) {
		var taxRate = getTaxRate();

		var r = new RegExp("\,", "i");
		document.adminForm.product_price_incl_tax.value = document.adminForm.product_price_incl_tax.value.replace( r, "." );

		var netValue = document.adminForm.product_price_incl_tax.value;

		if (taxRate > 0) {
			netValue = netValue / (taxRate + 1);
		}

		document.adminForm.product_price.value = doRound(netValue, 5);
	}
}

function updateDiscountedPrice() {
	if( document.adminForm.product_price.value != '' ) {
		try {
			var selected_discount = document.adminForm.product_discount_id.selectedIndex;
			var discountCalc = document.adminForm.product_discount_id[selected_discount].id;
			<?php if( PAYMENT_DISCOUNT_BEFORE == '1' ) : ?>
			var origPrice = document.adminForm.product_price.value;
			<?php else : ?>
			var origPrice = document.adminForm.product_price_incl_tax.value;
			<?php endif; ?>

			if( discountCalc ) {
				eval( 'var discPrice = ' + origPrice + discountCalc );
				if( discPrice != origPrice ) {
					document.adminForm.discounted_price_override.value = discPrice.toFixed( 2 );
				} else {
					document.adminForm.discounted_price_override.value = '';
				}
			}
		}
		catch( e ) { }
	}
}
function toggleProductList( enable ) {
	if(enable) {
    	document.getElementById('list_style0').disabled = false;
       document.getElementById('list_style0').checked = true;
    	document.getElementById('list_style1').disabled = false;
       document.getElementById('display_headers').disabled = false;
    	document.getElementById('product_list_child').disabled = false;
       document.getElementById('product_list_type').disabled = false;
	}
    else {
    	document.getElementById('list_style0').disabled = true;
    	document.getElementById('list_style1').disabled = true;
       document.getElementById('display_headers').disabled = true;
    	document.getElementById('product_list_child').disabled = true;
       document.getElementById('product_list_type').disabled = true;
       document.getElementById('display_headers').checked = false;
    	document.getElementById('product_list_child').checked = false;
       document.getElementById('product_list_type').checked = false;
	}
}
updateGross();
updateDiscountedPrice();
<?php
if( @$_REQUEST['no_menu'] != '1') {
	?>
	toggleDisable( document.adminForm.product_full_image_action[1], document.adminForm.product_thumb_image, true );
	<?php
}
?>
var productSearchField = function(){

    var relds = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            url: 'index2.php?option=com_virtuemart&page=product.ajax_tools&task=getproducts&ajax_request=1&func=&no_menu=1&only_page=1&no_html=1&product_id=<?php echo $product_id ?>',
            method: 'GET' }),
        reader: new Ext.data.JsonReader({
            root: 'products',
            totalProperty: 'totalCount',
            id: 'product_id'
	        }, [
	            {name: 'product'},
	            {name: 'category'},
	            {name: 'product_id'},
				{name: 'product_sku'}
	        ])
    });
    // Custom rendering Template
    var resultTpl = new Ext.XTemplate( '<tpl for="."><div class="x-combo-list-item">{product_sku}, {product} / {category}</div></tpl>' );
    relatedSelection = document.getElementById('relatedSelection');
    related_products = document.adminForm.related_products;
    var relProdSearch = new Ext.form.ComboBox({
    	applyTo: 'relatedProductSearch',
        store: relds,
        title: '<?php echo addslashes(JText::_('VM_PRODUCT_SELECT_ONE_OR_MORE')); ?>',
        displayField:'product',
        typeAhead: false,
        loadingText: '<?php echo addslashes(JText::_('VM_PRODUCT_SEARCHING')); ?>',
        width: 270,
        minListWidth: 270,
        pageSize:15,
        emptyText: "<?php  echo addslashes(JText::_('VM_SEARCH_TITLE')); ?>",
        tpl: resultTpl,
        onSelect: function(record) {
        	for(var i=0;i<relatedSelection.options.length;i++) {
        		if(relatedSelection.options[i].value==record.id) {
        			return;
        		}
        	}
        	o = new Option( record.data.product, record.id );
        	relatedSelection.options[relatedSelection.options.length] = o;
        	if( related_products.value != '') {
        		related_products.value += '|' + record.id;
        	} else {
        		related_products.value += record.id;
        	}
        }
    });

};
var categorySearchField = function(){

    var relds = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            url: 'index2.php?option=com_virtuemart&page=product.ajax_tools&task=getcategories&ajax_request=1&func=&no_menu=1&only_page=1&no_html=1',
            method: 'GET'
        }),
        reader: new Ext.data.JsonReader({
            root: 'categories',
            totalProperty: 'totalCount',
            id: 'category_id'
	        }, [
	            {name: 'category'},
	            {name: 'category_id'}
	        ])
    });

    // Custom rendering Template
    var resultTpl = new Ext.XTemplate(
    	'<tpl for="."><div class="x-combo-list-item">{category} (ID: {category_id})</div></tpl>'
    );
    relatedCatSelection = document.getElementById('relatedCatSelection');
    category_ids = document.adminForm.category_ids;
    var relProdSearch = new Ext.form.ComboBox({
    	applyTo: "categorySearch",
        store: relds,
        title: '<?php echo addslashes(JText::_('VM_PRODUCT_SELECT_ONE_OR_MORE')); ?>',
        displayField:'category',
        typeAhead: false,
        loadingText: '<?php echo addslashes(JText::_('VM_PRODUCT_SEARCHING')); ?>',
        width: 170,
        minListWidth: 170,
        pageSize:15,
        emptyText: "<?php  echo addslashes(JText::_('VM_SEARCH_TITLE')); ?>",
        tpl: resultTpl,
        onSelect: function(record) {
        	for(var i=0;i<relatedCatSelection.options.length;i++) {
        		if(relatedCatSelection.options[i].value==record.id) {
        			return;
        		}
        	}
        	o = new Option( record.data.category, record.id );

        	relatedCatSelection.options[relatedCatSelection.options.length] = o;
        	if( category_ids.value != '') {
        		category_ids.value += '|' + record.id;
        	} else {
        		category_ids.value += record.id;
        	}
        }
    });

};
if( Ext.isIE ) {
	Ext.EventManager.addListener( window, 'load', productSearchField );
	if( Ext.get("categorySearch") ) {
		Ext.EventManager.addListener( window, 'load', categorySearchField );
	}
}
else {
	Ext.onReady( productSearchField );
	if( Ext.get("categorySearch") ) {
		Ext.onReady( categorySearchField );
	}
}
function removeSelectedOptions(from, hiddenField ) {
	field = eval( "document.adminForm." + hiddenField );
	// Delete them from original
	var newOptions = [];
	for (var i=(from.options.length-1); i>=0; i--) {
		var o = from.options[i];
		if (o.selected) {
			from.options[i] = null;
		} else {
			newOptions.push(o.value);
		}
	}
	field.value = newOptions.join('|');
}
function loadProductTypeForm(ptype_id) {
	Ext.get("ProductTypeFormContainer").load( {
		url: "index.php",
	    params: {option: "com_virtuemart",
	    				product_type_id: ptype_id,
	    				page: "product.ajax_tools",
	    				task: "getProductTypeForm",
	    				no_html: 1, only_page: 1, no_menu: 1, format: "raw"
	    				}
	})
}
