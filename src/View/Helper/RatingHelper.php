<?php
/**
 * Copyright 2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Ratings\View\Helper;

use Cake\Utility\Text;
use Cake\View\Helper;

/**
 * CakePHP Ratings Plugin
 *
 * Rating helper
 */
class RatingHelper extends Helper {

	/**
	 * helpers variable
	 *
	 * @var array
	 */
	public $helpers = ['Html', 'Form'];

	/**
	 * Allowed types of html list elements
	 *
	 * @var array $allowedTypes
	 */
	public $allowedTypes = ['ul', 'ol', 'radio'];

	/**
	 * Default settings
	 *
	 * @var array $defaults
	 */
	public $defaults = [
		'stars' => 5,
		'item' => null,
		'value' => 0,
		'type' => 'ul',
		'createForm' => false,
		'url' => [],
		'link' => true,
		'redirect' => true,
		'class' => 'rating',
		'js' => false,
	];

	/**
	 * Sizes for rating image
	 *
	 * @var array
	 */
	public $sizes = ['large' => 28, 'medium' => 16, 'small' => 12];

	/**
	 * Rounds the value according to the steps used (between min/max inclusivly)
	 *
	 * @param int $value
	 * @param int $steps
	 * @param int $min
	 * @param int $max
	 * @return int Value
	 */
	public function round($value, $steps = 4, $min = 0, $max = 5) {
		if ($value <= $min) {
			return $min;
		}
		$v = round($value * $steps) / $steps;
		return min($v, $max);
	}

	/**
	 * @param float $value (0...X)
	 * @param array $options
	 * - type: defaults to fa (font-awesome), also possible: ui (jquery-ui)
	 * - stars (defaults to 5)
	 * - steps per image (defaults to 2 => 1/2 accuracy)
	 * - ...
	 * @param array $attributes for div container (id, style, ...)
	 * @return string $divContainer with rating images
	 */
	public function ratingImage($value, array $options = [], array $attributes = []) {
		$options += ['type' => 'fa'];
		$matching = [
			'fa' => 'FontAwesome',
			'ui' => 'JqueryUi'
		];
		$method = '_ratingImage' . $matching[$options['type']];

		return $this->$method($value, $options, $attributes);
	}

	/**
	 * @param float $value (0...X)
	 * @param array $options
	 * - stars (defaults to 5)
	 * - steps per image (defaults to 2 => 1/2 accuracy)
	 * - ...
	 * @param array $attributes for div container (id, style, ...)
	 * @return string $divContainer with rating images
	 */
	public function _ratingImageFontAwesome($value, array $options = [], array $attributes = []) {
		$array = [
			'full' => '<i class="fa fa-fw fa-star"></i>',
			'half' => '<i class="fa fa-fw fa-star-half-o"></i>',
			'empty' => '<i class="fa fa-fw fa-star-o"></i>',
		];

		$options += [
			'stars' => 5,
			'steps' => 2,
		];

		if ($value <= 0) {
			$roundedValue = 0;
			if (empty($attributes['title'])) {
				$attributes['title'] = __d('ratings', 'No rating available yet.');
			}
		} else {
			$roundedValue = $this->round($value, $options['steps'], 0, $options['stars']);
		}

		$res = '';
		for ($i = 0; $i < $options['stars']; $i++) {
			if ((int)$roundedValue > $i) {
				$k = 'full';
			} else {
				$k = 'empty';
			}
			if ($k === 'empty' && $roundedValue > $i && (2 * $roundedValue + 1) % 2 === 0) {
				$k = 'half';
			}

			if (abs($roundedValue - $i) < 0.5) {
			}
			/*
			if ($k === 'empty' && $options['steps'] > 1 && $roundedValue > $i && $roundedValue - $i <= 0.25) {
				$k = 'full';
			} elseif ($k === 'empty' && $options['steps'] > 1 && $roundedValue - $i > 0.25) {
				$k = 'half';
			}
			*/

			$res .= $array[$k];
		}

		$precision = 2;
		if ((int)$roundedValue == $roundedValue) {
			$precision = 0;
		} elseif ((int)(2 * $roundedValue) == 2 * $roundedValue) {
			$precision = 1;
		}
		$defaults = [
			'title' => __d('ratings', '{0} of {1} stars', number_format($roundedValue, $precision, ',', '.'), $options['stars']),
		];
		$attributes += $defaults;
		return $this->Html->div('ratingStars clearfix', $res, $attributes);
	}

