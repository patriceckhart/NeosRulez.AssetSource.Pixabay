<?php

namespace NeosRulez\AssetSource\Pixabay\Api;

use NeosRulez\AssetSource\Pixabay\Exception\ConfigurationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Neos\Cache\Frontend\VariableFrontend;

final class PixabayClient
{

    protected const API_URL = 'https://pixabay.com/api/';
    protected const QUERY_TYPE_CURATED = 'curated';
    protected const QUERY_TYPE_SEARCH = 'search';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var PixabayClient
     */
    private $client;

    /**
     * @var array
     */
    private $queryResults = [];

    /**
     * @var VariableFrontend
     */
    protected $filePropertyCache;


    /**
     * @param string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param int $pageSize
     * @param int $page
     * @return PixabayQueryResult
     * @throws GuzzleException
     * @throws \Neos\Cache\Exception
     */
    public function curated(int $pageSize = 20, int $page = 1): PixabayQueryResult
    {
        return $this->executeQuery(self::QUERY_TYPE_CURATED, $pageSize, $page);
    }

    /**
     * @param string $query
     * @param int $pageSize
     * @param int $page
     *
     * @return PixabayQueryResult
     * @throws GuzzleException
     * @throws \Neos\Cache\Exception
     */
    public function search(string $query, int $pageSize = 20, int $page = 1): PixabayQueryResult
    {
        return $this->executeQuery(self::QUERY_TYPE_SEARCH, $pageSize, $page, $query);
    }

    /**
     * @param string $identifier
     * @return mixed
     * @throws \Exception
     */
    public function findByIdentifier(string $identifier)
    {
        if (!$this->filePropertyCache->has($identifier)) {
            throw new \Exception(sprintf('file with id %s was not found in the cache', $identifier), 1525457755);
        }

        return $this->filePropertyCache->get($identifier);
    }

    /**
     * @param string $type
     * @param int $pageSize
     * @param int $page
     * @param string $query
     * @return PixabayQueryResult
     * @throws GuzzleException
     * @throws \Neos\Cache\Exception
     */
    private function executeQuery(string $type, int $pageSize = 20, int $page = 1, string $query = ''): PixabayQueryResult
    {

        $requestParameter = [
            'key' => $this->apiKey,
            'per_page' => $pageSize,
            'page' => $page,
            'type' => 'photo'
        ];

        if ($query !== '') {
            $requestParameter['q'] = $query;
        }

        $requestIdentifier = implode('+', $requestParameter);

        if (!isset($this->queryResults[$requestIdentifier])) {
            $result = $this->getClient()->request('GET', self::API_URL . '?' . http_build_query($requestParameter));

            $resultArray = \GuzzleHttp\json_decode($result->getBody()->getContents(), true);
            $this->queryResults[$requestIdentifier] = $this->processResult($resultArray);
        }

        return $this->queryResults[$requestIdentifier];

    }

    /**
     * @param array $resultArray
     * @return PixabayQueryResult
     * @throws \Neos\Cache\Exception
     */
    protected function processResult(array $resultArray): PixabayQueryResult
    {
        $files = $resultArray['hits'] ?? [];

        foreach ($files as $file) {
            if (isset($file['id'])) {
                $this->filePropertyCache->set((string)$file['id'], $file);
            }
        }

        return new PixabayQueryResult($files, $resultArray['totalHits']);
    }

    /**
     * @param string $url
     * @return false|resource
     */
    public function getFileStream(string $url)
    {

        $context = stream_context_create();

        $resource = fopen($url, 'r', false, $context);

        if (!is_resource($resource)) {
            throw new TransferException(sprintf('Unable to load an image from %s %s. Error: %s', $url, error_get_last()), 1600770625);
        }

        return $resource;
    }

    /**
     * @return Client
     * @throws ConfigurationException
     */
    private function getClient(): Client
    {
        if (trim($this->apiKey) === '') {
            throw new ConfigurationException('No API key for pixabay was defined. Get your API key at https://www.pixabay.com/api/ and add it to your settings', 1594199031);
        }

        if ($this->client === null) {
            $this->client = new Client([
                'key' => $this->apiKey,
                'type' => 'photo'
            ]);
        }

        return $this->client;
    }
}
