jQuery(document).ready(function() {

		//$('element').unbind('click', myhandler).bind('click',myhandler); 
		//$('#foo').bind('click', function()
	jQuery("[name='addtocart']").bind('click',function(e) { 
		e.preventDefault();
		sendtocart(jQuery(this).parents("form"));
	});
	jQuery("[name='setproducttype']").click(function(e) { 
		e.preventDefault();
		setproducttype(jQuery(this).parents("form"),jQuery(this).attr('id'));
	});
	
	
	jQuery("form[id^=addtocartproduct]").find('select').change(function(e) { 
		e.preventDefault();
		str = jQuery(this).parents("form").attr('id');
		str = str.substring(16);
		setproducttype(jQuery(this).parents("form"),str);
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
	return false;
};

function setproducttype(form,id){

	var datas = jQuery(form).serialize();
	jQuery("#productPrice"+id).fadeTo("slow", 0.33);
	jQuery.getJSON('index.php?option=com_virtuemart&view=productdetails&task=recalculate&format=json',encodeURIComponent(datas),
	
		function(datas, textStatus) {
			var pid= '';
			jQuery("#productPrice"+id).fadeTo("slow", 1);
//	toggle the div Prices
			for(key in datas) {
				var value = datas[key];
				pid= ("#productPrice"+id+" div .Price"+key);
				togglePriceVisibility(value,pid);
			}
		});
	return false; // prevent to reload the page
};

function togglePriceVisibility(newPrice,spanProduct){
	span = jQuery(spanProduct);
	if(newPrice!=0){
		span.show();
		span.html(newPrice);
	} else {
		span.html(0);
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