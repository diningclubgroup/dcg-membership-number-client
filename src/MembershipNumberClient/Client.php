<?php

namespace Dcg\Client\MembershipNumberClient;

use Dcg\Client\MembershipNumberClient\Exception\MembershipNumberException;
use GuzzleHttp\Client as ApiClient;
use GuzzleHttp\Exception\BadResponseException;

class Client extends ApiClient
{
    /**
     * Default error message for api failures
     */
    const DEFAULT_ERROR_MESSAGE = 'There was an error while contacting Membership Number Service';

    private $validClientIdentifiers = ['TC','GS'];

    /**
     * Api endpoint
     */
    const API_URL = 'http://192.168.33.13:8080/v1/numbers/number';

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
                self::API_URL,
                $options
            );

            $result = json_decode($response->getBody(), true);

            return $result['membership_number'];

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
}