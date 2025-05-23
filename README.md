# Ratings Plugin for CakePHP

[![CI](https://github.com/dereuromark/cakephp-ratings/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/dereuromark/cakephp-ratings/actions/workflows/ci.yml?query=branch%3Amaster)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-ratings/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-ratings)
[![License](https://poser.pugx.org/dereuromark/cakephp-ratings/license.svg)](LICENSE)
[![Downloads](https://poser.pugx.org/dereuromark/cakephp-ratings/d/total.svg)](https://packagist.org/packages/dereuromark/cakephp-ratings)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-8892BF.svg)](https://php.net/)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-yellow.svg)](https://github.com/php-fig-rectified/fig-rectified-standards)

The **Ratings** plugin will allow you by simply adding the ratings component to your controller to rate anything. The component will auto load a helper and behavior.

The core part of this plugin is the Ratable behavior that is attached to your models.
In most cases you don't need to attach it yourself, because the Rating component will take care of it.

Note: This branch is for **CakePHP 5.1+**. For details see [version map](https://github.com/dereuromark/cakephp-ratings/wiki#cakephp-version-map).

### Recommended

To have a nice star rating to chose from, it ships with the possibility to include JS.
The default JS tool in use is:

* https://github.com/kartik-v/bootstrap-star-rating

It should, however, be customizable to any other JS library and templating framework.

## Demo
https://sandbox.dereuromark.de/sandbox/ratings

## Documentation

For documentation, as well as tutorials, see the [docs](docs/) directory of this repository.

## Support

For bugs and feature requests, please use the [issues](https://github.com/dereuromark/cakephp-ratings/issues) section of this repository.
