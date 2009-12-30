<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: admin.user_field_form.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
mm_showMyFileName( __FILE__ );

vmCommonHTML::loadMooTools();

require_once( CLASSPATH . 'ps_userfield.php' );
require_once( CLASSPATH . 'ps_shopper_group.php' );

$ps_shopper_group = new ps_shopper_group();

$fieldid = JRequest::getVar('fieldid', 0);
if( is_array( $fieldid )) {
	$fieldid = (int)$fieldid[0];
}
$option = empty($option)?JRequest::getVar(  'option', 'com_virtuemart'):$option;

//First create the object and let it print a form heading
$formObj = &new formFactory( JText::_('VM_USERFIELD_FORM_LBL') );
//Then Start the form
$formObj->startForm();

$lists = array();

$params = '';

$webaddrtypes = array();

$webaddrtypes[0] = JText::_('VM_USERFIELDS_URL_ONLY');
$webaddrtypes[2] = JText::_('VM_USERFIELDS_HYPERTEXT_URL');

$fieldvalues = array();

if (!empty($fieldid)) {
	$db->query( "SELECT `fieldtitle`, `fieldvalue` "
	. "\n FROM `#__{vm}_userfield_values`"
	. "\n WHERE `fieldid`=$fieldid"
	. "\n ORDER BY ordering" );
	$fieldvalues = $db->loadObjectList();

    $q = "SELECT * FROM #__{vm}_userfield WHERE fieldid=$fieldid"; 
    $db->query($q);  
    $db->next_record();
    if( $db->f('params') ) {
    	$params = new vmParameters( $db->f('params') );
    }
	$lists['type'] = '<input type="hidden" value="'.$db->f('type').'" name="type" />'.$db->f('type');
}
else {
	$types = array();
	
	$types['text'] = JText::_('VM_FIELDS_TEXTFIELD');
	$types['checkbox'] = JText::_('VM_FIELDS_CHECKBOX_SINGLE');
	$types['multicheckbox'] = JText::_('VM_FIELDS_CHECKBOX_MULTIPLE');
	$types['date'] = JText::_('VM_FIELDS_DATE');
	$types['age_verification'] = JText::_('VM_FIELDS_AGEVERIFICATION');
	$types['select'] = JText::_('VM_FIELDS_DROPDOWN_SINGLE');
	$types['multiselect'] = JText::_('VM_FIELDS_DROPDOWN_MULTIPLE');
	$types['emailaddress'] = JText::_('VM_FIELDS_EMAIL');	
	$types['euvatid'] = JText::_('VM_FIELDS_EUVATID');
	$types['editorta'] = JText::_('VM_FIELDS_EDITORAREA');
	$types['textarea'] = JText::_('VM_FIELDS_TEXTAREA');
	$types['radio'] = JText::_('VM_FIELDS_RADIOBUTTON');
	$types['webaddress'] = JText::_('VM_FIELDS_WEBADDRESS');
	
	/* Captcha check changed */
	if( file_exists($mosConfig_absolute_path.'/administrator/components/com_securityimages/client.php')) {	
		$types['captcha'] = JText::_('VM_FIELDS_CAPTCHA');
	}
	$captchafile = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_securityimages'.DS.'class'.DS.'SecurityImagesHelper.php';
	if( file_exists($captchafile)) {	
		$types['captcha'] = JText::_('VM_FIELDS_CAPTCHA');
	}
	if( file_exists($mosConfig_absolute_path.'/components/com_yanc/yanc.php')) {
		$types['yanc_subscription'] = JText::_('VM_FIELDS_NEWSLETTER').' (YaNC)';
	}
	if( file_exists($mosConfig_absolute_path.'/components/com_anjel/anjel.php')) {
		$types['anjel_subscription'] = JText::_('VM_FIELDS_NEWSLETTER').' (ANJEL)' ;
	}
	if( file_exists($mosConfig_absolute_path.'/components/com_letterman/letterman.php')) {
		$types['letterman_subscription'] = JText::_('VM_FIELDS_NEWSLETTER').' (Letterman)';
	}
	if( file_exists($mosConfig_absolute_path.'/components/com_ccnewsletter/ccnewsletter.php')) {
		$types['ccnewsletter_subscription'] = JText::_('VM_FIELDS_NEWSLETTER').' (ccNewsletter)';
	}
	$types['delimiter'] = JText::_('VM_FIELDS_DELIMITER');
	
	$lists['type'] = ps_html::selectList( 'type', $db->f('type'), $types, 1, '', 'onchange="toggleType(this.options[this.selectedIndex].value);"' );
}

$lists['webaddresstypes'] = ps_html::selectList( 'webaddresstypes', $db->f('rows'), $webaddrtypes );