	/**
	 * @param float $value (0...X)
	 * @param array $options
	 * - stars (defaults to 5)
	 * - steps per image (defaults to 4 => 1/4 accuracy)
	 * - ...
	 * @param array $attributes for div container (id, style, ...)
	 * @return string $divContainer with rating images
	 */
	public function _ratingImageJqueryUi($value, array $options = [], array $attributes = []) {
		$size = !empty($options['size']) ? $options['size'] : '';
		if (!empty($size)) {
			$options['pixels'] = $this->sizes[$size];
		}
		$pixels = !empty($options['pixels']) ? $options['pixels'] : 16;
		$steps = !empty($options['steps']) ? $options['steps'] : 4;
		$stars = !empty($options['stars']) ? $options['stars'] : 5;
		if ($value <= 0) {
			$roundedValue = 0;
			if (empty($attributes['title'])) {
				$attributes['title'] = __d('ratings', 'No rating available yet.');
			}
		} else {
			$roundedValue = $this->round($value, $steps, 0, $stars);
		}

		$array = [
			0 => '<i class="fa fa-fw fa-star"></i>',
			1 => '<i class="fa fa-fw fa-star-o"></i>',
		];

		$res = '';
		$disable = 0;
		for ($i = 0; $i < $stars; $i++) {
			for ($j = 0; $j < $steps; $j++) {
				if (!$disable && ($i + $j * (1 / $steps)) >= $roundedValue) {
					$disable = 1;
				}
				$v = $array[$disable];

				if ($j === 0) {
					# try to use a single image if possible
					if ($i < floor($roundedValue) || $i >= ceil($roundedValue) || $i === 0 && $roundedValue >= 1) {
						$res .= Text::insert($v, ['margin' => 0, 'width' => $pixels], ['before' => '{', 'after' => '}']);
						break;
					}
				}

				$margin = 0 - ($pixels / $steps) * $j;
				$res .= Text::insert($v, ['margin' => $margin, 'width' => $pixels / $steps], ['before' => '{', 'after' => '}']);
			}
		}

		$precision = 2;
		if ((int)$roundedValue == $roundedValue) {
			$precision = 0;
		} elseif ((int)(2 * $roundedValue) == 2 * $roundedValue) {
			$precision = 1;
		}
		$defaults = [
			'title' => __d('ratings', '{0} of {1} stars', number_format($roundedValue, $precision, ',', '.'), $stars),
		];
		$attributes += $defaults;
		return $this->Html->div('ratingStars clearfix', $res, $attributes);
	}

