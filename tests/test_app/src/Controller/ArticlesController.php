<?php

namespace TestApp\Controller;

use Shim\Controller\Controller;

/**
 * @property \Ratings\Controller\Component\RatingComponent $Rating
 */
class ArticlesController extends Controller {

	/**
	 * Models used
	 *
	 * @var string
	 */
	protected $modelClass = 'Articles';

	/**
	 * Helpers used
	 *
	 * @var array
	 */
	protected array $helpers = ['Html', 'Form'];

	/**
	 * Components used
	 *
	 * @var array
	 */
	protected array $components = ['Ratings.Rating', 'Auth', 'Flash'];

}
