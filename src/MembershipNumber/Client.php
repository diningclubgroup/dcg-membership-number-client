<?php

namespace Dcg\Client\MembershipNumber;

use Dcg\Client\MembershipNumber\Config;
use Dcg\Client\MembershipNumber\Exception\ConfigValueNotFoundException;
use Dcg\Client\MembershipNumber\Exception\MembershipNumberException;
use GuzzleHttp\Client as ApiClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;

class Client extends ApiClient
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * The default headers to use for any requests
     * @var array
     */
    protected $defaultHeaders = [];

    /**
     * The headers to use for any requests. Overwrites default headers.
     * @var array
     */
    protected $headers = [];

    /**
     * Client constructor.
     * @param array $apiClientConfig (Optional)   The config for the http client
     * @param \Dcg\Config (Optional) $membershipNumberClientConfig    The config for the membership number client
     * @throws \Dcg\Config\Exception\ConfigFileNotFoundException
     * @throws \Dcg\Config\Exception\ConfigValueNotFoundException
     */
    public function __construct(array $apiClientConfig = [], \Dcg\Client\MembershipNumber\Config $membershipNumberClientConfig = null)
    {
        parent::__construct($apiClientConfig);

        $this->config = $membershipNumberClientConfig ?: Config::getInstance();
        $this->headers['Access-Token'] = $this->config->get('api_access_token');
    }

    /**
     * Default error message for api failures
     */
    const DEFAULT_ERROR_MESSAGE = 'There was an error while contacting Membership Number Service';

    /**
     * Api endpoint for getting new membership number
     */
    const NEW_MEMBERSHIP_NUMBER_URL = '/numbers/number';

    /**
     * Api endpoint for getting new bulk membership numbers
     */
    const NEW_BULK_MEMBERSHIP_NUMBER_URL = '/numbers/bulkNumbers';

    /**
     * Api endpoint for storing membership numbers
     */
    const STORE_MEMBERSHIP_NUMBER_URL = '/numbers';

    /**
     * Returns a new unused membership number
     *
     * @return string Membership number
     * @throws MembershipNumberException for any errors. Error messages from the api is available in the exception.
     * @throws ConfigValueNotFoundException
     */
    public function getNewMembershipNumber()
    {
        $options = [
            'headers' => $this->getHeaders()
        ];

        $membershipNumber = null;
        $attempt = 0;
        $tries = 3;
        $errors = [];

        do {

            try {
                $response = $this->post(
                    $this->config->get('api_base_url') . self::NEW_MEMBERSHIP_NUMBER_URL,
                    $options
                );

                $result = json_decode($response->getBody(), true);

                $membershipNumber = $result['membership_number'];

            } catch (ClientException $e) {
                // dont't retry for client exceptions
                $errors[] = $this->getErrorMessage($e);
                break;
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }

            $attempt++;

        } while (!$membershipNumber && ($attempt < $tries));

        if (!$membershipNumber) {
            // still no membership number, throw exception
            $error = 'Unable to get a membership number';
            if ($errors) {
                $error .= '. '.implode('; ', $errors);
            }
            throw new MembershipNumberException($error);
        }

        return $membershipNumber;
    }

    /**
     * Returns an aray of new unused membership numbers
     *
     * @param  array limit
     * @return array Membership numbers
     * @throws MembershipNumberException for any errors. Error messages from the api is available in the exception.
     * @throws ConfigValueNotFoundException
     */
    public function getNewBulkMembershipNumber(array $limit)
    {

        $options = [
            'headers' => $this->getHeaders(),
            'body' => json_encode($limit)
        ];

        $membershipNumber = [];
        $attempt = 0;
        $tries = 3;
        $errors = [];

        do {

            try { 
                $response = $this->post(
                    $this->config->get('api_base_url') . self::NEW_BULK_MEMBERSHIP_NUMBER_URL,
                    $options
                );

                $result = json_decode($response->getBody(), true);

                $membershipNumber = $result;

            } catch (ClientException $e) {
                // dont't retry for client exceptions
                $errors[] = $this->getErrorMessage($e);
                break;
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }

            $attempt++;

        } while (!$membershipNumber && ($attempt < $tries));

        if (!$membershipNumber) {
            // still no membership number, throw exception
            $error = 'Unable to get a membership number';
            if ($errors) {
                $error .= '. '.implode('; ', $errors);
            }
            throw new MembershipNumberException($error);
        }

        return $membershipNumber;
    }

    /**
     * Stores a batch of membership numbers
     *
     * @param array $membershipNumbers Membership numbers to store in the following format,
     *
     * [
     *  ['membership_number' => ''XXXXXX', 'brand' => 'TC'],
     *  ['membership_number' => 'YYYYYYY', 'brand' => 'GS']
     *  ...
     *  ...
     * ]
     *
     * @return bool
     * @throws MembershipNumberException
     * @throws ConfigValueNotFoundException
     */
    public function store(array $membershipNumbers)
    {
        if (!$this->isValid($membershipNumbers)) {
            throw new MembershipNumberException('Invalid data passed into store');
        }

        try {
            $response = $this->post(
                $this->config->get('api_base_url').self::STORE_MEMBERSHIP_NUMBER_URL,
                ['body' => json_encode($membershipNumbers)]
            );

            return $response->getStatusCode() == 200;

        } catch (BadResponseException $e) {

            throw new MembershipNumberException($this->getErrorMessage($e));
        }
    }

    /**
     * Retrieves error message from the Guzzle exception
     *
     * @param BadResponseException $e
     * @return string
     */
    private function getErrorMessage(BadResponseException $e)
    {
        $responseBody = $e->getResponse()->getBody();
        $responseCode = $e->getResponse()->getStatusCode();

        $responseArray = json_decode($responseBody, true);

        $errorMessage = self::DEFAULT_ERROR_MESSAGE;
        if (!empty($responseArray) && isset($responseArray['error'])) {
            $errorMessage = $responseArray['error'];
        }

        $errorMessage.= ". Response code : " . $responseCode;

        return $errorMessage;
    }

    private function isValid(array $membershipNumbers)
    {
        foreach ($membershipNumbers as $item) {
            if (!isset($item['membership_number']) || !isset($item['brand'])) {

                return false;
            }
        }

        return true;
    }

    /**
     * Get the headers to use for requests
     * @return array
     */
    public function getHeaders()
    {
        return array_merge($this->defaultHeaders, $this->headers);
    }

    /**
     * Set the headers to use for requests. Replaces existing headers and if a default header exists with the same
     * name it will be overwritten.
     *
     * @param array $headers Key-Value array of headers to set.
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }
}