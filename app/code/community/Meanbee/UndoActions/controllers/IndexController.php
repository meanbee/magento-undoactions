<?php

class Meanbee_UndoActions_IndexController extends Mage_Core_Controller_Front_Action {
    public function indexAction() {

        $requestParams = $this->getRequest()->getParams();

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


        // If $quoteItem is false, then we need to find it another way.
        if(!$quoteItem) {
            foreach($quote->getAllVisibleItems() as $item) {
                if($item->getProductId() == $productId) {
                    $quoteItem = $item;
                    break;
                }
            }
        }

        $newQty = $quoteItem->getTotalQty() - $qty;

        if ($newQty == 0) {
            $quote->removeItem($quoteItem->getId())->save();
        } else {
            $quoteItem->setQty($newQty)->save();
        }
        $quote->save();

        // Make sure totals are recalculated!
        $quote->setTotalsCollectedFlag(false)->collectTotals();
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

        Mage::getSingleton('core/session')->unsetData('undo_actions');

    }
}