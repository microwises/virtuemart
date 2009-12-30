/***********************************************
* Switch Menu script- by Martial B of http://getElementById.com/
* Modified by Dynamic Drive for format & NS4/IE4 compatibility
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/



window.addEvent('domready', function() {
	var togs = $$('.title-smenu'); //here you declare you togglers/elements array
	var elems = $$('.section-smenu');
 
	togs.each(function(el, i){ //here you set your cookie
		el.addEvent('click', function(){
			Cookie.set('voir', i);
		});	
	})
 
	if (Cookie.get('voir')){ //here you retrieve the cookie value if there is
		var voir = Cookie.get('voir').toInt();
	}
	else { //if there isn't, back to the default 0 value
		voir = 0;
	}

	var accordion = new Accordion(togs,elems, {
		opacity: 0,
		onActive: function(toggler) { 
			toggler.setStyle('color', '#000000');
			toggler.addClass('title-smenu-down'); },
		onBackground: function(toggler) { 
			toggler.setStyle('color', '#666666'); 
			toggler.removeClass('title-smenu-down') },
		display: voir
	});
});