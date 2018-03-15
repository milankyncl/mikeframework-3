<?php


namespace Structure\Mvc\Model;

use Exception\InvalidArgumentException;
use Postmix\Exception\OutOfRangeException;
use Postmix\Structure\Mvc\Model;

/**
 * Class Group
 * @package Structure\Mvc\Model
 */

class Group implements \Iterator, \Countable {

	/**
	 * @var Model[]
	 */

	private $data;

	/**
	 * Group constructor.
	 *
	 * @param Model[] $data
	 *
	 * @throws InvalidArgumentException
	 */

	public function __construct(array $data) {

		foreach($data as $model)
			if(!$model instanceof Model)
				throw new InvalidArgumentException(self::class . ' instance can be created only by array of ' . Model::class);

		$this->data = $data;
	}

	public function count() {

		return count($this->data);
	}

	public function next() {

		next($this->data);
	}

	public function rewind() {

		reset($this->data);
	}

	public function current() {

		return current($this->data);
	}

	public function key() {

		return key($this->data);
	}

	public function valid() {

		$key = key($this->data);

		return (!is_null($key) && $key !== false);
	}

	public function delete($permanently = false) {

		foreach($this->data as $model)
			$model->delete($permanently);
	}

	public function get($key) {

		if(!isset($this->data[$key]))
			throw new OutOfRangeException('Missing key `' . $key . '` in Model-Group\'s data feed.');

		return $this->data[$key];
	}
}