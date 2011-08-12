<?php
define( '_VALID_MOS', 1 );
define( '_JEXEC', 1 );

/**
 * Virtuemart Product SOA Connector (For J16 and VM2)
 * Virtuemart Product SOA Connector (Provide functions GetProductFromId, GetProductFromId, GetChildsProduct, GetProductsFromCategory)
 * The return classes are a "Product", "Currencies", "Countries" ... 
 * attributes, parent produit, child id)
 *
 * @package    com_vm_soa
 * @subpackage component
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  2010 Mickael Cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    $Id:$
 */
 
/** loading framework **/
include_once('VM_Commons.php');


/**
 * Class Product
 *
 * Class "Product" with attribute : product_id, virtuemart_vendor_id, product_sku, product_name, product_s_desc, product_length)
 *
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class Product {
		public $product_id="";
		public $virtuemart_vendor_id="";
		public $product_parent_id="";
		public $product_sku="";
		public $product_name="";
		public $slug="";
		public $product_s_desc="";
		public $product_desc="";
		public $product_weight="";
		public $product_weight_uom="";
		public $product_length="";
		public $product_width="";
		public $product_height="";
		public $product_lwh_uom="";
		public $product_url="";
		public $product_in_stock="";
		public $low_stock_notification="";
		public $product_available_date="";
		public $product_availability="";
		public $product_special="";
		public $ship_code_id="";
		public $product_sales="";
		public $product_unit="";
		public $product_packaging="";
		public $product_order_levels="";
		public $hits="";
		public $intnotes="";
		public $metadesc="";
		public $metakey="";
		public $metarobot="";
		public $metaauthor="";
		public $layout="";
		public $published="";
		public $product_categories="";
		public $manufacturer_id="";
		public $prices="";
		
		
		//constructeur
		/**
		 * Enter description here...
		 *
		 * @param String $name
		 * @param String $price
		 * @param String $description
		 * @param String $image
		 * @param String $product_order_levels
		 * @param String $id
		 */
		function __construct($product_id, $virtuemart_vendor_id, $product_parent_id, $product_sku, $product_name, $slug, $product_s_desc, $product_desc, $product_weight
							, $product_weight_uom, $product_length, $product_width, $product_height, $product_lwh_uom, $product_url, $product_in_stock, $low_stock_notification, $product_available_date, $product_availability, $product_special
							, $ship_code_id, $product_sales, $product_unit, $product_packaging, $product_order_levels, $hits, $intnotes, $metadesc, $metakey, $metarobot, $metaauthor, $layout, $published,$product_categories,$manufacturer_id,$prices) {
			
			$this->product_id 				= $product_id;
			$this->virtuemart_vendor_id 	= $virtuemart_vendor_id;
			$this->product_parent_id 		= $product_parent_id;
			$this->product_sku 				= $product_sku;
			$this->product_name 			= $product_name;
			$this->slug 					= $slug;
			$this->product_s_desc 			= $product_s_desc;
			$this->product_desc 			= $product_desc;
			$this->product_weight 			= $product_weight;
			$this->product_weight_uom 		= $product_weight_uom;
			$this->product_length 			= $product_length;
			$this->product_width 			= $product_width;
			$this->product_height 			= $product_height;
			$this->product_lwh_uom 			= $product_lwh_uom;
			$this->product_url 				= $product_url;
			$this->product_in_stock 		= $product_in_stock;
			$this->low_stock_notification 	= $low_stock_notification;
			$this->product_available_date 	= $product_available_date;
			$this->product_availability 	= $product_availability;
			$this->product_special 			= $product_special;
			$this->ship_code_id 			= $ship_code_id;
			$this->product_sales 			= $product_sales;
			$this->product_unit 			= $product_unit;
			$this->product_packaging 		= $product_packaging;
			$this->product_order_levels 	= $product_order_levels;
			$this->hits 					= $hits;
			$this->intnotes 				= $intnotes;
			$this->metadesc 				= $metadesc;
			$this->metakey 					= $metakey;
			$this->metarobot 				= $metarobot;
			$this->metaauthor 				= $metaauthor;
			$this->layout 					= $layout;
			$this->published 				= $published;
			$this->product_categories 		= $product_categories;
			$this->manufacturer_id 			= $manufacturer_id;
			$this->prices 					= $prices;
			
		}
	}

/**
 * Class OrderItemInfo
 *
 * Class "OrderItemInfo" with attribute : id, name, description, price, quantity, image, fulliamage ,
 * attributes, parent Product, child id)
 *
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class OrderItemInfo {
		
		public $order_id="";
		public $userinfo_id="";
		public $vendor_id="";
		public $product_id="";
		public $order_item_sku="";
		public $order_item_name="";
		public $product_quantity="";
		public $product_item_price="";
		public $product_final_price="";
		public $order_item_currency="";
		public $order_status="";
		public $product_attribute;
		public $created_on="";
		public $modified_on="";
		
		
		//constructeur
		/**
		 * Enter description here...
		 *
		 * @param String $name
		 * @param String $price
		 * @param String $description
		 * @param String $image
		 * @param String $fullimage
		 * @param String $id
		 */
		function __construct($order_id, $userinfo_id, $vendor_id, $product_id, $order_item_sku, $order_item_name, $product_quantity, $product_item_price, $product_final_price,$order_item_currency,$order_status,$product_attribute,$created_on,$modified_on) {
			
			$this->order_id = $order_id;
			$this->userinfo_id = $userinfo_id;
			$this->vendor_id = $vendor_id;
			$this->product_id = $product_id;
			$this->order_item_sku = $order_item_sku;
			$this->order_item_name = $order_item_name; 
			$this->product_quantity = $product_quantity;
			$this->product_item_price = $product_item_price;
			$this->product_final_price = $product_final_price;
			$this->order_item_currency = $order_item_currency;
			$this->order_status = $order_status;
			$this->product_attribute = $product_attribute;
			$this->created_on = $created_on;
			$this->modified_on = $modified_on;
			
			
		}
	}	

	/**
 * Class Currency
 *
 * Class "Currency" with attribute : id, name, code, 
 *
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class Currency {
	
		public $currency_id="";
		public $vendor_id="";
		public $currency_name="";
		public $currency_code_2="";
		public $currency_code_3="";
		public $currency_numeric_code="";
		public $currency_exchange_rate="";
		public $currency_symbol="";
		public $currency_decimal_place="";
		public $currency_decimal_symbol="";
		public $currency_thousands="";
		public $currency_positive_style="";
		public $currency_negative_style="";
		public $ordering="";
		public $shared="";
		public $published="";
		

		//constructeur
		/**
		 * Enter description here...
		 *
		 * @param String $currency_id
		 * @param String $currency_name
		 * @param String $currency_code_2
		 */
		function __construct($currency_id, $vendor_id, $currency_name, $currency_code_2, $currency_code_3, $currency_numeric_code, $currency_exchange_rate, $currency_symbol
						, $currency_decimal_place, $currency_decimal_symbol, $currency_thousands, $currency_positive_style, $currency_negative_style, $ordering, $shared, $published) {
			
			$this->currency_id 				= $currency_id;
			$this->vendor_id 				= $vendor_id;
			$this->currency_name 			= $currency_name;
			$this->currency_code_2 			= $currency_code_2;
			$this->currency_code_3 			= $currency_code_3;
			$this->currency_numeric_code 	= $currency_numeric_code;
			$this->currency_exchange_rate 	= $currency_exchange_rate;
			$this->currency_symbol 			= $currency_symbol;
			$this->currency_decimal_place 	= $currency_decimal_place;
			$this->currency_decimal_symbol 	= $currency_decimal_symbol;
			$this->currency_thousands 		= $currency_thousands;
			$this->currency_positive_style 	= $currency_positive_style;
			$this->currency_negative_style 	= $currency_negative_style;
			$this->ordering 				= $ordering;
			$this->shared 					= $shared;
			$this->published 				= $published;
			
			
		}
	}	
	
	
	/**
	 * Class ProductPrice
	 *
	 * Class "ProductPrice" with attribute : product_price_id ...
	 *
	 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
	 * @copyright  Mickael cabanas
	 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
	 * @version    Release:
	 */
	class ProductPrice {
		public $product_price_id="";
		public $product_id="";
		public $product_price="";
		public $product_currency="";
		public $product_price_vdate="";
		public $product_price_edate="";
		public $created_on="";
		public $modified_on="";
		public $shopper_group_id="";
		public $price_quantity_start="";
		public $price_quantity_end="";
		public $override="";
		public $product_override_price="";
		public $product_tax_id="";
		public $product_discount_id="";

		//constructeur
		/**
		 * Enter description here...
		 *
		 * @param String $product_price_id
		 * @param String $product_id
		 * @param String $product_price
		 * ...
		 */
		function __construct($product_price_id, $product_id, $product_price,$product_currency,$product_price_vdate,$product_price_edate,
							$created_on,$modified_on,$shopper_group_id,$price_quantity_start,$price_quantity_end,$override,$product_override_price,$product_tax_id,$product_discount_id) {
			
			$this->product_price_id 		= $product_price_id;
			$this->product_id 				= $product_id;
			$this->product_price 			= $product_price;
			$this->product_currency 		= $product_currency;
			$this->product_price_vdate 		= $product_price_vdate;
			$this->product_price_edate 		= $product_price_edate;
			$this->created_on 				= $created_on;
			$this->modified_on 				= $modified_on;
			$this->shopper_group_id 		= $shopper_group_id;
			$this->price_quantity_start 	= $price_quantity_start;
			$this->price_quantity_end 		= $price_quantity_end;
			$this->override 				= $override;
			$this->product_override_price 	= $product_override_price;
			$this->product_tax_id 			= $product_tax_id;
			$this->product_discount_id 		= $product_discount_id;
		}
	}	
	
	/**
	 * Class ProductFile
	 *
	 * Class "ProductFile" with attribute : file_id ...
	 *
	 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
	 * @copyright  Mickael cabanas
	 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
	 * @version    Release:
	 */
	class ProductFile {
		public $file_id="";
		public $file_product_id="";
		public $file_name="";
		public $file_title="";
		public $file_description="";
		public $file_extension="";
		public $file_mimetype="";
		public $file_url="";
		public $file_published="";
		public $file_is_image="";
		public $file_image_height="";
		public $file_image_width="";
		public $file_image_thumb_height="";
		public $file_image_thumb_width="";

		//constructeur
		/**
		 * Enter description here...
		 *
		 * @param String $file_id
		 * @param String $file_product_id
		 * @param String $file_name
		 * ...
		 */
		function __construct($file_id, $file_product_id, $file_name,$file_title,$file_description,$file_extension,$file_mimetype,$file_url,$file_published,$file_is_image,$file_image_height,$file_image_width,$file_image_thumb_height,$file_image_thumb_width) {
			$this->file_id = $file_id;
			$this->file_product_id = $file_product_id;
			$this->file_name = $file_name;
			$this->file_title = $file_title;
			$this->file_description = $file_description;
			$this->file_extension = $file_extension;
			$this->file_mimetype = $file_mimetype;
			$this->file_url = $file_url;
			$this->file_published = $file_published;
			$this->file_is_image = $file_is_image;
			$this->file_image_height = $file_image_height;
			$this->file_image_width = $file_image_width;
			$this->file_image_thumb_height = $file_image_thumb_height;
			$this->file_image_thumb_width = $file_image_thumb_width;
		}
	}	
	
	/**
	 * Class Tax
	 *
	 * Class "Tax" with attribute : tax_rate_id
	 *
	 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
	 * @copyright  Mickael cabanas
	 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
	 * @version    Release:
	 */
	class Tax {
		public $tax_rate_id="";
		public $vendor_id="";
		public $tax_state="";
		public $tax_country="";
		public $mdate="";
		public $tax_rate="";

		//constructeur
		/**
		 * Enter description here...
		 *
		 * @param String $tax_rate_id
		 * @param String $vendor_id
		 * @param String $tax_state
		 */
		function __construct($tax_rate_id, $vendor_id, $tax_state,$tax_country,$mdate,$tax_rate) {
			$this->tax_rate_id = $tax_rate_id;
			$this->vendor_id = $vendor_id;
			$this->tax_state = $tax_state;
			$this->tax_country = $tax_country;
			$this->mdate = $mdate;
			$this->tax_rate = $tax_rate;
		}
	}	
	
	/**
	 * Class Discount
	 *
	 * Class "Discount" with attribute : discount_id ...
	 *
	 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
	 * @copyright  Mickael cabanas
	 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
	 * @version    Release:
	 */
	class Discount {
		public $discount_id="";
		public $vendor_id="";
		public $calc_name="";
		public $calc_descr="";
		public $calc_kind="";
		public $calc_value_mathop="";
		public $calc_value="";
		public $calc_currency="";
		public $calc_shopper_published="";
		public $calc_vendor_published="";
		public $publish_up="";
		public $publish_down="";
		public $calc_qualify="";
		public $calc_affected="";
		public $calc_amount_cond="";
		public $calc_amount_dimunit="";
		public $for_override="";
		public $ordering="";
		public $shared="";
		public $published="";
		public $discount_cat_ids="";
		public $discount_countries_ids="";
		public $discount_shoppergroups_ids="";
		public $discount_states_ids="";
		

		//constructeur
		/**
		 * Enter description here...
		 *
		 * @param String $discount_id
		 * @param String $vendor_id
		 * @param String $calc_name
		 * @param String $calc_descr
		 * @param String $calc_kind
		 */
		function __construct($discount_id, $vendor_id, $calc_name, $calc_descr, $calc_kind, $calc_value_mathop, $calc_value,
							$calc_currency, $calc_shopper_published, $calc_vendor_published, $publish_up, $publish_down,
							$calc_qualify, $calc_affected, $calc_amount_cond, $calc_amount_dimunit, $for_override, 
							$ordering, $shared, $published,$discount_cat_ids,$discount_countries_ids,$discount_shoppergroups_ids,$discount_states_ids) {
			
			$this->discount_id 				= $discount_id;
			$this->vendor_id 				= $vendor_id;
			$this->calc_name 				= $calc_name;
			$this->calc_descr 				= $calc_descr;
			$this->calc_kind 				= $calc_kind;
			$this->calc_value_mathop 		= $calc_value_mathop;
			$this->calc_value 				= $calc_value;
			$this->calc_currency 			= $calc_currency;
			$this->calc_shopper_published 	= $calc_shopper_published;
			$this->calc_vendor_published 	= $calc_vendor_published;
			$this->publish_up 				= $publish_up;
			$this->publish_down 			= $publish_down;
			$this->calc_qualify 			= $calc_qualify;
			$this->calc_affected 			= $calc_affected;
			$this->calc_amount_cond 		= $calc_amount_cond;
			$this->calc_amount_dimunit 		= $calc_amount_dimunit;
			$this->for_override 			= $for_override;
			$this->ordering 				= $ordering;
			$this->shared 					= $shared;
			$this->published 				= $published;
			$this->discount_cat_ids 		= $discount_cat_ids;
			$this->discount_countries_ids 	= $discount_countries_ids;
			$this->discount_shoppergroups_ids = $discount_shoppergroups_ids;
			$this->discount_states_ids 		= $discount_states_ids;
		}
	}	
	
