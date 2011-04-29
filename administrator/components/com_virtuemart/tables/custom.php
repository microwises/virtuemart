<?php
/**
*
* Media table
*
* @package	VirtueMart
* @subpackage Media
* @author  Patrick Kohl
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: media.php 3057 2011-04-19 12:59:22Z Electrocity $
*/

// Check to ensure this custom is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Custom table class
 * The class is to manage description of custom fields in the shop.
 *
 * @author Patrick Kohl
 * @package		VirtueMart
 */
class TableCustom extends JTable {

	/** @var int Primary key */
	var $custom_id		= 0;
	/** @var int parent */
	var $custom_parent_id		= 0;
	/** @var int(1)  1= only back-end display*/
	var $admin_only		= 0;

    /** @var string custom field value */
	var $custom_title	= '';
    /** @var string custom Meta or alt  */
	var $custom_tip		= '';
    /** @var string custom Meta or alt  */
	var $custom_value	= '';
    /** @var string custom Meta or alt  */
	var $custom_field_desc	= '';
	/**
	 *@var varchar(1)  
	 * Type = S:string,I:int,P:parent, B:bool,D:date,T:time,H:hidden	  
	 */
	var $field_type= '';

	/** @var int(1)  1= Is this a list of value ? */
	var $is_list		= 0;

	/** @var int(1)  1= hidden field info */
	var $is_hidden		= 0;

	/** @var int(1)  1= cart attributes and price added to cart */
	var $is_cart_attribute		= 0;

	/** @var int custom published or not */
	var $published		= 0;

	
	/**
	 * @author  Patrick Kohl
	 * @param $db A database connector object
	 */
	function __construct(&$db) {
		parent::__construct('#__vm_custom', 'custom_id', $db);
	}

	/**
	 *
	 * @author  Patrick Kohl
	 * @return boolean True .
	  * No check at moment
	 */
	function check(){
		if(empty($this->custom_title)) {
			$this->setError(JText::_('COM_VIRTUEMART_CUSTOM_MUST_HAVE_TITLE'));
			return false ;
		}
		if(empty($this->field_type)) {
			$this->setError(JText::_('COM_VIRTUEMART_CUSTOM_MUST_HAVE_A_FIELD_TYPE'));
			return false ;
		}
		if( $this->custom_id > 0  AND $this->custom_id==$this->custom_parent_id ) {
			$this->setError(JText::_('COM_VIRTUEMART_CUSTOM_CANNOT_PARENT'));
			return false ;
		}
		return true;
	}

}
// pure php no closing tag
