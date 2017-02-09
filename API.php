<?php
namespace cookieguru\googlemusicsearch;

/**
 * Searches the Google Play Music store for tracks.
 *
 * @author    Cookie Guru
 * @copyright 2014
 * @license   MIT
 * @link      https://github.com/cookieguru/google-music-search
 * @version   1.0.2
 */
class API {
	const BASE = 'https://play.google.com';
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
	 * @param  string $query The string to query
	 * @return \cookieguru\googlemusicsearch\GoogleMusicTrack[]
	 */
	public function search($query) {
		curl_setopt($this->ch, CURLOPT_URL, self::BASE . '/store/search?c=music&q=' . urlencode($query));

		$html = curl_exec($this->ch);
		if(strpos($html, 'We couldn\'t find anything for your search') !== FALSE) {
			return array();
		}

		if(preg_match('/href="(\/store\/music\/collection\/5:search_cluster:4.*?)"/', $html, $matches)) {
			if($_html = $this->getAllSongs(html_entity_decode($matches[1]))) {
				$html = $_html;
			}
		}

		return $this->parseElements($html);
	}

	/**
	 * Make a second request for just the full list songs
	 * @param $href
	 * @return string|bool
	 */
	private function getAllSongs($href) {
		curl_setopt($this->ch, CURLOPT_REFERER, curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL));
		curl_setopt($this->ch, CURLOPT_POST, true);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $fields = 'xhr=1');
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($fields)));
		curl_setopt($this->ch, CURLOPT_URL, self::BASE . $href);

		$html = curl_exec($this->ch);
		if(strlen($html) < 100 || strpos($html, 'the requested URL was not found on this server') !== false) {
			return false;
		}
		return $html;
	}

	private function parseElements($html) {
		$doc = new \DOMDocument();
		$doc->formatOutput = false;
		@$doc->loadHTML($html);
		$finder = new \DomXPath($doc);

		$links = array();
		foreach($finder->query("//*[contains(@class,'card-list')]")->item(0)->getElementsByTagName('div') as $div) {
			$xml = simplexml_load_string($doc->saveXML($div));
			$title = $xml->xpath("//*[contains(@class,'title')]");
			if(isset($title[0]) && isset($title[0]->attributes()->href)) {
				$artist = $xml->xpath("//*[contains(@class,'subtitle-container')]");

				$price = $xml->xpath("//*[contains(@class,'price-container')]");
				if(isset($price[0]->span[2])) {
					$price = (string)$price[0]->span[2];
				} else {
					$price = $price[0]->xpath("//button[contains(@class,'price')][contains(@class,'buy')]");
					$price = (string)$price[0]->span;
				}

				$temp = new \cookieguru\googlemusicsearch\GoogleMusicTrack();
				$temp->url    = self::BASE . $title[0]->attributes()->href;
				$temp->artist = (string)$artist[0]->a;
				$temp->title  = trim($title[0]);
				$temp->price  = $price;

				$links[] = $temp;
			}
		}

		return array_values(array_unique($links));
	}
}
