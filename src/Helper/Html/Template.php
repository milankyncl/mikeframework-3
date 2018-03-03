<?php


namespace UI;


use Postmix\Helper\Html\Tag;

class Template {

	private $metas = [];

	private $title = '';

	public function __construct() {

	}

	public function addMetaTag($name, $content, $attributeName = 'name') {

		$this->metas[] = [
			'attribute' => $attributeName,
			'content' => $content,
			'name' => $name
		];
	}

	public function head() {

		$head = '';

		foreach($this->metas as $meta)
			$head .= "\t" . Tag::meta([ $meta['attribute'] => $meta['name'], 'content' => $meta['content'] ]);

		echo $head;
	}

}