if( in_array( $db->f('name'), ps_userfield::getSkipFields() )) {
	$lists['required'] = '<input type="hidden" name="required" class="inputbox" value="'. $db->sf('required').'" />'.($db->sf('required')?JText::_('VM_ADMIN_CFG_YES'):JText::_('VM_ADMIN_CFG_NO'));
	$lists['published'] = '<input type="hidden" name="published" class="inputbox" value="'. $db->sf('required').'" />'.($db->sf('required')?JText::_('VM_ADMIN_CFG_YES'):JText::_('VM_ADMIN_CFG_NO'));
	$lists['registration'] = '<input type="hidden" name="registration" class="inputbox" value="'. $db->sf('required').'" />'.($db->sf('required')?JText::_('VM_ADMIN_CFG_YES'):JText::_('VM_ADMIN_CFG_NO'));
} else {
	$lists['required'] = ps_html::yesnoSelectList( 'required', $db->sf('required')?$db->sf('required'): '0' );
	$lists['published'] = ps_html::yesnoSelectList( 'published', $db->sf('published') );
	$lists['registration'] = ps_html::yesnoSelectList( 'registration', $db->sf('registration') );
}
$lists['readonly'] = ps_html::yesnoSelectList( 'readonly', $db->sf('readonly') != '' ? $db->sf('readonly') : '0' );

$lists['shipping'] = ps_html::yesnoSelectList( 'shipping', $db->sf('shipping') != '' ? $db->sf('shipping') : '0' );

$lists['account'] = ps_html::yesnoSelectList( 'account', $db->sf('account') );

