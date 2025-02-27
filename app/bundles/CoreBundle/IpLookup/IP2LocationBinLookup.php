<?php

namespace Mautic\CoreBundle\IpLookup;

use IP2Location\Database;

class IP2LocationBinLookup extends AbstractLocalDataLookup
{
    public function getAttribution(): string
    {
        return 'IP2Location Local Bin File DB9BIN only';
    }

    /**
     * @return string
     */
    public function getLocalDataStoreFilepath()
    {
        return $this->getDataDir();
    }

    /**
     * @return string
     */
    public function getRemoteDateStoreDownloadUrl()
    {
        $usernamePass = explode(':', $this->auth);
        $data         = [];

        if (isset($usernamePass[0]) && isset($usernamePass[1])) {
            $data['login']       = $usernamePass[0];
            $data['password']    = $usernamePass[1];
            $data['productcode'] = 'DB9BIN';
            $queryString         = http_build_query($data);
            // the system gets the file name from end of remove file path url so use hardedcoded name
            $queryString .= '&filename=/ip2locaion.zip';

            return 'https://www.ip2location.com/download?'.$queryString;
        } else {
            $this->logger->warn('Both username and password are required');
        }
    }

    /**
     * Extract the IP from the local database.
     */
    protected function lookup()
    {
        try {
            $reader = new Database($this->getLocalDataStoreFilepath().'/IP-COUNTRY-REGION-CITY-LATITUDE-LONGITUDE-ZIPCODE.BIN', Database::FILE_IO);
            $record = $reader->lookup($this->ip, Database::ALL);

            if (isset($record['countryName'])) {
                $this->country   = $record['countryName'];
                $this->region    = $record['regionName'];
                $this->city      = $record['cityName'];
                $this->latitude  = $record['latitude'];
                $this->longitude = $record['longitude'];
                $this->zipcode   = $record['zipCode'];
                $this->isp       = $record['isp'];
                $this->timezone  = $record['timeZone'];
            }
        } catch (\Exception $exception) {
            if ($this->logger) {
                $this->logger->warn('IP LOOKUP: '.$exception->getMessage());
            }
        }
    }
}
