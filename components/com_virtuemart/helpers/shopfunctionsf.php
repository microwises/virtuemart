<?php
/**
*
* Contains shop functions for the front-end
*
* @package	VirtueMart
* @subpackage Helpers
* @author RolandD
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

class shopFunctionsF {
	
	public function getLastVisitedCategoryId(){
		
		$session = JFactory::getSession();
		return $session->get('vmlastvisitedcategoryid', 0, 'vm');
		
	}

	public function setLastVisitedCategoryId($categoryId){
		$session = JFactory::getSession();
		return $session->set('vmlastvisitedcategoryid', (int) $categoryId, 'vm');
		
	}
		
	/**
	 * function to create a div to show the prices, is necessary for JS
	 * 
	 * @author Max Milbers
	 * 
	 * @param string name of the price
	 * @param String description key
	 * @param array the prices of the product
	 * return a div for prices which is visible according to config and have all ids and class set
	 */
	public function createPriceDiv($name,$description,$product_price){
			
		//This could be easily extended by product specific settings
		if(VmConfig::get($name) =='1'){
	 		if(!empty($product_price[$name])){
	 			$vis = "block";
	 			$calculator = calculationHelper::getInstance();
	 			$product_price[$name] = $calculator->priceDisplay($product_price[$name]);
	 		} else {
	 			$vis = "none";
	 		}
		return '<div style="display : '.$vis.';" id="'.$name.'D" class="PriceDescr'.$name.'" >'.JText::_($description).'<span id="'.$name.'" class="Price'.$name.'" >'.$product_price[$name].'</span></div>'; 	
		}
	}
	
	/**
	* function to create a hyperlink
	*
	* @param string $link
	* @param string $text
	* @param string $target
	* @param string $title
	* @param array $attributes
	* @return string
	*/
	public function hyperLink( $link, $text, $target='', $title='', $attributes='' ) {
		$options = array();
		if( $target ) {
			$options['target'] = $target;
		}
		if( $title ) {
			$options['title'] = $title;
		}
		if( $attributes ) {
			$options = array_merge($options, $attributes);
		}
		return JHTML::_('link', $link, $text, $options);
	}
	
	/**
	 * Writes a PDF icon
	 *
	 * @param string $link
	 * @param boolean $use_icon
	 */
	function PdfIcon( $link, $use_icon=true ) {
		if (VmConfig::get('pshop_pdf_button_enable', 1) == '1' && !JRequest::getVar('pop')) {
			$link .= '&amp;pop=1';
			if ( $use_icon ) {
				$text = self::ImageCheck( 'pdf_button.png', '/images/M_images/', NULL, NULL, JText::_('CMN_PDF'), JText::_('CMN_PDF') );
			} else {
				$text = JText::_('CMN_PDF') .'&nbsp;';
			}
			return self::vmPopupLink($link, $text, 640, 480, '_blank', JText::_('CMN_PDF'));
		}
	}

	/**
	 * Writes an Email icon
	 *
	 * @param string $link
	 * @param boolean $use_icon
	 */
	function EmailIcon( $product_id, $use_icon=true ) {
		if (VmConfig::get('vm_show_emailfriend', 1) == '1' && !JRequest::getVar('pop') && $product_id > 0  ) {
			$link = JRoute::_('index2.php?page=shop.recommend&amp;product_id='.$product_id.'&amp;pop=1&amp;tmpl=component');
			if ( $use_icon ) {
				$text = self::ImageCheck( 'emailButton.png', '/images/M_images/', NULL, NULL, JText::_('CMN_EMAIL'), JText::_('CMN_EMAIL') );
			} else {
				$text = '&nbsp;'. JText::_('CMN_EMAIL');
			}
			return self::vmPopupLink($link, $text, 640, 480, '_blank', JText::_('CMN_EMAIL'), 'screenX=100,screenY=200');
		}
	}

	function PrintIcon( $link='', $use_icon=true, $add_text='' ) {
		global  $cur_template, $Itemid;
		if (VmConfig::get('vm_show_printicon', 1) == '1') {
			if( !$link ) {
				$query_string = str_replace( 'only_page=1', 'only_page=0', JRequest::getVar('QUERY_STRING'));
				$link = 'index2.php?'.$query_string.'&amp;pop=1&amp;tmpl=component';
			}
			// checks template image directory for image, if non found default are loaded
			if ( $use_icon ) {
				$filter = JFilterInput::getInstance();
				$text = self::ImageCheck( 'printButton.png', '/images/M_images/', NULL, NULL, JText::_('CMN_PRINT'), JText::_('CMN_PRINT') );
				$text .= $filter->clean($add_text);
			} else {
				$text = '|&nbsp;'. JText::_('CMN_PRINT'). '&nbsp;|';
			}
			$isPopup = JRequest::getVar( 'pop' );
			if ( $isPopup ) {
				// Print Preview button - used when viewing page
				$html = '<span class="vmNoPrint">
				<a href="javascript:void(0)" onclick="javascript:window.print(); return false;" title="'. JText::_('CMN_PRINT').'">
				'. $text .'
				</a></span>';
				return $html;
			} else {
				// Print Button - used in pop-up window
				return self::vmPopupLink($link, $text, 640, 480, '_blank', JText::_('CMN_PRINT'));
			}
		}

	}
	
	/**
	* A function to create a XHTML compliant and JS-disabled-safe pop-up link
	*
	* @param string $link The HREF attribute
	* @param string $text The link text
	* @param int $popupWidth
	* @param int $popupHeight
	* @param string $target The value of the target attribute
	* @param string $title
	* @param string $windowAttributes
	* @return string
	*/
	public function vmPopupLink( $link, $text, $popupWidth=640, $popupHeight=480, $target='_blank', $title='', $windowAttributes='' ) {
		if( $windowAttributes ) {
			$windowAttributes = ','.$windowAttributes;
		}
		return self::hyperLink( $link, $text, '', $title, array("onclick" => "void window.open('$link', '$target', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=$popupWidth,height=$popupHeight,directories=no,location=no".$windowAttributes."');return false;" ));
	
	}
	
	/**
	* Checks to see if an image exists in the current templates image directory
 	* if it does it loads this image.  Otherwise the default image is loaded.
	* Also can be used in conjunction with the menulist param to create the chosen image
	* load the default or use no image
	*/
	function ImageCheck( $file, $directory='/images/M_images/', $param=NULL, $param_directory='/images/M_images/', $alt=NULL, $name=NULL, $type=1, $align='middle', $title=NULL, $admin=NULL ) {
		$mainframe = JFactory::getApplication();
		$cur_template = $mainframe->getTemplate();

		$name 	= ( $name 	? ' name="'. $name .'"' 	: '' );
		$title 	= ( $title 	? ' title="'. $title .'"' 	: '' );
		$alt 	= ( $alt 	? ' alt="'. $alt .'"' 		: ' alt=""' );
		$align 	= ( $align 	? ' align="'. $align .'"' 	: '' );

		// change directory path from frontend or backend
		if ($admin) {
			$path 	= '/administrator/templates/'. $cur_template .'/images/';
		} else {
			$path 	= '/templates/'. $cur_template .'/images/';
		}

		if ( $param ) {
			$image = JURI::base(). $param_directory . $param;
			if ( $type ) {
				$image = '<img src="'. $image .'" '. $alt . $name . $align .' border="0" />';
			}
		} else if ( $param == -1 ) {
			$image = '';
		} else {
			if ( file_exists( JPATH_SITE . $path . $file ) ) {
				$image = JURI::base() . $path . $file;
			} else {
				// outputs only path to image
				$image = JURI::base(). $directory . $file;
			}

			// outputs actual html <img> tag
			if ( $type ) {
				$image = '<img src="'. $image .'" '. $alt . $name . $title . $align .' border="0" />';
			}
		}

		return $image;
	}
	
	/**
	 * Sends the mail joomla conform
	 * TODO people often send media with emails. Like pictures, serials,...
	 * 
	 * @author Max Milbers
	 * @param $body the html body to send, the content of the email
	 * @param $recipient the recipients of the mail, can be array also 
	 * @param $mediaToSend an array for the paths which holds the files which should be sent to
	 * @param $vendorId default is 1 (mainstore)
	 */
	function sendMail($body,$recipient,$subject='TODO set subject', $vendor_id=1, $mediaToSend = false ){
		
		$mailer =& JFactory::getMailer();
		
		//This is now just without multivendor
		$config =& JFactory::getConfig();
		$sender = array( 
    		$config->getValue( 'config.mailfrom' ),
    		$config->getValue( 'config.fromname' ) ); 
 
		$mailer->setSender($sender);

		$mailer->addRecipient($recipient);
				
		$mailer->setSubject($subject);  
		
		// Optional file attached  //this information must come from the cart
		if($mediaToSend){
			//Test if array, if not make an array out of it
			foreach ($mediaToSend as $media){
				//Todo test and such things.
				$mailer->addAttachment($media);				
			}
		}
		
		$mailer->isHTML(true);
		$mailer->setBody($body);
		
		// Optionally add embedded image  //TODO Test it
		$store = $this->getModel('store','VirtuemartModel');
		$store->setId($vendor_id);
		$_store = $store->getStore();
		
		$mailer->AddEmbeddedImage( VmConfig::get('media_path').DS.$_store->vendor_full_image, 'base64', 'image/jpeg' );
		
		return $mailer->Send();
		
		//Perfect Exampel for a misplaced return message. The function is used in different locations, so the messages should be set there!
//		if ( $send !== true ) {
//		    echo 'Error sending email: ' . $send->message;
//		} else {
//		    echo 'Mail sent';
//		}
		
	}
	
	function setVmTemplate($view,$catTpl=0,$prodTpl=0,$catLayout=0,$prodLayout=0){
		
		//Lets get here the template set in the shopconfig, if there is nothing set, get the joomla standard
		$template = VmConfig::get('vmtemplate','default');
		
		//Set specific category template
		if(!empty($catTpl) && empty($prodTpl)){
			if(is_Int($catTpl)){
				$db = JFactory::getDBO();
				$q = 'SELECT `category_template` FROM `#__vm_category` WHERE `category_id` = "'.$catTpl.'" ';
				$db->setQuery($q);
				$temp = $db->loadResult();
				if ($temp) $template = $temp;
			} else {
				$template = $catTpl;
			}	
		}
		
		//Set specific product template
		if(!empty($prodTpl)){
			if(is_Int($prodTpl)){
				$db = JFactory::getDBO();
				$q = 'SELECT `product_template` FROM `#__vm_product` WHERE `product_id` = "'.$prodTpl.'" ';
				$db->setQuery($q);
				$temp = $db->loadResult();				
				if($temp) $template = $temp;
			} else {
				$template = $prodTpl;
			}
		}
		dump($template,'$template');
		shopFunctionsF::setTemplate($template);

		//Lets get here the layout set in the shopconfig, if there is nothing set, get the joomla standard
		$layout = VmConfig::get('vmlayout','default');

		//Set specific category layout
		if(!empty($catLayout) && empty($prodLayout)){
			if(is_Int($catLayout)){
				$db = JFactory::getDBO();
				$q = 'SELECT `layout` FROM `#__vm_category` WHERE `category_id` = "'.$catLayout.'" ';
				$db->setQuery($q);
				$temp = $db->loadResult();
				if ($temp) $layout = $temp;
			} else {
				$layout = $catLayout;
			}
		}
		dump($layout,'$layout for category');
		//Set specific product layout
		if(!empty($prodLayout)){
			if(is_Int($prodLayout)){
				$db = JFactory::getDBO();
				$q = 'SELECT `layout` FROM `#__vm_category` WHERE `category_id` = "'.$catLayout.'" ';
				$db->setQuery($q);
				$temp = $db->loadResult();
				if ($temp) $layout = $temp;
			} else {
				$layout = $prodLayout;
			}
		}
		
		dump($layout,'$layout and $prodLayout '.$prodLayout);
		$view->setLayout(strtolower($layout));
	}
	
	function setTemplate( $template ){
		if(!empty($template)){
			if (is_dir(JPATH_THEMES.DS.$template)) {
				$mainframe = JFactory::getApplication();
				$mainframe->set('setTemplate', $template);
			}			
		}
	} 
	

	function dumpIt($var,$desc){
		global $dumper;
		$dumper[] = $desc.':<br /> <pre>'.print_r($var,true).'</pre>';
//		<small><pre>'.print_r(debug_backtrace(),true).' </pre> </small>'; 
	}
	
	function displayDumps(){
		global $dumper;
		if(is_array($dumper)){
			foreach($dumper as $dump){
				echo $dump.'<br />';
			}			
		}

	}
}

// pure php no closing tag
