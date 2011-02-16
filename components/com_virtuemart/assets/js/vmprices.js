jQuery(document).ready(function() {

	jQuery("form[id^='addtocartproduct']").each(function(){
		var formId = jQuery(this).attr("id");
		jQuery("[name='addtocart']").click(function(e) { 
		e.preventDefault();
		sendtocart(formId);
		});
		jQuery("[name='setproducttype']").click(function(e) { 
		e.preventDefault();
		setproducttype(formId);
		});
	});
});

function sendtocart(formId){
	
	var id = formId;

	if (id != NaN) {
		jQuery.post('index.php?option=com_virtuemart&view=cart&task=addJS', jQuery(id).serialize(), 
	
		function(newPrices, textStatus) {
//			alert(newPrices+' and '+textStatus);
			if(newPrices==1){
				alert('Product added to cart ');
			}else{
				alert('Product not added to cart, may out of stock ');
			}
		});
	}
	return false; // prevent to reload the page

};

function setproducttype(formId){

	var id = formId;

	jQuery.getJSON('index.php?option=com_virtuemart&view=productdetails&task=recalculate',jQuery(id).serialize(),
	
		function(newPrices, textStatus) {
//			jQuery('#basePrice').html(newPrices.basePrice);
			togglePriceVisibility(newPrices.basePrice,'#basePrice');
			togglePriceVisibility(newPrices.variantModification,'#variantModification');
			togglePriceVisibility(newPrices.basePriceVariant,'#basePriceVariant');
			togglePriceVisibility(newPrices.basePriceWithTax,'#basePriceWithTax');
			togglePriceVisibility(newPrices.discountedPriceWithoutTax,'#discountedPriceWithoutTax');
			togglePriceVisibility(newPrices.salesPriceWithDiscount,'#salesPriceWithDiscount');
			togglePriceVisibility(newPrices.salesPrice,'#salesPrice');
			togglePriceVisibility(newPrices.priceWithoutTax,'#priceWithoutTax');
			togglePriceVisibility(newPrices.discountAmount,'#discountAmount');
			togglePriceVisibility(newPrices.taxAmount,'#taxAmount');
			togglePriceVisibility(newPrices.variantModification,'#variantModification'); 
		});
	return false; // prevent to reload the page
};

function togglePriceVisibility(newPrice,divname){
	div = jQuery(divname+"D");
	span = jQuery(divname);
	if(newPrice!=0){
		div.show();
		span.show();
		span.html(newPrice);
	} else {
		span.html(0);
		div.hide();
		span.hide();
	}
}

function add(nr) {
	var currentVal = parseInt(jQuery('#quantity'+nr).val());
	if (currentVal != NaN) {
		jQuery('#quantity'+nr).val(currentVal + 1);
	}
};

function minus(nr) {
	var currentVal = parseInt(jQuery('#quantity'+nr).val());
	if (currentVal != NaN && currentVal>0) {
		jQuery('#quantity'+nr).val(currentVal - 1);
	}
};