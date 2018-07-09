<?php

namespace Dcg\Client\MembershipNumberClient;


class Client
{
    private $validClientIdentifiers = ['TC','GS'];
    private $apiClient = null;

    public function __construct()
    {
    }

    public function activate($membershipNumber, $expiryDate)
    {
        //@TODO
        throw new \BadMethodCallException("Not Implemented",102);
    }

    /**
     * Make the call to the Membershipship Number Service API
     * @param $expiryDate
     * @return string
     */
    public function getNewMembershipNumber($clientIdentifier,$expiryDate){

        if(!$this->isValidClient($clientIdentifier)){
            throw new \Exception("Invalid Client Identifier: '$clientIdentifier' ", 101);
        }

        //$response =  $this->apiClient->post('',$payload = []);
        srand();
        $newnumber = (time()*time())/rand(1,time());
        error_log($newnumber);
        return $newnumber;
    }

    private function isValidClient($clientIdentifier)
    {
        return in_array($clientIdentifier, $this->validClientIdentifiers);
    }
}