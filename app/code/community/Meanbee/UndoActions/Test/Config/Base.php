<?php

class Meanbee_UndoActions_Test_Config_Base extends EcomDev_PHPUnit_Test_Case_Config {

    /**
     * Test for the correct module version
     * @test
     */
    public function testModuleVersion() {
        $this->assertModuleVersion('1.0.0');
    }

    /**
     * Test that the correct code pool is used
     * @test
     */
    public function testCodePool() {
        $this->assertModuleCodePool('community');
    }

    /**
     * Test that my observers are defined
     * @test
     */
    public function testObserversDefined() {
        $this->assertEventObserverDefined('global', 'checkout_cart_product_add_after', 'Meanbee_UndoActions_Model_Observer', 'checkoutCartProductAddAfter');
        $this->assertEventObserverDefined('global', 'controller_action_postdispatch_checkout_cart_add', 'Meanbee_UndoActions_Model_Observer', 'controllerActionPostdispatchCheckoutCartAdd');
        $this->assertEventObserverDefined('global', 'sales_quote_remove_item', 'Meanbee_UndoActions_Model_Observer', 'salesQuoteRemoveItem');
    }

    /**
     * Test that my router front name is correct
     * @test
     */
    public function testRouterFrontName() {
        $this->assertRouteFrontName('undoactions');
    }
}