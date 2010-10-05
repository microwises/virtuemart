jQuery(document).ready(function() {
	
	var carts = document.getElementsByName('addtocart');
	jQuery(carts).each(function(id) {
		this.type='button';
    	jQuery(this).bind('click', sendtocart);
   	
 	 });
	
	var recalcs = document.getElementsByName('setproducttype');
	jQuery(recalcs).each(function(id) {
		this.type = 'button';
		jQuery(this).bind('click', setproducttype);		
	});

});


function sendtocart(){
	
	var id = parseInt(this.id);

	if (id != NaN) {
		jQuery.post('index.php?option=com_virtuemart&view=cart&task=addJS', jQuery("#addtocartproduct"+id).serialize(), 
	
		function(newPrices, textStatus) {
//			alert(newPrices+' and '+textStatus);
			if(newPrices==1){
				alert('Product added to cart '+id);
			}else{
				alert('Product not added to cart, may out of stock '+id);
			}
		});
	}
};

function setproducttype(){

	var id = parseInt(this.id);

	jQuery.getJSON('index.php?option=com_virtuemart&view=productdetails&task=recalculate',jQuery("#addtocartproduct"+id).serialize(),
	
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