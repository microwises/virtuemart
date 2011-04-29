<?php
/**
*
* product_media_xref table ( for media)
*
* @package	VirtueMart
* @subpackage Calculation tool
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: calc.php 3002 2011-04-08 12:35:45Z alatak $
*/

defined('_JEXEC') or die();

/**
 * Calculator table class
 * The class is is used to manage the media in the shop.
 *
 * @author Max Milbers
 * @package		VirtueMart
 */
class TableProduct_media_xref extends JTable {

	/** @var int Primary key */
	var $id					= 0;
	/** @var int category_id  */
	var $product_id		= 0;
	/** @var int file_id name */
	var $file_ids           = array();


	/**
	 * @author Max Milbers
	 * @param $db A database connector object
	 */
	function __construct(&$db){
		parent::__construct('#__vm_product_media_xref', 'id', $db);
	}

    /**
     * @author Max Milbers
     * @param
     */
    function check() {

        if (empty($this->product_id)) {
            $this->setError('Serious error cant save product media xref without product id');
            return false;
        }

		if (empty($this->file_ids)) {
            $this->setError('Serious error cant save product media xref without media id');
            return false;
        }
//     	if(empty($this->cdate)) $this->cdate = time();
//     	$this->mdate = time();

        return true;
    }

     /**
     * Records in this table are arrays. Therefore we need to overload the load() function.
     *
	 * @author Max Milbers
     * @param int $id
     */
    function load($id=0){

    	if(empty($this->_db)) $this->_db = JFactory::getDBO();
		if(empty($this->id)) $this->id = $id;
		$q = 'SELECT `file_ids` FROM `'.$this->_tbl.'` WHERE `product_id` = "'.$this->id.'"';
		$this->_db->setQuery($q);

    	if ($result = $this->_db->loadResultArray() ) {
			return $result;
		}
		else
		{
			$this->setError( $this->_db->getErrorMsg() );
			return false;
		}
    }

    /**
     * Records in this table are arrays. Therefore we need to overload the store() function.
     *
     * @author Max Milbers
     * @see libraries/joomla/database/JTable#store($updateNulls)
     */
    public function store() {

		if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');
		return modelfunctions::storeArrayData($this->_tbl,'product_id','file_ids', $this->product_id,$this->file_ids);

    }
}
