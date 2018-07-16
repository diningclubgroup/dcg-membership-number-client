<?php

namespace Dcg\Client\MembershipNumber;

use Dcg\Client\MembershipNumber\Config\Config;
use Dcg\Client\MembershipNumber\Exception\MembershipNumberException;
use GuzzleHttp\Client as ApiClient;
use GuzzleHttp\Exception\BadResponseException;

class Client extends ApiClient
{
	/**
	 * @var Config
	 */
	protected $config;

	public function __construct(array $config = [])
	{
		parent::__construct($config);

		$this->config = Config::getInstance();
	}

    /**
     * Default error message for api failures
     */
    const DEFAULT_ERROR_MESSAGE = 'There was an error while contacting Membership Number Service';

    private $validClientIdentifiers = ['TC','GS'];

    /**
     * Api endpoint for getting new membership number
     */
    const NEW_MEMBERSHIP_NUMBER_URL = '/numbers/number';

    /**
     * Api endpoint for storing membership numbers
     */
    const STORE_MEMBERSHIP_NUMBER_URL = '/numbers';

    /**
     * Returns a new unused membership number
     *
     * @param string $brand Brand the membership number is requested for (TS, GS etc.)
     * @return string Membership number
     * @throws MembershipNumberException for any errors. Error messages from the api is available in the exception.
     */
    public function getNewMembershipNumber($brand)
    {
        $options = [
            'headers' => ['Brand' => $brand]
        ];

        try {
            $response = $this->post(
				$this->config->get('api_base_url').self::NEW_MEMBERSHIP_NUMBER_URL,
                $options
            );

            $result = json_decode($response->getBody(), true);

            return $result['membership_number'];

        } catch (BadResponseException $e) {

            throw new MembershipNumberException($this->getErrorMessage($e));
        }
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

    private function isValidClient($clientIdentifier)
    {
        return in_array($clientIdentifier, $this->validClientIdentifiers);
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
}