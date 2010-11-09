<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
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

// Load the model framework
jimport( 'joomla.application.component.model');

/**
* Model for VirtueMart Product Files
*
* @package	VirtueMart
* @author RolandD
*/
class VirtueMartModelProductFiles extends JModel {

	/* Private variables */
	private $_total;
	private $_pagination;

	/**
	 * Constructor for product files
	 */
	function __construct() {
		parent::__construct();

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( JRequest::getVar('option').'.limitstart', 'limitstart', 0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Loads the pagination
	 */
    private function getPagination() {
		if ($this->_pagination == null) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}

	/**
	 * Gets the total number of products
	 */
	private function getTotal() {
    	if (empty($this->_total)) {
    		$db = JFactory::getDBO();
    		$filter = '';
            if (JRequest::getInt('product_id', 0) > 0) $filter .= ' WHERE `#__vm_product_files`.`file_product_id` = '.JRequest::getInt('product_id');
			$q = "SELECT COUNT(*) FROM `#__vm_product_files` ".$filter;
			$db->setQuery($q);
			$this->_total = $db->loadResult();
        }

        return $this->_total;
    }

     /**
     * Select the products to list on the product list page
     */
    public function getProductFilesList() {
    	$db = JFactory::getDBO();

    	/* Pagination */
     	$this->getPagination();

    	$filter = '';
    	if (JRequest::getInt('product_id', 0) > 0) $filter = ' WHERE file_product_id = '.JRequest::getInt('product_id');
    	/* Get the files from the product files table */
    	$q = "SELECT file_id, file_is_image, file_product_id, file_extension, file_url, file_published AS published, file_name, file_title,
    				IF (LOWER(attribute_name) = 'download', 1, 0) AS isdownloadable,
    				product_name
    		FROM #__vm_product_files
    		LEFT JOIN #__vm_product_attribute
    		ON #__vm_product_files.file_product_id = #__vm_product_attribute.product_id
    		LEFT JOIN #__vm_product
    		ON #__vm_product_files.file_product_id = #__vm_product.product_id ";
    	$q .= $filter;
    	$q .= " ORDER BY file_is_image DESC";
    	$db->setQuery($q);
    	$productfileslist = $db->loadObjectList();

    	/* Get the files from the product table */
    	//TODO replace IMAGEURL with VmConfig::get('media_product_path')
    	if (JRequest::getInt('product_id', 0) > 0) $filter = ' AND product_id = '.JRequest::getInt('product_id');
    	$q = "SELECT 'product_images' AS file_id, '1' AS file_is_image, product_id AS file_product_id, '1' AS published,
    				product_full_image AS file_name, product_full_image AS file_title, '0' AS isdownloadable, product_name,
    				CONCAT('".VmConfig::get('media_product_path')."','product/', product_full_image) AS file_url, SUBSTRING(product_full_image, -3, 3) AS file_extension
    		FROM #__vm_product
    		WHERE LENGTH(product_full_image) > 0 ";
    	$q .= $filter;
    	$q .= " ORDER BY file_is_image DESC";
    	$db->setQuery($q);
    	$productlist = $db->loadObjectList();

    	$productfileslist = array_merge($productfileslist, $productlist);
    	JRequest::setVar('productfileslist', $productfileslist);
    	JRequest::setVar('pagination', $this->_pagination);

    	/** THIS SECTION NEEDS TO BE MOVED TO THE VIEW.HTML.PHP **/
    	JRequest::setVar('productfilesroles', $this->getProductFilesRoles());

    	/** END **/

    }

    /**
	 * Returns the number of files AND images which are assigned to $pid
	 *
	 * @param int $pid
	 * @param string $type Filter the query by file_is_image: [files|images|(empty)]
	 * @return int
	 */
	public function countFilesForProduct($pid, $type = '') {
		$db = JFactory::getDBO();
		switch ($type) {
			case 'files': $type_sql = 'AND file_is_image=0'; break;
			case 'images': $type_sql = 'AND file_is_image=1'; break;
			default: $type_sql = ''; break;
		}
		$q = "SELECT COUNT(file_id) AS files
			FROM #__vm_product_files
			WHERE file_product_id=".intval($pid).' '.$type_sql;
		$db->setQuery($q);
		$files = $db->loadResult();
		return $files;
	}

	/**
	 * Set the different roles available for a file
	 */
	public function getProductFilesRoles() {
	 	return array(
	 			'isDownlodable' => VmConfig::get('assets_general_path').'images/vmgeneral/downloadable.gif',
				'isImage' => VmConfig::get('assets_general_path').'images/vmgeneral/image.gif',
				'isProductImage' => VmConfig::get('assets_general_path').'images/vmgeneral/image.png',
				'isFile' => VmConfig::get('assets_general_path').'images/vmgeneral/attachment.gif',
				'isRemoteFile' => VmConfig::get('assets_general_path').'images/vmgeneral/url.gif'
		);
	}

	/**
	 * Get the image details to edit  them
	 */
	public function getImageDetails() {
		/* Check if the item is being edited or created new */
		if (JRequest::getVar('task', 'addnew') == 'addnew') {
			$productfile = new StdClass();
			$productfile->file_id = null;
			$productfile->file_is_image = null;
			$productfile->file_product_id = null;
			$productfile->file_extension = null;
			$productfile->file_url = null;
			$productfile->published = null;
			$productfile->file_name = null;
			$productfile->file_title = null;
			$productfile->isdownloadable = null;
			$productfile->product_name = null;
			return $productfile;

		}
		else {
			$db = JFactory::getDBO();
			$q = "SELECT file_id, file_is_image, file_product_id, file_extension, file_url, file_published AS published, file_name, file_title,
						IF (LOWER(attribute_name) = 'download', 1, 0) AS isdownloadable,
						product_name
				FROM #__vm_product_files
				LEFT JOIN #__vm_product_attribute
				ON #__vm_product_files.file_product_id = #__vm_product_attribute.product_id
				LEFT JOIN #__vm_product
				ON #__vm_product_files.file_product_id = #__vm_product.product_id
				WHERE #__vm_product.product_id = ".JRequest::getInt('product_id');
			$db->setQuery($q);
			return $db->loadObject();
		}

	}
}
?>
