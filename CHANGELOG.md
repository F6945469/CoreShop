# Within V2

## 2.0.0-beta.2 to 2.0.0-beta.3
 - **BC break** Signature of following interfaces changed:
    - ```CoreShop\Component\Index\Interpreter\InterpreterInterface```: public function interpret($value, IndexableInterface $object, IndexColumnInterface $config, $interpreterConfig = []);
    - ```CoreShop\Component\Index\Interpreter\LocalizedInterpreterInterface```: public function interpretForLanguage($language, $value, IndexableInterface $object, IndexColumnInterface $config, $interpreterConfig = []);
    - ```CoreShop\Component\Index\Interpreter\RelationInterpreterInterface```: public function interpretRelational($value, IndexableInterface $indexable, IndexColumnInterface $config, $interpreterConfig = []);
    - ```CoreShop\Component\Customer\Model\UserInterface::ROLE_DEFAULT``` renamed ```CoreShop\Component\Customer\Model\UserInterface::CORESHOP_ROLE_DEFAULT```
    - ```CoreShop\Component\Customer\Model\UserInterface::ROLE_SUPER_ADMIN``` renamed ```CoreShop\Component\Customer\Model\UserInterface::CORESHOP_ROLE_SUPER_ADMIN```

 - **BC break** Shipment / Invoice Creation via API changed
    - Before adding a new Shipment / Invoice you need to dispatch a request state to your order. Read more about it [here](./docs/03_Development/06_Order/05_Invoice/01_Invoice_Creation.md) and [here](./docs/03_Development/06_Order/06_Shipment/01_Shipment_Creation.md).

## 2.0.0-beta.1 to 2.0.0-beta.2
 - Link Generator implemented. If you want to use nice urls, you need to add the link generator service:
    - CoreShopProduct: add `@coreshop.object.link_generator.product` as Link Provider
    - CoreShopCategory: add `@coreshop.object.link_generator.category` as Link Provider
    - Change `{{ path('') }}` to `{{ coreshop_path('') }}`. You may want to checkout the FrontendBundle to get a deeper insight.
 - Deprecated Field Names in - ```CoreShop\Component\Payment\Model\PaymentInterface```:
    - getName is now getTitle
    - getOrderId is now getOrder and directly returns a OrderInterface
 - Deprecated Field Names in - ```CoreShop\Component\Shipping\Model\CarrierInterface```:
    - getLabel is not getTitle
    - getName is now getIdentifier


## 2.0.0-alpha.4 to 2.0.0-beta.1
 - **BC break** Signature of following interfaces changed:
    - ```CoreShop\Component\Index\Interpreter\InterpreterInterface```: public function interpret($value, IndexableInterface $object, IndexColumnInterface $config);
    - ```CoreShop\Component\Index\Interpreter\LocalizedInterpreterInterface```: public function interpretForLanguage($language, $value, IndexableInterface $object, IndexColumnInterface $config);
    - ```CoreShop\Component\Index\Interpreter\RelationInterpreterInterface```: public function interpretRelational($value, IndexableInterface $indexable, IndexColumnInterface $config);

 - **BC break** CoreShop now takes advantage of the dependent bundle feature introduced in Pimcore 5.1.2. Therefore,
 all bundles are now automatically loaded. This is a BC break, as when updating, you might run into issues.
 To solve the issues, check following things:
    - remove ```RegisterBundles``` entry from ```app/AppKernel.php```
    - remove loading CoreShopCoreBundle Configuration in ```app/config.yml```
    - enable CoreShopCoreBundle via CLI or manually in ```var/config/extensions.php```: ```"CoreShop\\Bundle\\CoreBundle\\CoreShopCoreBundle" => TRUE```
 - **BC break** Upgraded Default Layout to Bootstrap 4. This will most certainly cause issues when you just override certain templates. Best would be to copy all templates from before the Bootstrap 4 Upgrade.

## 2.0.0-alpha.4 to 2.0.0-alpha.5
 - **BC break** added Component\Core\Model\OrderItem and Component\Core\Model\QuoteItem. If you already customized them, inherit them from the Core Models.
 - **BC break** changed the way CoreShop processes Cart-Rules. If you implemented a custom-condition, inherit from ```CoreShop\Component\Order\Cart\Rule\Condition\AbstractConditionChecker``` and implement ```isCartRuleValid``` instead of ```isValid```
- Deprecated: remove `cart/add-price-rule/` static route.

## 2.0.0-alpha.3 to 2.0.0-alpha.4
 - **BC break** decoupled MoneyBundle from CurrencyBundle, therefore the Twig Extension for Money Conversion went to the CurrencyBundle. Therefore the name of that extension was renamed from
   **coreshop_convert_money** to **coreshop_convert_currency**. If you use it directly in your code, please rename all of them.
 - Deprecated CoreShopAdminBundle which responsability was only handling installation and deliviering pimcore resources like JS and CSS files.
    * Installation has been moved to CoreShopCoreBundle
    * Delivering of resources is now handled by CoreShopResourceBundle, this also makes it easier to use CoreShop Bundles without handling resources yourself.

## 2.0.0-alpha.2 to 2.0.0-alpha.3
 - **BC break** getPrice in PurchasableInterface and ProductInterface has been removed. In favor of this a new coreShopStorePrice editable has been introduced, which stores prices for each store. This makes handling of multiple currencies way more elegant.
 
   If you still want to use the old getPrice, create a new Subclass of \CoreShop\Component\Core\Model\Product, implement \CoreShop\Component\Order\Model\PriceAwarePurchasableInterface and set your class to CoreShopProduct parents.

# V1 to V2
 - CoreShop 2 is not backward compatible. Due to the framework change, we decided to re-make CoreShop from scratch. If you still have instances running and want to migrate, there is a basic migration way which gets you data from V1 to V2.
 - [Export from CoreShop1](https://github.com/coreshop/CoreShopExport)
 - [Import into CoreShop2](https://github.com/coreshop/ImportBundle)

# Within V1
 - Nothing available