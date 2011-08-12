<?php

/**
 * @version		$Id: virtuemart.php 2789 2011-02-28 12:41:01Z oscar $
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

require_once JPATH_SITE.'/components/com_virtuemart/router.php';
// $mainframe->registerEvent('onSearch', 'plgSearchVirtuemart');
// $mainframe->registerEvent('onSearchAreas', 'plgSearchVirtuemartAreas');

/**
 * @return array An array of search areas
 */
 class plgSearchVirtuemart extends JPlugin
{
	function onContentSearchAreas() {

		static $areas = array(
	'virtuemart' => 'Products'
		);
		return $areas;
	}

	/**
	 * virtuemart Products Search method
	 *
	 * The sql must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav
	 * @param string Target search string
	 * @param string mathcing option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 */
	function onContentSearch($text, $phrase='', $ordering='', $areas=null) {
		$db		= JFactory::getDbo();
		$app	= JFactory::getApplication();
		$user = & JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$tag = JFactory::getLanguage()->getTag();
		$searchText = $text;

		if (is_array($areas)) {
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas()))) {
				return array();
			}
		}

		// load plugin params info
		// $plugin = & JPluginHelper::getPlugin('search', 'virtuemart');
		// $pluginParams = new JParameter($plugin->params);

		// $limit = $pluginParams->def('search_limit', 50);
		$limit = $this->params->def('search_limit',		50);

		/* TO do it work with date
		$nullDate		= $db->getNullDate();
		$date = JFactory::getDate();
		$now = $date->toMySQL();
		*/

		$text = trim($text);
		if ($text == '') {
			return array();
		}

		$section = JText::_('Products');
		$wheres = array();
		switch ($phrase) {
			case 'exact':
				$text = $db->Quote('%' . $db->getEscaped($text, true) . '%', false);
				$wheres2 = array();
				$wheres2[] = 'a.product_sku LIKE ' . $text;
				$wheres2[] = 'a.product_name LIKE ' . $text;
				$wheres2[] = 'a.product_s_desc LIKE ' . $text;
				$wheres2[] = 'a.product_desc LIKE ' . $text;
				$wheres2[] = 'b.category_name LIKE ' . $text;
				$where = '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words = explode(' ', $text);
				$wheres = array();
				foreach ($words as $word) {
					$word = $db->Quote('%' . $db->getEscaped($word, true) . '%', false);
					$wheres2 = array();
					$wheres2[] = 'a.product_sku LIKE ' . $word;
					$wheres2[] = 'a.product_name LIKE ' . $word;
					$wheres2[] = 'a.product_s_desc LIKE ' . $word;
					$wheres2[] = 'a.product_desc LIKE ' . $word;
					$wheres2[] = 'b.category_name LIKE ' . $word;
					$wheres[] = implode(' OR ', $wheres2);
				}
				$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}
		switch ($ordering) {
			case 'alpha':
				$order = 'a.product_name ASC';
				break;

			case 'category':
				$order = 'b.category_name ASC, a.product_name ASC';
				break;

			case 'popular':
				$order = 'a.product_name ASC';
				break;
			case 'newest':
				$order = 'a.created_on DESC';
				break;
			case 'oldest':
				$order = 'a.created_on ASC';
				break;
			default:
				$order = 'a.product_name DESC';
		}
		// search product
		$text = $db->Quote('%' . $db->getEscaped($text, true) . '%', false);
		$query = "SELECT DISTINCT CONCAT( a.product_name,' (',a.product_sku,')' ) AS title, a.virtuemart_product_id , b.virtuemart_category_id ,   a.product_s_desc   AS text, b.category_name as section,
				 a.created_on as created, '2' AS browsernav
				FROM #__virtuemart_products AS a
				LEFT JOIN #__virtuemart_product_categories AS xref ON xref.virtuemart_product_id = a.virtuemart_product_id
				LEFT JOIN #__virtuemart_categories AS b ON b.virtuemart_category_id = xref.virtuemart_category_id"
				. ' WHERE ' . $where
				. ' ORDER BY ' . $order
		;
		$db->setQuery($query, 0, $limit);
		$rows = $db->loadObjectList();
		if ($rows) {
			foreach ($rows as $key => $row) {
				$rows[$key]->href = 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $row->virtuemart_product_id . '&virtuemart_category_id=' . $row->virtuemart_category_id;
				// $rows[$key]->text = $text;
			}
		}
		return $rows;
	}
}
