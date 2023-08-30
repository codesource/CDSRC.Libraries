<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Traceable\Utility;

use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Exception;
use Neos\Flow\Security\Account;

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
    protected static array $REMOTE_ADDRESS_DATA = [];

    /**
     * @var Bootstrap
     */
    protected static Bootstrap $bootstrap;

    /**
     * Get remote address
     *
     * @param bool $includeLocalIp
     *
     * @return string|null
     */
    public static function getRemoteAddress(bool $includeLocalIp = true): ?string
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
     * @return Account|null
     *
     * @throws Exception
     */
    public static function getAuthenticatedAccount(): ?Account
    {
        self::initializeBootstrap();
        if (self::$bootstrap) {
            $objectManager = self::$bootstrap->getObjectManager();
            //TODO: We could return "null" if the Object Manager is not available at this stage of the bootstrap run.
            $securityContext = $objectManager->get('\Neos\Flow\Security\Context');
            if ($securityContext && $securityContext->canBeInitialized()) {
                return $securityContext->getAccount();
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
            self::$bootstrap = Bootstrap::$staticObjectManager->get('Neos\Flow\Core\Bootstrap');
        }
    }

}
