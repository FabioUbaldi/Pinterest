<?php

namespace Finder;

use 
	Finder\Scamper
;

use 
	Common\Writer,
	Common\Reader
;


class Parser {

	/**
	* Папка с ресурсами
	* @var string
	*/
	protected $resource = '';

	/**
	* Конфиг
	*/
	protected $config;

	public function __construct() {
		$this->resource = 'Resource';

		if (!is_dir($this->resource)) {
			throw new \Exception('I can not find the folder with the resources');
		}

		$this->config = new Reader('config.xml');
	}

	public function handle() {

		$site 			= $this->config->get('parse_url');
		$parseCategory 	= $this->config->get('parse_category');

		// парсим категории
		$scamper = new Scamper($parseCategory['path']);
		$catNode = $scamper->byId('CategoriesDropdown');

		/**
		* Должен вернуть массив, типа:
		* array( 'name' => 'Humor', 'link' => '/all/?category=humor' )
		*/
		$cats = $scamper->loadCategories( $catNode, $parseCategory['exceptions']->name  );

		
		$writer = new Writer( $this->config->get('storage_category') );
		$writer
			->writeArray($cats, 'category')
			->create();
		
		// подгружаем картинки из категорий
		foreach( $cats as $cat ) {
			$url = preg_replace("#/$#", "", $site) . $cat['link'];
			$scamper->load($url);

			$catContent = $scamper->byId('ColumnContainer');
			$content 	= $scamper->getContent( $catContent );

			// если первый раз - создадим файлик
			if ( !file_exists("Resource/Categories/" . $cat['file_name'])) {
				file_put_contents(
					"Resource/Categories/" . $cat['file_name'], 
					'<?xml version="1.0" encoding="UTF-8"?><no_empty/>'
				);
			}

			// создаем массив для сравнения
			$compFile   = new Reader("Categories/" . $cat['file_name']);
			$comparison = $this->createComparison( $compFile->get('container'), 'small_image' );

			if ( !empty($comparison['to_write']) ) {
				while( $wr = array_pop($content) ) {
					if ( !in_array($wr['small_image'], $comparison['comparison']) ) {
						array_unshift($comparison['to_write'], $wr);
					}
				}
				$content = $this->clearCache($comparison['to_write']);
			} else {
				$content = array_slice($content, 0, (int)$this->config->get('max_cache'));
			}

			$wr = new Writer( 'Categories/' . $cat['file_name'] );
			
			$wr
				->writeArray($content, 'container')
				->create();
		}
	}

	/**
	* Очистка лишнего кеша
	* @return array
	*/
	private function clearCache($data) {
		$limit = (int)$this->config->get('max_cache');

		if ( (count($data) < $limit) || ($limit < 1) ) {
			return $data;
		}

		return array_slice($data, 0, $limit);
	}

	/**
	* Массив для будующего сравнения
	* @param $readyInfo - исходный массив
	* @param $field     - по какому полю
	* @return array
	*/
	private function createComparison($readyInfo = array(), $field) {

		if (!$field) {
			throw new \Exception('Field is not specified for comparison');
		}

		$comparison = array();

		foreach($readyInfo as $info) {
			$comparison[] = $info[$field];
		}

		return array(
			'to_write' 	 => $readyInfo,
			'comparison' => $comparison
		);
	}

}