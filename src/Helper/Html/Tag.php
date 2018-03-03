<?php

namespace Postmix\Helper\Html;

/**
 * Class Tag
 * @package Postmix\Helper\Html
 */

class Tag {

	/**
	 * Generate meta tag
	 *
	 * @param $attributes
	 *
	 * @return string
	 */

	public static function meta($attributes) {

		return self::generate('meta', $attributes);
	}

	/**
	 * Generate custom tag
	 *
	 * @param $name
	 * @param array $attributes
	 * @param bool $closing
	 *
	 * @return string
	 */

	private static function generate($name, $attributes = [], $closing = false) {

		$tag = '<' . ($closing ? '/' : '') . $name;

		if(!$closing) {

			foreach($attributes as $attribute => $value)
				$tag .= ' ' . $attribute . '="' . $value . '"';

		}

		$tag .= '>';

		return $tag;
	}

}