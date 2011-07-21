(function($) {

  var methods = {
	tabs : function (cookie) {
		if ($.cookie(cookie) == null) var idx=0;
		else var idx = $.cookie(cookie);
		if (idx == null) idx=0;
		var options = { path: '/', expires: 2},
			list = '<ul id="tabs">';
		this.find('div.tabs').each(
			function(i) { 
				list += '<li style="display:inline;">'+ $(this).attr('title')+'</li>' ;
				$(this).removeAttr('title');
			}
		);
		this.prepend(list+'</ul>');
		this.children('div').hide();
		// select & open menu
		var li = $('#tabs li'),
			div = this.children('div');
		li.eq(idx).addClass('current');
		div.eq(idx).slideDown(1000);

		li.click(
			function () {
				if ($(this).not("current")){
					var idx = li.index(this);
					if (cookie !== "" ) $.cookie(cookie, idx, options);
					oldIndex = $(this).addClass("current").siblings('li.current').removeClass("current").index();
					div.eq(idx).slideDown();
					div.eq( oldIndex ).slideUp();
				}
			}
		);
	},
    accordeon :  function () {
		var idx = $.cookie('accordeon'),
			options = { path: '/', expires: 2},
			div = this.children('div') ,
			h3 = this.children('h3') ;
		if (idx == null) idx=0;
		div.hide();
		h3.eq( idx ).addClass('current');
		div.eq(idx ).show();

		h3.click(
			function () {
				var menu = $(this) ;
				if (menu.not("current")){
					menu.siblings('h3.current').removeClass("current").next().slideUp(200);
					menu.addClass("current").next().slideDown(200);
					$.cookie('accordeon', h3.index(this), options);
				}
			}
		);
	} ,
	media :  function () {
		var page=0,
			max=20,
			container = jQuery("#ImagesContainer");

			var formatTitle = function(title, currentArray, currentIndex, currentOpts) {
				return '<a id="fancybox-left" href="javascript:;" onclick="display(0);" style="display: inline;"><span id="fancybox-left-ico" class="fancy-ico"></span></a><a id="fancybox-right" href="javascript:;" onclick="display(1);" style="display: inline;"><span id="fancybox-right-ico" class="fancy-ico"></span></a><div id="tip7-title">' + (title && title.length ? '<b>' + title + '</b>' : '' ) + ' - <span class="page">Page ' + (page + 1) + '</span></div>';
			}
			
			container.delegate("a.vm_thumb", "click",function(event) {
				jQuery.fancybox({
					"type"		: "image",
					"titlePosition"	: "inside",
					"title"		: this.title,
					"href"		: this.href});
				event.preventDefault();
			});
			jQuery("#dialog" ).delegate(".vm_thumb_image", "click",function(event) {
				event.preventDefault();
				that = jQuery(this);
				
				jQuery(this).clone().appendTo(container).unbind("click").append('<div class="trash"></div><div class="edit-24-grey"><div>');
				that.hide().fadeIn();
			});

			container.delegate(".trash", "click",function() { 
				jQuery(this).closest(".vm_thumb_image").fadeOut("500",function() {jQuery(this).remove()});
			});

			jQuery("#addnewselectimage2").fancybox({
				"hideOnContentClick": false,
				"autoDimensions"	: true,
				//"width": 800,
				"titlePosition"		: "inside",
				"title"		: "Media list",
				"titleFormat"	: formatTitle,
				"onComplete": function() {
					//jQuery("#dialog").css("display","block");
				}
			});

		container.delegate(".edit-24-grey", "click",function() {

			var data = jQuery(this).parent().find("input").val();
			jQuery.getJSON("index.php?option=com_virtuemart&view=media&task=viewJson&format=json&virtuemart_media_id="+data ,
			function(datas, textStatus) { 
				if (datas.msg =="OK") {
					jQuery("#vm_display_image").attr("src", datas.file_root+datas.file_url);
					jQuery("#vm_display_image").attr("alt", datas.file_title);
					jQuery("#file_title").html(datas.file_title);
					jQuery(".adminform [name=file_title]").val(datas.file_title);
					jQuery(".adminform [name=file_description]").val(datas.file_description);
					jQuery(".adminform [name=file_meta]").val(datas.file_meta);
					jQuery(".adminform [name=file_url]").val(datas.file_url);
					jQuery(".adminform [name=file_url_thumb]").val(datas.file_url_thumb);
					jQuery("[name=active_media_id]").val(datas.virtuemart_media_id);
					if (datas.file_url_thumb !== "undefined") { jQuery("#vm_thumb_image").attr("src",datas.file_root+datas.file_url_thumb); }
					else { jQuery("#vm_thumb_image").attr("src","");}
				} else jQuery("#file_title").html(datas.msg);
			});
		}); 

		function submitbutton(pressbutton) {
			jQuery( "#dialog" ).remove();
			submitform(pressbutton);
		
		} 

		var display = function(num) {
			if ( typeof display.page == "undefined" ) {
				display.page = 0;
			}
			if (num === 0 && display.page > 0 ) {
				--display.page 
			} else if (num>0) { ++ display.page}
			jQuery.get("index.php?option=com_virtuemart&view=media&task=viewJson&format=json&start="+display.page ,
				function(data) {
					if (data != "ERROR") {
						jQuery("#dialog").html(data);
						jQuery(".page").text( "Page(s) "+ (display.page+1)) ;
					} else  {
						--display.page ;
						jQuery(".page").text( "No  more results : Page(s) "+ (display.page+1)) ;
					}
					page = display.page;
				}
			);
			
		}
	}
  };

	$.fn.vm2admin = function( method ) {
		
		if ( methods[method] ) {
		  return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
		  return methods.init.apply( this, arguments );
		} else {
		  $.error( 'Method ' +  method + ' does not exist on Vm2 admin jQuery library' );
		}    
	  
	};

})(jQuery);


// load defaut scripts 
 jQuery(document).ready( function() {

	jQuery('#admin-ui-menu').vm2admin('accordeon');
	jQuery('dl#system-message').hide().slideDown(400);
	jQuery('.hasTip').tipTip();
	jQuery('.modal').fancybox();
	// jQuery('#admin-content').jqTransform();

});