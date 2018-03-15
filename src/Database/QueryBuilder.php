<?php


namespace Postmix\Database;


use Exception\InvalidArgumentException;
use Postmix\Exception\Model\MissingSourceException;
use Postmix\Exception\QueryBuilder\UnknownStatementTypeException;
use Postmix\Structure\Mvc\Model;

class QueryBuilder {

	private $statement = self::STATEMENT_SELECT;

	private $source;

	private $columns = '*';

	private $conditions;

	private $limit;

	private $order;

	/**
	 * QueryBuilder constructor.
	 *
	 * @param array $parameters
	 */

	public function __construct($parameters = []) {

		if(isset($parameters['from']))
			$this->from($parameters['from']);

		if(isset($parameters['columns']))
			$this->columns($parameters['columns']);

		if(isset($parameters['order']))
			$this->order($parameters['order']);

		if(isset($parameters['conditions']) && is_array($parameters['conditions'])) {

			foreach($parameters['conditions'] as $condition) {

				$this->where($condition);
			}

		}

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

	public function from($source) {

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

	public function columns($columns) {

		$this->columns = $columns;

		return $this;
	}

	/**
	 * Where
	 *
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

	/**
	 * And Where
	 *
	 * @param $condition
	 *
	 * @return QueryBuilder
	 */

	public function andWhere($condition) {

		if(isset($this->conditions))
			$this->conditions = '(' . $this->conditions . ')';

		return $this->where($condition);

	}

	/**
	 * Or Where
	 *
	 * @param $condition
	 *
	 * @return QueryBuilder
	 */

	public function orWhere($condition) {

		if(isset($this->conditions))
			$this->conditions = '(' . $this->conditions . ') OR ';

		$this->conditions .= $condition;

		return $this;

	}

	public function limit($limit, $offset = 0) {

		$this->limit = $limit;
	}

	public function order($order) {

		$this->order = $order;
	}

	/**
	 * Get Query
	 *
	 * @return string
	 */

	public function getQuery() {

		if(!isset($this->source))
			throw new MissingSourceException('Data source for query is missing.');

		switch($this->statement) {

			/**
			 * Select statement
			 */

			case self::STATEMENT_SELECT:

				return 'SELECT ' . $this->columns .
				       ' FROM `' . $this->source . '`' .
				       ' WHERE ' . $this->conditions .
				       (isset($this->order) ? ' ORDER BY ' . $this->order : '') .
				       (isset($this->limit) ? ' LIMIT ' . $this->limit : '');

				break;

			/**
			 * Insert statement
			 */

			case self::STATEMENT_INSERT:

				return 'INSERT INTO `' . $this->source . '`';

				break;

			/**
			 * Update statement
			 */

			case self::STATEMENT_UPDATE:

				return 'UPDATE `' . $this->source . '`';

				break;

			/**
			 * Delete statement
			 */

			case self::STATEMENT_DELETE:

				return 'DELETE' .
				       ' FROM `' . $this->source . '`' .
				       ' WHERE ' . $this->conditions .
				       (isset($this->limit) ? ' LIMIT ' . $this->limit : '');

				break;

			default:

				throw new UnknownStatementTypeException('Unkown statement type, use pre-defined constants instead.');
		}

	}

	const STATEMENT_SELECT = 1;

	const STATEMENT_INSERT = 2;

	const STATEMENT_UPDATE = 3;

	const STATEMENT_DELETE = 4;
}