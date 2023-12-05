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
	 * @var string|null
	 */
	protected ?string $modelClass = 'Articles';

	/**
	 * Helpers used
	 *
	 * @var array|null
	 */
	protected ?array $helpers = ['Html', 'Form'];

	/**
	 * Components used
	 *
	 * @var array|null
	 */
	protected ?array $components = ['Ratings.Rating', 'Flash'];

}
