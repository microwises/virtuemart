<?php
/**
*
* Product details view
*
* @package VirtueMart
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

// Load the view framework
jimport( 'joomla.application.component.view' );

/**
* Product details
*
* @package VirtueMart
* @author RolandD
* @author Max Milbers
*/
class VirtueMartViewProductdetails extends JView {

	/**
	* Collect all data to show on the template
	*
	* @author RolandD, Max Milbers
	*/
	function display($tpl = null) {

		//TODO get plugins running
//		$dispatcher	= JDispatcher::getInstance();
//		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		$show_prices  = VmConfig::get('show_prices',1);
		if($show_prices == '1'){
			if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
		}
		$this->assignRef('show_prices', $show_prices);


		$document = JFactory::getDocument();

		/* add javascript for price and cart */
		VmConfig::jPrice();

		$mainframe = JFactory::getApplication();
		$pathway = $mainframe->getPathway();
		$task = JRequest::getCmd('task');

		/* Set the helper path */
		$this->addHelperPath(JPATH_VM_ADMINISTRATOR.DS.'helpers');

		/* Load helpers */
		$this->loadHelper('image');


		/* Load the product */
//		$product = $this->get('product');	//Why it is sensefull to use this construction? Imho it makes it just harder
		$product_model = $this->getModel('product');

		$virtuemart_product_idArray = JRequest::getInt('virtuemart_product_id',0);
		if(is_array($virtuemart_product_idArray)){
			$virtuemart_product_id=$virtuemart_product_idArray[0];
		} else {
			$virtuemart_product_id=$virtuemart_product_idArray;
		}

		$product = $product_model->getProduct($virtuemart_product_id);

		if(empty($product->slug)){

			//Todo this should be redesigned to fit better for SEO
			$mainframe -> enqueueMessage(JText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND'));
			$virtuemart_category_id = shopFunctionsF::getLastVisitedCategoryId();
			$categoryLink='';
			if(!$virtuemart_category_id){
				$virtuemart_category_id = JRequest::getInt('virtuemart_category_id',false);
			}
			if($virtuemart_category_id){
				$categoryLink='&virtuemart_category_id='.$virtuemart_category_id;
			}

			$mainframe -> redirect( JRoute::_('index.php?option=com_virtuemart&view=category'.$categoryLink.'&error=404'));

			return;
		}

		$product_model->addImages($product);
		$this->assignRef('product', $product);

		/* Load the neighbours */
		$product->neighbours = $product_model->getNeighborProducts($product);
//		if(!empty($product->neighbours) && is_array($product->neighbours) && !empty($product->neighbours[0]))$product_model->addImages($product->neighbours);

//		$product->related = $product_model->getRelatedProducts($virtuemart_product_id);
//		if(!empty($product->related) && is_array($product->related) && !empty($product->related[0]))$product_model->addImages($product->related);

		/* Load the category */
		$category_model = $this->getModel('category');
		/* Get the category ID */
		$virtuemart_category_id = JRequest::getInt('virtuemart_category_id');
		if ($virtuemart_category_id == 0 && !empty($product)) {
			if (array_key_exists('0', $product->categories)) $virtuemart_category_id = $product->categories[0];
		}

		shopFunctionsF::setLastVisitedCategoryId($virtuemart_category_id);

		if($category_model){
			$category = $category_model->getCategory($virtuemart_category_id);
			$category_model->addImages($category);
			$this->assignRef('category', $category);

			if ($category->parents) {
				foreach ($category->parents as $c){
					$pathway->addItem($c->category_name,JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$c->virtuemart_category_id));
				}
			}
			if($category->children)	$category_model->addImages($category->children);
		}
		$format = JRequest::getCmd('format','html');
		if ($format=='html') {
			/* Set Canonic link */
			$document->addHeadLink( $product->link , 'canonical', 'rel', '' );
		}


		/* Set the titles */
		$document->setTitle( $category->category_name.' : '.$product->product_name);

		$uri = JURI::getInstance();
		//$pathway->addItem(JText::_('COM_VIRTUEMART_PRODUCT_DETAILS'), $uri->toString(array('path', 'query', 'fragment')));
		$pathway->addItem($product->product_name);

		$ratingModel = $this->getModel('ratings');

		$allowReview = $ratingModel->allowReview($product->virtuemart_product_id);
		$this->assignRef('allowReview', $allowReview);

		$showReview = $ratingModel->showReview($product->virtuemart_product_id);
		$this->assignRef('showReview', $showReview);

		if($showReview){

			$review = $ratingModel->getReviewByProduct($product->virtuemart_product_id);
			$this->assignRef('review', $review);

			$rating_reviews = $ratingModel->getReviews($product->virtuemart_product_id);
			$this->assignRef('rating_reviews', $rating_reviews);

		}

		$showRating = $ratingModel->showRating($product->virtuemart_product_id);
		$this->assignRef('showRating', $showRating);

		if($showRating){
			$vote = $ratingModel->getVoteByProduct($product->virtuemart_product_id);
			$this->assignRef('vote', $vote);

			$rating = $ratingModel->getRatingByProduct($product->virtuemart_product_id);
			$this->assignRef('rating', $rating);

		}

		$allowRating = $ratingModel->allowRating($product->virtuemart_product_id);
		$this->assignRef('allowRating', $allowRating);

		/* Check for editing access */
		/** @todo build edit page */
		if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
		if (Permissions::getInstance()->check("admin,storeadmin")) {
			$url = JRoute::_('index2.php?option=com_virtuemart&view=productdetails&task=edit&virtuemart_product_id='.$product->virtuemart_product_id);
			$edit_link = JHTML::_('link', $url, JHTML::_('image', 'images/M_images/edit.png', JText::_('COM_VIRTUEMART_PRODUCT_FORM_EDIT_PRODUCT'), array('width' => 16, 'height' => 16, 'border' => 0)));
		}
		else {
			$edit_link = "";
		}
		$this->assignRef('edit_link', $edit_link);

		/* Load the user details */
		$this->assignRef('user', JFactory::getUser());

		/* More reviews link */
		$uri = JURI::getInstance();
		$uri->setVar('showall', 1);
		$this->assignRef('more_reviews', $uri->toString());

		if ($product->metadesc) {
			$document->setDescription( $product->metadesc );
		}
		if ($product->metakey) {
			$document->setMetaData('keywords', $product->metakey);
		}

		if ($product->metarobot) {
			$document->setMetaData('robots', $product->metarobot);
		}

		if ($mainframe->getCfg('MetaTitle') == '1') {
			$document->setMetaData('title', $product->product_name);  //Maybe better product_name
		}
		if ($mainframe->getCfg('MetaAuthor') == '1') {
			$document->setMetaData('author', $product->metaauthor);
		}


		$showBasePrice = Permissions::getInstance()->check('admin'); //todo add config settings
		$this->assignRef('showBasePrice', $showBasePrice);

	    if(empty($category->category_template)){
	    	$category->category_template = VmConfig::get('categorytemplate');
	    }

	    if(empty($product->layout)){
	    	$product->layout = VmConfig::get('productlayout');
	    }

		shopFunctionsF::setVmTemplate($this,$category->category_template,0,$category->category_layout,$product->layout);

		shopFunctionsF::addProductToRecent($virtuemart_product_id);

		$currency = CurrencyDisplay::getInstance( );
		$this->assignRef('currency', $currency);

		//TODO add params, add event
//		$params = new JParameter();
//		/*
//		 * Process the prepare content plugins
//		 */
//		JPluginHelper::importPlugin('content');
//		$results = $dispatcher->trigger('onPrepareContent', array (& $product, & $params, $limitstart));
//
//		/*
//		 * Handle display events
//		 */
//		$article->event = new stdClass();
//		$results = $dispatcher->trigger('onAfterDisplayTitle', array (&$product, &$params, $limitstart));
//		$article->event->afterDisplayTitle = trim(implode("\n", $results));
//
//		$results = $dispatcher->trigger('onBeforeDisplayContent', array (&$product, &$params, $limitstart));
//		$article->event->beforeDisplayContent = trim(implode("\n", $results));
//
//		$results = $dispatcher->trigger('onAfterDisplayContent', array (&$product, &$params, $limitstart));
//		$article->event->afterDisplayContent = trim(implode("\n", $results));


		/* Display it all */
		parent::display($tpl);
	}

	private function showLastCategory($tpl) {
			$virtuemart_category_id = shopFunctionsF::getLastVisitedCategoryId();
			$categoryLink='';
			if($virtuemart_category_id){
				$categoryLink='&virtuemart_category_id='.$virtuemart_category_id;
			}
			$continue_link = JRoute::_('index.php?option=com_virtuemart&view=category'.$categoryLink);

			$continue_link_html = '<a href="'.$continue_link.'" />'.JText::_('COM_VIRTUEMART_CONTINUE_SHOPPING').'</a>';
			$this->assignRef('continue_link_html', $continue_link_html);
			/* Display it all */
			parent::display($tpl);
	}

}

// pure php no closing tag