/**
 * Class AvalaibleImage
 *
 * Class "AvalaibleImage" with attribute : id, name, code, 
 *
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class AvalaibleImage {
		public $image_name="";
		public $image_url="";
		public $realpath="";
		public $image_dir="";

		//constructeur
		/**
		 * Enter description here...
		 *
		 * @param String $image_name
		 * @param String $image_url
		 */
		function __construct($image_name, $image_url, $realpath,$image_dir) {
			$this->image_name = $image_name;
			$this->image_url = $image_url;	
			$this->realpath = $realpath;	
			$this->image_dir = $image_dir;			
		}
	}	
	
	/**
	 * Class AvalaibleImage
	 *
	 * Class "AvalaibleImage" with attribute : id, name, code, 
	 *
	 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
	 * @copyright  Mickael cabanas
	 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
	 * @version    Release:
	 */
	class AvalaibleFile {
		public $file_name="";
		public $file_url="";
		public $realpath="";

		//constructeur
		/**
		 * Enter description here...
		 *
		 * @param String $file_name
		 * @param String $file_url
		 */
		function __construct($file_name, $file_url, $realpath) {
			$this->file_name = $file_name;
			$this->file_url = $file_url;
			$this->realpath = $realpath;			
		}
	}	
	
	/**
	 * Class ProductVote
	 *
	 * Class "ProductVote" with attribute : id, name, code, 
	 *
	 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
	 * @copyright  Mickael cabanas
	 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
	 * @version    Release:
	 */
	class ProductVote {
		public $product_id="";
		public $product_name="";
		public $product_sku="";
		public $votes="";
		public $allvotes="";
		public $rating="";
		public $lastip="";

		//constructeur
		/**
		 * Enter description here...
		 *
		 * @param String $product_id
		 * @param String $product_name
		 */
		function __construct($product_id, $product_name, $product_sku,$votes, $allvotes, $rating, $lastip) {
			$this->product_id = $product_id;
			$this->product_name = $product_name;
			$this->product_sku = $product_sku;
			$this->votes = $votes;
			$this->allvotes = $allvotes;
			$this->rating = $rating;
			$this->lastip = $lastip;			
		}
	}	
	
	/**
	 * Class productReview
	 *
	 * Class "productReview" with attribute : id, name, code, 
	 *
	 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
	 * @copyright  Mickael cabanas
	 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
	 * @version    Release:
	 */
	class ProductReview {
		public $review_id="";
		public $product_id="";
		public $comment="";
		public $userid="";
		public $time="";
		public $user_rating="";
		public $review_ok="";
		public $review_votes="";
		public $published="";

		//constructeur
		/**
		 * Enter description here...
		 *
		 * @param String $review_id
		 * @param String $product_id
		 */
		function __construct($review_id, $product_id, $comment,$userid, $time, $user_rating, $review_ok, $review_votes, $published) {
			$this->review_id = $review_id;
			$this->product_id = $product_id;
			$this->comment = $comment;
			$this->userid = $userid;
			$this->time = $time;
			$this->user_rating = $user_rating;
			$this->review_ok = $review_ok;	
			$this->review_votes = $review_votes;
			$this->published = $published;			
		}
	}	
	
		
