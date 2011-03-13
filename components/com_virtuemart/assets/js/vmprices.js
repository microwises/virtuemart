
(function($) {
	$.fn.product = function(options) {
		
		this.each(function(){
		var cart = $(this),
			addtocart = cart.find("[name='addtocart']"),
			plus   = cart.find('.quantity-plus'),
			minus  = cart.find('.quantity-minus'),
			select = cart.find('select'),
			product_id = cart.attr('id').substring(16),
			quantity = cart.find('#quantity'+product_id);
			addtocart.bind('click',function(e) { 
				e.preventDefault();
				sendtocart(cart);
			});
			plus.click(function() {
				currentVal = parseInt(quantity.val());
				if (currentVal != NaN) {
					quantity.val(currentVal + 1);
				}
			});

			minus.click(function() {
				currentVal = parseInt(quantity.val());
				if (currentVal != NaN && currentVal>0) {
					quantity.val(currentVal - 1);
				}
			});
			select.change(function(e) { 
				e.preventDefault();
				setproducttype(cart,product_id);
			});
		});

			function sendtocart(form){

			var datas = $(form).serialize();
		//	$.post('index.php?option=com_virtuemart&view=cart&task=addJS&format=raw', datas, 
			$.post('index.php?option=com_virtuemart&controller=cart&task=addJS', datas, 
			
			function(datas, textStatus) {

				if(datas!=0){
					var value = $(form).find("[name='quantity[]']").val() ;
					var txt = value+" "+$(form).find(".pname").val()+' '+vmCartText;
					$("#productCartModule").html(datas+"<div>"+txt+"</div>");
					$.facebox({ text: datas+"<H4>"+txt+"</H4>",
						closeImage : closeImage,
						loadingImage : loadingImage,
						faceboxHtml : faceboxHtml
						}, 'my-groovy-style');
				} else {
					$.facebox({ text: vmCartError,
					closeImage : closeImage,
					loadingImage : loadingImage,
					faceboxHtml : faceboxHtml
					}, 'my-groovy-style');
				}
			});
			return false;
		};

		function setproducttype(form,id){

			var datas = $(form).serialize();
			$("#productPrice"+id).fadeTo("slow", 0.33);
			$.getJSON('index.php?option=com_virtuemart&view=productdetails&task=recalculate&format=json',encodeURIComponent(datas),
			
				function(datas, textStatus) {
					var pid= '';
					$("#productPrice"+id).fadeTo("slow", 1);
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
			span = $(spanProduct);
			if(newPrice!=0){
				span.show();
				span.html(newPrice);
			} else {
				span.html(0);
				span.hide();
			}
		}



	}


})(jQuery);

jQuery(document).ready(function() {
	jQuery(".product").product();

//Patrick, why is this here? 
// Patrick Kohl : this is the code to update minicart.php in module>>#productCartModule or in slimbox
// whe have no over place to see if cart is update as when you click the button [  addtocart ]
//	jQuery.ajax({
//	url: 'index.php?option=com_virtuemart&view=cart&format=raw',
//	success: function(datas) {
//		jQuery("#productCartModule").html(datas);
//		}
//	});

});



jQuery.noConflict();