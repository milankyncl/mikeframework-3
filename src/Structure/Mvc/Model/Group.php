<?php


namespace Structure\Mvc\Model;

use Exception\InvalidArgumentException;
use Postmix\Application;
use Postmix\Database\Adapter\PDO;
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

	private $columns;

	private $source;

	/**
	 * Group constructor.
	 *
	 * @param Model[] $data
	 *
	 * @throws InvalidArgumentException
	 */

	public function __construct(array $data) {

		foreach($data as $model) {

			if(!$model instanceof Model)
				throw new InvalidArgumentException(self::class . ' instance can be created only by array of ' . Model::class);

			if(!isset($this->columns)) {

				$injector = Application::getStaticInjector();

				/** @var PDO $connection */

				$connection = $injector->get('database');

				$this->source = $model->getSourceName();

				$this->columns = $connection->getTableColumns($this->source);
			}
		}

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

	/**
	 * Delete
	 * ------
	 *
	 * Delete whole model group.
	 *
	 * @param bool $permanently
	 *
	 * @throws \Postmix\Exception\Database\MissingColumnException
	 * @throws \Postmix\Exception\Database\MissingPrimaryKeyException
	 */

	public function delete($permanently = false) {

		foreach($this->data as $model)
			$model->delete($permanently);
	}

	/**
	 * Get
	 * ---
	 *
	 * Get data item by key.
	 *
	 * @param $key
	 *
	 * @return mixed|Model
	 * @throws OutOfRangeException
	 */

	public function get($key) {

		if(!isset($this->data[$key]))
			throw new OutOfRangeException('Missing key `' . $key . '` in Model Group data feed.');

		return $this->data[$key];
	}

	/**
	 * Sort
	 * ----
	 *
	 * Sort group's model data by column.
	 *
	 * @param $by
	 *
	 * @return $this
	 * @throws InvalidArgumentException
	 */

	public function sort($by) {

		if(!isset($this->columns[$by]))
			throw new InvalidArgumentException('Column `' . $by . '` doesn\'t exist.');

		usort($this->data, function($a, $b) use($by) {

			if(is_string($a) && is_string($b)) {

				return strcmp($a->{$by}, $b->{$by});

			} else {

				return $a->{$by} > $b->{$by};
			}
		});

		return $this;
	}

	/**
	 * Filter
	 * -----
	 *
	 * Filter group's model data field by passed conditions.
	 *
	 * @param array $filterData
	 *
	 * @return $this
	 */

	public function filter(array $filterData) {

		foreach($this->data as $key => $model) {

			foreach($filterData as $filterColumn => $filterValue) {

				if($model->{$filterColumn} != $filterValue) {

					unset($this->data[$key]);
					break;
				}
			}
		}

		return $this;
	}
}