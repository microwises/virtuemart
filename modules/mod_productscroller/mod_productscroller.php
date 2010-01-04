<?php
/**
* VirtueMart Product Scroller Module
* NOTE: THIS MODULE REQUIRES AN INSTALLED VirtueMart COMPONENT!
*
* @version $Id$
* @package VirtueMart
* @subpackage modules
* 
* @copyright (C) 2005-2007 Soeren Eberhardt
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*/
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

global $my, $mosConfig_absolute_path;

// Load the virtuemart main parse code
if( file_exists(dirname(__FILE__).'/../../components/com_virtuemart/virtuemart_parser.php' )) {
	require_once( dirname(__FILE__).'/../../components/com_virtuemart/virtuemart_parser.php' );
} else {
	require_once( dirname(__FILE__).'/../components/com_virtuemart/virtuemart_parser.php' );
}

/**
* This class sets all Parameters.
* Must first call the MOS function, something like: 
* $params = mosParseParams( $module->params );
* and send the $params variable to this class (productScroller)
* @param $params the results from mosParseParams( $module->params );
* @example $scroller = new productScroller($params);
*/
if( !class_exists('productScroller')) { // Prevent double class declaration

	class productScroller {
		/**
		* @var $NumberOfProducts
		*/
		var $NumberOfProducts = 5;
		/**
		* @var $featuredProducts
		*/
		var $featuredProducts = false;
		/**
	  	* // scroll, alternate, slide
	  	* @var $ScrollBehavior
	  	*/
		var $ScrollBehavior = 'scroll';
		/**
  		* @var $PS_DIRECTION
  		*/
		var $ScrollDirection = 'up';
		/**
  		* @var $ScrollHeight
  		*/
		var $ScrollHeight = '125';
		/**
  		* @var $ScrollWidth
  		*/
		var $ScrollWidth = '150';
		/**
	 	 * @var $ScrollAmount
	 	 */
		var $ScrollAmount = '2';
		/**
  		* @var $ScrollDelay
 		 */
		var $ScrollDelay = '80';
		/**
 		 * @var $ScrollAlign
  		*/
		var $ScrollAlign = 'center';
		/**
  		* // newest [asc], oldest [desc], random [rand]
 		* @var $SortMethod
  		*/
		var $ScrollSortMethod = 'random';
		/**
  		* @var $ScrollTitles
  		*/
		var $ScrollTitles = 'yes';
		/**
  		* @var $ScrollSpaceChar
  		*/
		var $ScrollSpaceChar = '&nbsp;';
		/**
  		* @var $ScrollSpaceCharTimes
  		*/
		var $ScrollSpaceCharTimes = 5;
		/**
  		* @var $ScrollLineChar
  		*/
		var $ScrollLineChar = '<br />';
		/**
  		* @var $ScrollLineCharTimes
  		*/
		var $ScrollLineCharTimes = 2;
		/**
  		* @var $ScrollSection
  		*/
		var $ScrollSection = 0;

		// CSS override -----------------------
		/**
  		* @var $ScrollCSSOverride
  		*/
		var $ScrollCSSOverride = 'no';
		/**
  		* @var $ScrollTextAlign
  		*/
		var $ScrollTextAlign = 'left';
		/**
  		* @var $ScrollTextWeight
  		*/
		var $ScrollTextWeight = 'normal';
		/**
  		* @var $ScrollTextSize
  		*/
		var $ScrollTextSize = '10';
		/**
  		* @var $ScrollTextColor
  		*/
		var $ScrollTextColor = '#000000';
		/**
  		* @var $ScrollBGColor
  		*/
		var $ScrollBGColor = 'transparent';
		/**
  		* @var $ScrollMargin
  		*/
		var $ScrollMargin = '2';

		var $params = null;
		/**
        * set mammeters
        */ 
		function productScroller (&$params) {

			$this->params = $params;
			// standard mammeters
			$this->show_product_name                =  $params->get('show_product_name', "yes");
			$this->show_addtocart                   =  $params->get('show_addtocart', "yes");
			$this->show_price                               =  $params->get('show_price', "yes");
			$this->category_id                              =  intval( $params->get('category_id', 0 ) );
			// Limit by NoP
			$this->NumberOfProducts                 =  $params->get('NumberOfProducts', $this->NumberOfProducts);
			$this->featuredProducts					= $params->get('featuredProducts', $this->featuredProducts);

			$this->ScrollSection                    =  $params->get('ScrollSection', $this->ScrollSection);
			$this->ScrollBehavior           =  $params->get('ScrollBehavior', $this->ScrollBehavior);
			$this->ScrollDirection          =  $params->get('ScrollDirection', $this->ScrollDirection);
			$this->ScrollHeight             =  $params->get('ScrollHeight', $this->ScrollHeight);
			$this->ScrollWidth              =  $params->get('ScrollWidth', $this->ScrollWidth);
			$this->ScrollAmount             =  $params->get('ScrollAmount', $this->ScrollAmount);
			$this->ScrollDelay              =  $params->get('ScrollDelay', $this->ScrollDelay);
			$this->ScrollAlign              =  $params->get('ScrollAlign', $this->ScrollAlign);
			$this->ScrollSortMethod			=  $params->get('ScrollSortMethod', $this->ScrollSortMethod);
			$this->ScrollTitles             =  $params->get('ScrollTitles', $this->ScrollTitles);
			$this->ScrollSpaceChar         =  $params->get('ScrollSpaceChar', $this->ScrollSpaceChar);
			$this->ScrollSpaceCharTimes   =  $params->get('ScrollSpaceCharTimes', $this->ScrollSpaceCharTimes);
			$this->ScrollLineChar          =  $params->get('ScrollLineChar', $this->ScrollLineChar);
			$this->ScrollLineCharTimes    =  $params->get('ScrollLineCharTimes', $this->ScrollLineCharTimes);
			// customization mammeters
			$this->ScrollCSSOverride       =  $params->get('ScrollCSSOverride', $this->ScrollCSSOverride);
			$this->ScrollTextAlign          =  $params->get('ScrollTextAlign', $this->ScrollTextAlign);
			$this->ScrollTextWeight         =  $params->get('ScrollTextWeight', $this->ScrollTextWeight);
			$this->ScrollTextSize           =  $params->get('ScrollTextSize', $this->ScrollTextSize);
			$this->ScrollTextColor          =  $params->get('ScrollTextColor', $this->ScrollTextColor);
			$this->ScrollBGColor           =  $params->get('ScrollBGColor', $this->ScrollBGColor);
			$this->ScrollMargin             =  $params->get('ScrollMargin', $this->ScrollMargin);
		}

		/**
		* Display Product Data
		*/ 
		function displayScroller (&$rows) {
			global $mosConfig_absolute_path;

			$database = new ps_DB();
			require_once( CLASSPATH."ps_product.php" );
			$ps_product = new ps_product;

			$cnt=0;
			if($this->ScrollCSSOverride=='yes') {
				$txt_size = $this->ScrollTextSize . 'px';
				$margin = $this->ScrollMargin . 'px';
				//$height=($height-intval($margin+0));
				//$width=($width-intval($margin+30));
				echo $this->params->get( 'pretext', "");
				echo " <div style=\"text-align:".$this->ScrollAlign.";background-color: ".$this->ScrollBGColor."; width:".$this->ScrollWidth."px;
                       margin-top: $margin; margin-right: $margin; margin-bottom: $margin; margin-left: $margin;\" >
               <marquee behavior=\"".$this->ScrollBehavior."\" 
                        direction=\"".$this->ScrollDirection."\"  
                        height=\"".$this->ScrollHeight."\"
                        width=\"".$this->ScrollWidth."\"
                        scrollamount=\"".$this->ScrollAmount."\"
                        scrolldelay=\"".$this->ScrollDelay."\"
                        truespeed=\"true\" onmouseover=\"this.stop()\" onmouseout=\"this.start()\"
                        style=\"text-align: ".$this->ScrollTextAlign."; color: ".$this->ScrollTextColor."; font-weight: ".$this->ScrollTextWeight."; font-size: $txt_size;\" >"; 
			}
			else {

				echo " <div style=\"width:".$this->ScrollWidth."px;text-align:".$this->ScrollAlign.";\">
               <marquee behavior=\"".$this->ScrollBehavior."\" 
                        direction=\"".$this->ScrollDirection."\"  
                        height=\"".$this->ScrollHeight."\"
                        width=\"".$this->ScrollWidth."\"
                        scrollamount=\"".$this->ScrollAmount."\"
                        scrolldelay=\"".$this->ScrollDelay."\"
                        truespeed=\"true\" onmouseover=\"this.stop()\" onmouseout=\"this.start()\">"; 
			}
			$show_product_name = ( $this->show_product_name == "yes" ) ? true : false;
			$show_addtocart = ( $this->show_addtocart == "yes" ) ? true : false;
			$show_price = ( $this->show_price == "yes" ) ? true : false;
			if (($this->ScrollDirection=='left') || ($this->ScrollDirection=='right')) {
				echo '<table border="0"><tr>';
			}
			foreach($rows as $row) {
				if (($this->ScrollDirection=='left') || ($this->ScrollDirection=='right')) {
					echo '<td style="vertical-align:top;padding: 2px 5px 2px 5px;">';
				}
				$ps_product->show_snapshot( $row->product_sku, $show_price, $show_addtocart, $show_product_name );
				if (($this->ScrollDirection=='left') || ($this->ScrollDirection=='right')) {
					echo '</td>';

				} else {
					for($i=0;$i<$this->ScrollLineCharTimes;$i++) {
						echo $this->ScrollLineChar;
					}
				}
			}
			if (($this->ScrollDirection=='left') || ($this->ScrollDirection=='right')) {
				echo '</tr></table>';
			}
			echo "    </marquee>
            </div>";
		} // end displayScroller

	} // end class productScroller

	/**
	* Helper DB function
	*/
	function getProductSKU( $limit=0, $how=null, $category_id=0, $featuredProducts='no' ) {
		global $my, $mosConfig_offset;

		$database = new ps_DB();

		if($limit>0) {
			$limit = "LIMIT $limit";
		} else {
			$limit = "";
		}

		$query = "SELECT p.product_sku FROM #__{vm}_product AS p";

		$query .= "\nJOIN #__{vm}_product_category_xref as pc ON p.product_id=pc.product_id";
		if( $category_id != 0 ) {
			$query .= " AND pc.category_id=$category_id";
		}
		$query .= "\nJOIN #__{vm}_category as c ON pc.category_id=c.category_id";
		
		$query .= "\n WHERE p.product_publish = 'Y' AND c.published = '1' AND product_parent_id=0 ";
		if( CHECK_STOCK && PSHOP_SHOW_OUT_OF_STOCK_PRODUCTS != "1") {
			$query .= " AND product_in_stock > 0 ";
		}
		
		if( $featuredProducts=='yes' ) {
			$query .= "\n AND product_special = 'Y' ";
		}
		
		switch( $how ) {
			case 'random':
				$query .= "\n ORDER BY RAND() $limit";
				break;
			case 'newest':
				$query .= "\n ORDER BY p.cdate DESC $limit";
				break;
			case 'oldest':
				$query .= "\n ORDER BY p.cdate ASC $limit";
				break;
			default:
				$query .= "\n ORDER BY p.cdate DESC $limit";
				break;
		}
		$database->query( $query );

		$rows = $database->record;
		return $rows;
	}
}

// start of Product Scroller Script
$params =& new vmParameters( $module->params );
$scroller =& new productScroller($params);

/**
* Load Products
**/ 
$rows = getProductSKU( $scroller->NumberOfProducts, $scroller->ScrollSortMethod, $scroller->category_id, $scroller->featuredProducts );

/**
* Display Product Scroller
**/ 
$scroller->displayScroller($rows);

?>