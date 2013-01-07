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
		
		$this->config['img']['real_path'] = $this->getRealPath(
			(string)$this->config['copy_image']->real_path
		);

		$this->config['img']['web_path']  = $this->getRealPath(
			(string)$this->config['copy_image']->web_path
		);

		if ( !is_writable($this->config['img']['real_path']) ) {
			throw new \Exception( sprintf("Cannot write to directory: %s", $this->config['img']['real_path']));			
		}

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
				'slug' 		=> $slug
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

		global $wpdb; 

		// удаляем фильтры на время, чтобы записать в базу теги
		remove_filter('content_save_pre', 'wp_filter_post_kses');
		remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');

		foreach( $this->metaPosts['meta'] as $k => $v) {

			$r 			= new Reader('Categories/' . $k);
			$postsInfo 	= $r->get('container');

			foreach( $postsInfo as $post ) {

				// поиск похожих
				$sql = sprintf(
					"SELECT id FROM %s WHERE `post_name` = '%s'",
					$this->_wp_table_prefix . "posts", sanitize_title($post['title'])
				);
				$data = $wpdb->get_results($sql);

				if ( !empty($data) ) {
					continue;
				}

				$bigImageName = substr( $post['big_image'], strripos($post['big_image'], "/") + 1 );
				$catRealPath  = $this->config['img']['real_path'] . $v['category_name'] . "/";

				if ( !is_dir($catRealPath) ) {
					mkdir($catRealPath, 0777);
				}

				copy(
					$post['big_image'], 
					$catRealPath . $bigImageName
				);

				$postContent = sprintf(
					'<img src="%s" alt="%s" />',
					$this->config['img']['web_path'] . $v['category_name'] . '/' . $bigImageName, addslashes($post['title'])
				);

				$this->insertPost($post['title'], $postContent, $v['category_id']);
				
			}
		}

		// возвращаем фильтры
		add_filter('content_save_pre', 'wp_filter_post_kses');
		add_filter('content_filtered_save_pre', 'wp_filter_post_kses');	

	}

	private function insertPost($title, $postContent, $catId) {
		$my_post = array(
		  'post_title'    => $title,
		  'post_content'  => $postContent,
		  'post_status'   => 'publish',
		  'post_author'   => 1,
		  'post_category' => array($catId)
		);	

		wp_insert_post($my_post);
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

		$this->_wp_table_prefix = $table_prefix;
	}

	private function getRealPath($path) {
		return ( substr($path, -1, 1) == '/' ) 
				? $path
				: $path . "/";
	}

	public function getName() {
		return 'WordPress';
	}

}