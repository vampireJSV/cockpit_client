<?php

namespace Creativados\Cockpit_client;


use GuzzleHttp\Client;

class api {

	private $server = null;
	private $base = '';
	private $token = '';
	const API_URL_PATH = 'rest/api/';
	const BASE_URL_KEY = 'base_url';

	const COLLECTION_PATH = 'collections/get/';
	const GALLERY_PATH = 'galleries/get/';
	const REGIONS_PATH = 'regions/get/';
	const MEDIA_PATH = 'mediamanager/thumbnails';
	const COCKPIT_PATH = 'cockpit/call';

	const SORT_DIRECTION_ASC = 1;
	const SORT_DIRECTION_DESC = - 1;

	const FILTER_FUNCTION = '$func';
	const FILTER_NUMERIC_MODULE = '$mod';
	const FILTER_SIZE = '$size';
	const FILTER_REXPRESION = '$regex';
	const FILTER_ALL_EQUAL = '$all';
	const FILTER_HAS = '$has';
	const FILTER_IN = '$in';
	const FILTER_LESSER = '$lte';
	const FILTER_GREATTER = '$gte';
	const FILTER_NOT = '$not';
	const FILTER_EQUAL = '$eq';

	const FILTER_FUNCTION_MERGE_AND = '$and';
	const FILTER_FUNCTION_MERGE_OR = '$or';

	const MEDIA_OPTION_NAME_REBUILD = 'rebuild';
	const MEDIA_OPTION_NAME_CACHE = 'cachefolder';
	const MEDIA_OPTION_NAME_QUALITY = 'quality';
	const MEDIA_OPTION_NAME_BASE64 = 'base64';
	const MEDIA_OPTION_NAME_DOMAIN = 'domain';
	const MEDIA_OPTION_NAME_MODE = 'mode';
	const MEDIA_OPTION_NAME_MODE_OPTION_CROP = 'crop';
	const MEDIA_OPTION_NAME_MODE_OPTION_BEST_FIT = 'best_fit';
	const MEDIA_OPTION_NAME_MODE_OPTION_RESIZE = 'resize';

	/**
	 * Create a new API instance
	 */
	public function __construct( $base, $token ) {

		if ( substr( $base, - 1 ) != '/' ) {
			$base = $base . '/';
		}
		$this->base  = $base;
		$this->token = $token;
		$this->connect();

	}

	/**
	 *
	 * Execute module function
	 *
	 * @param $module
	 * @param $method
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function getCockpit( $module, $method, $args = [] ) {
		$response = $this->server->post( self::COCKPIT_PATH, [
			'query' => [ 'token' => $this->token ],
			'json'  => [
				'module' => $module,
				'method' => $method,
				'args'   => $args
			]
		] );

		return $response->getBody()->getContents();
	}


	/**
	 * Get region name
	 *
	 * @param $name
	 *
	 * @return mixed
	 */
	public function getRegions( $name ) {
		$response = $this->server->post( self::REGIONS_PATH . $name, [
			'query' => [ 'token' => $this->token ]
		] );

		return $response->getBody()->getContents();
	}

	/**
	 * get gallery name
	 *
	 * @param $name
	 * @param bool $autoDomain
	 *
	 * @return mixed
	 */
	public function getGallery( $name, $autoDomain = false ) {
		$response = $this->server->post( self::GALLERY_PATH . $name, [
			'query' => [ 'token' => $this->token ]
		] );
		$output   = json_decode( $response->getBody()->getContents() );
		if ( $autoDomain ) {
			foreach ( $output as $image ) {
				$image->path = str_replace( 'site:', $this->base, $image->path );
			}
		}

		return $output;
	}

	/**
	 * Get images thumbnails
	 *
	 * @param array $images
	 * @param int $width
	 * @param bool $height
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function getThumbnails(
		$images = [],
		$width = 50,
		$height = false,
		$options = []
	) {
		$response = $this->server->post( self::MEDIA_PATH, [
			'query' => [ 'token' => $this->token ],
			'json'  => [
				'images'  => $images,
				'w'       => $width,
				'h'       => $height,
				'options' => $options
			]
		] );

		$output = json_decode( $response->getBody()->getContents() );

		if ( count( $images ) == 1 ) {
			$output = $output->{$images[0]};
		}

		return $output;
	}

	/**
	 * Get list elements Collection $name
	 *
	 * @param string $name
	 * @param array $filters
	 * @param array $sort
	 * @param int $limit
	 * @param int $skip
	 *
	 * @return mixed
	 */
	public
	function getCollection(
		$name,
		$filters = null,
		$sort = null,
		$limit = null,
		$skip = null
	) {
		$response = $this->server->post( self::COLLECTION_PATH . $name, [
			'query' => [ 'token' => $this->token ],
			'json'  => [
				'filter' => $filters,
				'limit'  => $limit,
				'skip'   => $skip,
				'sort'   => $sort
			]
		] );

		return json_decode( $response->getBody()->getContents() );
	}

	/**
	 * Reconnect to server
	 */
	public
	function reconnect() {
		$this->connect();
	}

	/**
	 * @return string
	 */
	public
	function getBase() {
		return $this->base;
	}

	/**
	 * @param string $base
	 *
	 * @return api
	 */
	public
	function setBase(
		$base
	) {
		$this->base = $base;

		return $this;
	}

	/**
	 * @return string
	 */
	public
	function getToken() {
		return $this->token;
	}

	/**
	 * @param string $token
	 *
	 * @return api
	 */
	public
	function setToken(
		$token
	) {
		$this->token = $token;

		return $this;
	}

	/**
	 * @param $base
	 * @param $token
	 */
	private
	function connect() {
		$this->server = new Client( [ self::BASE_URL_KEY => $this->base . self::API_URL_PATH ] );
	}
}
