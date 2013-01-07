<?php

namespace Common;

class Reader {

	/**
	* Файл ресурса
	*/
	private $resource;

	/**
	* Загруженные данные с ресурса
	* @var \SimpleXMLElement
	*/
	private $fileData = null;

	public function __construct($file) {

		$this->resource = 'Resource/' . $file;

		if (!is_file($this->resource)) {
			throw new \Exception( sprintf("I can not find the <b>%s</b>", $file) );
		}

		$this->readFile();
	}

	public function get($key) {
		$node = $this->fileData->$key;

		if ( count($node) > 1 ) {
			$nodes = array();

			foreach( $this->fileData->$key as $v ) {
				$nodes[] = (array) $v;
			}

			return $nodes;
		}

		if ( 0 === count($node->children()) ) {
			return $node;
		}

		return (array)$node;

	}

	private function readFile() {
		if ('xml' !== substr($this->resource, -3)) {
			throw new \Exception( sprintf("File must be XML! <b>%s</b>", $this->resource) );
		}

		$this->fileData = new \SimpleXMLElement( $this->resource, NULL, TRUE );
	}

}