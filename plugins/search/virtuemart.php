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
defined( '_JEXEC' ) or die( 'Restricted access' );

// $mainframe->registerEvent( 'onSearch', 'plgSearchVirtuemart' );
// $mainframe->registerEvent( 'onSearchAreas', 'plgSearchVirtuemartAreas' );

// JPlugin::loadLanguage( 'plg_search_Virtuemart' );
class plgSearchVirtueMart extends JPlugin {
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	* @return array An array of search areas
	*/
	function &onContentSearchAreas()
	{
		static $areas = array(
			'Virtuemart' => 'Products'
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
	function onContentSearch( $text, $phrase='', $ordering='', $areas=null )
	{
		$db		=& JFactory::getDBO();
		$user	=& JFactory::getUser();

		if (is_array($areas)) {
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas()))) {
				return array();
			}
		}

		$limit = $this->params->get('search_limit', 50);

		$text = trim( $text );
		if ($text == '') {
			return array();
		}

		$section = JText::_( 'Products' );
		$wheres 	= array();
		switch ($phrase) {
			case 'exact':
				$text		= $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false );
				$wheres2 	= array();
				$wheres2[] 	= 'a.product_sku LIKE '.$text;
				$wheres2[] 	= 'a.product_name LIKE '.$text;
				$wheres2[] 	= 'a.product_s_desc LIKE '.$text;
				$wheres2[] 	= 'a.product_desc LIKE '.$text;
				$wheres2[] 	= 'b.category_name LIKE '.$text;
				$where 		= '(' . implode( ') OR (', $wheres2 ) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words 	= explode( ' ', $text );
				$wheres = array();
				foreach ($words as $word)
				{
					$word		= $db->Quote( '%'.$db->getEscaped( $word, true ).'%', false );
					$wheres2 	= array();
					$wheres2[] 	= 'a.product_sku LIKE '.$word;
					$wheres2[] 	= 'a.product_name LIKE '.$word;
					$wheres2[] 	= 'a.product_s_desc LIKE '.$word;
					$wheres2[] 	= 'a.product_desc LIKE '.$word;
					$wheres2[] 	= 'b.category_name LIKE '.$word;
					$wheres[] 	= implode( ' OR ', $wheres2 );
				}
				$where 	= '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
				break;
		}
		switch ( $ordering ) {
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
				$order = 'a.cdate DESC';
				break;
			case 'oldest':
				$order = 'a.cdate ASC';
				break;
			default:
				$order = 'a.product_name DESC';
		}

		$text	= $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false );
		$query	= "SELECT DISTINCT CONCAT( a.product_name,' (',a.product_sku,')' ) AS title, a.product_id AS slug, b.category_id AS catslug,CONCAT_WS( ' | ', a.product_s_desc, a.product_desc )  AS text, b.category_name as section,
			FROM_UNIXTIME( a.cdate, '%Y-%m-%d %H:%i:%s' ) AS created, '2' AS browsernav
			FROM #__virtuemart_products AS a
			LEFT JOIN #__virtuemart_product_categories AS xref ON xref.product_id = a.product_id
			LEFT JOIN #__virtuemart_categories AS b ON b.category_id = xref.category_id"
		. ' WHERE '. $where
		. ' ORDER BY '. $order
		;
		$db->setQuery( $query, 0, $limit );
		$rows = $db->loadObjectList();

		foreach($rows as $key => $row) {
			$rows[$key]->href = 'index.php?option=com_virtuemart&view=productdetails&product_id='.$row->slug.'&category_id='.$row->catslug ;
			// $rows[$key]->text = $text;
		}

		return $rows;
	}
}
