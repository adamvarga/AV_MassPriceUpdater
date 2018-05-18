AV_MassPriceUpdater for Magento 2.x
=====================
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://paypal.me/adamvarga28)

Mass Price Updater

You can update the different price type as regular price or special price

You can change the prices fixed or with percentage per category

TODO: tierprice, msrp price

-------------------------------
Installation Instructions
-------------------------
1, Clone the extension as a composer repository via GitHub 

2, Add the <strong>av/masspriceupdater</strong> composer package to your project. 

3, Require with 
```
composer require av/masspriceupdater
```
4, Clear the cache and upgrade the module environment with
 
 ```
 rm -rf var/cache/*
 rm -rf var/page_cache/*
 rm -rf var/generation/*
 php bin/magento setup:upgrade
 ```
 
5, Logout from the admin panel and then login again.

6, Change the config in System -> Configuration -> Catalog -> AV MassPriceUpdater - Price Configuration -> Configuration

Example setup:

![alt text](https://github.com/adamvarga/AV_MassPriceUpdater/blob/master/masspriceupdater_setup.png)


Uninstallation
--------------
1, Remove all extension files from your Magento installation OR

2, Remove via Composer and clear the caches

```
composer remove av/masspriceupdater
```

Support
-------
If you have any issues with this extension, open an issue on [GitHub](https://github.com/adamvarga).

Contribution
------------
Any contribution is highly appreciated. The best way to contribute code is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Developer
---------
Adam Varga
