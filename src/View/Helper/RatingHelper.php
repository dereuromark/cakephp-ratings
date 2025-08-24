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
use Exception;

/**
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \Cake\View\Helper\FormHelper $Form
 * @property \Cake\View\Helper\NumberHelper $Number
 * @property \Cake\Http\ServerRequest $request
 */
class RatingHelper extends Helper {

	/**
	 * helpers variable
	 *
	 * @var array<mixed>
	 */
	protected array $helpers = ['Html', 'Form', 'Number'];

	/**
	 * Default settings
	 *
	 * @var array<string, mixed>
	 */
	public $defaults = [
		'stars' => 5,
		'item' => null,
		'value' => 0,
		'type' => 'select', // Using a dropdown as fallback solution, you can also chose radio
		'createForm' => false,
		'url' => [],
		'link' => true,
		'redirect' => true, // PRG pattern
		'class' => 'rating',
		'js' => false, // Auto-include JS snippet (You still need to provide the JS and CSS library)
		'precision' => 1, // Rounding (decimal places)
	];

	/**
	 * Allowed types of HTML list elements
	 *
	 * @var array<string>
	 */
	protected array $allowedTypes = ['ul', 'ol', 'radio'];

	/**
	 * Sizes for rating image
	 *
	 * @var array<string, int>
	 */
	protected array $sizes = ['large' => 28, 'medium' => 16, 'small' => 12];

	/**
	 * Displays a bunch of rating links wrapped into a list element of your choice
	 *
	 * @param float $value
	 * @param array<string, mixed> $options
	 * @param array<string, mixed> $htmlAttributes Attributes for the rating links inside the list
	 * @return string Markup that displays the rating options as ul/li list
	 */
	public function display($value, array $options = [], array $htmlAttributes = []) {
		return $this->ratingImage($value, $options, $htmlAttributes);
	}

	/**
	 * Generats rating image.
	 *
	 * Options:
	 * - type: defaults to fa (font-awesome), also possible: ui (jquery-ui)
	 * - stars (defaults to 5)
	 * - steps per image (defaults to 2 => 1/2 accuracy)
	 * - ...
	 *
	 * @param float $value Value (0...X)
	 * @param array<string, mixed> $options
	 * @param array<string, mixed> $attributes for div container (id, style, ...)
	 * @return string Container with rating images
	 */
	public function ratingImage($value, array $options = [], array $attributes = []) {
		$options += ['type' => 'fa'];
		$matching = [
			'fa' => 'FontAwesome',
			'ui' => 'JqueryUi',
		];
		$method = '_ratingImage' . $matching[$options['type']];

		return $this->$method($value, $options, $attributes);
	}

	/**
	 * Options:
	 * - stars (defaults to 5)
	 * - steps per image (defaults to 2 => 1/2 accuracy)
	 * - ...
	 *
	 * @param float $value Value (0...X)
	 * @param array<string, mixed> $options
	 * @param array<string, mixed> $attributes for div container (id, style, ...)
	 * @return string Container with rating images
	 */
	protected function _ratingImageFontAwesome($value, array $options = [], array $attributes = []) {
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

			$res .= $array[$k];
		}

		$defaults = [
			'title' => __d('ratings', '{0} of {1} stars', $this->Number->format($roundedValue, $this->_config), $options['stars']),
		];
		$attributes += $defaults;

