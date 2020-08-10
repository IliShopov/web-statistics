<?php

class GA
{

    private $jsonFile;

    function __construct($jsonFile)
    {
        $this->jsonFile = $jsonFile;
    }
    private function errors($message)
    {
        $e=['error'=>$message];
        
        return $message;
    }
    function initializeAnalytics()
    {

        // Creates and returns the Analytics Reporting service object.
        // Use the developers console and download your service account
        // credentials in JSON format. Place them in this directory or
        // change the key file location if necessary.
        // $KEY_FILE_LOCATION = __DIR__ . '/to-spirit-285312-d3dc642e39bf.json';
        $KEY_FILE_LOCATION = __DIR__ . '/' . $this->jsonFile;

        // Create and configure a new client object.
        $client = new Google_Client();
        $client->setApplicationName("Hello Analytics Reporting");
/////////////////
        //error code:
        try {
            $client->setAuthConfig($KEY_FILE_LOCATION);
        } catch (Throwable $e) {            
            return( $this->errors($e->getMessage()) );
        }
        //error code^^^
///////////////
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $analytics = new Google_Service_Analytics($client);

        return $analytics;
    }

    function getFirstProfileId($analytics)
    {
        // Get the user's first view (profile) ID.
        // Get the list of accounts for the authorized user.

        try {
            $accounts = $analytics->management_accounts->listManagementAccounts();
        } catch (Throwable $e) {
             return( $this->errors($e->getMessage()) ); 
        }

        if (count($accounts->getItems()) > 0)
        {
            $items = $accounts->getItems();
            $firstAccountId = $items[0]->getId();

            // Get the list of properties for the authorized user.
            $properties = $analytics->management_webproperties
                    ->listManagementWebproperties($firstAccountId);

            if (count($properties->getItems()) > 0)
            {
                $items = $properties->getItems();
                $firstPropertyId = $items[0]->getId();

                // Get the list of views (profiles) for the authorized user.
                $profiles = $analytics->management_profiles
                        ->listManagementProfiles($firstAccountId, $firstPropertyId);

                if (count($profiles->getItems()) > 0)
                {
                    $items = $profiles->getItems();

                    // Return the first view (profile) ID.
                    return $items[0]->getId();
                }
                else
                {
                    throw new Exception('No views (profiles) found for this user.');
                }
            }
            else
            {
                throw new Exception('No properties found for this user.');
            }
        }
        else
        {
            throw new Exception('No accounts found for this user.');
        }
    }

    function OrganicTraffic($start_date,$end_date)
    {
        $analytics = $this->initializeAnalytics();
        $profileId = $this->getFirstProfileId($analytics);       

        $Params = [
            'dimensions' => 'ga:source',
            'filters' => 'ga:medium==organic',
            'metrics' => 'ga:sessions',
        ];
        ///////////////////////////
        ////////////////////
        try {
            return $analytics->data_ga->get(
                            'ga:' . $profileId, $start_date, $end_date, 'ga:sessions', $Params
            );
        } catch (Throwable $e) {
            return( $this->errors($e->getMessage()) ); 
        }
    }

    function OutputData($start_date,$end_date)
    {
        
       // return $t['totalsForAllResults']['ga:sessions'];
        return $this->OrganicTraffic($start_date,$end_date);
    }

}
