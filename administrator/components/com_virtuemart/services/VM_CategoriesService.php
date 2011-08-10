<?php
define( '_VALID_MOS', 1 );
define( '_JEXEC', 1 );

/**
 * Virtuemart Category SOA Connector
 *
 * Virtuemart Category SOA Connector (Provide functions GetCategoryFromId, GetCategoryFromId, GetChildsCategory, GetCategorysFromCategory)
 * The return classe is a "Category" classe with attribute : id, name, description, price, quantity, image, fulliamage ,
 * attributes, parent produit, child id)
 *
 * @package    com_vm_soa
 * @subpackage components
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  2011 Mickael Cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    $Id: VM_CategoriesService.php 3821 2011-08-09 10:08:04Z mike75 $
 */
 
/** loading framework **/
include_once('VM_Commons.php');

/**
 * Class Categorie
 *
 * Class "Categorie" with attribute : id, name, description,  image, fulliamage , parent category
 * attributes, parent produit, child id)
 *
 * @author     Mickael cabanas (cabanas.mickael|at|gmail.com)
 * @copyright  Mickael cabanas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    Release:
 */
	class Categorie {
		public $id="";
		public $vendor_id="";
		public $name="";
		public $slug="";
		public $description="";
		public $category_parent_id="";
		public $category_template="";
		public $category_layout="";
		public $category_product_layout="";
		public $products_per_row="";	
		public $limit_list_start="";
		public $limit_list_step="";
		public $limit_list_max="";	
		public $limit_list_initial="";
		public $hits="";
		public $published="";
		public $numberofproducts="";
		
				
		//constructeur
		function __construct($id, $vendor_id, $name, $slug, $description, $category_parent_id, $category_template, $category_layout, $category_product_layout,
								$products_per_row,$limit_list_start,$limit_list_step,$limit_list_max,$limit_list_initial,$hits,$published,$numberofproducts) {
								
			$this->id = $id;
			$this->vendor_id = $vendor_id;
			$this->name = $name;
			$this->slug = $slug;
			$this->description = $description;
			$this->category_parent_id = $category_parent_id;
			$this->category_template = $category_template;
			$this->category_layout = $category_layout;
			$this->category_product_layout = $category_product_layout;
			$this->products_per_row = $products_per_row;
			$this->limit_list_start = $limit_list_start;
			$this->limit_list_step = $limit_list_step;
			$this->limit_list_max = $limit_list_max;
			$this->limit_list_initial = $limit_list_initial;
			$this->hits = $hits;
			$this->published = $published;
			$this->numberofproducts = $numberofproducts;
			
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
 * Class Media
 *
 * Class "Media" with attribute : $virtuemart_media_id, $file_title, $file_description ...
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
		public $attachValue="";//only used in input
		
		//constructeur
		function __construct($virtuemart_media_id, $virtuemart_vendor_id, $file_title, $file_description, $file_meta, $file_mimetype, $file_type, $file_url, $file_url_thumb,
								$file_is_product_image,$file_is_downloadable,$file_is_forSale,$file_params,$ordering,$shared,$published,$attachValue) {
								
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
			$this->attachValue = $attachValue;
			
			
		}
	}
	
	
/**
 * Class CommonReturn
 *
 * Class "CommonReturn" with attribute : returnCode, message, $returnData, 
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
    * This function get Childs of a category for a category ID
	* (expose as WS)
    * @param Object
    * @return array of Categories
   */
	function GetChildsCategories($params) {
	
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		
		
		if ($conf['auth_cat_getall']=="off"){
			$result = "true";
		}

		//Auth OK
		if ($result == "true"){
		
			include('../vm_soa_conf.php');
			
			if (!class_exists( 'VirtueMartModelCategory' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\category.php');
			$VirtueMartModelCategory = new VirtueMartModelCategory;
			
			$p_category_id = isset($params->categoryId) ? $params->categoryId : "0";
			
			$db = JFactory::getDBO();
			
			$query  = "SELECT * FROM `#__virtuemart_categories` CAT ";
			$query .= "JOIN `#__virtuemart_category_categories` REF ON CAT.virtuemart_category_id=REF.category_child_id ";
			$query .= "WHERE category_parent_id = '$p_category_id'  ";
			
			if (!empty($params->category_publish)){
				if ($params->category_publish == "Y"){
					$query .= "AND published = 1 ";
				} else {
					$query .= "AND published = 0 ";
				}
			}
			
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			
			foreach ($rows as $row)	{
			
				$parent_cat 	= $VirtueMartModelCategory->getParentCategory($row->virtuemart_category_id);
				$nbProd 		= $VirtueMartModelCategory->countProducts($row->virtuemart_category_id);
				
				$Categorie = new Categorie( $row->virtuemart_category_id,
											$row->virtuemart_vendor_id,
											$row->category_name,
											$row->slug,
											$row->category_description,
											isset($parent_cat->virtuemart_category_id) ? $parent_cat->virtuemart_category_id : 0,
											$row->category_template,
											$row->category_layout,
											$row->category_product_layout,
											$row->products_per_row,
											$row->limit_list_start,
											$row->limit_list_step,
											$row->limit_list_max,
											$row->limit_list_initial,
											$row->hits,
											$row->published,
											$nbProd
											);
				$catArray[] = $Categorie;
			
			}
			return $catArray;
			
			
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
		
	}
	
	/**
    * This function get All the categories
	* (expose as WS)
    * @param Object
    * @return array of Categories
   */
	function GetAllCategories($params) {
	
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		if ($conf['auth_cat_getall']=="off"){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
		
			if (!class_exists( 'VirtueMartModelCategory' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\category.php');
			$VirtueMartModelCategory = new VirtueMartModelCategory;
						
			$db = JFactory::getDBO();
			$query = "SELECT * FROM `#__virtuemart_categories` WHERE 1 ";
			if (!empty($params->category_publish)){
				if ($params->category_publish == "Y"){
					$query .= "AND published = 1 ";
				} else {
					$query .= "AND published = 0 ";
				}
			}
			
			if (!empty($params->category_id)){
				$query .= "AND virtuemart_category_id = '$params->category_id' ";
			}
			
			$db->setQuery($query);

			$rows = $db->loadObjectList();
			foreach ($rows as $row){
				
				$parent_cat 	= $VirtueMartModelCategory->getParentCategory($row->virtuemart_category_id);
				$nbProd 		= $VirtueMartModelCategory->countProducts($row->virtuemart_category_id);
				
				$Categorie = new Categorie($row->virtuemart_category_id,
											$row->virtuemart_vendor_id,
											$row->category_name,
											$row->slug,
											$row->category_description,
											isset($parent_cat->virtuemart_category_id) ? $parent_cat->virtuemart_category_id : 0,
											$row->category_template,
											$row->category_layout,
											$row->category_product_layout,
											$row->products_per_row,
											$row->limit_list_start,
											$row->limit_list_step,
											$row->limit_list_max,
											$row->limit_list_initial,
											$row->hits,
											$row->published,
											$nbProd
											);
				$catArray[] = $Categorie;
			}
			return $catArray;
			
		
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}

	/**
    * This function add category
	* (expose as WS)
    * @param Object
    * @return CommonReturn
   */
	function AddCategory($params) {
		
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		if ($conf['auth_cat_addcat']=="off"){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
		
			setToken();
			
			include('../vm_soa_conf.php');
			$category_id = array($params->category_id);
			
			if (!class_exists( 'VirtueMartModelCategory' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\category.php');
			$VirtueMartModelCategory = new VirtueMartModelCategory;
			
			$vendor_id= $params->category->vendor_id;
			if (empty($params->category->vendor_id)){
				$vendor_id = "1";
			}
			
			if ($params->category->published == "Y" || $params->category->published == "1"){
				$publish = 1;
			}else {
				$publish = 0;
			}

			//$data["virtuemart_category_id"]	= "1";
			$data["virtuemart_vendor_id"] 		= $vendor_id;
			$data['category_name']				= $params->category->name;
			$data['slug']						= $params->category->slug;
			$data['category_description']		= $params->category->description;
			$data['category_parent_id']			= $params->category->category_parent_id;
			$data['category_template']			= $params->category->category_template;
			$data["category_layout"] 			= $params->category->category_layout;
			$data['category_product_layout']	= $params->category->category_product_layout;
			$data['products_per_row']			= $params->category->products_per_row;
			$data['limit_list_start'] 			= $params->category->limit_list_start;
			$data['limit_list_step'] 			= $params->category->limit_list_step;
			$data['limit_list_max'] 			= $params->category->limit_list_max;
			$data['limit_list_initial'] 		= $params->category->limit_list_initial;
			$data['hits'] 						= $params->category->hits;
			$data['published']					= $publish;
			
			$cat_id = $VirtueMartModelCategory->store($data);
			
			if ($cat_id == false ){
				return new SoapFault("VMCategoryCreationFault", "Cannot create category. Error : ".$VirtueMartModelCategory->getError().'\n');
			}else{
				$commonReturn = new CommonReturn(OK,"Category created : ".$params->category->name,$cat_id);
				return $commonReturn;
			}
		
		}else if ($result== "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}
	}

	/**
    * This function Update Category
	* (expose as WS)
    * @param Object
    * @return CommonReturn
   */
	function UpdateCategory($params) {
		
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		if ($conf['auth_cat_updatecat']=="off"){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
			
			setToken();
			
			include('../vm_soa_conf.php');
			
			if (empty($params->category->id)){
				return new SoapFault("VMUpdateCategoryFault", "category->id must be set");
			}
					
			if (!class_exists( 'VirtueMartModelCategory' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\category.php');
			$VirtueMartModelCategory = new VirtueMartModelCategory;
			
			$vendor_id= $params->category->vendor_id;
			if (empty($params->category->vendor_id)){
				$vendor_id = "1";
			}
			
			if ($params->category->published == "Y" || $params->category->published == "1"){
				$publish = 1;
			}else {
				$publish = 0;
			}
				
			$data["virtuemart_category_id"]		= $params->category->id;
			$data["virtuemart_vendor_id"] 		= $vendor_id;
			$data['category_name']				= $params->category->name;
			$data['slug']						= $params->category->slug;
			$data['category_description']		= $params->category->description;
			$data['category_parent_id']			= $params->category->category_parent_id;
			$data['category_template']			= $params->category->category_template;
			$data["category_layout"] 			= $params->category->category_layout;
			$data['category_product_layout']	= $params->category->category_product_layout;
			$data['products_per_row']			= $params->category->products_per_row;
			$data['limit_list_start'] 			= $params->category->limit_list_start;
			$data['limit_list_step'] 			= $params->category->limit_list_step;
			$data['limit_list_max'] 			= $params->category->limit_list_max;
			$data['limit_list_initial'] 		= $params->category->limit_list_initial;
			$data['hits'] 						= $params->category->hits;
			$data['published']					= $publish;
			
			$cat_id = $VirtueMartModelCategory->store($data);
			
			if ($cat_id == false ){
				return new SoapFault("VMUpdateCategoryFault", "Cannot UpdateCategory category. Error : ".$VirtueMartModelCategory->getError().'\n');
			}else{
				$commonReturn = new CommonReturn(OK,"Category updated : ".$params->category->name,$params->category->id);
				return $commonReturn;
			}
		
		}else if ($result== "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}
	}
	
	/**
    * This function DeleteCategory
	* (expose as WS)
    * @param Object
    * @return CommonReturn
   */
	function DeleteCategory($params) {
		
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		if ($conf['auth_cat_delcat']=="off"){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
			
			setToken();
			
			include('../vm_soa_conf.php');
			
			if (empty($params->category_id)){
				return new SoapFault("VMCategoryDeleteFault", "params->category_id must be set");
			}
			
			$category_id = array($params->category_id);
			
			if (!class_exists( 'VirtueMartModelCategory' )) require (JPATH_VM_ADMINISTRATOR.DS.'models\category.php');
			$VirtueMartModelCategory = new VirtueMartModelCategory;
			
			$ret = $VirtueMartModelCategory->remove($category_id);
		
			if ($ret == false ){
				return new SoapFault("VMCategoryDeleteFault", "Cannot delete category ".$params->category_id);
			}else{
				$commonReturn = new CommonReturn(OK,"Category deleted : ".$params->category_id,$params->category_id);
				return $commonReturn;
			}

		//Auth KO
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->login);
		}
	}

	/**
    * This function get All medias for category
	* (expose as WS)
    * @param Object
    * @return array of Media
   */
	function GetMediaCategory($params) {
	
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		if ($conf['auth_cat_getall']=="off"){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
		
			if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
			$mediaModel = new VirtueMartModelMedia();
			
			$_REQUEST['virtuemart_category_id'] = $params->category_id;
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
    * This function ADD medias for category
	* (expose as WS)
    * @param Object
    * @return commonReturn
   */
	function AddMediaCategory($params) {
	
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		if ($conf['auth_cat_addcat']=="off"){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
			
			setToken();
			
			//if data to attach -> write file (attachValue is data base64Binary encoded)
			if (!empty($params->media->attachValue)){
				
				$dataFile = $params->media->attachValue;//base64Binary 
				$ext = mimeTypeToExtention($params->media->file_mimetype);
				$filename = $params->media->file_title."".$ext;
				$ret = writeMedia($dataFile,$filename,'category',isMimeTypeImg($params->media->file_mimetype));//write file
				if ($ret != false){
					$params->media->file_url = $ret[0];
					$params->media->file_url_thumb=  $ret[1];
				}
			}
			$data['media_action']='upload';
			
			/// this function add media in media table/remove old category media link and add this new
			/// todo Add media and dont remove old media
			if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
			$mediaModel = new VirtueMartModelMedia();
			
			//get old media	
			$_REQUEST['virtuemart_category_id'] = $params->category_id;
			$p_medias = $mediaModel->getFiles();
						
			$data['virtuemart_media_id'] = null;
			
			foreach ($p_medias as $media){
				$media_ids[] = $media->virtuemart_media_id;
			}
			
			if (!empty($params->virtuemart_media_id)){
				$media_ids[] = $params->virtuemart_media_id;
				
			}
			$data['virtuemart_media_id']=$media_ids;
			
			$data['virtuemart_category_id'] = $params->category_id;
			$data['virtuemart_vendor_id'] 	= isset($params->media->virtuemart_vendor_id) ? $params->media->virtuemart_vendor_id : 1;
			$data['file_title'] 			= $params->media->file_title;
			$data['file_description'] 		= $params->media->file_description;
			$data['file_meta'] 				= $params->media->file_meta;
			$data['file_mimetype'] 			= $params->media->file_mimetype;
			$data['file_type'] 				= isset($params->media->file_type) ? $params->media->file_type : 'category';
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
			
			$file_id = $mediaModel->storeMedia($data,'category');
			$errors = $mediaModel->getErrors();

			foreach($errors as $error){
				$error .= '\n'.$error;
			}
			if ($file_id==false){
				return new SoapFault("AddMediaCategoryFault", "Cannot add media category ",$error);
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
	
	/**
    * This function DeleteMediaCategory
    * we don't delete media directly, media is maybe related to other category
    * we just delete ref to cat
	* (expose as WS)
    * @param Object
    * @return CommonReturn
    */
	function DeleteMediaCategory($params) {
		
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		if ($conf['auth_cat_delcat']=="off"){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
			
			setToken();
			
			// we don't remove media directly media is maybe related to other category
			
			/*if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
			$mediaModel = new VirtueMartModelMedia();
			$data['virtuemart_media_id'] = $params->media_id;
			$ret = $mediaModel->remove($data);//this don't remove relation
			*/
			
			// del Media just only delete relation
			
			$db = JFactory::getDBO();	
			$query  = "SELECT id FROM #__virtuemart_category_medias ";
			$query .= "WHERE virtuemart_category_id = '$params->category_id' ";
			$query .= "AND virtuemart_media_id = '$params->media_id' ";
			
			$db->setQuery($query);
			
			$rows = $db->loadObjectList();
			
			$cat_media_id;
			foreach ($rows as $row){
					$cat_media_id = $row->id;
			}
			if (empty($cat_media_id)){
				return new SoapFault("MediaCategoryDeleteFault", "Cannot delete media category (No relation found)",$params->category_id);
			}
			
			if (!class_exists( 'TableCategory_medias' )) require (JPATH_VM_ADMINISTRATOR.DS.'tables\category_medias.php');
			$tableCategory_medias = new TableCategory_medias($db);
			$tableCategory_medias->id = $cat_media_id;
			$ret = $tableCategory_medias->delete();
			
		
			if ($ret == false ){
				return new SoapFault("MediaCategoryDeleteFault", "Cannot delete media category ".$params->id);
			}else{
				$commonReturn = new CommonReturn(OK,"Media Category deleted : ".$params->category_id,$params->category_id);
				return $commonReturn;
			}

		//Auth KO
		}else if ($result == "false"){
			return new SoapFault("JoomlaServerAuthFault", "Authentication KO for : ".$params->loginInfo->login);
		}else if ($result == "no_admin"){
			return new SoapFault("JoomlaServerAuthFault", "User is not a Super Administrator : ".$params->loginInfo->login);
		}else{
			return new SoapFault("JoomlaServerAuthFault", "User does not exist : ".$params->loginInfo->login);
		}
	}
	
	/**
    * This function get Get Available Images on server 
    * (dir images/stories/virtuemart/category/)
	* (expose as WS)
    * @param Object
    * @return Array
   */
	function GetAvailableImages($params) {
	
		include('../vm_soa_conf.php');
		
		/* Authenticate*/
		$result = onAdminAuthenticate($params->loginInfo->login, $params->loginInfo->password,$params->loginInfo->isEncrypted);
		if ($conf['auth_cat_getimg']=="off"){
			$result = "true";
		}
		
		//Auth OK
		if ($result == "true"){
		
			if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');
			$vmConfig = VmConfig::loadConfig();
			
			$media_category_path = $vmConfig->get('media_category_path');
			if (empty($media_category_path)){
				return new SoapFault("GetAvailableImagesFault","media_category_path is not set, please check your virtuemart settings");
			}
			
			$uri = JURI::base();
			$uri = str_replace('administrator/components/com_virtuemart/services/', "", $uri);
			
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
	

	/* SOAP SETTINGS */
		
	if ($vmConfig->get('soap_ws_cat_on')==1){
		
		/* SOAP SETTINGS */
		$cache = "0";
		if ($conf['cat_cache'] == "on")$cache = "1";
		ini_set("soap.wsdl_cache_enabled", $cache); // wsdl cache settings
		
		if ($conf['soap_version'] == "SOAP_1_1"){
			$options = array('soap_version' => SOAP_1_1);
		}else {
			$options = array('soap_version' => SOAP_1_2);
		}

		/** SOAP SERVER **/
		if (empty($conf['BASESITE']) && empty($conf['URL'])){
			$server = new SoapServer(JURI::root(false).'/VM_CategoriesWSDL.php');
		}else if (!empty($conf['BASESITE'])){
			$server = new SoapServer('http://'.$conf['URL'].'/'.$conf['BASESITE'].'/administrator/components/com_virtuemart/services/VM_CategoriesWSDL.php');
		}else {
			$server = new SoapServer('http://'.$conf['URL'].'/administrator/components/com_virtuemart/services/VM_CategoriesWSDL.php');
		}
		
		/* Add Functions */
		$server->addFunction("GetAllCategories");
		$server->addFunction("GetChildsCategories");
		$server->addFunction("AddCategory");
		$server->addFunction("DeleteCategory");
		$server->addFunction("GetAvailableImages");
		$server->addFunction("UpdateCategory");
		$server->addFunction("GetMediaCategory");
		$server->addFunction("AddMediaCategory");
		$server->addFunction("DeleteMediaCategory");
		
		$server->handle();
		
	}else{
		echo "This Web Service (Categories) is disabled";
	}
?> 