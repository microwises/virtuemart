// <?php // This should fool phpDocumentor into parsing this file
/**
*	Debug Function, that works like print_r for Objects in Javascript
*/
function var_dump(obj) {
	var vartext = "";
	for (var prop in obj) {
		if( isNaN( prop.toString() )) {
			vartext += "\t->"+prop+" = "+ eval( "obj."+prop.toString()) +"\n";
		}
    }
   	if(typeof obj == "object") {
    	return "Type: "+typeof(obj)+((obj.constructor) ? "\nConstructor: "+obj.constructor : "") + "\n" + vartext;
   	} else {
      	return "Type: "+typeof(obj)+"\n" + vartext;
	}
}//end function var_dump
/**
* All these functions are there in Joomla!
* But we have modified them here to be able
* to handle multiple forms at once
*/
function vm_isChecked(isitchecked, frmName){
    var f = eval( "document."+frmName );
	if (isitchecked == true){
		f.boxchecked.value++;
	}
	else {
		f.boxchecked.value--;
	}
}

/**
*/
function vm_listItemTask( id, task, frmName, pageName, func ) {
	var f = eval( "document."+frmName );
	cb = eval( 'f.' + id );
	if (cb) {
		cb.checked = true;
		f.page.value = pageName;
		vm_submitListFunc(task, frmName, func);
	}
	return false;
}
/**
* Default function.  Usually would be overriden by the component
*/
function vm_submitButton(pressbutton, frmName, pageName) {
	vm_submitForm(pressbutton, frmName, pageName);
}

/**
* Submit the admin form
*/
function vm_submitForm(pressbutton, frmName, pageName){
	
	var f = eval( "document."+frmName );
	if( pressbutton == "cancel" ) {
		if( parent.closePanel && parent.Ext ){
			parent.closePanel( getURLParam('panelid') );
		}
		f.func.value = "";
	}
	f.target = '_self';
	f.task.value = pressbutton;
	f.page.value = pageName;
	try {
		f.onsubmit();
	}
	catch(e){}
	f.submit();
}

function vm_submitListFunc( pressbutton, frmName, funcName ) {
	var f = eval( "document."+frmName );
	f.task.value = pressbutton;
	f.func.value = funcName;
	try {
		f.onsubmit();
	}
	catch(e){}
	f.submit();
}
function vm_windowClose() {
	window.close();
}
function MM_findObj(n, d) { //v4.01
	var p,i,x;
	if(!d) d=document;
	if((p=n.indexOf("?"))>0&&parent.frames.length) {
		d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);
	}
	if(!(x=d[n])&&d.all) x=d.all[n];
	for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
	for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
	if(!x && d.getElementById) x=d.getElementById(n);
	return x;
}
function MM_swapImage() { //v3.0
	var i,j=0,x,a=MM_swapImage.arguments;
	document.MM_sr=new Array;
	for(i=0;i<(a.length-2);i+=3)
	if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x;
	if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}
function MM_swapImgRestore() { //v3.0
	var i,x,a=document.MM_sr;
	for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_preloadImages() { //v3.0
	var d=document;
	if(d.images){
	if(!d.MM_p) d.MM_p=new Array();
	var i,j=d.MM_p.length,a=MM_preloadImages.arguments;
	for(i=0; i<a.length; i++)
	if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}
function getURLParam( strParamName, myWindow){
	if( !myWindow ){
		myWindow = window;
	}
  var strReturn = "";
  var strHref = myWindow.location.href;
  if ( strHref.indexOf("?") > -1 ){
    var strQueryString = strHref.substr(strHref.indexOf("?")).toLowerCase();
    var aQueryString = strQueryString.split("&");
    for ( var iParam = 0; iParam < aQueryString.length; iParam++ ){
      if ( aQueryString[iParam].indexOf(strParamName + "=") > -1 ){
        var aParam = aQueryString[iParam].split("=");        
        strReturn = aParam[1];
        break;
      }
    }
  }
  return strReturn;
}
/**
 * Function to add a new Tab to the Administrator Panel
 * @param title
 * @param link
 * @return boolean
 */
function addSimplePanel( title, link ) {
	if( Ext.getCmp('center-panel')) {
		myId = title.replace( /\W/, '' );
		if( Ext.getCmp('center-panel').getComponent( myId )) {
			Ext.getCmp('center-panel').activate( panelArr[myId] );
			return false;
		}

		var panel = new Ext.Panel( { id: myId, 
													html: {tag: "iframe", frameBorder: 0, width: "100%", height: "100%", src: link+"&panelId="+myId }, 
													title: title, 
													fitToFrame: true, 
													closable:true ,
													tbar: [{xtype:"tbspacer"}]
													}
												);	
		Ext.getCmp('center-panel').add( panel );		
		
		Ext.getCmp('center-panel').activate(panel);
		return false;
	}
	return true;
}
/**
 * Function to remove a Tab from the Administrator Panel
 * @param id
 * @return boolean
 */
function closePanel( id ) {

	if( Ext.getCmp('center-panel')) {
		if( Ext.getCmp('center-panel').items.getCount() > 1 ) {
			tabToClose = Ext.getCmp('center-panel').getActiveTab();
			if( tabToClose.getId() != "vmpage-panel" ) {
					Ext.getCmp('center-panel').remove( tabToClose );
			}
			return true;
		}
		return false;
	} else {
		return false;
	}
}