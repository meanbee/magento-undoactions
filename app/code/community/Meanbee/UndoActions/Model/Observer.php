<?php
class Meanbee_UndoActions_Model_Observer {

    protected $_session = null;

    public function checkoutCartProductAddAfter($observer) {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $observer->getProduct();
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        $quoteItem = $observer->getQuoteItem();

        if($quoteItem->getParentItem() instanceof Mage_Sales_Model_Quote_Item) {
            $quoteItem = $quoteItem->getParentItem();
            Mage::log(get_class($quoteItem),null, 'ashsmith.log', true);
        }

        Mage::getSingleton('core/session')->setUndoActions(
            array(
                'product_id' => $quoteItem->getProductId(),
                'product_name' => $product->getName(),
                'quote_item_id' => $quoteItem->getId()
            )
        );
    }

    public function controllerActionPostdispatchCheckoutCartAdd($observer) {

        $coreSession = Mage::getSingleton('core/session');
        $data = $coreSession->getUndoActions();
        $productName = $data['product_name'];

        // Make sure data was actually set, otherwise product wasn't added to cart
        if (!isset($data)) {
            return;
        }

        $checkoutSession = Mage::getSingleton('checkout/session');

        // Params we pass to update the item, we only need the product id and qty
        $urlParams = array(
            'action' => 'undocartadd',
            'qty' => Mage::app()->getRequest()->getPost('qty'),
            'product_id' => $data['product_id']
        );
        $message = Mage::helper('checkout')->__(
            '%s was added to your shopping cart. <a class="undo-action" href="%s">Undo</a>',
            Mage::helper('core')->escapeHtml($productName),
            $this->_undoUrl($urlParams)
        );
        $this->_updateMessages($checkoutSession, $message);

        //Unset last undo action session data.
        $coreSession->unsetData('undo_actions');
    }

    public function salesQuoteRemoveItem($observer) {

        $checkoutSession = Mage::getSingleton('checkout/session');
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        $quoteItem = $observer->getQuoteItem();

        $product_id = $quoteItem->getData('product_id');
        $options = array();
        foreach($quoteItem->getOptions() as $option) {
            $options[] = unserialize($option->getData('value'));
        }
        $options = array_filter(array_map('array_filter', $options)); //Remove empty values.

        Mage::getSingleton('core/session')->setUndoActions(
            array(
                'product_id' => $product_id,
                'quote_item_options' => $options
            )
        );

        $message = Mage::helper('checkout')->__(
            '%s was removed from your shopping cart. <a class="undo-action" href="%s">Undo</a>',
            Mage::helper('core')->escapeHtml($observer->getQuoteItem()->getProduct()->getName()),
            $this->_undoUrl(array('action' => 'undocartremove'))
        );
        $this->_updateMessages($checkoutSession, $message);
    }

    protected function _undoUrl($params) {
        return Mage::getUrl('undoactions/index', $params);
    }

    protected function _updateMessages($session, $message) {
        //Clear Any messages, so we can re-add our message next.
        $session->getMessages(true);
        //Add new success message
        $session->addSuccess($message);
    }
}