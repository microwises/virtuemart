jQuery(document).ready(function() {

		jQuery("[name='addtocart']").click(function(e) { 
		e.preventDefault();
		sendtocart(jQuery(this).parents("form"));
		});
		jQuery("[name='setproducttype']").click(function(e) { 
		e.preventDefault();
		setproducttype(jQuery(this).parents("form"),jQuery(this).attr('id'));
		});

});

function sendtocart(form){

	     var datas = jQuery(form).serialize();
		jQuery.post('index.php?option=com_virtuemart&view=cart&task=addJS&format=raw', datas, 
	
		function(datas, textStatus) {
			alert(datas+' '+textStatus);
/*			if(datas==1){
				alert(datas);
			}else{
				alert('Product not added to cart, may out of stock ');
			}*/
		});

};

function setproducttype(form,id){

	var datas = jQuery(form).serialize();

	jQuery.getJSON('index.php?option=com_virtuemart&view=productdetails&task=recalculate&format=json',encodeURIComponent(datas),
	
		function(datas, textStatus) {
			var pid= '';
//	toggle the div Prices
			for(key in datas) {
				var value = datas[key];
				pid= ("#productPrice"+id+" div .Price"+key);
				togglePriceVisibility(value,pid);
			}
		});
	return false; // prevent to reload the page
};

function togglePriceVisibility(newPrice,productDiv){
	div = jQuery(productDiv);
	if(newPrice!=0){
		div.show();
		div.html(newPrice);
	} else {
		div.html(0);
		div.hide();
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