<?php declare(strict_types = 1);

namespace Yau\Functions;

/**
 * Filter elements of an array using a callback function against the keys
 *
 * @param array $input    the array to iterate over
 * @param array $callback the callback function to use
 * @return array
 * @todo remove first ternary operation when we upgrade to PHP8 since callback can be null
 */
function array_filter_key(array $input, $callback = null)
{
	$filtered_keys = (empty($callback))
		? array_filter(array_keys($input))
		: array_filter(array_keys($input), $callback);
	return ($filtered_keys)
		? array_intersect_key($input, array_flip($filtered_keys))
		: [];
}