?>
	<table cellpadding="4" cellspacing="0" border="0" width="100%">
		<tr>
			<td class="sectionname"><img src="<?php echo $mosConfig_live_site.'/administrator/images/addusers.png' ?>" align="middle"><?php echo JText::_('VM_MANAGE_USER_FIELDS') ?></td>
		</tr>
	</table>

	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" name="adminForm">
	
	<table class="adminform">
		<tr class="row0">
			<td width="20%"><?php echo JText::_('VM_FIELDMANAGER_TYPE') ?>:</td>
			<td width="20%"><?php echo $lists['type']; ?></td>
			<td>&nbsp;</td>
		</tr>
		<tr class="row1">
			<td width="20%"><?php echo JText::_('VM_FIELDMANAGER_NAME') ?>:</td>
			<td align="left"  width="20%"><input onchange="prep4SQL(this);" type="text" name="name" mosReq=1 mosLabel="Name" class="inputbox" value="<?php $db->sp('name'); ?>" <?php if($db->f('sys')) echo 'readonly="readonly"' ?> /></td>
			<td>&nbsp;</td>
		</tr>
		<tr class="row0">
			<td width="20%"><?php echo JText::_('VM_FIELDMANAGER_TITLE') ?>:</td>
			<td width="20%" align="left"><input type="text" name="title" mosReq=1 mosLabel="Title" class="inputbox" value="<?php $db->sp('title'); ?>" /></td>
			<td>&nbsp;</td>
		</tr>
		<tr class="row1">
			<td width="20%"><?php echo JText::_('VM_USERFIELDS_DESCRIPTION') ?>:</td>
			<td width="20%" align="left"><textarea name="description" cols=50 rows=6 maxlength='255' mosReq=0 mosLabel="Description" class="inputbox"><?php $db->sp('description'); ?></textarea></td>
			<td>&nbsp;</td>
		</tr>
		<tr class="row0">
			<td width="20%"><?php echo JText::_('VM_FIELDMANAGER_REQUIRED') ?>?:</td>
			<td width="20%"><?php echo $lists['required']; ?></td>
			<td>&nbsp;</td>
		</tr>
		<tr class="row1">
			<td width="20%"><?php echo JText::_('VM_FIELDMANAGER_SHOW_ON_REGISTRATION') ?>?:</td>
			<td width="20%"><?php echo $lists['registration']; ?></td>
			<td>&nbsp;</td>
		</tr>
		<tr class="row0">
			<td width="20%"><?php echo JText::_('VM_FIELDMANAGER_SHOW_ON_ACCOUNT') ?>?:</td>
			<td width="20%"><?php echo $lists['account']; ?></td>
			<td>&nbsp;</td>
		</tr>
		<tr class="row1">
			<td width="20%"><?php echo JText::_('VM_FIELDMANAGER_SHOW_ON_SHIPPING') ?>?:</td>
			<td width="20%"><?php echo $lists['shipping']; ?></td>
			<td>&nbsp;</td>
		</tr>
		<tr class="row0">
			<td width="20%"><?php echo JText::_('VM_USERFIELDS_READONLY') ?>?:</td>
			<td width="20%"><?php echo $lists['readonly']; ?></td>
			<td>&nbsp;</td>
		</tr>
		<tr class="row1">
			<td width="20%"><?php echo JText::_('VM_FIELDMANAGER_PUBLISHED') ?>:</td>
			<td width="20%"><?php echo $lists['published']; ?></td>
			<td>&nbsp;</td>
		</tr>
		<tr class="row0">
			<td width="20%"><?php echo JText::_('VM_USERFIELDS_SIZE') ?>:</td>
			<td width="20%"><input type="text" name="size" mosLabel="Size" class="inputbox" value="<?php echo $db->f('size'); ?>" /></td>
			<td>&nbsp;</td>
		</tr>
		</table>
		<div id="page1"></div>
		
		<div id="divText">
			<table class="adminform">
			<tr class="row0">
				<td width="20%"><?php echo JText::_('VM_USERFIELDS_MAXLENGTH') ?>:</td>
				<td width="20%"><input type="text" name="maxlength" mosLabel="Max Length" class="inputbox" value="<?php echo $db->f('maxlength'); ?>" /></td>
				<td>&nbsp;</td>
			</tr>
			</table>
		</div>
		<div id="divColsRows">
			<table class="adminform">
			<tr class="row0">
				<td width="20%"><?php echo JText::_('VM_USERFIELDS_COLUMNS') ?>:</td>
				<td width="20%"><input type="text" name="cols" mosLabel="Cols" class="inputbox" value="<?php echo $db->f('cols'); ?>" /></td>
				<td>&nbsp;</td>
			</tr>
			<tr class="row1">
				<td width="20%"><?php echo JText::_('VM_USERFIELDS_ROWS') ?>:</td>
				<td width="20%"><input type="text" name="rows"  mosLabel="Rows" class="inputbox" value="<?php echo $db->f('rows'); ?>" /></td>
				<td>&nbsp;</td>
			</tr>
			</table>
		</div>
		<div id="divShopperGroups" >
			<table class="adminform">
	          <tr class="row1"> 
	        	<td class="labelcell"><?php echo JText::_('VM_USERFIELDS_EUVATID_MOVESHOPPER') ?>:</td>
	            <td ><?php
	            	$sg_id = is_a( $params, 'vmparameters' ) ? $params->get( 'shopper_group_id', 5 ) : '';
                   	echo ps_shopper_group::list_shopper_groups( "shopper_group_id", $sg_id );
                   ?>
                 </td>
                </tr>
			</table>
		</div>
		<div id="divAgeVerification" >
			<table class="adminform">
	          <tr class="row1"> 
	        	<td class="labelcell"><?php echo JText::_('VM_FIELDS_AGEVERIFICATION_MINIMUM') ?>:</td>
	            <td ><?php
	            	$min_age = is_a( $params, 'vmparameters' ) ? $params->get( 'minimum_age', 18 ) : 18;
	            	$ages = array();
	            	for( $i = 13; $i <= 25; $i++ ) {
	            		$ages[$i] = $i . ' ' . JText::_('CMN_YEARS');
	            	}
	            	ps_html::dropdown_display('minimum_age', $min_age, $ages );
                   ?>
                 </td>
                </tr>
			</table>
		</div>
		<div id="divWeb">
			<table cellpadding="4" cellspacing="1" border="0" width="100%" class="adminform">
			<tr class="row1">
				<td width="20%"><?php echo JText::_('VM_FIELDMANAGER_TYPE') ?>:</td>
				<td width="20%"><?php echo $lists['webaddresstypes']; ?></td>
				<td>&nbsp;</td>
			</tr>
			</table>
		</div>
		
		<div id="divValues" style="text-align:left;height: 200px;overflow: auto;">
			<?php echo JText::_('VM_USERFIELDS_ADDVALUES_TIP') ?><br />
			<input type="button" class="button" onclick="insertRow();" value="<?php echo JText::_('VM_USERFIELDS_ADDVALUE') ?>" />
			<table align=left id="divFieldValues" cellpadding="4" cellspacing="1" border="0" width="100%" class="adminform" >
			<thead>
				<th class="title" width="20%"><?php echo JText::_('VM_USERFIELDS_TITLE') ?></th>
				<th class="title" width="80%"><?php echo JText::_('VM_USERFIELDS_VALUE') ?></th>
			</thead>
			<tbody id="fieldValuesBody">
			<tr>
				<td>&nbsp;</td>
			</tr>
			<?php	
			//echo "count:".count( $fieldvalues );
			//print_r (array_values($fieldvalues));
			$n=count( $fieldvalues );
			for ($i=0; $i < $n; $i++) {
				//print "count:".$i;
				$fieldvalue = $fieldvalues[$i];
				if ($i==0) $req =1;
				else $req = 0;
				echo "<tr>\n<td width=\"20%\"><input type=\"text\" value=\"".stripslashes(@$fieldvalue->fieldtitle)."\" name=\"vNames[".$i."]\" /></td>\n";
				echo "\n<td width=\"80%\"><input type=\"text\" value=\"".stripslashes(@$fieldvalue->fieldvalue)."\" name=\"vValues[".$i."]\" /></td></tr>\n";
			}
			if(count( $fieldvalues )< 1) {
				echo "<tr>\n<td width=\"20%\"><input type=\"text\" value=\"\" name=\"vNames[0]\" /></td>\n";
				echo "\n<td width=\"80%\"><input type=\"text\" value=\"\" name=\"vValues[0]\" /></td></tr>\n";
				$i=0;
			}
			?>
			</tbody>
			</table>
		</div>
	  <table class="adminform">
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
	
	  </table>
 <?php
