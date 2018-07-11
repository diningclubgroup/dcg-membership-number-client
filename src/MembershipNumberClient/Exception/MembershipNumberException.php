<?php

namespace Dcg\Client\MembershipNumberClient\Exception;

class MembershipNumberException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}