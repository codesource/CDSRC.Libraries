<?php

namespace CDSRC\Libraries\Traceable\Utility;

/*******************************************************************************
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ******************************************************************************/

use TYPO3\Flow\Core\Bootstrap;

/**
 * GeneralUtility for traceable library
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class GeneralUtility
{

    /**
     * Store remote IP address data
     *
     * @var array
     */
    protected static $REMOTE_ADDRESS_DATA = array();

    /**
     * @var \TYPO3\Flow\Core\Bootstrap
     */
    protected static $bootstrap;

    /**
     * Get remote address
     *
     * @param bool $includeLocalIp
     *
     * @return NULL|string
     */
    public static function getRemoteAddress($includeLocalIp = true)
    {
        if (!$includeLocalIp && isset(self::$REMOTE_ADDRESS_DATA['external'])) {
            return self::$REMOTE_ADDRESS_DATA['external'];
        }
        if ($includeLocalIp && isset(self::$REMOTE_ADDRESS_DATA['all'])) {
            return self::$REMOTE_ADDRESS_DATA['all'];
        }
        $ipKeys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        $ipFilter = $includeLocalIp ? FILTER_FLAG_IPV4 : FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key])) {
                foreach (array_filter(explode(',', $_SERVER[$key])) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, $ipFilter) !== false) {
                        if (!$includeLocalIp) {
                            self::$REMOTE_ADDRESS_DATA['external'] = $ip;
                        }
                        self::$REMOTE_ADDRESS_DATA['all'] = $ip;

                        return $ip;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Retrieve current authenticated account
     *
     * @return \TYPO3\Flow\Security\Account|NULL
     */
    public static function getAuthenticatedAccount()
    {
        self::initializeBootstrap();
        if (self::$bootstrap) {
            $objectManager = self::$bootstrap->getObjectManager();
            if ($objectManager) {
                $securityContext = $objectManager->get('\TYPO3\Flow\Security\Context');
                if ($securityContext && $securityContext->canBeInitialized()) {
                    return $securityContext->getAccount();
                }
            }
        }

        return null;
    }

    /**
     * Initialize bootstrap
     */
    protected static function initializeBootstrap()
    {
        if (!self::$bootstrap) {
            self::$bootstrap = Bootstrap::$staticObjectManager->get('TYPO3\Flow\Core\Bootstrap');
        }
    }

}
