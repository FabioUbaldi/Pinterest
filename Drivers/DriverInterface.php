<?php

namespace Drivers;

interface DriverInterface {

	public function getName();

	/**
	* Создание категорий
	*/
	public function createCategories();


	/**
	* Добавление материалов
	*/
	public function createPosts();
}