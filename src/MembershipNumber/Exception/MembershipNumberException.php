<?php

namespace Dcg\Client\MembershipNumber\Exception;

class MembershipNumberException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}