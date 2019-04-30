<?php

namespace cookieguru\googlemusicsearch;

use DOMElement;

/**
 * Searches the Google Play Music store for tracks.
 *
 * @author    Cookie Guru
 * @copyright 2014
 * @license   MIT
 * @link      https://github.com/cookieguru/google-music-search
 * @version   1.0.4
 */
class API {
	private $ch;

	public function __construct() {
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
	}

	/**
	 * Sets the HTTP User Agent used by the cURL request(s)
	 *
	 * @param string $user_agent The User Agent to send
	 */
	public function setUserAgent($user_agent) {
		curl_setopt($this->ch, CURLOPT_USERAGENT, $user_agent);
	}

	/**
	 * Sets whether cURL verifies the authenticity of Google's certificate
	 *
	 * @param bool $bool Whether or not to verify the certificate
	 */
	public function verifyPeer($bool) {
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $bool);
	}

	/**
	 * Performs a search in the Google Play store (screen scraping)
	 *
	 * @param string $query The string to query
	 * @return GoogleMusicTrack[]
	 */
	public function search($query) {
		curl_setopt($this->ch, CURLOPT_URL, 'https://play.google.com/store/search?c=music&q=' . urlencode($query));

		$html = curl_exec($this->ch);
		if(strpos($html, 'We couldn\'t find anything for your search') !== false) {
			return array();
		}

		if(preg_match('/aria-label="Check out more content from Songs".+href="(.+?)"/', $html, $matches)) {
			if($_html = $this->getAllSongs($matches[1])) {
				$html = $_html;
			}
		}

		return $this->parseElements($html);
	}

	/**
	 * Make a second request for just the full list songs
	 * @param string $href
	 * @return string|bool
	 */
	private function getAllSongs($href) {
		curl_setopt($this->ch, CURLOPT_REFERER, curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL));
		curl_setopt($this->ch, CURLOPT_URL, $href);
		$html = curl_exec($this->ch);
		if(strlen($html) < 100 || strpos($html, 'the requested URL was not found on this server') !== false) {
			return false;
		}

		return $html;
	}

	/**
	 * @param string $html
	 * @return GoogleMusicTrack[]
	 */
	private function parseElements($html) {
		$doc = new \DOMDocument();
		$doc->formatOutput = false;
		@$doc->loadHTML($html);
		$finder = new \DomXPath($doc);
		$return = array();

		foreach($finder->query('//button[@data-item-id]') as $button) {
			/** @var DOMElement $button */
			$container = $button;
			$i = 0;
			while($container->tagName != 'c-wiz' && $i < 20) {
				$container = $container->parentNode;
				$i++;
			}
			$links = $container->getElementsByTagName('a');
			$artist_href = $links[$links->length - 1];
			/** @var DOMElement $artist_href */
			$title_href = $links[$links->length - 2];
			/** @var DOMElement $title_href */

			$temp = new GoogleMusicTrack();
			$temp->artist = $artist_href->nodeValue;
			$temp->title = $title_href->nodeValue;
			$temp->url = rtrim($title_href->baseURI, '/') . $title_href->getAttribute('href');
			$temp->price = $button->textContent;

			$return[] = $temp;
		}

		return array_values(array_unique($return));
	}
}
