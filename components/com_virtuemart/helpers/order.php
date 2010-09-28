<?php
/**
 *
 * Helper class that's main purpose is to record finalized orders
 * in the database and perform related tasks like reducing the
 * inventory.
 * The purpose of extracting this functionality into a helper
 * class has the intention that it can then be used independently
 * of controller - e.g. it could be used for an order form
 * in the backend or an order API using XML-RPC.
 *
 * @author Markus Oehler
 *
 */
class OrderHelper {

  /**
   *
   * Records an order and performs all related tasks like
   * reducing inventory and increasing sales statistics.
   * @param $cart
   */
  function recordOrder($cart) {
		foreach($cart->products as $product){
			OrderHelper::updateProductAfterSale($product['product_id']);
		}
	  	
	  }
  }

  /**
   *
   * Enter description here ...
   * @param $product
   */
  function updateProductAfterSale($product)
  {
  	$db = JFactory::getDBO();
  	$query = "UPDATE " . $db->nameQuote("#__vm_product")
  	       . " SET " . $db->nameQuote("product_in_stock") . " = " . $db->nameQuote("product_in_stock") . " - " . $product['quantity']
  	       . " , " . $db->nameQuote("product_sales") . " = " . $db->nameQuote("product_sales") . " + " . $product['quantity']
  	       . " WHERE " . $db->nameQuote("product_id") . " = " . $product['product_id'];

    $db->setQuery($query);
  	$db->query();

  // TODO-MOE - i really don't know at the moment why the inventory is only set to 0 if all
  // child products have an inventory of 0. Is it also only set to 1 if there actually
  // are items in the parent product?!
  /*
  $productParentId;

  $query = "SELECT COUNT(" . $db->nameQuote("product_id") . ") "
         . "FROM " . $db->nameQuote("#__vm_product") . " "
         . "WHERE " . $db->nameQuote("product_parent_id") . " = " . $productParentId
         . " AND " . $db->nameQuote("product_in_stock") . " > 0";

  $db->setQuery($query);
  if (true) {
  	if ($dboi->f("product_parent_id") != 0) {
				$q = "SELECT COUNT(product_id) ";
				$q .= "FROM #__{vm}_product ";
				$q .= "WHERE product_parent_id = ".$dboi->f("product_parent_id");
				$q .= " AND product_in_stock > 0";
				$db->query($q);
				$db->next_record();
				if (!$db->f("COUNT(product_id)")) {
					$q = "UPDATE #__{vm}_product ";
					$q .= "SET product_in_stock = 0 ";
					$q .= "WHERE product_id = ".$dboi->f("product_parent_id")." LIMIT 1";
					$db->query($q);
			  }
			}
  }

  */


  }

}