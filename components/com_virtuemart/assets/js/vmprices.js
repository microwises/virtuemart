
(function($) {
	$.fn.product = function(options) {
		
		this.each(function(){
			var cart = $(this),
			addtocart = cart.find('input.addtocart-button'),
			plus   = cart.find('.quantity-plus'),
			minus  = cart.find('.quantity-minus'),
			select = cart.find('select'),
			radio = cart.find('input:radio'),
			virtuemart_product_id = cart.find('input[name="virtuemart_product_id[]"]').val(),
			quantity = cart.find('.quantity-input');
			//console.log(virtuemart_product_id);
			
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
				setproducttype(cart,virtuemart_product_id);
			});
			radio.change(function() {
				setproducttype(cart,virtuemart_product_id);
			});
		});

		function sendtocart(form){

			var datas = form.serialize();
			$.getJSON('index.php?option=com_virtuemart&view=cart&task=addJS&format=json',encodeURIComponent(datas),
				function(datas, textStatus) {
					if(datas.stat !=0){
						var value = form.find('.quantity-input').val() ;
						var txt = value+" "+form.find(".pname").val()+' '+vmCartText;
						$.facebox({ text: datas.msg +"<H4>"+txt+"</H4>",
							closeImage : closeImage,
							loadingImage : loadingImage,
							faceboxHtml : faceboxHtml
						}, 'my-groovy-style');
					} else {
						$.facebox({ text: "<H4>"+vmCartError+"</H4>"+datas.msg,
							closeImage : closeImage,
							loadingImage : loadingImage,
							faceboxHtml : faceboxHtml
						}, 'my-groovy-style');
					}
					if ($(".vmCartModule")[0]) {
						$.ajaxSetup({ cache: false })
						$($(".vmCartModule")).productUpdate();
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
	
	$.fn.productUpdate = function() {
	mod = $(this);
	$.getJSON("index.php?option=com_virtuemart&view=cart&task=viewJS&format=json",
		function(datas, textStatus) {
						
			if (datas.totalProduct >0) {
				product = productDisplay (mod , datas.products) ;
				mod.find(".total").html(datas.billTotal);
				mod.find(".show_cart").html(datas.cart_show);
			}
			mod.find(".total_products").html(datas.totalProductTxt);
			$.ajaxSetup({ cache: true });
		});

		function productDisplay (mod ,products) {
			
			var items = "";
			mod.find(".vm_cart_products").html("");
			$.each(products, function(key, val) {
				$("#hiddencontainer .container").clone().appendTo(".vmCartModule .vm_cart_products");
				$.each(val, function(key, val) {
				if ($("#hiddencontainer .container ."+key))
					mod.find(".vm_cart_products ."+key+":last").html(val) ;
					
				});
			});
			return mod.find(".vm_cart_products").html();
		}
	}

})(jQuery);
jQuery.noConflict();
jQuery(document).ready(function() {
	jQuery(".product").product();
});
