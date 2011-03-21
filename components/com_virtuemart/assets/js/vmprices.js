
(function($) {
	$.fn.product = function(options) {
		
		this.each(function(){
			var cart = $(this),
			addtocart = cart.find('.addtocart'),
			plus   = cart.find('.quantity-plus'),
			minus  = cart.find('.quantity-minus'),
			select = cart.find('select'),
			product_id = cart.find('input[name="product_id[]"]').val(),
			quantity = cart.find('.quantity-input');
			//console.log(product_id);
			
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
			select.change(function() {
				setproducttype(cart,product_id);
			});
		});

		function sendtocart(form){

			var datas = form.serialize();
		//	$.post('index.php?option=com_virtuemart&view=cart&task=addJS&format=raw', datas, 
			$.post('index.php?option=com_virtuemart&controller=cart&task=addJS', datas, 
			
		function(datas, textStatus) {

				if(datas!=0){
					var value = form.find('.quantity-input').val() ;
					var txt = value+" "+form.find(".pname").val()+' '+vmCartText;
					// $("#productCartModule").html(datas+"<div>"+txt+"</div>"); This is for module . not implemented @ Patrick Kohl
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

			var datas = form.serialize(),
			prices = $("#productPrice"+id);
			prices.fadeTo("slow", 0.33);
			$.getJSON('index.php?option=com_virtuemart&view=productdetails&task=recalculate&format=json',encodeURIComponent(datas),
			
				function(datas, textStatus) {
					var pid= '';
					prices.fadeTo("slow", 1);
		//	toggle the div Prices
					for(key in datas) {
						var value = datas[key];
						pid= prices.find(".Price"+key);
						togglePriceVisibility(value,pid);
					}
				});
			return false; // prevent to reload the page
		};

		function togglePriceVisibility(newPrice,span){
			
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
});
