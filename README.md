# Magento 2 Psalm Plugin

This module is based on `vismadc/magento2-psalm` and `tdgroot/magento2-psalm` but rewritten from scratch.

## Installation

```shell
composer require --dev renttek/magento2-psalm
vendor/bin/psalm-plugin enable renttek/magento2-psalm
```

## Features
- Mocking of Extension Attribute classes
- Mocking of Factory classes
- Mocking of Proxy classes
- Stubs for badly annotated classes

## Configuration

If you don't want to enable one of the mock categories or the loading of the stubs, you can disable them in your `psalm.xml` by setting these flags:

```xml
<?xml version="1.0"?>
<psalm>
    <!-- ... -->
    <plugins>
        <pluginClass class="Renttek\Magento2Psalm\Plugin">
            <enableExtensionAttributeMocker>false</enableExtensionAttributeMocker> <!-- disables mocking of extension attribute classes -->
            <enableFactoryMocker>false</enableFactoryMocker> <!-- disables mocking of factory classes -->
            <enableProxyMocker>false</enableProxyMocker> <!-- disables mocking of proxy classes -->
            <loadStubs>false</loadStubs> <!-- disables loading of static stub files -->
        </pluginClass>
    </plugins>
    <!-- ... -->
</psalm>
```
