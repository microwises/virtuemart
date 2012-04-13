if(typeof Virtuemart==="undefined")

var Virtuemart = {
	setproducttype : function(form,id){
		var $ = jQuery ;
		form.view = null ;
		var datas = form.serialize(),
		prices = $("#productPrice"+id);
		datas = datas.replace("&view=cart", "");
		prices.fadeTo("fast", 0.75);
		$.getJSON(window.vmSiteurl+'index.php?option=com_virtuemart&nosef=1&view=productdetails&task=recalculate&format=json'+window.vmLang,encodeURIComponent(datas),
			function(datas, textStatus) {
				prices.fadeTo("fast", 1);
				// refresh price
				for(key in datas) {
					var value = datas[key];
					if (value!=0) prices.find("span.Price"+key).show().html(value);
					else prices.find(".Price"+key).html(0).hide();
				}
			});
		return false; // prevent reload
	},
	productUpdate : function(mod) {
		var $ = jQuery ;
		$.ajaxSetup({ cache: false })
		$.getJSON(window.vmSiteurl+"index.php?option=com_virtuemart&nosef=1&view=cart&task=viewJS&format=json"+window.vmLang,
			function(datas, textStatus) {
				if (datas.totalProduct >0) {
					mod.find(".vm_cart_products").html("");
					$.each(datas.products, function(key, val) {
						$("#hiddencontainer .container").clone().appendTo(".vmCartModule .vm_cart_products");
						$.each(val, function(key, val) {
							if ($("#hiddencontainer .container ."+key)) mod.find(".vm_cart_products ."+key+":last").html(val) ;
						});
					});
					mod.find(".total").html(datas.billTotal);
					mod.find(".show_cart").html(datas.cart_show);
				}
				mod.find(".total_products").html(datas.totalProductTxt);
			}
		);
	},
	sendtocart : function (form){
		var $ = jQuery ;
		if (Virtuemart.addtocart_popup ==1) {
			$.ajaxSetup({ cache: false })
			var datas = form.serialize();
			$.getJSON(vmSiteurl+'index.php?option=com_virtuemart&nosef=1&view=cart&task=addJS&format=json'+vmLang,encodeURIComponent(datas),
			function(datas, textStatus) {
				if(datas.stat ==1){
					//var value = form.find('.quantity-input').val() ;
					var txt = form.find(".pname").val()+' '+vmCartText;
											$.facebox.settings.closeImage = closeImage;
											$.facebox.settings.loadingImage = loadingImage;
											$.facebox.settings.faceboxHtml = faceboxHtml;
					$.facebox({ text: datas.msg +"<H4>"+txt+"</H4>" }, 'my-groovy-style');
				} else if(datas.stat ==2){
					var value = form.find('.quantity-input').val() ;
					var txt = form.find(".pname").val();
											$.facebox.settings.closeImage = closeImage;
											$.facebox.settings.loadingImage = loadingImage;
											$.facebox.settings.faceboxHtml = faceboxHtml;
					$.facebox({ text: datas.msg +"<H4>"+txt+"</H4>" }, 'my-groovy-style');
				} else {
											$.facebox.settings.closeImage = closeImage;
											$.facebox.settings.loadingImage = loadingImage;
											$.facebox.settings.faceboxHtml = faceboxHtml;
					$.facebox({ text: "<H4>"+vmCartError+"</H4>"+datas.msg }, 'my-groovy-style');
				}
				if ($(".vmCartModule")[0]) {
					Virtuemart.productUpdate($(".vmCartModule"));
				}
			});
			$.ajaxSetup({ cache: true });
		} else {
			form.append('<input type="hidden" name="task" value="add" />');
			form.submit();
		}
	},
	product : function(carts) {
		carts.each(function(){
			var cart = jQuery(this),
			addtocart = cart.find('input.addtocart-button'),
			plus   = cart.find('.quantity-plus'),
			minus  = cart.find('.quantity-minus'),
			select = cart.find('select'),
			radio = cart.find('input:radio'),
			virtuemart_product_id = cart.find('input[name="virtuemart_product_id[]"]').val(),
			quantity = cart.find('.quantity-input');

			addtocart.click(function(e) { 
				Virtuemart.sendtocart(cart);
				return false;
			});
			plus.click(function() {
				var Qtt = parseInt(quantity.val());
				if (Qtt != NaN) {
					quantity.val(Qtt + 1);
				Virtuemart.setproducttype(cart,virtuemart_product_id);
				}
				
			});
			minus.click(function() {
				var Qtt = parseInt(quantity.val());
				if (Qtt != NaN && Qtt>1) {
					quantity.val(Qtt - 1);
				} else quantity.val(1);
				Virtuemart.setproducttype(cart,virtuemart_product_id);
			});
			select.change(function() {
				Virtuemart.setproducttype(cart,virtuemart_product_id);
			});
			radio.change(function() {
				Virtuemart.setproducttype(cart,virtuemart_product_id);
			});
			quantity.keyup(function() {
				Virtuemart.setproducttype(cart,virtuemart_product_id);
			});
		});

	}
};
jQuery.noConflict();
jQuery(document).ready(function($) {

	Virtuemart.product($(".product"));

	$("form.js-recalculate").each(function(){
		if ($(this).find(".product-fields").length) {
			var id= $(this).find('input[name="virtuemart_product_id[]"]').val();
			Virtuemart.setproducttype($(this),id);

		}
	});
});