	/**
	 * @param float $value (0...X)
	 * @param array $options
	 * - stars (defaults to 5)
	 * - steps per image (defaults to 4 => 1/4 accuracy)
	 * - ...
	 * @param array $attributes for div container (id, style, ...)
	 * @return string $divContainer with rating images
	 */
	public function image($value, array $options = [], array $attributes = []) {
		$defaults = [
			//'type' => 'bootstrap',
			'data-symbol' => '&#xf005;',
			'escape' => false,
			'data-rating-class' => 'rating-fa',
			'stars' => 5,
			'steps' => 4,
		];
		$options += $defaults;

		if ($value <= 0) {
			$roundedValue = 0;
		} else {
			$roundedValue = round($value * $options['steps']) / $options['steps'];
		}
		$percent = $this->percentage($roundedValue, $options['stars']);

		$precision = 2;
		if ((int)$roundedValue == $roundedValue) {
			$precision = 0;
		} elseif ((int)(2 * $roundedValue) == 2 * $roundedValue) {
			$precision = 1;
		}
		$title = __d('ratings', '{0} of {1} stars', number_format(min($roundedValue, $options['stars']), $precision, ',', '.'), $options['stars']);

		$attrContent = [
			'class' => 'rating-stars', 'data-content' => str_repeat($options['data-symbol'], $options['stars']), 'escape' => $options['escape'],
			'style' => 'width: ' . $percent . '%'
		];
		$content = $this->Html->div(null, '', $attrContent);

		//<div class="rating-container rating-fa" data-content="&#xf005;&#xf005;&#xf005;&#xf005;&#xf005;" title="x of y stars">
		//	<div class="rating-stars" data-content="&#xf005;&#xf005;&#xf005;&#xf005;&#xf005;" style="width: 20%;"></div>
		//</div>

		$attributes += ['title' => $title];
		$attributes = ['data-content' => str_repeat($options['data-symbol'], $options['stars']), 'escape' => $options['escape']] + $attributes;
		return $this->Html->div('rating-container ' . $options['data-rating-class'], $content, $attributes);

		//FIXME or remove
		$size = !empty($options['size']) ? $options['size'] : '';
		if (!empty($size)) {
			$options['pixels'] = $this->sizes[$size];
		}
		$pixels = !empty($options['pixels']) ? $options['pixels'] : 16;
		$steps = !empty($options['steps']) ? $options['steps'] : 4;
		if ($value <= 0) {
			$roundedValue = 0;
		} else {
			$roundedValue = round($value * $steps) / $steps;
		}
		$stars = !empty($options['stars']) ? $options['stars'] : 5;

		$array = [
			0 => '<div class="ui-stars-star' . ($size ? '-' . $size : '') . ' ui-stars-star' . ($size ? '-' . $size : '') . '-on" style="cursor: default; width: {width}px;"><a style="margin-left: {margin}px;">#</a></div>',
			1 => '<div class="ui-stars-star' . ($size ? '-' . $size : '') . ' ui-stars-star' . ($size ? '-' . $size : '') . '-disabled" style="cursor: default; width: {width}px;"><a style="margin-left: {margin}px;">#</a></div>',
		];

		$res = '';
		$disable = 0;
		for ($i = 0; $i < $stars; $i++) {
			for ($j = 0; $j < $steps; $j++) {
				if (!$disable && ($i + $j * (1 / $steps)) >= $roundedValue) {
					$disable = 1;
				}
				$v = $array[$disable];

				if ($j === 0) {
					# try to use a single image if possible
					if ($i < floor($roundedValue) || $i >= ceil($roundedValue) || $i === 0 && $roundedValue >= 1) {
						$res .= Text::insert($v, ['margin' => 0, 'width' => $pixels], ['before' => '{', 'after' => '}']);
						break;
					}
				}

				$margin = 0 - ($pixels / $steps) * $j;
				$res .= Text::insert($v, ['margin' => $margin, 'width' => $pixels / $steps], ['before' => '{', 'after' => '}']);
			}
		}

		$precision = 2;
		if ((int)$roundedValue == $roundedValue) {
			$precision = 0;
		} elseif ((int)(2 * $roundedValue) == 2 * $roundedValue) {
			$precision = 1;
		}
		$defaults = [
			'title' => number_format(min($roundedValue, $stars), $precision, ',', '.') . ' ' . __('von') . ' ' . $stars . ' ' . __('Sternen'),
		];
		$attributes += $defaults;
		return $this->Html->div('ratingStars clearfix', $res, $attributes);
	}

	/**
	 * @param array $options
	 * @param array $htmlAttributes
	 * @return string HTML
	 */
	public function input($field, array $options = [], array $htmlAttributes = []) {

		$htmlAttributes += $options;
		return $this->Form->input($field, $htmlAttributes);
	}

	/**
	 * Displays a bunch of rating links wrapped into a list element of your choice
	 *
	 * @param array $options
	 * @param array $htmlAttributes Attributes for the rating links inside the list
	 * @return string Markup that displays the rating options
	 */
	public function display(array $options = [], array $htmlAttributes = []) {
		$options += $this->defaults;
		if (empty($options['item'])) {
			throw new \Exception(__d('ratings', 'You must set the id of the item you want to rate.'), E_USER_NOTICE);
		}

		if ($options['type'] === 'bootstrap') {
			return $this->input($options, $htmlAttributes);
		}

		if ($options['type'] === 'radio' || $options['type'] === 'select') {
			return $this->starForm($options, $htmlAttributes);
		}

		$stars = null;
		for ($i = 1; $i <= $options['stars']; $i++) {
			$link = null;
			if ($options['link']) {
				$url = $options['url'];
				if (!isset($url['?'])) {
					$url['?'] = [];
				}
				$url['?'] = ['rate' => $options['item'], 'rating' => $i] + $url['?'];
				if ($options['redirect']) {
					$url['?']['redirect'] = 1;
				}

				$link = $this->Html->link($i, $url, $htmlAttributes);
			}
			$stars .= $this->Html->tag('div', $link, ['class' => 'ui-stars-star star' . $i]);
		}

		$id =  'star_' . $options['item'];

		$type = 'div';
		$stars = $this->Html->tag($type, '', ['id' => $id, 'data-rating' => round($options['value'], 0)]);
		$stars .= $this->Form->hidden('rate', ['value' => $options['item']]);

		$script = <<<HTML
<script>
;(function($) {
	$(function() {
		$('#$id').raty({
			starType: 'i',
			scoreName: 'rating',
			score: function() {
				return $(this).attr('data-rating');
			}
		});
	});
})(jQuery);
</script>
HTML;

		$this->_View->Blocks->concat('script', $script);

		return $stars;
	}