		return $this->Html->div('ratingStars clearfix', $res, $attributes);
	}

	/**
	 * Options:
	 * - stars (defaults to 5)
	 * - steps per image (defaults to 4 => 1/4 accuracy)
	 * - ...
	 *
	 * @param float $value Value (0...X)
	 * @param array<string, mixed> $options
	 * @param array<string, mixed> $attributes for div container (id, style, ...)
	 * @return string Container with rating images
	 */
	protected function _ratingImageJqueryUi($value, array $options = [], array $attributes = []) {
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

		$defaults = [
			'title' => __d('ratings', '{0} of {1} stars', $this->Number->format($roundedValue, $this->_config), $stars),
		];
		$attributes += $defaults;

		return $this->Html->div('ratingStars clearfix', $res, $attributes);
	}

	/**
	 * Options:
	 * - stars (defaults to 5)
	 * - steps per image (defaults to 4 => 1/4 accuracy)
	 * - ...
	 *
	 * @deprecated //FIXME or make ratingImage() to image()
	 *
	 * @param float $value Value (0...X)
	 * @param array<string, mixed> $options
	 * @param array<string, mixed> $attributes for div container (id, style, ...)
	 * @return string Container with rating images
	 */
	public function image($value, array $options = [], array $attributes = []) {
		$defaults = [
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

		$title = __d('ratings', '{0} of {1} stars', $this->Number->format(min($roundedValue, $options['stars']), $this->_config), $options['stars']);

		$attrContent = [
			'class' => 'rating-stars',
			'data-content' => str_repeat($options['data-symbol'], $options['stars']),
			'escape' => $options['escape'],
			'style' => 'width: ' . $percent . '%',
		];
		$content = $this->Html->div(null, '', $attrContent);

		//<div class="rating-container rating-fa" data-content="&#xf005;&#xf005;&#xf005;&#xf005;&#xf005;" title="x of y stars">
		//	<div class="rating-stars" data-content="&#xf005;&#xf005;&#xf005;&#xf005;&#xf005;" style="width: 20%;"></div>
		//</div>

		$attributes += ['title' => $title];
		$attributes = ['data-content' => str_repeat($options['data-symbol'], $options['stars']), 'escape' => $options['escape']] + $attributes;

		return $this->Html->div('rating-container ' . $options['data-rating-class'], $content, $attributes);

		/*
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
		*/
	}

	/**
	 * @param array<string, mixed> $options
	 * @param array<string, mixed> $htmlAttributes
	 * @return string HTML
	 */
	public function control(array $options, array $htmlAttributes = []) {
		$options += $this->defaults;

		return $this->starForm($options, $htmlAttributes);
	}

	/**
	 * Displays a star form
	 *
	 * @param array<string, mixed> $options
	 * @param array<string, mixed> $htmlAttributes Attributes for the rating links inside the list
	 * @throws \Exception
	 * @return string markup that displays the rating options
	 */
	public function starForm($options, $htmlAttributes = []) {
		$options += $this->defaults;
		if (empty($options['item'])) {
			throw new Exception('You must set the id of the item you want to rate.');
		}
		$id = $options['item'];

		if (empty($options['url'])) {
			$options['url']['?']['redirect'] = true;
			$passedParams = $this->_View->getRequest()->getParam('pass');
			foreach ($passedParams as $passedParam) {
				$options['url'][] = $passedParam;
			}
		}

		// Shim url into a createForm type
		if (!$options['createForm'] && $options['url']) {
			$options['createForm'] = [
				'url' => $options['url'],
			];
			unset($options['url']);
		}

		$result = '';
		if ($options['createForm']) {
			$result .= $this->Form->create(null, $options['createForm']) . "\n";
			$result .= $this->Form->hidden('rate', ['value' => $id]);
		}
		$inputField = 'rating';
		if (!empty($options['inputField'])) {
			$inputField = $options['inputField'];
		}

		$inputOptions = [
			'type' => in_array($options['type'], ['radio', 'select']) ? $options['type'] : 'radio',
			'id' => 'starelement_' . $id,
			'value' => isset($options['value']) ? round($options['value']) : 0,
			'options' => array_combine(range(1, $options['stars']), range(1, $options['stars'])),
			'empty' => true,
		];
		if ($options['js']) {
			$inputOptions['type'] = 'select';
		}
		$inputOptions += $htmlAttributes;

		$result .= '<div id="star_' . $id . '" class="' . (!empty($options['class']) ? $options['class'] : 'rating') . '">';
		$result .= $this->Form->control($inputField, $inputOptions);
		$result .= '</div>';
		if ($options['createForm']) {
			if (!empty($options['target']) && !empty($options['createForm']['url']) && !empty($options['createForm']['ajaxOptions'])) {
				//$result .= $this->Js->submit(__d('ratings', 'Rate!'), array_merge(['url' => $options['createForm']['url']], $options['createForm']['ajaxOptions'])) . "\n";
			} else {
				$result .= $this->Form->button(__d('ratings', 'Rate!')) . "\n";
			}
			$result .= $this->Form->end() . "\n";

			$disabled = empty($options['editable']) ? false : 'disabled';
			if ($disabled) {
				$split = 4;
			} else {
				$split = 1;
			}

			if ($options['js']) {
				$script = <<<HTML
<script>
;(function($) {
	$(function() {
		$('#star_$id').rating({
			filledStar: '<i class="fa fa-star"></i>',
			emptyStar: '<i class="fa fa-star-o"></i>',
			showClear: false,
			showCaption: false,
			size:'xs',
			step: 1
		});
		$('#star_$id').on('rating:change', function(event, value, caption) {
			$('#starelement_$id').val(value);
		}).hide();
	});
})(jQuery);
</script>
HTML;
				$this->_View->append('script', $script);
			}
		}

		$result = '<div class="star-rating">' . $result . '</div>';

		return $result;
	}

	/**
	 * Bar rating display.
	 *
	 * @param int $value
	 * @param int $total amount of rates
	 * @param array<string, mixed> $options
	 * @return string
	 */
	public function bar($value, $total, array $options = []) {
		$defaultOptions = [
			'innerClass' => 'inner',
			'innerHtml' => '<span>%value%</span>',
			'innerOptions' => [],
			'outerClass' => 'bar-rating',
			'outerOptions' => [],
			'element' => null,
		];
		$options += $defaultOptions;

		$percentage = $this->percentage($value, $total);

		if (!empty($options['element'])) {
			return $this->_View->element($options['element'], [
				'value' => $value,
				'percentage' => $percentage,
				'total' => $total,
			]);
		}

		$options['innerOptions']['style'] = 'width: ' . $percentage . '%';
		$innerContent = (string)str_replace('%value%', $this->Number->format($value, $this->_config), $options['innerHtml']);
		$innerContent = (string)str_replace('%percentage%', (string)$percentage, $innerContent);
		$inner = $this->Html->div($options['innerClass'], $innerContent, $options['innerOptions']);

		return $this->Html->div($options['outerClass'], $inner, $options['outerOptions']);
	}

	/**
	 * Calculates the percentage value
	 *
	 * @param float|int $value 0...1
	 * @param int $total amount
	 * @param int $precision Precision of rounding
	 * @return int Based on the precision value
	 */
	public function percentage($value, int $total, int $precision = 2): int {
		if ($total > 0) {
			return (int)(round($value / $total, $precision) * 100);
		}

		return 0;
	}

	/**
	 * Rounds the value according to the steps used (between min/max inclusivly)
	 *
	 * @param float|int $value
	 * @param int $steps
	 * @param int $min
	 * @param int $max
	 * @return float|int Value
	 */
	public function round($value, int $steps = 4, int $min = 0, int $max = 5) {
		if ($value <= $min) {
			return $min;
		}
		$v = round($value * $steps) / $steps;

		return min($v, $max);
	}

}