// Add necessary hidden fields
$formObj->hiddenField( 'fieldid', $fieldid );
$formObj->hiddenField( 'valueCount', $i );
$formObj->hiddenField( 'ordering', $db->f('ordering') );

// Write your form with mixed tags and text fields
// and finally close the form:
$formObj->finishForm( 'userfieldSave', $modulename.'.user_field_list', $option );


$duration = 500;
?>
<script type="text/javascript">
  function getObject(obj) {
    var strObj;
    if (document.all) {
      strObj = document.all.item(obj);
    } else if (document.getElementById) {
      strObj = document.getElementById(obj);
    }
    return strObj;
  }

  function insertRow() {
    var oTable = getObject("fieldValuesBody");
    var oRow, oCell ,oCellCont, oInput;
    var i, j;
    i=document.adminForm.valueCount.value;
    i++;
    // Create and insert rows and cells into the first body.
    oRow = document.createElement("TR");
    oTable.appendChild(oRow);

    oCell = document.createElement("TD");
    oInput=document.createElement("INPUT");
    oInput.name="vNames["+i+"]";
    oInput.setAttribute('id',"vNames_"+i);
    oCell.appendChild(oInput);
    oRow.appendChild(oCell);
    
    oCell = document.createElement("TD");
    oInput=document.createElement("INPUT");
    oInput.name="vValues["+i+"]";
    oInput.setAttribute('id',"vValues_"+i);
    oCell.appendChild(oInput);
    
    oRow.appendChild(oCell);
    oInput.focus();

    document.adminForm.valueCount.value=i;
  }

  function disableAll() {
    var elem;
    try{ 
    	divValues.slideOut();
    	divColsRows.slideOut();
    	divWeb.slideOut();
    	divShopperGroups.slideOut();
    	divAgeVerification.slideOut();
    	divText.slideOut();
    
    } catch(e){ }
    if (elem=getObject('vNames[0]')) {
      elem.setAttribute('mosReq',0);
    }
  }
  function toggleType( type ) {
	disableAll();
<?php if( !$db->f('sys') ) : ?>
	prep4SQL( document.adminForm.name );
<?php endif; ?>
	setTimeout( 'selType( \'' + type + '\' )', <?php echo ( $duration + 150 ) ?> );
  }
  function selType(sType) {
    var elem;
    
    switch (sType) {
      case 'editorta':
      case 'textarea':
        
        divText.toggle();
        divColsRows.toggle();
      break;
      
      case 'euvatid':
      	divShopperGroups.toggle();
      	break;
      case 'age_verification':
      	divAgeVerification.toggle();
      	break;
      	
      case 'emailaddress':
      case 'password':
      case 'text':
        
        divText.toggle();
      break;
      
      case 'select':
      case 'multiselect':

        divValues.toggle();
        if (elem=getObject('vNames[0]')) {
          elem.setAttribute('mosReq',1);
        }
      break;
      
      case 'radio':
      case 'multicheckbox':
        divColsRows.toggle();
        divValues.toggle();
        if (elem=getObject('vNames[0]')) {
          elem.setAttribute('mosReq',1);
        }
      break;

      case 'webaddress':
        divWeb.toggle();
      break;
	  	
      case 'delimiter':
      default: 
        
    }
  }

  function prep4SQL(o){
	if(o.value!='') {
		o.value=o.value.replace('vm_','');
    	o.value='vm_' + o.value.replace(/[^a-zA-Z]+/g,'');
	}
  }

</script>  
<?php 
	if($fieldid > 0) {
		echo vmCommonHTML::scriptTag( '', 'document.adminForm.name.readOnly=true;' );
	}
	echo vmCommonHTML::scriptTag( '', "		var divValues = new Fx.Slide('divValues' , {duration: $duration } );
		var divColsRows = new Fx.Slide('divColsRows' , {duration: $duration } );
		var divWeb = new Fx.Slide('divWeb' , {duration: $duration } );
		var divShopperGroups = new Fx.Slide('divShopperGroups' , {duration: $duration } );
		var divAgeVerification = new Fx.Slide('divAgeVerification' , {duration: $duration } );
		var divText = new Fx.Slide('divText' , {duration: $duration } ); 
		toggleType('".$db->f('type')."');" );	
?>
