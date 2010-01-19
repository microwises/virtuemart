<?php
/**
*
* Data module for shop calculation rules
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
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the model framework
jimport( 'joomla.application.component.model');

/**
 * Model class for shop calculation rules
 *
 * @package	VirtueMart
 * @subpackage Calculation tool
 * @author Max Milbers
 */
class VirtueMartModelCalc extends JModel{

	/** @var array Array of Primary keys */
    private $_cid;
	/** @var integer Primary key */
    private $_id;
	/** @var objectlist Calculation rule  data */
    private $_data;
	/** @var integer Total number of calculation rules in the database */
	private $_total;
	/** @var pagination Pagination for calculation rules list */
	private $_pagination;


    /**
     * Constructor for the calc model.
     *
     * The calc id is read and detmimined if it is an array of ids or just one single id.
     *
     * @author RickG
     */
    public function __construct()
    {
        parent::__construct();

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest(JRequest::getVar('option').'.limitstart', 'limitstart', 0, 'int');

		// Set the state pagination variables
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

        // Get the calc id or array of ids.
		$idArray = JRequest::getVar('cid',  0, '', 'array');
    	$this->setId((int)$idArray[0]);

    }


    /**
     * Resets the calc id and data
     *
     * @author RickG
     */
    public function setId($id)
    {
        $this->_id = $id;
        $this->_data = null;
    }


	/**
	 * Loads the pagination for the country table
	 *
     * @author RickG
     * @return JPagination Pagination for the current list of countries
	 */
    public function getPagination()
    {
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->_getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_pagination;
	}


	/**
	 * Gets the total number of countries
	 *
     * @author RickG
	 * @return int Total number of countries in the database
	 */
	public function _getTotal()
	{
    	if (empty($this->_total)) {
			$query = 'SELECT `calc_id` FROM `#__vm_calc`';
			$this->_total = $this->_getListCount($query);
        }
        return $this->_total;
    }


    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author RickG
     */
	public function getCalc()
	{
		$db = JFactory::getDBO();

  		if (empty($this->_data)) {
   			$this->_data = $this->getTable();
   			$this->_data->load((int)$this->_id);
  		}

  		if (!$this->_data) {
   			$this->_data = new stdClass();
   			$this->_id = 0;
   			$this->_data = null;
  		}
  		return $this->_data;
	}

	/**
	 * Retireve a list of calculation rules from the database.
	 *
     * @author Max Milbers
     * @param string $onlyPuiblished True to only retreive the publish countries, false otherwise
     * @param string $noLimit True if no record count limit is used, false otherwise
	 * @return object List of calculation rule objects
	 */
	public function getCalcs($onlyPublished=false, $noLimit=false)
	{
		$query = 'SELECT * FROM `#__vm_calc` ';
		if ($onlyPublished) {
			$query .= 'WHERE `#__vm_calc`.`published` = 1';
		}
		$query .= ' ORDER BY `#__vm_calc`.`calc_name`';
		if ($noLimit) {
			$this->_data = $this->_getList($query);
		}
		else {
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data;
	}

    /**
     * Publish a field
     *
     * @author Max Milbers
     *
     */
	public function published( $row, $i, $variable = 'published' )
	{
		$imgY = 'tick.png';
		$imgX = 'publish_x.png';
		$img 	= $row->$variable ? $imgY : $imgX;
		$task 	= $row->$variable ? 'unpublish' : 'publish';
		$alt 	= $row->$variable ? JText::_( 'Published' ) : JText::_( 'Unpublished' );
		$action = $row->$variable ? JText::_( 'Unpublish Item' ) : JText::_( 'Publish item' );

		$href = '
		<a title="'. $action .'">
		<img src="images/'. $img .'" border="0" alt="'. $alt .'" /></a>'
		;
		return $href;
	}


	/**
	 * Bind the post data to the calculation table and save it
     *
     * @author RickG
     * @return boolean True is the save was successful, false otherwise.
	 */
    public function store()
	{
		$table = $this->getTable('calc');

		$data = JRequest::get('post');

		// Bind the form fields to the calculation table
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		// Make sure the calculation record is valid
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Save the country record to the database
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}


	/**
	 * Delete all record ids selected
     *
     * @author Max Milbers
     * @return boolean True is the delete was successful, false otherwise.
     */
	public function delete()
	{
		$calcIds = JRequest::getVar('cid',  0, '', 'array');
    	$table = $this->getTable('calc');

    	foreach($calcIds as $calcId) {

    		if (!$table->delete($calcId)) {
        		$this->setError($table->getError());
        		return false;
    		}
        	else {
        		//$this->setError('Could not remove country states!');
        		return true;
        	}
    	}

    	return true;
	}


	/**
	 * Publish/Unpublish all the ids selected
     *
     * @author Max Milbers
     * @param boolean $publishId True is the ids should be published, false otherwise.
     * @return boolean True is the delete was successful, false otherwise.
     */
	public function publish($publishId = false)
	{
		$table = $this->getTable('calc');
		$calcIds = JRequest::getVar( 'cid', array(0), 'post', 'array' );

        if (!$table->publish($calcIds, $publishId)) {
			$this->setError($table->getError());
			return false;
        }

		return true;
	}


	/**
	 * Publish/Unpublish all the ids selected
     *
     * @author jseros
     *
     * @return int 1 is the publishing action was successful, -1 is the unsharing action was successfully, 0 otherwise.
     */
	public function shopperPublish($categories){

		foreach ($categories as $id){

			$quotedId = $this->_db->Quote($id);
			$query = 'SELECT calc_shopper_published
					  FROM #__vm_calc
					  WHERE calc_id = '. $quotedId;

			$this->_db->setQuery($query);
			$calc = $this->_db->loadObject();

			$publish = ($calc->calc_shopper_published > 0) ? 0 : 1;

			$query = 'UPDATE #__vm_calc
					  SET calc_shopper_published = '.$publish.'
					  WHERE calc_id = '.$quotedId;

			$this->_db->setQuery($query);

			if( !$this->_db->query() ){
				$this->setError( $this->_db->getErrorMsg() );
				return false;
			}

		}

		return ($publish ? 1 : -1);
	}



	/**
	 * Publish/Unpublish all the ids selected
     *
     * @author jseros
     *
     * @return int 1 is the publishing action was successful, -1 is the unsharing action was successfully, 0 otherwise.
     */
	public function vendorPublish($categories){

		foreach ($categories as $id){

			$quotedId = $this->_db->Quote($id);
			$query = 'SELECT calc_vendor_published
					  FROM #__vm_calc
					  WHERE calc_id = '. $quotedId;

			$this->_db->setQuery($query);
			$calc = $this->_db->loadObject();

			$publish = ($calc->calc_vendor_published > 0) ? 0 : 1;

			$query = 'UPDATE #__vm_calc
					  SET calc_vendor_published = '.$publish.'
					  WHERE calc_id = '.$quotedId;

			$this->_db->setQuery($query);

			if( !$this->_db->query() ){
				$this->setError( $this->_db->getErrorMsg() );
				return false;
			}

		}

		return ($publish ? 1 : -1);
	}

}