	/**
	 * Bar rating
	 *
	 * @param int $value
	 * @param int $total amount of rates
	 * @param array $options
	 * @return string
	 */
	public function bar($value, $total, array $options = []) {
		$defaultOptions = [
			'innerClass' => 'inner',
			'innerHtml' => '<span>%value%</span>',
			'innerOptions' => [],
			'outerClass' => 'barRating',
			'outerOptions' => [],
			'element' => null];
		$options += $defaultOptions;

		$percentage = $this->percentage($value, $total);

		if (!empty($options['element'])) {
			return $this->_View->element($options['element'], [
				'value' => $value,
				'percentage' => $percentage,
				'total' => $total]);
		}

		$options['innerOptions']['style'] = 'width: ' . $percentage . '%';
		$innerContent = str_replace('%value%', $value, $options['innerHtml']);
		$innerContent = str_replace('%percentage%', $percentage, $innerContent);
		$inner = $this->Html->div($options['innerClass'], $innerContent, $options['innerOptions']);

		return $this->Html->div($options['outerClass'], $inner, $options['outerOptions']);
	}

	/**
	 * Calculates the percentage value
	 *
	 * @param integer value
	 * @param integer total amount
	 * @param integer precision of rounding
	 * @return mixed float or integer based on the precision value
	 */
	public function percentage($value, $total, $precision = 2) {
		if ($total > 0) {
			return (round($value / $total, $precision) * 100);
		}
		return 0;
	}

	/**
	 * Displays a star form
	 *
	 * @param array $options
	 * @param array $htmlAttributes Attributes for the rating links inside the list
	 * @return string markup that displays the rating options
	 */
	public function starForm($options = [], $htmlAttributes = []) {
		$options += $this->defaults;
		$flush = false;
		if (empty($options['item'])) {
			trigger_error(__d('ratings', 'You must set the id of the item you want to rate.'), E_USER_NOTICE);
		}
		$id = $options['item'];

		$result = '';
		if ($options['createForm']) {
			$result .= $this->Form->create(null, $options['createForm']) . "\n";
		}
		$inputField = 'rating';
		if (!empty($options['inputField'])) {
			$inputField = $options['inputField'];
		}

		$inputOptions = [
			'type' => in_array($options['type'], ['radio', 'select']) ? $options['type'] : 'radio',
			'id' => 'starelement_' . $id,
			'value' => isset($options['value']) ? round($options['value']) : 0,
			'options' => array_combine(range(1, $options['stars']), range(1, $options['stars']))
		];
		if ($options['js']) {
			$inputOptions['type'] = 'select';
		}
		$inputOptions += $htmlAttributes;

		$result .= '<div id="star_' . $id . '" class="' . (!empty($options['class']) ? $options['class'] : 'rating') . '">';
		$result .= $this->Form->input($inputField, $inputOptions);
		$result .= '</div>';
		if ($options['createForm']) {
			if (!empty($options['target']) && !empty($options['createForm']['url']) && !empty($options['createForm']['ajaxOptions'])) {
				$result .= $this->Js->submit(__d('ratings', 'Rate!'), array_merge(['url' => $options['createForm']['url']], $options['createForm']['ajaxOptions'])) . "\n";
				$flush = true;
			} else {
				$result .= $this->Form->submit(__d('ratings', 'Rate!')) . "\n";
			}
			$result .= $this->Form->end() . "\n";

			$disabled = empty($editable) ? false : 'disabled';
			if ($disabled) {
				$split = 4;
			} else {
				$split = 1;
			}
			if ($options['js']) {
			/*
			$this->Js->buffer('
			$("#star_' . $id . '").stars({
				cancelValue: 0,
				inputType: "select",
				split:' . $split . ',
				cancelShow: true,
					'.(!$disabled ? 'captionEl: $("#cap_' . $id . '"),' : '').'
					callback: function(ui, type, value) {
						' . (isset($callback) ? $callback . '("star_' . $id . '");' : '') . '
					}
			}); eval();
	');
		*/
		$result .= '<script>$(document).ready(function () {
$("#star_' . $id . '").stars({
	cancelValue: 0,
	inputType: "' . $inputOptions['type'] . '",
	split:1,
	cancelShow: true,
	captionEl: $("#cap_' . $id . '"),
	callback: function(ui, type, value) {
	' . (isset($options['callback']) ? ($options['callback'] . PHP_EOL) : '') . '}
	});
});</script>';
			}

			if ($flush) {
				$this->Js->writeBuffer();
			}
		}
		return $result;
	}
}
