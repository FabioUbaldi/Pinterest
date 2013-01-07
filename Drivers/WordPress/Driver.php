<?php

namespace Drivers\WordPress;

use 
	Common\Writer,
	Common\Reader
;

class Driver implements \Drivers\DriverInterface {

	/**
	* Конфигурация драйвера
	*/
	public $config = array();

	/**
	* Список спарсеных категорий
	* @param array
	*/
	protected $categories = array();

	/**
	* Данные для поста
	* @param array
	*/
	protected $metaPosts  = array();

	public function __construct() {

		$reader = new Reader('drivers.xml');
		$this->config = $reader->get( $this->getName() );
		
		$confCat = new Reader('categories.xml');
		$this->categories = $confCat->get('category');

		$this->includeFiles();

		$this->metaPosts['meta'] = array();
	}

	/**
	* Создаем категории
	*/
	public function createCategories() {

		$categoryList = get_categories(array('hide_empty' => 0));
		$catSlugs     = $this->getSlugs($categoryList);


		foreach($this->categories as $cat) {

			$slug = sanitize_title($cat['name']);

			// если нет категории - добавим
			if ( !in_array($slug, $catSlugs) ) {
				wp_create_category($cat['name'], 0);
			}

			$this->metaPosts['tmp_cat'][$cat['name']] = array(
				'file_name' => $cat['file_name'],
				'slug' => $slug
			);

		}


		foreach($this->metaPosts['tmp_cat'] as $k => $v) {
			$catInfo = get_category_by_slug($v['slug']);

			$this->metaPosts['meta'][$v['file_name']] = array(
				'category_name' => $k,
				'category_id'   => $catInfo->term_id
			);
		}

	}


	public function createPosts() {

	}

	private function getSlugs($obj) {
		$slugs = array();

		foreach($obj as $v) {
			$slugs[] = $v->slug;
		}

		return $slugs;
	}


	/**
	* Подлючаем нужные файлы
	*/
	private function includeFiles() {
		foreach( $this->config['includes'] as $file ) {
			if (file_exists($file)) {
				require_once $file;
			}
		}
	}


	public function getName() {
		return 'WordPress';
	}

}