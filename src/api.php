<?php

namespace Creativados\Cockpit_client;


use GuzzleHttp\Client;

class api
{

    private $server = null;
    private $base = '';
    private $token = '';
    private $locale = '';
    const API_URL_PATH = 'api/';
    const BASE_URL_KEY = 'base_url';

    const COLLECTION_PATH = 'collections/get/';
    const GALLERY_PATH = 'cockpit/image';
    const REGIONS_PATH = 'regions/get/';
    const MEDIA_PATH = 'cockpit/image';
    const COCKPIT_PATH = 'cockpit/call';

    const SORT_DIRECTION_ASC = 1;
    const SORT_DIRECTION_DESC = -1;

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

    const MEDIA_OPTION_NAME_MODE = 'm';
    const MEDIA_OPTION_NAME_QUEALITY = 'q';
    const MEDIA_OPTION_NAME_BASE64 = 'b64';
    const MEDIA_OPTION_NAME_DOMAIN = 'd';
    const MEDIA_OPTION_NAME_MODE_OPTION_THUMBNAIL = 'thumbnail';
    const MEDIA_OPTION_NAME_MODE_OPTION_BEST_FIT = 'bestFit';
    const MEDIA_OPTION_NAME_MODE_OPTION_RESIZE = 'resize';
    const MEDIA_OPTION_NAME_MODE_OPTION_FITWIDTH = 'fitToWidth';
    const MEDIA_OPTION_NAME_MODE_OPTION_FITHEIGHT = 'fitToHeight';

    const DEFAULT_TEXT = 'default';

    const STATIC_FIELDS = [
        "modified",
        "created",
        "_id",
        "_uid",
    ];

    /**
     * Create a new API instance
     */
    public function __construct($base, $token, $locale = self::DEFAULT_TEXT)
    {

        if (substr($base, -1) != '/') {
            $base = $base.'/';
        }
        $this->base   = $base;
        $this->token  = $token;
        $this->locale = $locale;
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
    public function getCockpit($module, $method, $args = [])
    {
        $response = $this->server->post(self::COCKPIT_PATH, [
            'query' => ['token' => $this->token],
            'json'  => [
                'module' => $module,
                'method' => $method,
                'args'   => $args,
            ],
        ]);

        return $response->getBody()->getContents();
    }


    /**
     * Get region name
     *
     * @param $name
     *
     * @return mixed
     */
    public function getRegions($name)
    {
        $response = $this->server->post(self::REGIONS_PATH.$name, [
            'query' => ['token' => $this->token],
        ]);

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
    public function getGallery($name, $autoDomain = false)
    {
        $response = $this->server->post(self::GALLERY_PATH.$name, [
            'query' => ['token' => $this->token],
        ]);
        $output   = json_decode($response->getBody()->getContents());
        if ($autoDomain) {
            foreach ($output as $image) {
                $image->path = str_replace('site:', $this->base, $image->path);
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
        $image,
        $width = 50,
        $height = 50,
        $options = []
    ) {
        $options["w"]   = $width;
        $options["src"] = $image;
        $options["h"]   = $height;

        $response = $this->server->post(self::MEDIA_PATH, [
            'query' => ['token' => $this->token],
            'json'  => $options,
        ]);

        return $response->getBody()->getContents();
    }

    public function getMetaCollection($name)
    {
        return json_decode($this->getCockpit('collections', 'get_collection',
            ["name" => $name]));
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
        $response = $this->server->post(self::COLLECTION_PATH.$name, [
            'query' => ['token' => $this->token],
            'json'  => [
                'filter' => $filters,
                'limit'  => $limit,
                'skip'   => $skip,
                'sort'   => $sort,
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }

    public function getCollectionMultilang(
        $name,
        $filters = null,
        $sort = null,
        $limit = null,
        $skip = null
    ) {

        $output = [];
        foreach ($this->getCollection($name, $filters, $sort, $limit, $skip) as $element) {
            $output[] = $this->traslateOutput($this->getMetaCollection($name), $element);
        }

        return $output;
    }

    private function traslateOutput($metacollection, $collection)
    {
        $output = new \stdClass();
        foreach (self::STATIC_FIELDS as $field) {
            $output->{$field} = $collection->{$field};
        }

        foreach ($metacollection->fields as $field) {
            $name_field             = $field->name;
            $output->{$field->name} = $collection->{$name_field};
            if ($field->slug) {
                $output->{$field->name.'_slug'} = $collection->{$name_field.'_slug'};
            }

            if ($this->locale != self::DEFAULT_TEXT && $field->localize) {
                $name_field = $name_field.'_'.$this->locale;
                if ($collection->{$name_field} != '') {
                    $output->{$field->name} = $collection->{$name_field};
                }
                if ($field->slug && $collection->{$name_field.'_slug'} != '') {
                    $output->{$field->name.'_slug'} = $collection->{$name_field.'_slug'};
                }
            }


        }

        return $output;
    }

    /**
     * Reconnect to server
     */
    public
    function reconnect()
    {
        $this->connect();
    }

    /**
     * @return string
     */
    public
    function getBase()
    {
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
    function getToken()
    {
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
    function connect()
    {
        $this->server = new Client([self::BASE_URL_KEY => $this->base.self::API_URL_PATH]);
    }
}
