<?php

namespace CDSRC\Libraries\Traceable\Utility;

/*
 * Copyright (C) 2015 Matthias Toscanelli <m.toscanelli@code-source.ch>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of GeneralUtility
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class GeneralUtility {

    protected static $REMOTE_ADDR = NULL;
    protected static $REMOTE_ADDR_WLOCAL = NULL;

    /**
     * Get remote address
     * 
     * @return string|NULL
     */
    public static function getRemoteAddr($includeLocalIp = TRUE) {
        if (self::$REMOTE_ADDR_WLOCAL !== NULL) {
            return self::$REMOTE_ADDR_WLOCAL;
        }
        if (!$includeLocalIp && self::$REMOTE_ADDR !== NULL) {
            return self::$REMOTE_ADDR;
        }
        $ipKeys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        $ipFilter = $includeLocalIp ? FILTER_FLAG_IPV4 : FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key])) {
                foreach (array_filter(explode(',', $_SERVER[$key])) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, $ipFilter) !== FALSE) {
                        if (!$includeLocalIp) {
                            self::$REMOTE_ADDR = $ip;
                        }
                        self::$REMOTE_ADDR_WLOCAL = $ip;
                        return $ip;
                    }
                }
            }
        }
        return NULL;
    }

}
