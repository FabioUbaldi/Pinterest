<?php

namespace Finder;

class Scamper {

	/**
	* URL или файл документа
	*/
	private $url = '';

	/**
	* Объект DOM
	* @var \DOMDocument
	*/
	private $dom = null;

	public function __construct($url = '') {
		$this->dom = new \DOMDocument('1.0', 'UTF-8');

		$this->setUrl($url);
		$this->load($url);
	}

	public function load($url) {
		$this->dom->loadHTMLFile($url);
	}


	/**
	* Ищем картинки внутри категории
	*
	* @param $node \DOMElement
	*
	* @return array	
	*/
	public function getContent(\DOMElement $node) {

		$content 	= array();
		$smallImg	= array();		
		$divs 		= $node->getElementsByTagName('div');

		foreach($divs as $div) {
			$className = $div->getAttribute('class');

			if ( 'pin' !== $className ) {
				continue;
			}

			foreach( $div->getElementsByTagName('img') as $img ) {
				if ( 'PinImageImg' == $img->getAttribute('class') ) {
					$smallImg = array(
						'small_image' 	=> trim($img->getAttribute('src')),
						'title' 		=> trim($img->getAttribute('alt'))	
					);
				}
			}

			$content[] = array_merge(
				$smallImg,
				array('big_image' => trim($div->getAttribute('data-closeup-url')))
			);

		}

		return $content;
	}

	/**
	* Поиск категорий по сайту
	*
	* @param $node \DOMElement
	* @param $discarded - не нужные ссылки категорий
	*
	* @return array
	*/
	public function loadCategories(\DOMElement $node, $discarded) {
		
		$categories = array();
		$discarded  = (array)$discarded;

		// дети списка категорий
		$spans = $node->getElementsByTagName('span');

		foreach($spans as $span) {
			// там внутри ссылки, бегим по ним
			$childs = $span->getElementsByTagName('a');
			foreach($childs as $a) {

				$href 		= trim($a->getAttribute('href'));
				$catName 	= trim($a->nodeValue);

				if ( in_array($catName, $discarded) ) {
					continue;
				}

				$categories[] = array(
					'name' 		=> $catName,
					'link' 		=> $href,
					'file_name' => preg_replace("#[\W\s&]#", "", strtolower($catName))  . '.xml'
				);
			}
		}

		return $categories;
	}


	public function byId($id) {
		return $this->dom->getElementById($id); 
	}

	public function setUrl($url) {
		$this->url = $url;
	}

	public function getUrl() {
		return $this->url;
	}

}