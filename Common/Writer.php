<?php

namespace Common;

class Writer {

	/**
	* Ресурс куда будем писать
	* @var string
	*/
	private $resource = '';

	/**
	* Родительский узел у элемента
	* @var string
	*/
	private $parentName = '';

	/**
	* Объект SimpleXMLElement
	* @var \SimpleXMLElement
	*/
	private $xml = null;

	public function __construct($file) {

		$this->resource = 'Resource/' . $file;

		if ( is_file($this->resource) && !is_writable($this->resource)) {
			throw new \Exception( sprintf("I cannot write to <b>%s</b>", $file) );
		}
		
		if ( false === strpos($file, "/") )	{	
			$this->parentName = strtolower(substr($file, 0, -4));
		} else {
			list(, $this->parentName ) 	= explode("/", $file);
			$this->parentName 			= strtolower(substr($this->parentName, 0, -4));
		}

		$this->xml = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><" . $this->parentName . "/>");
	}

	/**
	* Пишим в файл
	* @return boolean
	*/
	public function create() {
		if (is_file($this->resource) && !is_writable($this->resource)) {
			throw new \Exception( sprintf("I cannot write to <b>%s</b>", $this->resource) );
		}

		return file_put_contents($this->resource, $this->xml->asXml());

	}

	/**
	* Создаем XML из массива
	* @return $this
	*/
	public function writeArray( array $toXml, $parent ) {

		foreach($toXml as $cat) {
			$parentNode = $this->xml->addChild($parent);
			
			foreach($cat as $k => $v) {
				$newNode 	= $parentNode->addChild($k);
				$newNode[0] = $v;
			}
		}

		return $this;
	}


}