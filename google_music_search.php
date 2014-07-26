<?php
/**
 * Searches the Google Play Music store for tracks.
 *
 * @author    Cookie Guru
 * @copyright 2014
 * @license   MIT
 * @link      https://github.com/cookieguru/google-music-search
 * @version   0.1
 */
class Google_Music_API {
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
	public function set_user_agent($user_agent) {
		curl_setopt($this->ch, CURLOPT_USERAGENT, $user_agent);
	}

	/**
	 * Sets whether cURL verifies the authenticity of Google's certificate
	 *
	 * @param bool $bool Whether or not to verify the certificate
	 */
	public function verify_peer($bool) {
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $bool);
	}

	/**
	 * Sets the HTTP User Agent used by the cURL request(s)
	 *
	 * @param  string $query The string to query
	 * @return array An array of objects containing the results
	 */
	public function search($query) {
		curl_setopt($this->ch, CURLOPT_URL, self::BASE . '/store/search?c=music&docType=4&q=' . urlencode($query));

		$html = curl_exec($this->ch);
		if(strpos($html, 'We couldn\'t find anything for your search') !== FALSE) {
			return array();
		}

		$doc = new DOMDocument();
		$doc->formatOutput = false;
		@$doc->loadHTML($html);
		$finder = new DomXPath($doc);

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

				$temp = new Google_Music_Track();
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

/**
 * When iterating over results from the Google Play Store search page, there are
 * often duplicates.  This class exists solely to provide a value for the
 * array_unique() function as objects in PHP are not serialized by default
 *
 * @author    Cookie Guru
 * @copyright 2014
 * @license   MIT
 * @link      https://github.com/cookieguru/google-music-search
 * @version   0.1
 */
class Google_Music_Track {
    public function __toString() {
        return serialize($this);
    }
}