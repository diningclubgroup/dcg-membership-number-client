<?php

namespace Dcg\Client\MembershipNumber;

class Config extends \Dcg\Config {

    /**
     * Get the default config file to use
     * @return string
     */
    protected static function getDefaultConfigFile() {
        return self::getRootDir().'/config/membership-number-config.php';
    }
}