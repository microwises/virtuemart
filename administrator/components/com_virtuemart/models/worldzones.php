<?php
/**
*
* Data module for the shipping zones
*
* @package	VirtueMart
* @subpackage Shipping
* @author RickG
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
 * Model class for shipping zone
 *
 * @package	VirtueMart
 * @subpackage Shipping
 * @author RickG
 */
class VirtueMartModelWorldzones extends JModel {

   /**
    * Shipping Zone Id
    *
    * @var $_id;
    */
    var $_id;

    /**
     * Zone data record
     *
     * @var object;
     */
    var $_data;


    /**
     * Constructor for the shpping zone model.
     *
     * The zone id is read and detmimined if it is an array of ids or just one single id.
     *
     * @author RickG
     */
    function __construct()
    {
        parent::__construct();

        $cid = JRequest::getVar('virtuemart_worldzone_id', false, 'DEFAULT', 'array');
        if ($cid) {
            $id = $cid[0];
        }
        else {
            $id = JRequest::getInt('virtuemart_worldzone_id', 1);
        }

        $this->setId($id);
    }


    /**
     * Resets the zone id and data
     *
     * @author RickG
     */
    function setId($id)
    {
        $this->_id = $id;
        $this->_data = null;
    }


    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author RickG
     */
	function getShippingZone()
	{
		$db =& JFactory::getDBO();

		if (empty($this->_data)) {
			$query = 'SELECT * ';
			$query .= 'FROM `#__virtuemart_worldzones` ';
			$query .= 'WHERE `virtuemart_worldzone_id` = ' . (int)$this->_id;
			$db->setQuery($query);
			$this->_data = $db->loadObject();
		}

		if (!$this->_data) {
			$this->_data = new stdClass();
			$this->_id = 0;
			$this->_data = null;
		}

		return $this->_data;
	}


    /**
     * Retrieve a list of zone ids and zone names for use in a HTML select list.
     *
     * @author RickG
     */
    function getWorldZonesSelectList()
    {
    	$db =& JFactory::getDBO();

    	$query = 'SELECT `virtuemart_worldzone_id`, `zone_name` ';
		$query .= 'FROM `#__virtuemart_worldzones`';
		$db->setQuery($query);

		return $db->loadObjectList();
	}
}
// pure php no closing tag