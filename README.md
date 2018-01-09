# Ratings Plugin for CakePHP

[![Build Status](https://secure.travis-ci.org/dereuromark/cakephp-ratings.svg)](http://travis-ci.org/dereuromark/cakephp-ratings)
[![License](https://poser.pugx.org/dereuromark/cakephp-ratings/license)](https://packagist.org/packages/dereuromark/cakephp-ratings)
[![Downloads](https://poser.pugx.org/dereuromark/cakephp-ratings/d/total.png)](https://packagist.org/packages/dereuromark/cakephp-ratings)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-yellow.svg)](https://github.com/php-fig-rectified/fig-rectified-standards)

The **Ratings** plugin will allow you by simply adding the ratings component to your controller to rate anyting. The component will auto load a helper and behavior.

The core part of this plugin is the ratable behavior that is attached to your models. In most cases you don't need attach it yourself, because the rating component will take care of it.

## Requirements

* CakePHP 3.0+
* PHP 5.4+

This branch only works for **CakePHP3.x** - please use the respective branch for CakePHP 2.x!

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
