<?php


namespace Postmix\Database;


use Exception\InvalidArgumentException;
use Postmix\Structure\Mvc\Model;

class QueryBuilder {

	private $source;

	private $columns = '*';

	private $conditions;

	/**
	 * QueryBuilder constructor.
	 *
	 * @param array $parameters
	 */

	public function __construct($parameters = []) {

	}

	/**
	 * From
	 * ----
	 *
	 * Set query source
	 *
	 * @param $source string
	 *
	 * @throws InvalidArgumentException
	 */

	public function from(string $source) {

		if(class_exists($source)) {

			$model = new $source();

			if(!$model instanceof Model)
				throw new InvalidArgumentException('Source class must be instance of ' . Model::class);

			/** @var Model $model */

			$this->source = $model->getSourceName();

		} else if(is_scalar($source)) {

			$this->source = $source;

		} else
			throw new InvalidArgumentException('Argument for method `from` must be scalar or class namespace.');

		/**
		 * Return self
		 */

		return $this;
	}

	/**
	 * Pick columns
	 * ------------
	 *
	 * Set columns for query
	 *
	 * @param string $columns
	 *
	 * @return self
	 */

	public function columns(string $columns) {

		$this->columns = $columns;

		return $this;
	}

	/**
	 * @param $condition
	 *
	 * @return self
	 */

	public function where($condition) {

		if(isset($this->condition))
			$this->conditions .= ' AND ';

		$this->conditions .= $condition;

		return $this;
	}

	public function andWhere($condition) {


	}

}