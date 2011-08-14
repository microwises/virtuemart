(function($) {

  var methods = {
	tabs : function (cookie) {
		if ($.cookie(cookie) == null) var idx=0;
		else var idx = $.cookie(cookie);
		if (idx == null) idx=0;
		var options = { path: '/', expires: 2},
			list = '<ul id="tabs">';
		var tabscount = this.find('div.tabs').length;
		var tabswidth = 100/tabscount;
		this.find('div.tabs').each(
			function(i) {
				list += '<li style="width:'+tabswidth+'%"><span>'+ $(this).attr('title')+'<span></li>' ;
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
	media :  function (mediatype,total) {
		var page=0,
			max=24,
			container = jQuery(this);
		var pagetotal = Math.ceil(total/max) ;
		console.log (pagetotal+' '+total+' '+max);
		var cache = new Array();

			var formatTitle = function(title, currentArray, currentIndex, currentOpts) {
				var pagination='' ,pagetotal = total/max ;
			if (pagetotal >0) {
				pagination='<span><<</span><span><</span>';
				for (i=0; i<pagetotal; i++) {
					pagination+='<span>'+(i+1)+'</span>';
				}
				pagination+='<span>></span><span>>></span>';
			}
				return '<div class="media-pagination">' + (title && title.length ? '<b>' + title + '</b>' : '' ) + ' '+pagination+'</div>';
			}
			
			jQuery("#fancybox-title" ).delegate(".media-pagination span", "click",function(event) {
				var newPage = $(this).text();
				display(newPage);
				event.preventDefault();
			});			
			container.delegate("a.vm_thumb", "click",function(event) {
				jQuery.fancybox({
					"type"		: "image",
					"titlePosition"	: "inside",
					"title"		: this.title,
					"href"		: this.href
					});
				event.preventDefault();
			});
			jQuery("#dialog" ).delegate(".vm_thumb_image", "click",function(event) {
				event.preventDefault();
				var id = $(this).find('input').val(),ok = 0;
				var inputArray = new Array();
				$('#ImagesContainer input:hidden').each (
					function() { inputArray.push($(this).val()) }
				);
				if ($.inArray(id,inputArray) == -1){
					that = jQuery(this);
					jQuery(this).clone().appendTo(container).unbind("click").append('<div class="trash"></div><div class="edit-24-grey"><div>');
					that.hide().fadeIn();
				}
				
			});

			container.delegate(".trash", "click",function() { 
				jQuery(this).closest(".vm_thumb_image").fadeOut("500",function() {jQuery(this).remove()});
			});

			jQuery("#addnewselectimage2").fancybox({
				"hideOnContentClick": false,
				"autoDimensions"	: true,
				"titlePosition"		: "inside",
				"title"		: "Media list",
				"titleFormat"	: formatTitle,
				"onComplete": function() {
					$('.media-pagination').children().eq(page+3).addClass('media-page-selected');
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

		var display = function(num) {
			if ( typeof this.page == "undefined" ) {
				this.oldPage =this.page = 0;
			}
			switch (num) {
				 case '<':
				if (this.page > 0 ) --this.page ;
				else return ;
				 break;
				 case '>':
				if (this.page < pagetotal-1 ) ++this.page ;
				else return ;
				 break;
				 case '<<':
				 this.page = 0;
				 break;
				 case '>>':
				 this.page = pagetotal-1;
				 break;
				 default :
				 this.page = num-1 ;
				 break;
				}
			if (this.oldPage != this.page) {
				//var cache = this.cache ;
				var start = this.page ;
				if (typeof cache[start] == "undefined") {
					jQuery.get("index.php?option=com_virtuemart&view=media&task=viewJson&format=json&mediatype="+mediatype ,
						{ start: start },
						function(data) {
							if (data != "ERROR") {
								cache[start] = data ;
								jQuery("#dialog").html(cache[start]);
								jQuery(".page").text( "Page(s) "+ (start+1)) ;
							} else  {
								jQuery(".page").text( "No  more results : Page(s) "+ (start+1)) ;
							}
						}
					);
				} else jQuery("#dialog").html(cache[start]);
				page = this.oldPage = this.page;
				$('.media-pagination').children().removeClass('media-page-selected');
				$('.media-pagination').children().eq(start+3).addClass('media-page-selected');
			}
			
		}

	},
	tips : function(image) {    
		var xOffset = -20; // x distance from mouse
		var yOffset = 10; // y distance from mouse       
		tip = this ;
		tip.unbind().hover(    
			function(e) {
				tip.t = this.title;
				this.title = ''; 
				tip.top = (e.pageY + yOffset); tip.left = (e.pageX + xOffset);
				$('body').append( '<p id="vtip"><img id="vtipArrow" /><B>'+$(this).html()+'</B><br/ >' + tip.t + '</p>' );
				$('#vtip #vtipArrow').attr("src", image);
				$('#vtip').css("top", tip.top+"px").css("left", tip.left+"px").fadeIn("slow");
			},
			function() {
				this.title = tip.t;
				$("#vtip").fadeOut("slow").remove();
			}
		).mousemove(
			function(e) {
				tip.top = (e.pageY + yOffset);
				tip.left = (e.pageX + xOffset);
				$("#vtip").css("top", tip.top+"px").css("left", tip.left+"px");
			}
		);
		
	},
	toggle : function() {
		var options = { path: '/', expires: 2};
		if ($.cookie('vmmenu') ) { 
			var status = $.cookie('vmmenu');
			if (status == 'hide' ) {
				this.removeClass('vmicon-show').addClass('vmicon-hide');
				$('.menu-wrapper').toggle('slide');
			}
		}
		
		this.click(function () {
			if (this.hasClass('vmicon-show')) {
				this.removeClass('vmicon-show').addClass('vmicon-hide');
				$('.menu-wrapper').toggle('slide');
				$.cookie('vmmenu', 'hide', options);
			} else {
				this.removeClass('vmicon-hide').addClass('vmicon-show');
				$('.menu-wrapper').toggle('slide');
				$.cookie('vmmenu', 'show', options);
			}
		});
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
jQuery.noConflict();
 jQuery( function($) {

	$('dl#system-message').hide().slideDown(400);
	$('.virtuemart-admin-area .toggler').vm2admin('toggle');
	$('#admin-ui-menu').vm2admin('accordeon');
	if ( typeof (virtuemartcookie) !== 'undefined' ) {
		$("#admin-ui-tabs").vm2admin("tabs",virtuemartcookie);
		$('select').chosen(); 
	}

	$('#content-box [title]').vm2admin('tips',tip_image);
	$('.modal').fancybox();


});