/**
 * Class Media
 *
 * Class "Media" with attribute : id, name, description,  image, fulliamage , parent category
 * attributes, parent produit, child id)
 *
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class Media {
		public $virtuemart_media_id="";
		public $virtuemart_vendor_id="";
		public $file_title="";
		public $file_description="";
		public $file_meta="";
		public $file_mimetype="";
		public $file_type="";
		public $file_url="";
		public $file_url_thumb="";
		public $file_is_product_image="";	
		public $file_is_downloadable="";
		public $file_is_forSale="";
		public $file_params="";	
		public $ordering="";
		public $shared="";
		public $published="";
		
		
				
		//constructeur
		function __construct($virtuemart_media_id, $virtuemart_vendor_id, $file_title, $file_description, $file_meta, $file_mimetype, $file_type, $file_url, $file_url_thumb,
								$file_is_product_image,$file_is_downloadable,$file_is_forSale,$file_params,$ordering,$shared,$published) {
								
			$this->virtuemart_media_id = $virtuemart_media_id;
			$this->virtuemart_vendor_id = $virtuemart_vendor_id;
			$this->file_title = $file_title;
			$this->file_description = $file_description;
			$this->file_meta = $file_meta;
			$this->file_mimetype = $file_mimetype;
			$this->file_type = $file_type;
			$this->file_url = $file_url;
			$this->file_url_thumb = $file_url_thumb;
			$this->file_is_product_image = $file_is_product_image;
			$this->file_is_downloadable = $file_is_downloadable;
			$this->file_is_forSale = $file_is_forSale;
			$this->file_params = $file_params;
			$this->ordering = $ordering;
			$this->shared = $shared;
			$this->published = $published;
			
			
		}
	}
/**
 * Class CommonReturn
 *
 * Class "CommonReturn" with attribute : returnCode, message, code, 
 *
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class CommonReturn {
		public $returnCode="";
		public $message="";
		public $returnData="";

		//constructeur
		/**
		 * Enter description here...
		 *
		 * @param String $returnCode
		 * @param String $message
		 */
		function __construct($returnCode, $message, $returnData) {
			$this->returnCode = $returnCode;
			$this->message = $message;	
			$this->returnData = $returnData;				
		}
	}		

	
	/**
    * This function get Attributes for a product ID
	* (not expose as WS)
    * @param string The if of the product
    * @return array of attribute and value
   */
	function getAttributes($product_id){
	
		////////////// WARNING TABLE NOT IN VM2 ////////////////
		$db = JFactory::getDBO();	
		$query  = "SELECT at.attribute_name, at.attribute_value  ";
		$query .= "FROM #__virtuemart_product_attribute at WHERE ";
		$query .= "at.product_id = '$product_id' ";
		$query .= " LIMIT 0,100 "; 
		$db->setQuery($query);
		
		$rows = $db->loadObjectList();
		
		foreach ($rows as $row){
				$attributesArray = array("name" => $row->attribute_name, "value" => $row->attribute_value);
		}
		return $attributesArray;
		
		
		/*$list  = "SELECT at.attribute_name, at.attribute_value  ";
		$list .= "FROM #__{vm}_product_attribute at WHERE ";
		$q .= "at.product_id = '$product_id' ";
		$list .= $q . " LIMIT 0,100 "; 
		
		$db = new ps_DB;
		$db->query($list);
		
		while ($db->next_record()) {
				 			  			 
			  $attributesArray = array("name" => $db->f("attribute_name"), "value" => $db->f("attribute_value") );
		}
		return $attributesArray;*/
	}

	/**
    * This function get categoriesID for a product ID
	* (not expose as WS)
    * @param string The if of the product
    * @return array of categorie id
   */
	function getCategoriesIds($product_id){
		
		$db = JFactory::getDBO();	
				
		$query  = "SELECT `virtuemart_category_id` FROM `#__virtuemart_product_categories` ";
		$query .= "WHERE `virtuemart_product_id` = '" . $product_id . "' ";
		$query .= " LIMIT 0,500 "; 
		
		$db->setQuery($query);
		
		$rows = $db->loadObjectList();
		
		$str = "";
		foreach ($rows as $row){
			$str .= $row->virtuemart_category_id."|";
		}
		
		return $str;

	}	
	/**
    * This function get getManufacturerId for a product ID
	* (not expose as WS)
    * @param string The if of the product
    * @return array of categorie id
   */
	function getManufacturerId($product_id){
		
		$db = JFactory::getDBO();	
				
		$query  = "SELECT `virtuemart_manufacturer_id` FROM `#__virtuemart_product_manufacturers` ";
		$query .= "WHERE `virtuemart_product_id` = '" . $product_id . "' ";
		$query .= " LIMIT 0,500 "; 
		
		$db->setQuery($query);
		
		$rows = $db->loadObjectList();
		
		$mf_id = "";
		foreach ($rows as $row){
			$mf_id .= $row->virtuemart_manufacturer_id;
		}
		
		return $mf_id;
	}	
	
	/**
    * This function get getManufacturerId for a product ID
	* (not expose as WS)
    * @param string The if of the product
    * @return array of categorie id
   */
	function getPrices($product_id){
		
		$db = JFactory::getDBO();	
				
		$query  = "SELECT * FROM `#__virtuemart_product_prices` pr ";
		$query .= "JOIN `#__virtuemart_currencies` cur ON cur.virtuemart_currency_id = pr.virtuemart_currency_id";
		$query .= "WHERE `virtuemart_product_id` = '" . $product_id . "' ";
		$query .= " LIMIT 0,500 "; 
		
		$db->setQuery($query);
		
		$rows = $db->loadObjectList();
		
		$price = "";
		foreach ($rows as $row){
			$price .= $row->product_price;
			$price .= ' '.$row->currency_code_3;
		}
		
		return $price;
	}	
	
	/**
    * This function get Product for a product ID
	* (expose as WS)
    * @param string The if of the product
    * @return array (Product)
   */
	function GetProductFromId($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_getprod')==0){
			$result = "true";
		}
				
		//Auth OK
		if ($result == "true"){
			
			$product_id = $params->product_id;
			
			if (!class_exists( 'VirtueMartModelProduct' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\product.php');
			$VirtueMartModelProduct = new VirtueMartModelProduct;
			
			$ProductDetails = $VirtueMartModelProduct->getProduct($product_id);
			
			unset($prod_prices);//because getprices for each product could be long
			if ($params->include_prices == 'Y' || $params->include_prices == '1' ){
				unset($params->shopper_group_id);
				unset($params->product_currency);
				$params->product_id = $ProductDetails->virtuemart_product_id;
				$prod_prices = GetProductPrices($params);
			}
			if ($ProductDetails->virtuemart_product_id==0){
				return new SoapFault("GetProductFromIdFault", "No result found");
			}
			$Product = new Product($ProductDetails->virtuemart_product_id ,
									$ProductDetails->virtuemart_vendor_id,
									$ProductDetails->product_parent_id,
									$ProductDetails->product_sku,
									$ProductDetails->product_name,
									$ProductDetails->slug ,
									$ProductDetails->product_s_desc,
									$ProductDetails->product_desc ,
									$ProductDetails->product_weight ,
									$ProductDetails->product_weight_uom,
									$ProductDetails->product_length,
									$ProductDetails->product_width,
									$ProductDetails->product_height,
									$ProductDetails->product_lwh_uom,
									$ProductDetails->product_url,
									$ProductDetails->product_in_stock,
									$ProductDetails->low_stock_notification,
									$ProductDetails->product_available_date,
									$ProductDetails->product_availability,
									$ProductDetails->product_special,
									$ProductDetails->ship_code_id,
									$ProductDetails->product_sales,
									$ProductDetails->product_unit,
									$ProductDetails->product_packaging,
									$ProductDetails->product_order_levelss,
									$ProductDetails->hits,
									$ProductDetails->intnotes,
									$ProductDetails->metadesc, 
									$ProductDetails->metakey, 
									$ProductDetails->metarobot,
									$ProductDetails->metaauthor,
									$ProductDetails->layout,
									$ProductDetails->published, 
									getCategoriesIds($ProductDetails->virtuemart_product_id),
									getManufacturerId($ProductDetails->virtuemart_product_id),
									$prod_prices
									);
				
			return $Product;
		
			$errMsg=  $VirtueMartModelProduct->getError();
			//return new SoapFault("GetProductFromIdError", "DB message : \n".$ProductDetails->product_name);
			if ($errMsg==null){
				//return $Produit;
			} else {
				return new SoapFault("GetProductFromIdError", "DB message : \n".$errMsg);
			}
			
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function get Product for a SKU
	* (not expose as WS)
    * @param string product SKU
    * @return Product_id
   */
	function GetProductIDFromSKU($product_sku) {
	
		$db = JFactory::getDBO();	
		$query  = "SELECT virtuemart_product_id  FROM #__virtuemart_products WHERE product_sku = '$product_sku' ";
		$db->setQuery($query);
		
		$rows = $db->loadObjectList();
		
		$product_id="";
		foreach ($rows as $row){
			$product_id = $row->virtuemart_product_id;
		}
		
		return $product_id;
	
	}
	/**
    * This function get Product for a SKU
	* (expose as WS)
    * @param string The if of the product
    * @return array (Product)
   */
	function GetProductFromSKU($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_getprod')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
			
			$product_sku = $params->product_sku;
		
			$product_id = GetProductIDFromSKU($product_sku);
			if (empty($product_id)){
				return new SoapFault("GetProductFromSKUFault","No SKU found");
			}
			$params->product_id=$product_id;
			
			return GetProductFromId($params);

			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	
	
	/**
    * This function get All Childs product for a product ID
	* (expose as WS)
    * @param string The if of the product
    * @return array of Products
   */
	function GetChildsProduct($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_getprod')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
			
			$product_id = $params->product_id;
			
			$db = JFactory::getDBO();	
			$query  = "SELECT virtuemart_product_id FROM #__virtuemart_products WHERE product_parent_id = '$product_id' ";
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			foreach ($rows as $row){
							
				$params->product_id = $row->virtuemart_product_id;
				$Product = GetProductFromId($params);
				$ProductArray[] = $Product;
				
			}
			return $ProductArray;
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}
	
	/**
    * This function get All Products for a category ID
	* (expose as WS)
    * @param string The if of the product
    * @return array of attribute and value
   */
	function GetProductsFromCategory($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);

		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_getprod')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
			
			$categorie_id = $params->catgory_id;
			
			if (!class_exists( 'VirtueMartModelProduct' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\product.php');
			$VirtueMartModelProduct = new VirtueMartModelProduct;
			
			
			$_REQUEST['virtuemart_category_id'] = $categorie_id;
			$_POST['virtuemart_category_id'] = $categorie_id;
			$_GET['virtuemart_category_id'] = $categorie_id;
			$products = $VirtueMartModelProduct->getProductsInCategory($categorie_id);
			
			foreach ($products as $ProductDetails){
			
				unset($prod_prices);
				if ($params->include_prices == 'Y' || $params->include_prices == '1' ){
					unset($params->shopper_group_id);
					unset($params->product_currency);
					$params->product_id = $ProductDetails->virtuemart_product_id;
					$prod_prices = GetProductPrices($params);
				}
				
				
				$Product = new Product($ProductDetails->virtuemart_product_id ,
									$ProductDetails->virtuemart_vendor_id,
									$ProductDetails->product_parent_id,
									$ProductDetails->product_sku,
									$ProductDetails->product_name,
									$ProductDetails->slug ,
									$ProductDetails->product_s_desc,
									$ProductDetails->product_desc ,
									$ProductDetails->product_weight ,
									$ProductDetails->product_weight_uom,
									$ProductDetails->product_length,
									$ProductDetails->product_width,
									$ProductDetails->product_height,
									$ProductDetails->product_lwh_uom,
									$ProductDetails->product_url,
									$ProductDetails->product_in_stock,
									$ProductDetails->low_stock_notification,
									$ProductDetails->product_available_date,
									$ProductDetails->product_availability,
									$ProductDetails->product_special,
									$ProductDetails->ship_code_id,
									$ProductDetails->product_sales,
									$ProductDetails->product_unit,
									$ProductDetails->product_packaging,
									$ProductDetails->product_order_levelss,
									$ProductDetails->hits,
									$ProductDetails->intnotes,
									$ProductDetails->metadesc, 
									$ProductDetails->metakey, 
									$ProductDetails->metarobot,
									$ProductDetails->metaauthor,
									$ProductDetails->layout,
									$ProductDetails->published, 
									getCategoriesIds($ProductDetails->virtuemart_product_id),
									getManufacturerId($ProductDetails->virtuemart_product_id),
									$prod_prices
									);

				$ProductArray[] = $Product;
			
			}
			return $ProductArray;
			
				
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}		
	}
	
	
	
	/**
    * This function Get RelatedProducts
	* (expose as WS)
    * @param string The if of the product
    * @return array of attribute and value
   */
	function GetRelatedProducts($params) {

		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_getprod')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
			$categorie_id = $params->catgory_id;
			return new SoapFault("GetRelatedProducts", "GetRelatedProducts : NOT IMPLEMENTED YET");
			/*if (!class_exists( 'VirtueMartModelProduct' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\product.php');
			$VirtueMartModelProduct = new VirtueMartModelProduct;
			REMOVE IN RC2 ??
			$products = $VirtueMartModelProduct->getRelatedProducts($params->product_id);*/
			
			$db = JFactory::getDBO();	
			$query  = "SELECT virtuemart_product_id FROM #__virtuemart_products WHERE product_parent_id = '$product_id' ";
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			foreach ($rows as $row){
			
			}
			
			foreach ($products as $ProductDetails){
			
				unset($prod_prices);
				if ($params->include_prices == 'Y' || $params->include_prices == '1' ){
					unset($params->shopper_group_id);
					unset($params->product_currency);
					$params->product_id = $ProductDetails->virtuemart_product_id;
					$prod_prices = GetProductPrices($params);
				}
			
				$Product = new Product($ProductDetails->virtuemart_product_id ,
									$ProductDetails->virtuemart_vendor_id,
									$ProductDetails->product_parent_id,
									$ProductDetails->product_sku,
									$ProductDetails->product_name,
									$ProductDetails->slug ,
									$ProductDetails->product_s_desc,
									$ProductDetails->product_desc ,
									$ProductDetails->product_weight ,
									$ProductDetails->product_weight_uom,
									$ProductDetails->product_length,
									$ProductDetails->product_width,
									$ProductDetails->product_height,
									$ProductDetails->product_lwh_uom,
									$ProductDetails->product_url,
									$ProductDetails->product_in_stock,
									$ProductDetails->low_stock_notification,
									$ProductDetails->product_available_date,
									$ProductDetails->product_availability,
									$ProductDetails->product_special,
									$ProductDetails->ship_code_id,
									$ProductDetails->product_sales,
									$ProductDetails->product_unit,
									$ProductDetails->product_packaging,
									$ProductDetails->product_order_levelss,
									$ProductDetails->hits,
									$ProductDetails->intnotes,
									$ProductDetails->metadesc, 
									$ProductDetails->metakey, 
									$ProductDetails->metarobot,
									$ProductDetails->metaauthor,
									$ProductDetails->layout,
									$ProductDetails->published, 
									getCategoriesIds($ProductDetails->virtuemart_product_id),
									getManufacturerId($ProductDetails->virtuemart_product_id),
									$prod_prices
									
									); 
							$ProductArray[] = $Product;
			
			}
			return $ProductArray;
			
		
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}		
	}
		
	/**
    * This function Set RelatedProducts
	* (expose as WS)
    * @param string The if of the product
    * @return array of attribute and value
   */
	function SetRelatedProducts($params) {
			
		$categorie_id = $params->catgory_id;
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otheradd')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
	
			$list  = "SELECT * FROM #__{vm}_product_relations WHERE product_id = '$params->product_id' ";
			$list .= " LIMIT 0,100 "; 

			$db = new ps_DB;
			$db->query($list);

			$update=false;
			while ($db->next_record()) {
				$update=true;
			}
			
			if ($update==true) {
				$type='UPDATE';
				$db = new ps_DB;
				$values['related_products']=$params->related_product_ids; // format id|id2|id3 ...
				$whereClause = " WHERE product_id =".$params->product_id."";
				$db->buildQuery($type,"#__{vm}_product_relations",$values,$whereClause);
				$result = $db->query();
			} else {
				
				$type='INSERT';
				$db = new ps_DB;
				$values['related_products']=$params->related_product_ids; // format id|id2|id3 ...
				$values['product_id'] = $params->product_id;
				$db->buildQuery($type,"#__{vm}_product_relations",$values);
				$result = $db->query();
			
			}
						
			
			
			$errMsg=  $db->getErrorMsg();
			if ($errMsg==null){
				return "SetRelatedProducts successfull for product id  ".$params->product_id;
			} else {
				return new SoapFault("SetRelatedProductsError", "DB message : \n".$errMsg);
			}

		 
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}		
	}
	
	/**
    * This function Search Products from params 
	* (expose as WS)
    * @param 
    * @return array of products
   */
	function SearchProducts($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
	
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_getprod')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
	
			$limite_start = $params->limite_start;
			if (empty($limite_start)){
				$limite_start = "0";
			}
			$limite_end = $params->limite_end;
			if (empty($limite_end)){
				$limite_end = "500";
			}
			
			//AND or OR between fields for criteria 
			$operator = "AND";
			if (!empty($params->operator) && ($params->operator == "OR" || $params->operator == "or")  ){
				$operator = "OR";
			}
			
			//more or less for criteria 
			$operator_more_less_equal = "=";
			if (!empty($params->operator_more_less_equal) && $params->operator_more_less_equal == "more" ){
				$operator_more_less_equal=">";
			} else if (!empty($params->operator_more_less_equal) && $params->operator_more_less_equal == "less" ){
				$operator_more_less_equal="<";
			} else if (!empty($params->operator_more_less_equal) && $params->operator_more_less_equal == "equal" ){
				$operator_more_less_equal="=";
			}else if (!empty($params->operator_more_less_equal) && $params->operator_more_less_equal == "moreequal" ){
				$operator_more_less_equal=">=";
			}else if (!empty($params->operator_more_less_equal) && $params->operator_more_less_equal == "lessequal" ){
				$operator_more_less_equal="<=";
			}
			
			//categories IDS
			if(!empty($params->product_categories)){
				$cat_ids = explode('|', $params->product_categories);
				$cat_ids_in_sql=""; // make sql request like : in (2,5,8,10)
				if (is_array($cat_ids)){
					$cat_ids_in_sql .="(";
					$count = count($cat_ids);
					for ($i = 0; $i < $count; $i++) {
						if ($i==$count-1){
							$cat_ids_in_sql.= " ".$cat_ids[$i]." )";
						}else {
							$cat_ids_in_sql.= " ".$cat_ids[$i].",";
						}
					}
				}else {
					$cat_ids_in_sql .= "(".$cat_ids.")";
				}
			}
			$categorie_id = $cat_ids[0];
			
			if ($params->product_publish == "N"){
				$product_publish = 0;
			}else if ($params->product_publish == "Y"){
				$product_publish = 1;
			}else {
				$product_publish="";
			}
			
			$with_childs = $params->with_childs;
			if ($with_childs == "N"){
				$with_childs = "N";
			}else {
				$with_childs = "Y";
			}
			
			$db = JFactory::getDBO();	
			$query   = "SELECT *  FROM #__virtuemart_products p ";
			$query  .= "JOIN #__virtuemart_product_categories c ON p.virtuemart_product_id=c.virtuemart_product_id ";
			$query  .= "JOIN #__virtuemart_product_manufacturers mf ON p.virtuemart_product_id=mf.virtuemart_product_id ";
			$query  .= "JOIN #__virtuemart_product_prices pr ON p.virtuemart_product_id=pr.virtuemart_product_id ";
			$query  .= "WHERE 1 ";
			/////////////
			/*$list  = "SELECT p.product_id, p.product_sales as sales, p.product_parent_id, p.product_name, p.product_thumb_image, p.product_full_image , p.product_s_desc, p.product_desc, c.category_id, c.category_flypage, p.product_in_stock, pr.product_price, pr.product_currency , p.product_sku, p.product_publish, p.product_weight, p.product_weight_uom, p.product_length,p.product_width, p.product_height, p.product_lwh_uom, p.product_unit, p.product_packaging, p.product_url, p.custom_attribute, p.product_available_date, p.product_availability, p.product_special, p.child_options, p.quantity_options, p.product_discount_id, p.product_tax_id, p.child_option_ids, p.product_order_levels, p.vendor_id, mf.manufacturer_id  ";
			$list .= "FROM #__{vm}_product p ";
			$list .= "left join #__{vm}_product_category_xref pc on pc.product_id = p.product_id ";
			$list .= "left join #__{vm}_category c on pc.category_id = c.category_id ";
			$list .= "left join  #__{vm}_product_mf_xref mf on mf.product_id = p.product_id ";
			//$list .= "FROM #__{vm}_product_category_xref pc, #__{vm}_category c, #__{vm}_product_mf_xref mf, #__{vm}_product p  ";
			$q = "LEFT OUTER JOIN  #__{vm}_product_price pr ON pr.product_id = p.product_id ";*/
			
			if (!empty($product_publish)){
				$query .= "AND p.published='$product_publish' ";
			}
			if ($with_childs == "N"){
				$query .= "AND p.product_parent_id = '0' ";
			}
			
			if ($operator == "OR"){
				$query .= "AND ( 0 ";
			}else {
				$query .= "AND ( 1 ";
			}
			
			if(!empty($params->product_categories)){
				$query .=$operator." c.virtuemart_category_id in ".$cat_ids_in_sql ." " ; // p.category_id in (10,12,2,0,33)
			}
			if(!empty($params->product_id)){
				$query .=$operator." p.virtuemart_product_id = '$params->product_id' " ;
			}
			if(!empty($params->product_sku)){
				$query .=$operator." p.product_sku = '$params->product_sku' " ;
			}
			if(!empty($params->product_name)){
				$query .=$operator." p.product_name like '%$params->product_name%' " ;
			}
			if(!empty($params->product_desc)){
				$query .=$operator." p.product_desc like '%$params->product_desc%' " ;
			}
			if(!empty($params->product_sdesc)){
				$query .=$operator." p.product_sdesc like '%$params->product_sdesc%' " ;
			}
			if(!empty($params->product_sales)){
				$query .=$operator." p.product_sales $operator_more_less_equal '$params->product_sales' " ;
			}
			if(!empty($params->price)){
				$query .=$operator." pr.product_price $operator_more_less_equal '$params->price' " ;
			}
			if(!empty($params->quantity)){
				$query .=$operator." p.product_in_stock $operator_more_less_equal '$params->quantity' " ;
			}
			if(!empty($params->product_currency)){
				$query .=$operator." pr.product_currency = '$params->product_currency' " ;
			}
			if(!empty($params->manufacturer_id)){
				$query .=$operator." mf.manufacturer_id = '$params->manufacturer_id' " ;
			}
			if(!empty($params->vendor_id)){
				$query .=$operator." p.virtuemart_vendor_id = '$params->vendor_id' " ;
			}
			if(!empty($params->product_weight)){
				$query .=$operator." p.product_weight $operator_more_less_equal '$params->product_weight' " ;
			}
			if(!empty($params->product_weight_uom)){
				$query .=$operator." p.product_weight_uom $operator_more_less_equal '$params->product_weight_uom' " ;
			}
			if(!empty($params->product_width)){
				$query .=$operator." p.product_width $operator_more_less_equal '$params->product_width' " ;
			}
			if(!empty($params->product_height)){
				$query .=$operator." p.product_height $operator_more_less_equal '$params->product_height' " ;
			}
			if(!empty($params->product_length)){
				$query .=$operator." p.product_length $operator_more_less_equal '$params->product_length' " ;
			}
			if(!empty($params->product_lwh_uom)){
				$query .=$operator." p.product_lwh_uom $operator_more_less_equal '$params->product_lwh_uom' " ;
			}
			if(!empty($params->product_unit)){
				$query .=$operator." p.product_unit = '$params->product_unit' " ;
			}
			if(!empty($params->product_packaging)){
				$query .=$operator." p.product_packaging = '$params->product_packaging' " ;
			}
			if(!empty($params->product_url)){
				$query .=$operator." p.product_url like  '%$params->product_url%' " ;
			}
			if(!empty($params->product_special)){
				$query .=$operator." p.product_special =  '$params->product_special' " ;
			}
			if(!empty($params->parent_product_id)){
				$query .=$operator." p.product_parent_id =  '$params->parent_product_id' " ;
			}
			
			$query .= " ) ";
			
			$query .= "GROUP BY p.virtuemart_product_id ";
			$query .= "ORDER BY p.virtuemart_product_id ASC ";
			$query .= "LIMIT $limite_start,$limite_end "; 

			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			foreach ($rows as $ProductDetails){
				
				unset($prod_prices);
				if ($params->include_prices == 'Y' || $params->include_prices == '1' ){
					unset($params->shopper_group_id);
					unset($params->product_currency);
					$params->product_id = $ProductDetails->virtuemart_product_id;
					$prod_prices = GetProductPrices($params);
				}
				
				$Product = new Product($ProductDetails->virtuemart_product_id/*$ProductDetails->prices[0]*/ ,
									$ProductDetails->virtuemart_vendor_id,
									$ProductDetails->product_parent_id,
									$ProductDetails->product_sku,
									$ProductDetails->product_name,
									$ProductDetails->slug ,
									$ProductDetails->product_s_desc,
									$ProductDetails->product_desc ,
									$ProductDetails->product_weight ,
									$ProductDetails->product_weight_uom,
									$ProductDetails->product_length,
									$ProductDetails->product_width,
									$ProductDetails->product_height,
									$ProductDetails->product_lwh_uom,
									$ProductDetails->product_url,
									$ProductDetails->product_in_stock,
									$ProductDetails->low_stock_notification,
									$ProductDetails->product_available_date,
									$ProductDetails->product_availability,
									$ProductDetails->product_special,
									$ProductDetails->ship_code_id,
									$ProductDetails->product_sales,
									$ProductDetails->product_unit,
									$ProductDetails->product_packaging,
									$ProductDetails->product_order_levelss,
									$ProductDetails->hits,
									$ProductDetails->intnotes,
									$ProductDetails->metadesc, 
									$ProductDetails->metakey, 
									$ProductDetails->metarobot,
									$ProductDetails->metaauthor,
									$ProductDetails->layout,
									$ProductDetails->published, 
									getCategoriesIds($ProductDetails->virtuemart_product_id),
									getManufacturerId($ProductDetails->virtuemart_product_id),
									$prod_prices
									);
					$ProductArray[] = $Product;
			
			}
			//return $ProduitArray;
		
			
			$errMsg=  $db->getErrorMsg();
			if ($errMsg==null){
				return $ProductArray;
			} else {
				return new SoapFault("SQLError", "Error while searshing product",$errMsg);
			}
			
		
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}		
	}
	
	
	
	/**
    * This function set var for childs option 
	* (expose as WS)
    * @param string The if of the product
    * @return array of attribute and value
   */
	 function set_child_options( $str, &$d ) {
	 
		$opts = explode(',', $str);
			
			if (is_array($opts)){
				$_REQUEST['display_use_parent'] = $opts[0];
				
				$product_list = $opts[1];
				
				if ($product_list == "Y"){
					$d['list_style']="one";
					$_REQUEST['product_list'] = "Y";
					$d['product_list']  = "Y";
				} else if ($product_list == "YM") {
					$_REQUEST['product_list'] = "Y";
					$d['product_list']  = "Y";
				} else {
					$_REQUEST['product_list'] = "N";
					$d['product_list']  = "N";
				}
				
				/*$_REQUEST['product_list'] = $opts[1];
				$d['product_list']  = $opts[1];*/
				
				
				$_REQUEST['display_headers'] = $opts[2];
				$_REQUEST['product_list_child'] = $opts[3];
				$_REQUEST['product_list_type'] = $opts[4];
				$_REQUEST['display_desc'] = $opts[5];
				$_REQUEST['desc_width'] = $opts[6];
				$_REQUEST['attrib_width'] = $opts[7];
				$_REQUEST['child_class_sfx'] = $opts[8];
			}
    }
	/**
    * This function set var for quantity option 
	* (expose as WS)
    * @param string The if of the product
    * @return array of attribute and value
   */
	 function set_quantity_options(  $str , &$d) {
	 
			$opts = explode(',', $str);
			
			if (is_array($opts)){
				$d['quantity_box']  = $opts[0];
				$_REQUEST['quantity_box'] = $opts[0];
				$_REQUEST['quantity_start'] = $opts[1];
				$_REQUEST['quantity_end'] = $opts[2];
				$_REQUEST['quantity_step'] = $opts[3];
			
			}
    	
    }
	
	/**
    * This function update a product
	* (expose as WS)
    * @param string The if of the product
    * @return array of attribute and value
   */
   //TODO
	function UpdateProduct($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_upprod')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
			
			setToken();
			
			$product = $params->product;
			
			if (!class_exists( 'VirtueMartModelProduct' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\product.php');
			$modelProduct = new VirtueMartModelProduct;
			
			//Get Old Prices
			$params->product_id = $params->product->product_id;
			$prod_prices = GetProductPrices($params);
			
			foreach ($prod_prices as $price){
				bindObject($price,$data);
			}
			
			//Get Old Manufacturer
			if (!class_exists( 'VirtueMartModelManufacturer' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\manufacturer.php');
			$modelManufacturer = new VirtueMartModelManufacturer;
			
			$modelManufacturer->_id = $params->product->manufacturer_id;
			$manufac = $modelManufacturer->getManufacturer();
			bindObject($manufac,$data);
			
			//get old media
			if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
			$mediaModel = new VirtueMartModelMedia();
			
			$_REQUEST['virtuemart_product_id'] = $params->product->product_id;
			$p_medias = $mediaModel->getFiles();
			
			//$s_media = count($p_medias);
			
			$data['virtuemart_media_id'] = null;
			
			foreach ($p_medias as $media){
				$media_ids[] = $media->virtuemart_media_id;
			}
			
			/// BUG // ADD virtuemart_media_id = 0 in xref
			$data['virtuemart_media_id']=$media_ids;
			//$s_media_ids = count($data['virtuemart_media_id']);
			//return new SoapFault("JoomlaServerAuthFault", '-'.$s_media_ids);
			
			
			
			$data['virtuemart_product_id']= $params->product->product_id;
			$data['virtuemart_vendor_id']= isset($params->product->virtuemart_vendor_id) ? $params->product->virtuemart_vendor_id : 1;
			$data['product_parent_id']= isset($params->product->product_parent_id) ? $params->product->product_parent_id : 0;
			$data['product_sku']= $params->product->product_sku;
			$data['product_name']= $params->product->product_name;
			$data['slug']= $params->product->slug;
			$data['product_s_desc']= $params->product->product_s_desc;
			$data['product_desc']= $params->product->product_desc;
			$data['product_weight']= $params->product->product_weight;
			$data['product_weight_uom']= $params->product->product_weight_uom;
			$data['product_length']= $params->product->product_length;
			$data['product_width']= $params->product->product_width;
			$data['product_height']= $params->product->product_height;
			$data['product_lwh_uom']= $params->product->product_lwh_uom;
			$data['product_url']= $params->product->product_url;
			$data['product_in_stock']= $params->product->product_in_stock;
			$data['low_stock_notification']= $params->product->low_stock_notification;
			$data['product_available_date']= $params->product->product_available_date;
			$data['product_availability']= $params->product->product_availability;
			$data['product_special']= $params->product->product_special;
			$data['ship_code_id']= $params->product->ship_code_id;
			$data['product_sales']= $params->product->product_sales;
			$data['product_unit']= $params->product->product_unit;
			$data['product_packaging']= $params->product->product_packaging;
			$data['product_order_levels']= $params->product->product_order_levels;
			$data['hits']= $params->product->hits;
			$data['intnotes']= $params->product->intnotes;
			$data['metadesc']= $params->product->metadesc;
			$data['metakey']= $params->product->metakey;
			$data['metarobot']= $params->product->metarobot;
			$data['metaauthor']= $params->product->metaauthor;
			$data['layout']= $params->product->layout;
			$data['published']= $params->product->published;
			$data["categories"] = explode ('|',$params->product->product_categories);
			//$data['product_categories']= $params->product->product_categories;
			$data['manufacturer_id']= isset($params->product->manufacturer_id) ? $params->product->manufacturer_id : 1;
			
			$res = $modelProduct->store($data);
			
			
			if ($res != false){
				$CommonReturn = new CommonReturn(OK,"Product sucessfully updated ".$product->name,$res);
				return $CommonReturn;
				
			}else {			
				return new SoapFault("VMAddProductFault", "Cannot update product : ".$product->name);			
			}

			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}

	/**
    * This function update a product
	* (expose as WS)
    * @param string The if of the product
    * @return array of attribute and value
   */
	function AddProduct($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_addprod')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
			
			$product = $params->product;
			
			setToken();
			if (!class_exists( 'VirtueMartModelProduct' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\product.php');
			$modelProduct = new VirtueMartModelProduct;
			
			
			$data['virtuemart_vendor_id']= isset($params->product->virtuemart_vendor_id) ? $params->product->virtuemart_vendor_id : 1;
			$data['product_parent_id']= isset($params->product->product_parent_id) ? $params->product->product_parent_id : 0;
			$data['product_sku']= $params->product->product_sku;
			$data['product_name']= $params->product->product_name;
			$data['slug']= $params->product->slug;
			$data['product_s_desc']= $params->product->product_s_desc;
			$data['product_desc']= $params->product->product_desc;
			$data['product_weight']= $params->product->product_weight;
			$data['product_weight_uom']= $params->product->product_weight_uom;
			$data['product_length']= $params->product->product_length;
			$data['product_width']= $params->product->product_width;
			$data['product_height']= $params->product->product_height;
			$data['product_lwh_uom']= $params->product->product_lwh_uom;
			$data['product_url']= $params->product->product_url;
			$data['product_in_stock']= $params->product->product_in_stock;
			$data['low_stock_notification']= $params->product->low_stock_notification;
			$data['product_available_date']= $params->product->product_available_date;
			$data['product_availability']= $params->product->product_availability;
			$data['product_special']= $params->product->product_special;
			$data['ship_code_id']= $params->product->ship_code_id;
			$data['product_sales']= $params->product->product_sales;
			$data['product_unit']= $params->product->product_unit;
			$data['product_packaging']= $params->product->product_packaging;
			$data['product_order_levels']= $params->product->product_order_levels;
			$data['hits']= $params->product->hits;
			$data['intnotes']= $params->product->intnotes;
			$data['metadesc']= $params->product->metadesc;
			$data['metakey']= $params->product->metakey;
			$data['metarobot']= $params->product->metarobot;
			$data['metaauthor']= $params->product->metaauthor;
			$data['layout']= $params->product->layout;
			$data['published']= $params->product->published;
			$data["categories"] = explode ('|',$params->product->product_categories);
			//$data['product_categories']= $params->product->product_categories;
			$data['manufacturer_id']= isset($params->product->manufacturer_id) ? $params->product->manufacturer_id : 1;
			
			$res = $modelProduct->store($data);
			
			
			
			
			
			
			if ($res != false){
				$CommonReturn = new CommonReturn(OK,"Product sucessfully added ".$product->name,$res);
				return $CommonReturn;
				
			}else {			
				return new SoapFault("VMAddProductFault", "Cannot Add product : ".$product->name);			
			}

			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}

	/**
    * This function update a product
	* (expose as WS)
    * @param string The if of the product
    * @return array of attribute and value
   */
   //TODO
	function DeleteProduct($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_delprod')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
			
			setToken();
			if (!class_exists( 'VirtueMartModelProduct' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\product.php');
			$modelProduct = new VirtueMartModelProduct;
			
			$ids[] = $params->product_id;
			$res = $modelProduct->remove($ids);
			
			if ($res == true){
				$CommonReturn = new CommonReturn(OK,"Product deleted successfully ".$product->name,$params->product_id);
				return $CommonReturn;
				
			}else{
				return new SoapFault("VMDeleteProductFault", "Cannot delete product  : ".$params->product_id);
			}
						
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	
/**
    * This function get Products for a order ID
	* (expose as WS)
    * @param string 
    * @return array (Product)
   */
	function GetProductsFromOrderId($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_getprod')==0){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
			
			$order_id = $params->order_id;
					
			$db = JFactory::getDBO();	
			$query  = "SELECT *  FROM #__virtuemart_order_items WHERE virtuemart_order_id = '$order_id'  ";
	
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			foreach ($rows as $row){
				
				$OrderItemInfo = new OrderItemInfo($row->virtuemart_order_id,
									$row->virtuemart_userinfo_id,
									$row->virtuemart_vendor_id,
									$row->virtuemart_product_id,
									$row->order_item_sku,
									$row->order_item_name,
									$row->product_quantity,
									$row->product_item_price,
									$row->product_final_price,
									$row->order_item_currency,
									$row->order_status,
									$row->product_attribute,
									$row->created_on,
									$row->modified_on
									
									);
				  
				$OrderItemInfoArray[] =  $OrderItemInfo;
			
			}

	
			return $OrderItemInfoArray;
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}	
	
	/**
    * This function get All currency
	* (expose as WS)
    * @param string 
    * @return array of currency
   */
	function GetAllCurrency($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherget')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
		
			$product_id = $params->product_id;		
			
			if (!class_exists( 'VirtueMartModelCurrency' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\currency.php');
			$VirtueMartModelCurrency = new VirtueMartModelCurrency;
			
			$currencies = $VirtueMartModelCurrency->getCurrenciesList();
			
			foreach ($currencies as $currencie){
				$Currency = new Currency($currencie->virtuemart_currency_id,
										$currencie->virtuemart_vendor_id,
										$currencie->currency_name,
										$currencie->currency_code_2,
										$currencie->currency_code_3,
										$currencie->currency_numeric_code,
										$currencie->currency_exchange_rate,
										$currencie->currency_symbol,
										$currencie->currency_decimal_place,
										$currencie->currency_decimal_symbol,
										$currencie->currency_thousands,
										$currencie->currency_positive_style,
										$currencie->currency_negative_style,
										$currencie->ordering,
										$currencie->shared,
										$currencie->published);
				$CurrencyArray[] = $Currency;
			
			}
			return $CurrencyArray;
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}
	
	
	/**
    * This function get All currency
	* (expose as WS)
    * @param string 
    * @return array of currency
   */
	function GetProductVote($params) {
			
		$product_id = $params->product_id;
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherget')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
		
			$eq = " = ";
			if (!empty($params->moreless)){
				if ($params->moreless == "more"){
					$eq = " >= ";
				}
				if ($params->moreless == "less"){
					$eq = " <= ";
				}
				if ($params->moreless == "equal"){
					$eq = " = ";
				}
			}
		
			$list  = "SELECT pv.product_id as product_id , product_name, product_sku, votes, allvotes, rating, lastip FROM #__{vm}_product_votes pv join #__{vm}_product p on p.product_id=pv.product_id ";
			$list .= "WHERE 1 ";
			
			if (!empty($params->product_id)){
				$list .= " AND pv.product_id = '$params->product_id' ";
			}
			if (!empty($params->votes)){
				$list .= " AND votes $eq '$params->votes' ";
			}
			if (!empty($params->allvotes)){
				$list .= " AND allvotes $eq '$params->allvotes' ";
			}
			if (!empty($params->rating)){
				$list .= " AND rating $eq '$params->rating' ";
			}
			
			$db = new ps_DB;
			$db->query($list);
			while ($db->next_record()) {
				$ProductVote = new ProductVote($db->f("product_id"),$db->f("product_name"),$db->f("product_sku"),$db->f("votes"),$db->f("allvotes"),$db->f("rating"),$db->f("lastip"));
				$ProductVoteArray[] = $ProductVote;
			}
			
			$errMsg=  $db->getErrorMsg();
			
			if ($errMsg==null){
				return $ProductVoteArray;
			} else {
				return new SoapFault("GetProductVoteFault", "cannot GetProductVote  "." | ERRLOG : ".$errMsg);				
			}
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}
	
	
	
	/**
    * This function Get Product Reviews
	* (expose as WS)
    * @param string 
    * @return array of currency
   */
	function GetProductReviews($params) {
			
		$product_id = $params->product_id;
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherget')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
		
			$eq = " = ";
			if (!empty($params->moreless)){
				if ($params->moreless == "more"){
					$eq = " >= ";
				}
				if ($params->moreless == "less"){
					$eq = " <= ";
				}
				if ($params->moreless == "equal"){
					$eq = " = ";
				}
			}
		
			$list  = "SELECT * FROM #__{vm}_product_reviews ";
			$list .= "WHERE 1 ";
			
			if (!empty($params->review_id)){
				$list .= " AND review_id = '$params->review_id' ";
			}
			if (!empty($params->product_id)){
				$list .= " AND product_id $eq '$params->product_id' ";
			}
			if (!empty($params->userid)){
				$list .= " AND userid $eq '$params->userid' ";
			}
			if (!empty($params->published)){
				$list .= " AND published = '$params->published' ";
			}
			
			$db = new ps_DB;
			$db->query($list);
			while ($db->next_record()) {
				$ProductReview = new ProductReview($db->f("review_id"),$db->f("product_id"),$db->f("comment"),$db->f("userid"),$db->f("time"),$db->f("user_rating"),$db->f("review_ok"),$db->f("review_votes"),$db->f("published"));
				$ProductReviewArray[] = $ProductReview;
			}
			
			$errMsg=  $db->getErrorMsg();
			
			if ($errMsg==null){
				return $ProductReviewArray;
			} else {
				return new SoapFault("GetProductReviewsFault", "cannot GetProductReviews  "." | ERRLOG : ".$errMsg);				
			}
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}
	
	/**
    * This function Publish Review
	* (expose as WS)
    * @param string 
    * @return string
   */
	function PublishReviews($params) {
	
		$product_id = $params->product_id;
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		if ($conf['auth_prod_addprod']=="off"){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
		
			$published = 'N';
			if ($params->published == "Y"){
				$published = 'Y';
			}
			
			$list  = "UPDATE #__{vm}_product_reviews SET published = '$published' ";
			$list .= "WHERE review_id = '$params->review_id' ";
			
			$db = new ps_DB;
			$db->query($list);
			
			$errMsg=  $db->getErrorMsg();
			
			if ($errMsg==null){
				return "Publish/Unpublish ($published) review OK : review id ".$params->review_id;
			} else {
				return new SoapFault("GetProductReviewsFault", "cannot PublishReview  "." | ERRLOG : ".$errMsg);				
			}
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}
	
	/**
	*
	**/
	function discountIdExist($discount_id) {
	
		if ($discount_id == 0){
			return true;
		}
		$list  = "SELECT * FROM #__{vm}_product_discount WHERE discount_id = '$discount_id' ";
		$db = new ps_DB;
		$db->query($list);
		$i=0;
		while ($db->next_record()) {
			$i++;
		}
		if ($i ==0){
			return false;
		}
		return true;
	}
	
	function ProductFileIdExist($file_id) {
	
		/*if ($discount_id == 0){
			return true;
		}*/
		$list  = "SELECT * FROM #__{vm}_product_files WHERE file_id = '$file_id' ";
		$db = new ps_DB;
		$db->query($list);
		$i=0;
		while ($db->next_record()) {
			$i++;
		}
		if ($i ==0){
			return false;
		}
		return true;
	}
	
	
	/**
    * This function GetProductFile
	* (expose as WS)
    * @param string 
    * @return result
   */
	function GetProductFile($params) {
			
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherget')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
		
			/*if (!empty($params->product_id)){
				$product_id = $params->product_id;
				$list  = "SELECT * FROM #__{vm}_product_file WHERE product_id = '$product_id' ";
				$db = new ps_DB;
				$db->query($list);
				while ($db->next_record()) {
					$params->discount->discount_id = $db->f("product_discount_id");
				}
			}*/
			
			$list  = "SELECT * FROM #__{vm}_product_files WHERE 1";
			
			
			if (!empty($params->file_id)){
				$file_id = $params->file_id;
				$list  .= " AND file_id = '$file_id' ";
			}
			if (!empty($params->file_product_id)){
				$file_product_id = $params->file_product_id;
				$list  .= " AND file_product_id = '$file_product_id' ";
			}
			if (!empty($params->file_name)){
				$file_name = $params->file_name;
				$list  .= " AND file_name like '%$file_name%' ";
			}
			if (!empty($params->file_published)){
				$file_published = $params->file_published;
				$list  .= " AND file_published = '$file_published' ";
			}
			if (!empty($params->file_extension)){
				$file_extension = $params->file_extension;
				$list  .= " AND file_extension = '$file_extension' ";
			}
			if (!empty($params->file_is_image)){
				$file_is_image = $params->file_is_image;
				$list  .= " AND file_is_image = '$file_is_image' ";
			}
			
			
			$db = new ps_DB;
			$db->query($list);
			while ($db->next_record()) {
				$ProductFile = new ProductFile($db->f("file_id"),$db->f("file_product_id"),$db->f("file_name"),$db->f("file_title"),$db->f("file_description"),$db->f("file_extension"),$db->f("file_mimetype"),$db->f("file_url"),$db->f("file_published"),$db->f("file_is_image"),$db->f("file_image_height"),$db->f("file_image_width"),$db->f("file_image_thumb_height"),$db->f("file_image_thumb_width"));
				$ProductFileArray[] = $ProductFile;
			}

			$errMsg=  $db->getErrorMsg();
			
			if ($errMsg==null){
				return $ProductFileArray;
			} else {
				return new SoapFault("GetProductFileFault", "cannot Get ProductFile  "." | ERRLOG : ".$errMsg);				
			}
			//return $ProductPriceArray;
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}	
	
	function AddProductFile($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otheradd')==0){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
		
			$allOk=true;
			if (is_array($params->ProductFiles->ProductFile)){
				
					$count = count($params->ProductFiles->ProductFile);
					for ($i = 0; $i < $count; $i++) {
											
						$type='INSERT';
						$whereClause = " WHERE 1 ";
						
						if (!empty($params->ProductFiles->ProductFile[$i]->file_product_id)){
						
							$values['file_product_id']= $params->ProductFiles->ProductFile[$i]->file_product_id;
							$values['file_name']= $params->ProductFiles->ProductFile[$i]->file_name;
							$values['file_title']= $params->ProductFiles->ProductFile[$i]->file_title;
							$values['file_description']= $params->ProductFiles->ProductFile[$i]->file_description;
							$values['file_extension']= $params->ProductFiles->ProductFile[$i]->file_extension;
							$values['file_url']= $params->ProductFiles->ProductFile[$i]->file_url;
							$values['file_published']= $params->ProductFiles->ProductFile[$i]->file_published;
							$values['file_is_image']= $params->ProductFiles->ProductFile[$i]->file_is_image;
							$values['file_image_height']= $params->ProductFiles->ProductFile[$i]->file_image_height;
							$values['file_image_width']= $params->ProductFiles->ProductFile[$i]->file_image_width;
							$values['file_image_thumb_height']= $params->ProductFiles->ProductFile[$i]->file_image_thumb_height;
							$values['file_image_thumb_width']= $params->ProductFiles->ProductFile[$i]->file_image_thumb_width;
							$values['file_mimetype']= $params->ProductFiles->ProductFile[$i]->file_mimetype;
							
							$db = new ps_DB;
							$db->buildQuery($type,'#__{vm}_product_files',$values,$whereClause);
							$result = $db->query();
							$errMsg=  $db->getErrorMsg();
							$id = $db->last_insert_id();
						} else {
							$result = false;
						}

						if ($result){
							$cpnIdsStr .= " ".$id;
						}else{
							$allOk=false;
						}
					}
				} else {
						
					$type='INSERT';
					$whereClause = " WHERE 1 ";
					
					if (empty($params->ProductFiles->ProductFile->file_product_id)){
						return new SoapFault("AddProductFileFault", "file_product_id must be setted");	
					}
					
					$values['file_product_id']= $params->ProductFiles->ProductFile->file_product_id;
					$values['file_name']= $params->ProductFiles->ProductFile->file_name;
					$values['file_title']= $params->ProductFiles->ProductFile->file_title;
					$values['file_description']= $params->ProductFiles->ProductFile->file_description;
					$values['file_extension']= $params->ProductFiles->ProductFile->file_extension;
					$values['file_url']= $params->ProductFiles->ProductFile->file_url;
					$values['file_published']= $params->ProductFiles->ProductFile->file_published;
					$values['file_is_image']= $params->ProductFiles->ProductFile->file_is_image;
					$values['file_image_height']= $params->ProductFiles->ProductFile->file_image_height;
					$values['file_image_width']= $params->ProductFiles->ProductFile->file_image_width;
					$values['file_image_thumb_height']= $params->ProductFiles->ProductFile->file_image_thumb_height;
					$values['file_image_thumb_width']= $params->ProductFiles->ProductFile->file_image_thumb_width;
					$values['file_mimetype']= $params->ProductFiles->ProductFile->file_mimetype;
					
					
					$db = new ps_DB;
					$db->buildQuery($type,'#__{vm}_product_files',$values,$whereClause);
					$result = $db->query();
					$errMsg=  $db->getErrorMsg();
					$id = $db->last_insert_id();
					
					if ($errMsg==null){
						$CommonReturn = new CommonReturn(OK,"AddProductFile sucessfull",$id);
						return $CommonReturn;
					} else {
						return new SoapFault("AddProductFileFault", "cannot Add ProductFile  "." | ERRLOG : ".$errMsg);	
					}	
					
				}
				if ($allOk){
					$CommonReturn = new CommonReturn(OK,"All ProductFile successfully added : ",$cpnIdsStr);
					return $CommonReturn;
					
				} else {
					return new SoapFault("AddProductFileFault", "Not all ProductFile added, only ProductFile id : ".$cpnIdsStr);
				}			
		
		
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	
	}
	
	function UpdateProductFile($params) {
	

		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherupdate')==0){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
		
			$allOk=true;
			if (is_array($params->ProductFiles->ProductFile)){
				
					$count = count($params->ProductFiles->ProductFile);
					for ($i = 0; $i < $count; $i++) {
											
						$type='UPDATE';
						$whereClause = "WHERE file_id = ".(int)$params->ProductFiles->ProductFile[$i]->file_id." ";
						
						if (!empty($params->ProductFiles->ProductFile[$i]->file_id)){
						
							if (!empty($params->ProductFiles->ProductFile[$i]->file_product_id))
							$values['file_product_id']= $params->ProductFiles->ProductFile[$i]->file_product_id;
							if (!empty($params->ProductFiles->ProductFile[$i]->file_name))
							$values['file_name']= $params->ProductFiles->ProductFile[$i]->file_name;
							if (!empty($params->ProductFiles->ProductFile[$i]->file_title))
							$values['file_title']= $params->ProductFiles->ProductFile[$i]->file_title;
							if (!empty($params->ProductFiles->ProductFile[$i]->file_description))
							$values['file_description']= $params->ProductFiles->ProductFile[$i]->file_description;
							if (!empty($params->ProductFiles->ProductFile[$i]->file_extension))
							$values['file_extension']= $params->ProductFiles->ProductFile[$i]->file_extension;
							if (!empty($params->ProductFiles->ProductFile[$i]->file_url))
							$values['file_url']= $params->ProductFiles->ProductFile[$i]->file_url;
							if (!empty($params->ProductFiles->ProductFile[$i]->file_published))
							$values['file_published']= $params->ProductFiles->ProductFile[$i]->file_published;
							if (!empty($params->ProductFiles->ProductFile[$i]->file_is_image))
							$values['file_is_image']= $params->ProductFiles->ProductFile[$i]->file_is_image;
							if (!empty($params->ProductFiles->ProductFile[$i]->file_image_height))
							$values['file_image_height']= $params->ProductFiles->ProductFile[$i]->file_image_height;
							if (!empty($params->ProductFiles->ProductFile[$i]->file_image_width))
							$values['file_image_width']= $params->ProductFiles->ProductFile[$i]->file_image_width;
							if (!empty($params->ProductFiles->ProductFile[$i]->file_image_thumb_height))
							$values['file_image_thumb_height']= $params->ProductFiles->ProductFile[$i]->file_image_thumb_height;
							if (!empty($params->ProductFiles->ProductFile[$i]->file_image_thumb_width))
							$values['file_image_thumb_width']= $params->ProductFiles->ProductFile[$i]->file_image_thumb_width;
							if (!empty($params->ProductFiles->ProductFile[$i]->file_mimetype))
							$values['file_mimetype']= $params->ProductFiles->ProductFile[$i]->file_mimetype;
							
							$db = new ps_DB;
							$db->buildQuery($type,'#__{vm}_product_files',$values,$whereClause);
							$result = $db->query();
							$errMsg=  $db->getErrorMsg();
							$id = $db->last_insert_id();
						} else {
							$result = false;
						}

						if ($result){
							$cpnIdsStr .= " ".$id;
						}else{
							$allOk=false;
						}
					}
				} else {
						
					$type='UPDATE';
					$whereClause = "WHERE file_id = ".(int)$params->ProductFiles->ProductFile->file_id." ";
					
					if (empty($params->ProductFiles->ProductFile->file_id)){
						return new SoapFault("UpdateProductFileFault", "file_id must be setted");	
					}
					if (!empty($params->ProductFiles->ProductFile->file_product_id))
					$values['file_product_id']= $params->ProductFiles->ProductFile->file_product_id;
					if (!empty($params->ProductFiles->ProductFile->file_name))
					$values['file_name']= $params->ProductFiles->ProductFile->file_name;
					if (!empty($params->ProductFiles->ProductFile->file_title))
					$values['file_title']= $params->ProductFiles->ProductFile->file_title;
					if (!empty($params->ProductFiles->ProductFile->file_description))
					$values['file_description']= $params->ProductFiles->ProductFile->file_description;
					if (!empty($params->ProductFiles->ProductFile->file_extension))
					$values['file_extension']= $params->ProductFiles->ProductFile->file_extension;
					if (!empty($params->ProductFiles->ProductFile->file_url))
					$values['file_url']= $params->ProductFiles->ProductFile->file_url;
					if (!empty($params->ProductFiles->ProductFile->file_is_image))
					$values['file_is_image']= $params->ProductFiles->ProductFile->file_is_image;
					if (!empty($params->ProductFiles->ProductFile->file_image_height))
					$values['file_image_height']= $params->ProductFiles->ProductFile->file_image_height;
					if (!empty($params->ProductFiles->ProductFile->file_image_width))
					$values['file_image_width']= $params->ProductFiles->ProductFile->file_image_width;
					if (!empty($params->ProductFiles->ProductFile->file_image_thumb_height))
					$values['file_image_thumb_height']= $params->ProductFiles->ProductFile->file_image_thumb_height;
					if (!empty($params->ProductFiles->ProductFile->file_image_thumb_width))
					$values['file_image_thumb_width']= $params->ProductFiles->ProductFile->file_image_thumb_width;
					if (!empty($params->ProductFiles->ProductFile->file_mimetype))
					$values['file_mimetype']= $params->ProductFiles->ProductFile->file_mimetype;
					
					if ($params->ProductFiles->ProductFile->file_published == "0") {
						$values['file_published']= 0;
					} else {
						$values['file_published']= 1;
					}
					
					
					$db = new ps_DB;
					$db->buildQuery($type,'#__{vm}_product_files',$values,$whereClause);
					$result = $db->query();
					$errMsg=  $db->getErrorMsg();
					$id = $db->last_insert_id();
					
					if ($result){
						$CommonReturn = new CommonReturn(OK,"UpdateProductFile sucessfull",$id);
						return $CommonReturn;
					} else {
						return new SoapFault("UpdateProductFileFault", "cannot Add ProductFile  ".$file_id." | ERRLOG : ".$errMsg);	
					}	
					
				}
				if ($allOk){
					$CommonReturn = new CommonReturn(OK,"All ProductFile successfully updated : ",$cpnIdsStr);
					return $CommonReturn;
					
				} else {
					return new SoapFault("AddProductFileFault", "Not all ProductFile updated, only ProductFile id : ".$cpnIdsStr);
				}			
		
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	
	}
	
	function DeleteProductFile($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherdelete')==0){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
			$allOk=true;
			if (is_array($params->ids->id)){
				$count = count($params->ids->id);
				for ($i = 0; $i < $count; $i++) {

					$d = array('file_id' => $params->ids->id[$i]
						);
						
					$ps_product_files = new ps_product_files;
					$result = $ps_product_files->delete($d);
					
					if ($result){
						$cpnIdsStr .= $params->ids->id[$i]." ";
					}else{
						$allOk=false;
					}
				}
			} else {
					$d = array('file_id' => $params->ids->id
						);
						
					$ps_product_files = new ps_product_files;
					$result = $ps_product_files->delete($d);
					
				if ($result){
					$CommonReturn = new CommonReturn(OK,"ProductFile sucessfully deleted:  ".$d['file_id'],$d['file_id']);
					return $CommonReturn;
				}else {
					return new SoapFault("DeleteProductFileFault", "Cannot delete ProductFile  : ".$d['file_id']);
				}
			}
			if ($allOk){
				$CommonReturn = new CommonReturn(OK,"ProductFile successfully deleted : ".$cpnIdsStr,$cpnIdsStr);
				return $CommonReturn;
			} else {
				return new SoapFault("DeleteProductFileFault", "Not all ProductFile deleted, only ProductFile id : ".$cpnIdsStr);
			}	
		
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	
	}
	
	/**
    * This function GetDiscount
	* (expose as WS)
    * @param string 
    * @return result
   */
	function UpdateProductDiscount($params) {

		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherupdate')==0){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
		
			if (!discountIdExist($params->discount_id)){
				return new SoapFault("UpdateProductDiscountFault", "discount_id does not exist $params->discount_id ");
			}
		
			if (!empty($params->product_id)){
				$product_id = $params->product_id;
				$list  = "UPDATE #__{vm}_product SET product_discount_id = '$params->discount_id' WHERE product_id = '$product_id' ";
				$db = new ps_DB;
				$db->query($list);
				while ($db->next_record()) {
					
				}
				$errMsg=  $db->getErrorMsg();
			
				if ($errMsg==null){
					$CommonReturn = new CommonReturn(OK,"UpdateProductDiscount sucessfull","Product id $product_id");
					return $CommonReturn;
				}else {
					return new SoapFault("UpdateProductDiscountFault", "UpdateProductDiscount error Product id $product_id");
				}
			}
			
			if (!empty($params->product_sku)){
				$product_sku = $params->product_sku;
				$list  = "UPDATE #__{vm}_product SET product_discount_id = '$params->discount_id' WHERE product_sku = '$product_sku' ";
				$db = new ps_DB;
				$db->query($list);
				while ($db->next_record()) {
					
				}
				$errMsg=  $db->getErrorMsg();
			
				if ($errMsg==null){
					$CommonReturn = new CommonReturn(OK,"UpdateProductDiscount sucessfull","SKU ".$params->product_sku);
					return $CommonReturn;
				}else {
					return new SoapFault("UpdateProductDiscountFault", "UpdateProductDiscount error ".$params->product_sku);
				}
			}

		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}
	
	
	/**
    * This function GetDiscount
	* (expose as WS)
    * @param string 
    * @return result
   */
	function GetDiscount($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherget')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
			
			if (!class_exists( 'VirtueMartModelCalc' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\calc.php');
			$modelCalc = new VirtueMartModelCalc;
			
			//unset($modelCalc->_id);
			
			if (!empty($params->discount_id)){
				$modelCalc->_id=$params->discount_id;
				$rows[] = $modelCalc->getCalc();
			}else if (!empty($params->calc_name)) {
				$rows = $modelCalc->getCalcs(false,true,$params->calc_name);
			} else {
				$rows = $modelCalc->getCalcs(false,true);
			}
			
			foreach ($rows as $row){
				
				
				$modelCalcDetail = new VirtueMartModelCalc;
				$modelCalcDetail->_id=$row->virtuemart_calc_id;
				$calcDetail = $modelCalcDetail->getCalc();
				
			
				$discount_cat_ids = implode ('|',$calcDetail->calc_categories);
				$discount_countries_ids = implode ('|',$calcDetail->virtuemart_shoppergroup_ids);
				$discount_shoppergroups_ids = implode ('|',$calcDetail->calc_countries);
				$discount_states_ids = implode ('|',$calcDetail->virtuemart_state_ids);
				
				

				$Discount = new Discount($row->virtuemart_calc_id,
								$row->virtuemart_vendor_id,
								$row->calc_name,
								$row->calc_descr,
								$row->calc_kind,
								$row->calc_value_mathop,
								$row->calc_value,
								$row->calc_currency,
								$row->calc_shopper_published,
								$row->calc_vendor_published,
								$row->publish_up,
								$row->publish_down,
								$row->calc_qualify,
								$row->calc_affected,
								$row->calc_amount_cond,
								$row->calc_amount_dimunit,
								$row->for_override,
								$row->ordering,
								$row->shared,
								$row->published,
								$discount_cat_ids,
								$discount_countries_ids,
								$discount_shoppergroups_ids,
								$discount_states_ids
								);
				$DiscountArray[] = $Discount;

			}
			return $DiscountArray;
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}	
	

	/**
    * This function AddDiscount
	* (expose as WS)
    * @param string 
    * @return result
   */
	function AddDiscount($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otheradd')==0){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
			//$_SESSION["auth"]= "admin";
			$allOk=true;
			
			setToken();
			
			if (!class_exists( 'VirtueMartModelCalc' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\calc.php');
			$modelCalc = new VirtueMartModelCalc;
			
			
			/*****/
			
			if (!class_exists( 'TableCalc_categories' )) require (JPATH_VM_ADMINISTRATOR.DS.'tables\calc_categories.php');
									$db = JFactory::getDBO();
									$tableDiscountXref = new TableCalc_categories($db);
									
			$cat_id = $params->Discounts->Discount->discount_type_id;
			$calc_id = $params->Discounts->Discount->discount_id;
			/*$tableDiscountXref->id = $calc_id;
			$tableDiscountXref->calc_categories = $cat_id;
			$tableDiscountXref->virtuemart_category_id = $params->Discounts->Discount->discount_type_id;
			$tableDiscountXref->virtuemart_calc_id = $calc_id;*/
			//$tableDiscountXref->_pkey = $calc_id;
			//$tableDiscountXref->_pvalue = $cat_id;
			//$resRef = $tableDiscountXref->store();
			
		//	return new SoapFault("AddDiscountFault", "DEBUG  resRef : ".$resRef. ' calc_id ' .$calc_id. ' cat_id ' .$cat_id);
			
			/******/
			
		
			if (is_array($params->Discounts->Discount)){
				
					$count = count($params->Discounts->Discount);
					for ($i = 0; $i < $count; $i++) {
						
						
						//$data['discount_id'] = $params->Discounts->Discount[$i]->discount_id;
						$data['virtuemart_vendor_id'] = isset($params->Discounts->Discount[$i]->vendor_id) ? $params->Discounts->Discount[$i]->vendor_id : 1;
						$data['calc_name'] = $params->Discounts->Discount[$i]->calc_name;
						$data['calc_descr'] = $params->Discounts->Discount[$i]->calc_descr;
						$data['calc_kind'] = $params->Discounts->Discount[$i]->calc_kind;
						$data['calc_value_mathop'] = $params->Discounts->Discount[$i]->calc_value_mathop;
						$data['calc_value'] = $params->Discounts->Discount[$i]->calc_value;
						$data['calc_currency'] = $params->Discounts->Discount[$i]->calc_currency;
						$data['calc_shopper_published'] = $params->Discounts->Discount[$i]->calc_shopper_published;
						$data['calc_vendor_published'] = $params->Discounts->Discount[$i]->calc_vendor_published;
						$data['publish_up'] = $params->Discounts->Discount[$i]->publish_up;
						$data['publish_down'] = $params->Discounts->Discount[$i]->publish_down;
						$data['calc_qualify'] = $params->Discounts->Discount[$i]->calc_qualify;
						$data['calc_affected'] = $params->Discounts->Discount[$i]->calc_affected;
						$data['calc_amount_cond'] = $params->Discounts->Discount[$i]->calc_amount_cond;
						$data['calc_amount_dimunit'] = $params->Discounts->Discount[$i]->calc_amount_dimunit;
						$data['for_override'] = $params->Discounts->Discount[$i]->for_override;
						$data['ordering'] = $params->Discounts->Discount[$i]->ordering;
						$data['shared'] = $params->Discounts->Discount[$i]->shared;
						$data['published'] = $params->Discounts->Discount[$i]->published;
						
						//store in CALC table
						$result = $modelCalc->store($data);
						
						//if store ok store xref
						if ($result != false) {
						
							$data['virtuemart_calc_id'] = $result;
							
							$cat_ids = explode ('|',$params->Discounts->Discount[$i]->discount_cat_ids);
							$data['calc_categories'] = $cat_ids;
							
							$countries_ids = explode ('|',$params->Discounts->Discount[$i]->discount_countries_ids);
							$data['virtuemart_country_id'] = $countries_ids;
							
							$shoppergroups_ids = explode ('|',$params->Discounts->Discount[$i]->discount_shoppergroups_ids);
							$data['virtuemart_shoppergroup_id'] = $shoppergroups_ids;
							
							$states_ids = explode ('|',$params->Discounts->Discount[$i]->discount_states_ids);
							$data['virtuemart_state_id'] = $states_ids;
							
							//Store (update) calc with xref
							$resultXref = $modelCalc->store($data);
						
						}

						if ($result){
							$cpnIdsStr .= " ".$resultXref;
						}else{
							$allOk=false;
						}
					}
				} else {
						
						$data['virtuemart_vendor_id'] = isset($params->Discounts->Discount->vendor_id) ? $params->Discounts->Discount->vendor_id : 1;
						$data['calc_name'] = $params->Discounts->Discount->calc_name;
						$data['calc_descr'] = $params->Discounts->Discount->calc_descr;
						$data['calc_kind'] = $params->Discounts->Discount->calc_kind;
						$data['calc_value_mathop'] = $params->Discounts->Discount->calc_value_mathop;
						$data['calc_value'] = $params->Discounts->Discount->calc_value;
						$data['calc_currency'] = $params->Discounts->Discount->calc_currency;
						$data['calc_shopper_published'] = $params->Discounts->Discount->calc_shopper_published;
						$data['calc_vendor_published'] = $params->Discounts->Discount->calc_vendor_published;
						$data['publish_up'] = $params->Discounts->Discount->publish_up;
						$data['publish_down'] = $params->Discounts->Discount->publish_down;
						$data['calc_qualify'] = $params->Discounts->Discount->calc_qualify;
						$data['calc_affected'] = $params->Discounts->Discount->calc_affected;
						$data['calc_amount_cond'] = $params->Discounts->Discount->calc_amount_cond;
						$data['calc_amount_dimunit'] = $params->Discounts->Discount->calc_amount_dimunit;
						$data['for_override'] = $params->Discounts->Discount->for_override;
						$data['ordering'] = $params->Discounts->Discount->ordering;
						$data['shared'] = $params->Discounts->Discount->shared;
						$data['published'] = $params->Discounts->Discount->published;
						
						//Store calc
						$result = $modelCalc->store($data);
						
						//if store ok store xref
						if ($result != false) {
						
							$data['virtuemart_calc_id'] = $result;
							
							$cat_ids = explode ('|',$params->Discounts->Discount->discount_cat_ids);
							$data['calc_categories'] = $cat_ids;
							
							$countries_ids = explode ('|',$params->Discounts->Discount->discount_countries_ids);
							$data['virtuemart_country_id'] = $countries_ids;
							
							$shoppergroups_ids = explode ('|',$params->Discounts->Discount->discount_shoppergroups_ids);
							$data['virtuemart_shoppergroup_id'] = $shoppergroups_ids;
							
							$states_ids = explode ('|',$params->Discounts->Discount->discount_states_ids);
							$data['virtuemart_state_id'] = $states_ids;
							
							//Store (update) calc with xref
							$resultXref = $modelCalc->store($data);
						
						}
						
						
					if ($result != false && $resultXref != false){
						$CommonReturn = new CommonReturn(OK,"Discount sucessfully added  : ".$params->Discounts->Discount->calc_name,$result);
						return $CommonReturn;
					
					}else if ($result != false){
						$CommonReturn = new CommonReturn(WARNING,"Discount sucessfully added (Warning xref)  : ".$params->Discounts->Discount->calc_name,$result);
						return $CommonReturn;
					} else {
						return new SoapFault("AddDiscountFault", "Cannot Add Discount  : ".$params->Discounts->Discount->calc_name);
					}
				}
				if ($allOk){
					$CommonReturn = new CommonReturn(OK,"All Discount successfully added : ",$cpnIdsStr);
					return $CommonReturn;
					
				} else {
					return new SoapFault("AddDiscountFault", "Not all Discount added, only Discount id : ".$cpnIdsStr);
				}			
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}
		/**
    * This function UpdateDiscount
	* (expose as WS)
    * @param string 
    * @return result
   */
	function UpdateDiscount($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherupdate')==0){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
		
			setToken();
			if (!class_exists( 'VirtueMartModelCalc' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\calc.php');
			$modelCalc = new VirtueMartModelCalc;
			
			$allOk=true;
			if (is_array($params->Discounts->Discount)){
				
					$count = count($params->Discounts->Discount);
					for ($i = 0; $i < $count; $i++) {
						
						
						$data['virtuemart_vendor_id'] = isset($params->Discounts->Discount[$i]->vendor_id) ? $params->Discounts->Discount[$i]->vendor_id : 1;
						$data['calc_name'] = $params->Discounts->Discount[$i]->calc_name;
						$data['calc_descr'] = $params->Discounts->Discount[$i]->calc_descr;
						$data['calc_kind'] = $params->Discounts->Discount[$i]->calc_kind;
						$data['calc_value_mathop'] = $params->Discounts->Discount[$i]->calc_value_mathop;
						$data['calc_value'] = $params->Discounts->Discount[$i]->calc_value;
						$data['calc_currency'] = $params->Discounts->Discount[$i]->calc_currency;
						$data['calc_shopper_published'] = $params->Discounts->Discount[$i]->calc_shopper_published;
						$data['calc_vendor_published'] = $params->Discounts->Discount[$i]->calc_vendor_published;
						$data['publish_up'] = $params->Discounts->Discount[$i]->publish_up;
						$data['publish_down'] = $params->Discounts->Discount[$i]->publish_down;
						$data['calc_qualify'] = $params->Discounts->Discount[$i]->calc_qualify;
						$data['calc_affected'] = $params->Discounts->Discount[$i]->calc_affected;
						$data['calc_amount_cond'] = $params->Discounts->Discount[$i]->calc_amount_cond;
						$data['calc_amount_dimunit'] = $params->Discounts->Discount[$i]->calc_amount_dimunit;
						$data['for_override'] = $params->Discounts->Discount[$i]->for_override;
						$data['ordering'] = $params->Discounts->Discount[$i]->ordering;
						$data['shared'] = $params->Discounts->Discount[$i]->shared;
						$data['published'] = $params->Discounts->Discount[$i]->published;
						
						$data['virtuemart_calc_id'] = $params->Discounts->Discount[$i]->discount_id;
						
						$cat_ids = explode ('|',$params->Discounts->Discount[$i]->discount_cat_ids);
						$data['calc_categories'] = $cat_ids;
						
						$countries_ids = explode ('|',$params->Discounts->Discount[$i]->discount_countries_ids);
						$data['virtuemart_country_id'] = $countries_ids;
						
						$shoppergroups_ids = explode ('|',$params->Discounts->Discount[$i]->discount_shoppergroups_ids);
						$data['virtuemart_shoppergroup_id'] = $shoppergroups_ids;
						
						$states_ids = explode ('|',$params->Discounts->Discount[$i]->discount_states_ids);
						$data['virtuemart_state_id'] = $states_ids;
						
						//Store (update) calc with xref
						$result = $modelCalc->store($data);
						
						
						if ($result){
							$cpnIdsStr .= $result." ";
						}else{
							$allOk=false;
						}
					}
				} else {
				
						$data['virtuemart_vendor_id'] = isset($params->Discounts->Discount->vendor_id) ? $params->Discounts->Discount->vendor_id : 1;
						$data['calc_name'] = $params->Discounts->Discount->calc_name;
						$data['calc_descr'] = $params->Discounts->Discount->calc_descr;
						$data['calc_kind'] = $params->Discounts->Discount->calc_kind;
						$data['calc_value_mathop'] = $params->Discounts->Discount->calc_value_mathop;
						$data['calc_value'] = $params->Discounts->Discount->calc_value;
						$data['calc_currency'] = $params->Discounts->Discount->calc_currency;
						$data['calc_shopper_published'] = $params->Discounts->Discount->calc_shopper_published;
						$data['calc_vendor_published'] = $params->Discounts->Discount->calc_vendor_published;
						$data['publish_up'] = $params->Discounts->Discount->publish_up;
						$data['publish_down'] = $params->Discounts->Discount->publish_down;
						$data['calc_qualify'] = $params->Discounts->Discount->calc_qualify;
						$data['calc_affected'] = $params->Discounts->Discount->calc_affected;
						$data['calc_amount_cond'] = $params->Discounts->Discount->calc_amount_cond;
						$data['calc_amount_dimunit'] = $params->Discounts->Discount->calc_amount_dimunit;
						$data['for_override'] = $params->Discounts->Discount->for_override;
						$data['ordering'] = $params->Discounts->Discount->ordering;
						$data['shared'] = $params->Discounts->Discount->shared;
						$data['published'] = $params->Discounts->Discount->published;
						
						$data['virtuemart_calc_id'] = $params->Discounts->Discount->discount_id;
	
						$cat_ids = explode ('|',$params->Discounts->Discount->discount_cat_ids);
						$data['calc_categories'] = $cat_ids;
						
						$countries_ids = explode ('|',$params->Discounts->Discount->discount_countries_ids);
						$data['virtuemart_country_id'] = $countries_ids;
						
						$shoppergroups_ids = explode ('|',$params->Discounts->Discount->discount_shoppergroups_ids);
						$data['virtuemart_shoppergroup_id'] = $shoppergroups_ids;
						
						$states_ids = explode ('|',$params->Discounts->Discount->discount_states_ids);
						$data['virtuemart_state_id'] = $states_ids;
						
						//Store (update) calc with xref
						$result = $modelCalc->store($data);
						
						
						
						
					if ($result){
					$CommonReturn = new CommonReturn(OK,"Discount sucessfully updated for discount_id  : ".$data['discount_id'],$data['discount_id']);
					return $CommonReturn;
					
					}else {
						return new SoapFault("UpdateDiscountFault", "Cannot Update Discount for discount id  : ".$data['discount_id']);
					}
				}
				if ($allOk){
					$CommonReturn = new CommonReturn(OK,"All Discount successfully Updated : ",$cpnIdsStr);
					return $CommonReturn;
					
				} else {
					return new SoapFault("UpdateDiscountFault", "Not all Product Discount updated, only Product Discount id : ".$cpnIdsStr);
				}	
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}
	
	
	/**
    * This function DeleteDiscount
	* (expose as WS)
    * @param string 
    * @return result
   */
	function DeleteDiscount($params) {
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherdelete')==0){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
			$allOk=true;
			
			if (!class_exists( 'VirtueMartModelCalc' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\calc.php');
			$modelCalc = new VirtueMartModelCalc;
			
			if (is_array($params->ids->id)){
					$count = count($params->ids->id);
					for ($i = 0; $i < $count; $i++) {

						$ids[] = $params->ids->id[$i];
						
						$result = $modelCalc->remove($ids);
						
						if ($result){
							$cpnIdsStr .= $params->ids->id[$i]." ";
						}else{
							$allOk=false;
						}
					}
				} else {
						
						$ids[] = $params->ids->id;
						$result = $modelCalc->remove($ids);
						
					if ($result){
						$CommonReturn = new CommonReturn(OK,"Product Discount sucessfully deleted:  ".$d['discount_id'],$d['discount_id']);
						return $CommonReturn;
					}else {
						return new SoapFault("DeleteDiscountFault", "Cannot delete Discount  : ".$d['discount_id']);
					}
				}
				if ($allOk){
					$CommonReturn = new CommonReturn(OK,"Product Discount successfully deleted : ".$cpnIdsStr,$cpnIdsStr);
					return $CommonReturn;
				} else {
					return new SoapFault("DeleteDiscountFault", "Not all Product Discount deleted, only Product Discount id : ".$cpnIdsStr);
				}	
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}
	
		/**
    * This function GetProductPrices
	* (expose as WS)
    * @param string 
    * @return result
   */
	function GetProductPrices($params) {

		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherget')==0){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
		
			// Get Price is not DB result
			/*if (!class_exists( 'VirtueMartModelProduct' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\product.php');
			$VirtueMartModelProduct = new VirtueMartModelProduct;
			
			$prices = $VirtueMartModelProduct->getPrice($params->product_id);
			
			foreach ($prices as $price){
							
				$ProductPrice = new ProductPrice($db->f("product_price_id"),$db->f("product_id"),$db->f("product_price"),$db->f("product_currency"),$db->f("product_price_vdate"),$db->f("product_price_edate") ,$db->f("cdate"),$db->f("mdate"),$db->f("shopper_group_id"),$db->f("price_quantity_start"),$db->f("price_quantity_end"));
				$ProductPriceArray[] = $ProductPrice;
				
			}
			return $ProductPriceArray;*/
			
			$db = JFactory::getDBO();	
			$query  = "SELECT *  FROM #__virtuemart_product_prices WHERE 1 ";
			if (!empty($params->product_id)){
				$query  .= " AND virtuemart_product_id = $params->product_id ";
			}
			if (!empty($params->shopper_group_id)){
				$query  .= " AND virtuemart_shoppergroup_id = $params->shopper_group_id ";
			}
			if (!empty($params->product_currency)){
				$query  .= " AND product_currency = '$params->product_currency' ";
			}
			
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			foreach ($rows as $row){
				$ProductPrice = new ProductPrice($row->virtuemart_product_price_id,
													$row->virtuemart_product_id,
													$row->product_price,
													$row->product_currency,
													$row->product_price_vdate,
													$row->product_price_edate ,
													$row->created_on,
													$row->modified_on,
													$row->virtuemart_shoppergroup_id,
													$row->price_quantity_start,
													$row->price_quantity_end,
													$row->override,
													$row->product_override_price,
													$row->product_tax_id,
													$row->product_discount_id
													);
				$ProductPriceArray[] = $ProductPrice;
			}
			return $ProductPriceArray;
			/*
			$list  = "SELECT * FROM #__{vm}_product_price WHERE 1";
			
			
			if (!empty($params->product_id)){
				$list  .= " AND product_id = $params->product_id ";
			}
			if (!empty($params->shopper_group_id)){
				$list  .= " AND shopper_group_id = $params->shopper_group_id ";
			}
			if (!empty($params->product_currency)){
				$list  .= " AND product_currency = '$params->product_currency' ";
			}
			
			$db = new ps_DB;
			$db->query($list);
			while ($db->next_record()) {
				$ProductPrice = new ProductPrice($db->f("product_price_id"),$db->f("product_id"),$db->f("product_price"),$db->f("product_currency"),$db->f("product_price_vdate"),$db->f("product_price_edate") ,$db->f("cdate"),$db->f("mdate"),$db->f("shopper_group_id"),$db->f("price_quantity_start"),$db->f("price_quantity_end"));
				$ProductPriceArray[] = $ProductPrice;
			}

			$errMsg=  $db->getErrorMsg();
			
			if ($errMsg==null){
				return $ProductPriceArray;
			} else {
				return new SoapFault("JoomlaGetProductPricesFault", "cannot execute SQL Select Query  ".$list." | ERRLOG : ".$errMsg);				
			}*/
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}

	/**
    * This function AddProductPrices
	* (expose as WS)
    * @param string 
    * @return result
   */
	function AddProductPrices($params) {
	
		$product_id = $params->product_id;
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otheradd')==0){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
			
			// !!!! THIS WILL NOT ADD NEW PRICE AT THIS TIME BUT UPDATE (OR CREATE if not exit) EXISTING PRICE !!!!
			
			if (!class_exists( 'TableProduct_prices' )) require (JPATH_VM_ADMINISTRATOR.DS.'tables\product_prices.php');
			$db = JFactory::getDBO();
			$tableProduct_prices = new TableProduct_prices($db);
			
			$allOk=true;
			
			if (is_array($params->ProductPrices->ProductPrice)){
				
					$count = count($params->ProductPrices->ProductPrice);
					for ($i = 0; $i < $count; $i++) {
					
						$tableProduct_prices->virtuemart_product_id = $params->ProductPrices->ProductPrice[$i]->product_id;
						$tableProduct_prices->virtuemart_shoppergroup_id = $params->ProductPrices->ProductPrice[$i]->shopper_group_id;
						$tableProduct_prices->product_price = $params->ProductPrices->ProductPrice[$i]->product_price;
						$tableProduct_prices->override = $params->ProductPrices->ProductPrice[$i]->override;
						$tableProduct_prices->product_override_price = $params->ProductPrices->ProductPrice[$i]->product_override_price;
						$tableProduct_prices->product_tax_id = $params->ProductPrices->ProductPrice[$i]->product_tax_id;
						$tableProduct_prices->product_discount_id = $params->ProductPrices->ProductPrice[$i]->product_discount_id;
						$tableProduct_prices->product_currency = $params->ProductPrices->ProductPrice[$i]->product_currency;
						$tableProduct_prices->product_price_vdate = $params->ProductPrices->ProductPrice[$i]->product_price_vdate;
						$tableProduct_prices->product_price_edate = $params->ProductPrices->ProductPrice[$i]->product_price_edate;
						$tableProduct_prices->price_quantity_start = $params->ProductPrices->ProductPrice[$i]->price_quantity_start;
						$tableProduct_prices->price_quantity_end = $params->ProductPrices->ProductPrice[$i]->price_quantity_end;
						$tableProduct_prices->created_on = $params->ProductPrices->ProductPrice[$i]->created_on;
						$tableProduct_prices->modified_on = $params->ProductPrices->ProductPrice[$i]->modified_on;
						
						if ($tableProduct_prices->check()){
							$res = $tableProduct_prices->store();
						} else {
							$res = false;
						}
	
						if ($res){
							$cpnIdsStr .= $params->ProductPrices->ProductPrice[$i]->product_id;
						}else{
							$allOk=false;
						}
					}
				} else {
				
					$tableProduct_prices->virtuemart_product_id = $params->ProductPrices->ProductPrice->product_id;
					$tableProduct_prices->virtuemart_shoppergroup_id = $params->ProductPrices->ProductPrice->shopper_group_id;
					$tableProduct_prices->product_price = $params->ProductPrices->ProductPrice->product_price;
					$tableProduct_prices->override = $params->ProductPrices->ProductPrice->override;
					$tableProduct_prices->product_override_price = $params->ProductPrices->ProductPrice->product_override_price;
					$tableProduct_prices->product_tax_id = $params->ProductPrices->ProductPrice->product_tax_id;
					$tableProduct_prices->product_discount_id = $params->ProductPrices->ProductPrice->product_discount_id;
					$tableProduct_prices->product_currency = $params->ProductPrices->ProductPrice->product_currency;
					$tableProduct_prices->product_price_vdate = $params->ProductPrices->ProductPrice->product_price_vdate;
					$tableProduct_prices->product_price_edate = $params->ProductPrices->ProductPrice->product_price_edate;
					$tableProduct_prices->price_quantity_start = $params->ProductPrices->ProductPrice->price_quantity_start;
					$tableProduct_prices->price_quantity_end = $params->ProductPrices->ProductPrice->price_quantity_end;
					$tableProduct_prices->created_on = $params->ProductPrices->ProductPrice->created_on;
					$tableProduct_prices->modified_on = $params->ProductPrices->ProductPrice->modified_on;
					
					if ($tableProduct_prices->check()){
						$res = $tableProduct_prices->store();
					} else {
						$res = false;
					}
					
						
					if ($res){
						$CommonReturn = new CommonReturn(OK,"ProductPrices sucessfully added for product id : ".$d['product_id'],$_REQUEST['product_price_id']);
						return $CommonReturn;
					
					}else {
						return new SoapFault("AddProductPricesFault", "Cannot Add Product Prices for product id  : ".$d['product_id']);
					}
				}
				if ($allOk){
					$commonReturn = new CommonReturn(OK,"All Product Prices successfully added : ",$cpnIdsStr);
					return $commonReturn;
					
				} else {
					return new SoapFault("AddProductPricesFault", "Not all Product Prices added, only ProductPrices id : ".$cpnIdsStr);
				}			
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}
		/**
    * This function AddProductPrices
	* (expose as WS)
    * @param string 
    * @return result
   */
	function UpdateProductPrices($params) {
	
		$product_id = $params->product_id;
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherupdate')==0){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
			
			if (!class_exists( 'TableProduct_prices' )) require (JPATH_VM_ADMINISTRATOR.DS.'tables\product_prices.php');
			$db = JFactory::getDBO();
			$tableProduct_prices = new TableProduct_prices($db);
			
			$allOk=true;
			
			if (is_array($params->ProductPrices->ProductPrice)){
				
					$count = count($params->ProductPrices->ProductPrice);
					for ($i = 0; $i < $count; $i++) {
					
						$tableProduct_prices->virtuemart_product_price_id = $params->ProductPrices->ProductPrice[$i]->product_price_id;
						$tableProduct_prices->virtuemart_product_id = $params->ProductPrices->ProductPrice[$i]->product_id;
						$tableProduct_prices->virtuemart_shoppergroup_id = $params->ProductPrices->ProductPrice[$i]->shopper_group_id;
						$tableProduct_prices->product_price = $params->ProductPrices->ProductPrice[$i]->product_price;
						$tableProduct_prices->override = $params->ProductPrices->ProductPrice[$i]->override;
						$tableProduct_prices->product_override_price = $params->ProductPrices->ProductPrice[$i]->product_override_price;
						$tableProduct_prices->product_tax_id = $params->ProductPrices->ProductPrice[$i]->product_tax_id;
						$tableProduct_prices->product_discount_id = $params->ProductPrices->ProductPrice[$i]->product_discount_id;
						$tableProduct_prices->product_currency = $params->ProductPrices->ProductPrice[$i]->product_currency;
						$tableProduct_prices->product_price_vdate = $params->ProductPrices->ProductPrice[$i]->product_price_vdate;
						$tableProduct_prices->product_price_edate = $params->ProductPrices->ProductPrice[$i]->product_price_edate;
						$tableProduct_prices->price_quantity_start = $params->ProductPrices->ProductPrice[$i]->price_quantity_start;
						$tableProduct_prices->price_quantity_end = $params->ProductPrices->ProductPrice[$i]->price_quantity_end;
						$tableProduct_prices->created_on = $params->ProductPrices->ProductPrice[$i]->created_on;
						$tableProduct_prices->modified_on = $params->ProductPrices->ProductPrice[$i]->modified_on;
						
						if ($tableProduct_prices->check()){
							$res = $tableProduct_prices->store();
						} else {
							$res = false;
						}
	
						if ($res){
							$cpnIdsStr .= $params->ProductPrices->ProductPrice[$i]->product_id;
						}else{
							$allOk=false;
						}
					}
				} else {
				
					$tableProduct_prices->virtuemart_product_price_id = $params->ProductPrices->ProductPrice->product_price_id;
					$tableProduct_prices->virtuemart_product_id = $params->ProductPrices->ProductPrice->product_id;
					$tableProduct_prices->virtuemart_shoppergroup_id = $params->ProductPrices->ProductPrice->shopper_group_id;
					$tableProduct_prices->product_price = $params->ProductPrices->ProductPrice->product_price;
					$tableProduct_prices->override = $params->ProductPrices->ProductPrice->override;
					$tableProduct_prices->product_override_price = $params->ProductPrices->ProductPrice->product_override_price;
					$tableProduct_prices->product_tax_id = $params->ProductPrices->ProductPrice->product_tax_id;
					$tableProduct_prices->product_discount_id = $params->ProductPrices->ProductPrice->product_discount_id;
					$tableProduct_prices->product_currency = $params->ProductPrices->ProductPrice->product_currency;
					$tableProduct_prices->product_price_vdate = $params->ProductPrices->ProductPrice->product_price_vdate;
					$tableProduct_prices->product_price_edate = $params->ProductPrices->ProductPrice->product_price_edate;
					$tableProduct_prices->price_quantity_start = $params->ProductPrices->ProductPrice->price_quantity_start;
					$tableProduct_prices->price_quantity_end = $params->ProductPrices->ProductPrice->price_quantity_end;
					$tableProduct_prices->created_on = $params->ProductPrices->ProductPrice->created_on;
					$tableProduct_prices->modified_on = $params->ProductPrices->ProductPrice->modified_on;
					
					if ($tableProduct_prices->check()){
						$res = $tableProduct_prices->store();
					} else {
						$res = false;
					}
						
					if ($res){
						$CommonReturn = new CommonReturn(OK,"ProductPrices sucessfully updated, product_price_id : ".$d['product_price_id'].", product id ".$d['product_id'],$d['product_price_id']);
						return $CommonReturn;
					
					}else {
						return new SoapFault("UpdateProductPricesFault", "Cannot update Product Prices  : ".$d['product_price_id']);
					}
				}
				if ($allOk){
					$CommonReturn = new CommonReturn(OK,"All Product Prices successfully updated : ",$cpnIdsStr);
					return $CommonReturn;
					
				} else {
					return new SoapFault("UpdateProductPricesPricesFault", "Not all Product Prices updated, only ProductPrices id : ".$cpnIdsStr);
				}			
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}
	
	
	/**
    * This function Delete Tax
	* (expose as WS)
    * @param string 
    * @return result
   */
	function DeleteProductPrices($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherdelete')==0){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
		
			if (!class_exists( 'TableProduct_prices' )) require (JPATH_VM_ADMINISTRATOR.DS.'tables\product_prices.php');
			$db = JFactory::getDBO();
			$tableProduct_prices = new TableProduct_prices($db);
			
			$allOk=true;
			if (is_array($params->ids->id)){
					$count = count($params->ids->id);
					for ($i = 0; $i < $count; $i++) {

						$tableProduct_prices->virtuemart_product_price_id = $params->ids->id[$i];
						$res = $tableProduct_prices->delete();
						
						
						if ($res){
							$cpnIdsStr .= $params->ids->id[$i]." ";
						}else{
							$allOk=false;
						}
					}
				} else {
							
					$tableProduct_prices->virtuemart_product_price_id = $params->ids->id;
					$res = $tableProduct_prices->delete();
						
					if ($res){
						$commonReturn = new CommonReturn(OK,"Product Prices sucessfully deleted: ".$params->ids->id,$params->ids->id);
						return $commonReturn;
					}else {
						return new SoapFault("ProductPricesFault", "Cannot delete Product Prices  : ".$d['product_price_id']);
					}
				}
				if ($allOk){
					$commonReturn = new CommonReturn(OK,"Product Prices successfully deleted : ".$cpnIdsStr,$cpnIdsStr);
					return $commonReturn;
				} else {
					return new SoapFault("ProductPricesFault", "Not all Product Prices deleted, only Product Prices id : ".$cpnIdsStr);
				}	
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}
	
	
	/**
    * This function Get All Tax
	* (expose as WS)
    * @param string 
    * @return result
   */
	function GetAllTax($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->login, $params->password);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherget')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
		
			//A REVOIR COMPLETEMENT MODIFIER DANS VM2////
			$list  = "SELECT * FROM #__{vm}_tax_rate WHERE 1";
			
			$db = new ps_DB;
			$db->query($list);
			while ($db->next_record()) {
				$Tax = new Tax($db->f("tax_rate_id"),$db->f("vendor_id"),$db->f("tax_state"),$db->f("tax_country"),$db->f("mdate"),$db->f("tax_rate"));
				$TaxArray[] = $Tax;
			}

			return $TaxArray;
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}
		
	}
	
	/**
    * This function Add Tax
	* (expose as WS)
    * @param string 
    * @return result
   */
	function AddTax($params) {

		$product_id = $params->product_id;
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otheradd')==0){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
		
			return new SoapFault("AddTaxFault", "Not IN VM2 : Use Discount");
			
				
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}
	
	/**
    * This function Update Tax
	* (expose as WS)
    * @param string 
    * @return result
   */
	function UpdateTax($params) {

		$product_id = $params->product_id;
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherupdate')==0){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
		
			return new SoapFault("AddTaxFault", "Not IN VM2 : Use Discount");
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}
	
	/**
    * This function Delete Tax
	* (expose as WS)
    * @param string 
    * @return result
   */
	function DeleteTax($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherdelete')==0){
			$result = "true";
		}
		//Auth OK
		if ($result == "true"){
			return new SoapFault("AddTaxFault", "Not IN VM2 : Use Discount");
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}
	
	/**
    * This function get All Products
	* (expose as WS)
    * @param string
    * @return array of products
   */
	function GetAllProducts($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_getprod')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
		
			$limite_start = $params->limite_start;
			if (empty($limite_start)){
				$limite_start = "0";
			}
			$limite_end = $params->limite_end;
			if (empty($limite_end)){
				$limite_end = "500";
			}
			
			$db = JFactory::getDBO();	
			$query  = "SELECT virtuemart_product_id  ";
			$query .= "FROM #__virtuemart_products ";
			$query .= " LIMIT $limite_start,$limite_end "; 
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			$productIds;
			foreach ($rows as $row){
					$productIds[] = $row->virtuemart_product_id;
			}
			
			if (!class_exists( 'VirtueMartModelProduct' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\product.php');
			$VirtueMartModelProduct = new VirtueMartModelProduct;
			
			$products = $VirtueMartModelProduct->getProducts($productIds);
			
			foreach ($products as $ProductDetails){
				
				unset($prod_prices);
				if ($params->include_prices == 'Y' || $params->include_prices == '1' ){
					unset($params->shopper_group_id);
					unset($params->product_currency);
					$params->product_id = $ProductDetails->virtuemart_product_id;
					$prod_prices = GetProductPrices($params);
				}
				
		
				$Product = new Product($ProductDetails->virtuemart_product_id/*$ProductDetails->prices[0]*/ ,
								$ProductDetails->virtuemart_vendor_id,
								$ProductDetails->product_parent_id,
								$ProductDetails->product_sku,
								$ProductDetails->product_name,
								$ProductDetails->slug ,
								$ProductDetails->product_s_desc,
								$ProductDetails->product_desc ,
								$ProductDetails->product_weight ,
								$ProductDetails->product_weight_uom,
								$ProductDetails->product_length,
								$ProductDetails->product_width,
								$ProductDetails->product_height,
								$ProductDetails->product_lwh_uom,
								$ProductDetails->product_url,
								$ProductDetails->product_in_stock,
								$ProductDetails->low_stock_notification,
								$ProductDetails->product_available_date,
								$ProductDetails->product_availability,
								$ProductDetails->product_special,
								$ProductDetails->ship_code_id,
								$ProductDetails->product_sales,
								$ProductDetails->product_unit,
								$ProductDetails->product_packaging,
								$ProductDetails->product_order_levelss,
								$ProductDetails->hits,
								$ProductDetails->intnotes,
								$ProductDetails->metadesc, 
								$ProductDetails->metakey, 
								$ProductDetails->metarobot,
								$ProductDetails->metaauthor,
								$ProductDetails->layout,
								$ProductDetails->published, 
								getCategoriesIds($ProductDetails->virtuemart_product_id),
								getManufacturerId($ProductDetails->virtuemart_product_id),
								$prod_prices
								);
				$ProductArray[] = $Product;
					
			}
			return $ProductArray;
	
		 
		
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}		
	}
	
	/**
    * This function get Get Available Images on server (dir components/com_virtuemart/shop_image/product)
	* (expose as WS)
    * @param string
    * @return array of products
   */
	function GetAvailableImages($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherget')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
		
			if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');
			$vmConfig = VmConfig::loadConfig();
			
			$media_category_path = $vmConfig->get('media_product_path');
			
			$uri = JURI::base();
			$uri = str_replace('administrator/components/com_vm_soa/services/', "", $uri);
			
			$INSTALLURL = '';
			if (empty($conf['BASESITE']) && empty($conf['URL'])){
				$INSTALLURL = $uri;
			} else if (!empty($conf['BASESITE'])){
				$INSTALLURL = 'http://'.$conf['URL'].'/'.$conf['BASESITE'].'/';
			} else {
				$INSTALLURL = 'http://'.$conf['URL'].'/';
			}
			
			if ($params->img_type == "full" || $params->img_type == "all" || $params->img_type == ""){
			
				$dir = JPATH.DS.$media_category_path.'';		
				// Ouvre un dossier bien connu, et liste tous les fichiers
				if (is_dir($dir)) {
					if ($dh = opendir($dir)) {
						while (($file = readdir($dh)) !== false) {
							//echo "fichier : $file : type : " . filetype($dir . $file) . "\n";
							if ($file =="." || $file ==".." || $file =="index.html"){
								
							} else {
								$AvalaibleImage = new AvalaibleImage($file,$INSTALLURL.$media_category_path.$file,$dir,$media_category_path.$file);
								$AvalaibleImageArray[] = $AvalaibleImage;
							}
						}
						closedir($dh);
					}
				}
			}
			if ($params->img_type == "thumb" || $params->img_type == "all" || $params->img_type == ""){
				
				$dir = JPATH.DS.$media_category_path.'resized';
				
				// Ouvre un dossier bien connu, et liste tous les fichiers
				if (is_dir($dir)) {
					if ($dh = opendir($dir)) {
						while (($file = readdir($dh)) !== false) {
							
							if ($file =="." || $file ==".." || $file =="index.html"){
								
							} else {
							$AvalaibleImage = new AvalaibleImage($file,$INSTALLURL.$media_category_path.'resized/'.$file,$dir,$media_category_path.'resized/'.$file);
							$AvalaibleImageArray[] = $AvalaibleImage;
							}
						}
						closedir($dh);
					}
				}
			}
			return $AvalaibleImageArray;

			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}		
	}
	
	/**
    * This function get Get Available Images on server (dir components/com_virtuemart/shop_image/product)
	* (expose as WS)
    * @param string
    * @return array of products
   */
	function GetAvailableFiles($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->login, $params->password);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherget')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
		
		
			$dir = realpath( dirname(__FILE__).'/../../../../media' );
			$dirname = $dir;
			//$dir = "/tmp/php5";
			// Ouvre un dossier bien connu, et liste tous les fichiers
			if (is_dir($dir)) {
				if ($dh = opendir($dir)) {
					while (($file = readdir($dh)) !== false) {
						//echo "fichier : $file : type : " . filetype($dir . $file) . "\n";
						if (!empty($conf['BASESITE'])){
							$AvalaibleFile = new AvalaibleFile($file,'http://'.$conf['URL'].'/'.$conf['BASESITE'].'/media/'.$file,$dirname);
						}else {
							$AvalaibleFile = new AvalaibleFile($file,'http://'.$conf['URL'].'/media/'.$file,$dirname);
						}
						
						$AvalaibleFileArray[] = $AvalaibleFile;
					}
					closedir($dh);
				}
			}
			
			
			return $AvalaibleFileArray;

			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}		
	}
	
	
	/**
    * This function get All medias for product
	* (expose as WS)
    * @param string The id of the product
    * @return array of Media
   */
	function GetMediaProduct($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otherget')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
		
			if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
			$mediaModel = new VirtueMartModelMedia();
			
			$_REQUEST['virtuemart_product_id'] = $params->product_id;
			$files = $mediaModel->getFiles();
			
			
			foreach ($files as $file){
				
	
				$media = new Media($file->virtuemart_media_id,
											$file->virtuemart_vendor_id,
											$file->file_title,
											$file->file_description,
											$file->file_meta,
											$file->file_mimetype,
											$file->file_type,
											$file->file_url,
											$file->file_url_thumb,
											$file->file_is_product_image,
											$file->file_is_downloadable,
											$file->file_is_forSale,
											$file->file_params,
											$file->ordering,
											$file->shared,
											$file->published
											);
				$mediaArray[] = $media;
			}
			return $mediaArray;
			
		
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}	
	
	/**
    * This function ADD medias for product
	* (expose as WS)
    * @param string The media
    * @return commonReturn
   */
	function AddMediaProduct($params) {
	
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		$vmConfig = getVMconfig();
		if ($vmConfig->get('soap_auth_prod_otheradd')==0){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
		
			setToken();
			
			//if data to attach -> write file
			if (isset($params->media->attachValue)){
				$dataFile = $params->media->attachValue;//base64Binary 
				$ext = mimeTypeToExtention($params->media->file_mimetype);
				$filename = $params->media->file_title."".$ext;
				$ret = writeMedia($dataFile,$filename,'product',isMimeTypeImg($params->media->file_mimetype));//write file
				if ($ret != false){
					$params->media->file_url = $ret[0];
					$params->media->file_url_thumb=  $ret[1];
				}
			}
			
			/// this function add media in media table/remove old category media link and add this new
			/// todo Add media and dont remove old media
			if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
			$mediaModel = new VirtueMartModelMedia();
			
			//get old media	
			$_REQUEST['virtuemart_product_id'] = $params->product_id;
			$p_medias = $mediaModel->getFiles();
						
			$data['virtuemart_media_id'] = null;
			
			foreach ($p_medias as $media){
				$media_ids[] = $media->virtuemart_media_id;
			}
			
			
			//$data['virtuemart_media_id'] = $file->virtuemart_media_id,
			if (!empty($params->virtuemart_media_id)){
				$media_ids[] = $params->virtuemart_media_id;
			}
			$data['virtuemart_media_id']=$media_ids;
		
			
			
			//$data['virtuemart_media_id'] 	= $params->media->virtuemart_media_id;
			$data['virtuemart_product_id'] = $params->product_id;
			$data['virtuemart_vendor_id'] 	= isset($params->media->virtuemart_vendor_id) ? $params->media->virtuemart_vendor_id : 1;
			$data['file_title'] 			= $params->media->file_title;
			$data['file_description'] 		= $params->media->file_description;
			$data['file_meta'] 				= $params->media->file_meta;
			$data['file_mimetype'] 			= $params->media->file_mimetype;
			$data['file_type'] 				= isset($params->media->file_type) ? $params->media->file_type : 'product';
			$data['file_url'] 				= $params->media->file_url;
			$data['file_url_thumb'] 		= $params->media->file_url_thumb;
			$data['file_is_product_image'] 	= $params->media->file_is_product_image;
			$data['file_is_downloadable'] 	= $params->media->file_is_downloadable;
			$data['file_is_forSale'] 		= $params->media->file_is_forSale;
			$data['file_params'] 			= $params->media->file_params;
			$data['ordering'] 				= $params->media->ordering;
			$data['shared'] 				= $params->media->shared;
			$data['media_published'] 				= 0;
			if ($params->media->published == "1" || $params->media->published == "Y" ){
				$data['media_published'] 			= 1;
			}
			
			
			$file_id = $mediaModel->storeMedia($data,'product');
			$errors = $mediaModel->getErrors();
			
			foreach($errors as $error){
				$error .= '\n'.$error;
			}
			if (!empty($errors)){
				return new SoapFault("AddMediaCategoryFault", "Cannot add media category ".$error);
			}else{
				$commonReturn = new CommonReturn(OK,"Media Added for category : ".$params->category_id,$file_id);
				return $commonReturn;
			}

		
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}	
	
	
	
	/* SOAP SETTINGS */
	
	if ($vmConfig->get('soap_ws_prod_on')==1){
	
		/* SOAP SETTINGS */
		ini_set("soap.wsdl_cache_enabled", $vmConfig->get('soap_ws_prod_cache_on')); // wsdl cache settings
		
		$options = array('soap_version' => SOAP_1_2);

		
		/** SOAP SERVER **/
		if (empty($conf['BASESITE']) && empty($conf['URL'])){
			$server = new SoapServer(JURI::root(false).'/VM_ProductWSDL.php');
		}else if (!empty($conf['BASESITE'])){
			$server = new SoapServer('http://'.$conf['URL'].'/'.$conf['BASESITE'].'/administrator/components/com_virtuemart/services/VM_ProductWSDL.php');
		}else {
			$server = new SoapServer('http://'.$conf['URL'].'/administrator/components/com_virtuemart/services/VM_ProductWSDL.php');
		}
		
		/* Add Functions */
		$server->addFunction("GetProductsFromCategory");
		$server->addFunction("GetChildsProduct");
		$server->addFunction("GetProductFromId");
		$server->addFunction("GetProductFromSKU");
		$server->addFunction("UpdateProduct");
		$server->addFunction("GetProductsFromOrderId");
		$server->addFunction("AddProduct");
		$server->addFunction("DeleteProduct");	
		$server->addFunction("GetAllCurrency");	
		$server->addFunction("GetAllTax");	
		$server->addFunction("AddTax");	
		$server->addFunction("UpdateTax");	
		$server->addFunction("DeleteTax");	
		$server->addFunction("GetAllProducts");
		$server->addFunction("GetAvailableImages");
		$server->addFunction("GetProductPrices");
		$server->addFunction("AddProductPrices");
		$server->addFunction("UpdateProductPrices");
		$server->addFunction("DeleteProductPrices");
		$server->addFunction("GetDiscount");
		$server->addFunction("AddDiscount");
		$server->addFunction("UpdateDiscount");
		$server->addFunction("DeleteDiscount");
		$server->addFunction("UpdateProductDiscount");
		$server->addFunction("GetProductFile");
		$server->addFunction("AddProductFile");
		$server->addFunction("UpdateProductFile");
		$server->addFunction("DeleteProductFile");
		$server->addFunction("GetAvailableFiles");
		$server->addFunction("SearchProducts");
		$server->addFunction("GetProductVote");
		$server->addFunction("GetProductReviews");
		$server->addFunction("PublishReviews");
		$server->addFunction("GetRelatedProducts");
		$server->addFunction("SetRelatedProducts");
		$server->addFunction("AddMediaProduct");
		$server->addFunction("GetMediaProduct");
			
		$server->handle();

	}else{
		echoXmlMessageWSDisabled('Product');
	}
	
?> 