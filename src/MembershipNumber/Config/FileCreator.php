<?php

namespace Dcg\Client\MembershipNumber\Config;

class FileCreator extends \Dcg\Config\FileCreator
{
    /**
     * Get the location of the config file to use as an example (template)
     * @param Composer\Script\Event $event
     * @return string
     */
    protected static function getSourceFile(\Composer\Script\Event $event) {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        return $vendorDir . DIRECTORY_SEPARATOR . 'dcg' . DIRECTORY_SEPARATOR . 'dcg-lib-membership-number-client' . DIRECTORY_SEPARATOR . 'config.php';
    }

    /**
     * Get the location of where the config file should be copied to
     * @param Composer\Script\Event $event
     * @return string
     */
    protected static function getDestinationFile(\Composer\Script\Event $event) {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        return dirname($vendorDir) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'membership-number-config.php';
    }
    
}