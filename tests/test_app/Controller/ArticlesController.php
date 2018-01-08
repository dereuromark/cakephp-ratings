<?php

namespace TestApp\Controller;

use Cake\Controller\Controller;

/**
 * @property \Ratings\Controller\Component\RatingComponent $Rating
 */
class ArticlesController extends Controller {

	/**
	 * Models used
	 *
	 * @var string
	 */
	public $modelClass = 'Articles';

	/**
	 * Helpers used
	 *
	 * @var array
	 */
	public $helpers = ['Html', 'Form'];

	/**
	 * Components used
	 *
	 * @var array
	 */
	public $components = ['Ratings.Rating', 'Auth', 'Flash'];

}
