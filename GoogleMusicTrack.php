<?php

namespace cookieguru\googlemusicsearch;

/**
 * When iterating over results from the Google Play Store search page, there are
 * often duplicates.  This class exists solely to provide a value for the
 * array_unique() function as objects in PHP are not serialized by default
 *
 * @author    Cookie Guru
 * @copyright 2014
 * @license   MIT
 * @link      https://github.com/cookieguru/google-music-search
 * @version   1.0.4
 */
class GoogleMusicTrack {
	public $url = null;
	public $artist = null;
	public $title = null;
	public $price = null;

	public function __toString() {
		return serialize($this);
	}
}
