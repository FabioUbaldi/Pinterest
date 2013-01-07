<?php

namespace Drivers;

use Drivers\DriverInterface;

class Init {

	/**
	* Объект драйвера
	*/
	private $driver = null;

	public function __construct($driver) {
		$pathToDrivers = "Drivers/" . $driver;

		if (!is_dir($pathToDrivers)) {
			throw new \Exception('I cant find folder for driver: ' . $driver);
		}

		$this->driver = "\\" . str_replace("/", "\\", $pathToDrivers . "/Driver");
	}

	public function getHandlerDriver() {
		if (!class_exists($this->driver)) {
			throw new \Exception('Cannot find class: ' . $this->driver);
		}

		$driver = new $this->driver;

		if ( $driver instanceof DriverInterface ) {
			return $driver;
		} else {
			throw new \Exception( sprintf('The driver %s must have an interface %s', $this->driver, 'DriverInterface') );
		}
		
	}


}