<?php

class Meanbee_UndoActions_IndexController extends Mage_Core_Controller_Front_Action {
    public function indexAction() {

        $requestParams = $this->getRequest()->getParams();
        Mage::log($requestParams, null, 'ashsmith.log', true);

        switch ($requestParams['action']) {
            case 'undocartadd':
                $this->_undoAddToCart($requestParams['product_id'], $requestParams['qty']);
                break;
            case 'undocartremove':
                $this->_undoRemoveFromCart();
                break;
        }
        // Return to cart after
        $this->_redirectReferer();
    }

    protected function _undoAddToCart($productId = null, $qty) {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product')->load($productId);

        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        $quoteItem = $quote->getItemByProduct($product);

        $newQty = $quoteItem->getTotalQty() - $qty;

        if ($newQty == 0) {
            $quote->deleteItem($quoteItem)->save();
        } else {
            $quoteItem->setQty($newQty)->save();
            $quote->save();
        }

        // Make sure totals are recalculated!
        $quote->setTotalsCollectedFlag(false)->collectTotals();

        // Set success message to let the customer know we have undone the action...
        $message = Mage::helper('checkout')->__('Action un-done!');
        Mage::getSingleton('checkout/session')->addSuccess($message);

    }

    protected function _undoRemoveFromCart() {

        $undoActions = Mage::getSingleton('core/session')->getUndoActions();
        $cart = Mage::getSingleton('checkout/cart');
        $product = new Mage_Catalog_Model_Product();
        $product->load($undoActions['product_id']);
        if ($undoActions['quote_item_options'][0]) {
            $params = $undoActions['quote_item_options'][0];
        }
        $cart->addProduct($product, $params);
        $cart->save();
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);



//        /** @var Mage_Sales_Model_Quote $quote */
//        $quote = Mage::getSingleton('checkout/session')->getQuote();
//
//
//        $quoteItem = $undoActions['quote_item'];
//        Mage::log(get_class($item), null, 'ashsmith.log', true);
//        /** @var Mage_Sales_Model_Quote_Item $quoteItemModel */
//        $quoteItemModel = Mage::getModel('sales/quote_item')->setData($item);
//        /** @var Mage_Catalog_Model_Product $product */
//        $product = Mage::getModel('catalog/product')->load($quoteItemModel->getProductId());
//        $quote->it
//        $quoteItemModel->setProduct($product);
//        $quote->addItem($quoteItemModel);
//        $quote->save();
//        $quote->setTotalsCollectedFlag(false)->collectTotals();

        Mage::getSingleton('core/session')->unsetData('undo_actions');


        // Copied from Mage_Sales_Model_Quote::removeItem()
//        $item = $quote->getItemById($itemId);
//        $quote->getItem
//        Mage::log(get_class($item), null, 'ashsmith.log', true);
//
//        Mage::log($item->debug(), null, 'ashsmith.log', true);

//        if ($item) {
//            $item->setQuote($quote);
//            $quote->setIsMultiShipping(true);
//            $item->isDeleted(false);
//            if ($item->getHasChildren()) {
//                foreach ($item->getChildren() as $child) {
//                    $child->isDeleted(false);
//                }
//            }
//
//            $parent = $item->getParentItem();
//            if ($parent) {
//                $parent->isDeleted(false);
//            }
//            $item->save();
//        }
//        $quote->save();
//        $quote->setTotalsCollectedFlag(false)->collectTotals();

    }
}