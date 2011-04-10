
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
//			$.post('index.php?option=com_virtuemart&controller=cart&task=addJS', datas, 
			$.getJSON('index.php?option=com_virtuemart&view=cart&task=addJS&format=json',encodeURIComponent(datas),
				function(datas, textStatus) {
					if(datas.stat !=0){
						var value = form.find('.quantity-input').val() ;
						var txt = value+" "+form.find(".pname").val()+' '+vmCartText;
//						$("#productCartModule").html(datas+"<div>"+txt+"</div>"); This is for module . not implemented @ Patrick Kohl
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
						jQuery.ajaxSetup({ cache: false })
						$().productUpdate();
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
	jQuery.getJSON("index.php?option=com_virtuemart&view=cart&task=viewJS&format=json",
		function(datas, textStatus) {
						
			if (datas.totalProduct >0) {
				product = productDisplay (datas.products) ;
				// jQuery(".vmCartModule .products").html(product);
				
				jQuery(".vmCartModule .total").html(datas.billTotal);
				jQuery(".vmCartModule .show_cart").html(datas.cart_show);
				//jQuery(".vmCartModule .ajax_msg").html(datas.ajax_msg+" "+cart_add_to);
			}
			jQuery(".vmCartModule .total_products").html(datas.totalProductTxt);
			/*if (datas.view == 0 ) { 
				jQuery.facebox({ text: "<H4>"+datas.ajax_msg+" "+cart_add_to+"</H4><div class=\'showcart\' >"+show_cart+"<div>",
					closeImage : closeImage,
					loadingImage : loadingImage ,
					faceboxHtml : faceboxHtml
				}, "my-groovy-style");
			} else {
				 jQuery.facebox({ text: "<H4>"+product+"</H4><div class=\'showcart\' >"+show_cart+"<div>",
					closeImage : closeImage,
					loadingImage : loadingImage ,
					faceboxHtml : faceboxHtml
				}, "my-groovy-style");
			}*/
			jQuery.ajaxSetup({ cache: true });
		});

		function productDisplay (products) {
			
			var items = "";
			$(".vmCartModule .vm_cart_products").html("");
			$.each(products, function(key, val) {
				$("#hiddencontainer .container").clone().appendTo(".vmCartModule .vm_cart_products");
				$.each(val, function(key, val) {
				if ($("#hiddencontainer .container ."+key))
					$(".vmCartModule .vm_cart_products ."+key+":last").html(val) ;
					
				});
			});
			return $(".vmCartModule .vm_cart_products").html();
		}
	}

})(jQuery);

jQuery(document).ready(function() {
	jQuery(".product").product();
});
