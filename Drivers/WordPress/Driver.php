<?php

namespace Drivers\WordPress;

class Driver implements \Drivers\DriverInterface {
	public function __construct() {
		echo $this->getName();
	}


	public function createCategories() {

	}

	public function createPosts() {
		
	}


	public function getName() {
		return 'WordPress';
	}

}