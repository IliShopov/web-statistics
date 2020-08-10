<?php

require_once 'Statistics.php';
/* ===============================================

  From google analitics:
    
 * To use this module, you need to install the Google APIs Client Library
 * Please see this link: https://developers.google.com/analytics/devguides/config/mgmt/v3/quickstart/service-php
 *  If Google APIs Client Library is not installed you will get an error
 * If you will not be using it, put comments in the lines below
 
  ================================================ */
$data = [
    'json_file' => 'path_to_service-account-credentials.json'//path to service-account-credentials.json
];
require_once __DIR__ . '/../vendor/autoload.php';// path to vendor directory to Google APIs Client Library

$statisticsFromGoogle=new FromGoogle($data ,'2017-09-01','2020-09-01');//'2017-09-01', '2020-09-01'-start and end date of the period for which you want to receive statistics
$statGa=$statisticsFromGoogle->getStatistics();
echo "<p>$statGa</p>";
/* ===============================================

  From file:

  ================================================ */
$data = [
    'fileName' => 'stat.txt',//path to the file in which the visit data is saved
    'separator' => ',',//data separator in the row
    'newLine' => '\n',
    'date_offset'=>2,/*The shape of a rows looks like this. 
    (name1,data1,2017-12-10,1
     name2,data2,2018-11-28,1
     name3,data3,2019-12-28,3}
    //This is the item number of the item for the date. Тhe first item starts at 0. In this case dada is 2
      */
     
];
$statisticsFromFile = new FromFile($data, '2017-10-03', '2020-09-01');//'2017-09-01', '2020-09-01'-start and end date of the period for which you want to receive statistics
$statFile = $statisticsFromFile->getStatistics();
echo "<p>$statFile</p>";

/* ==============================================

  From DB:

  =============================================== */
$data = [
    //data to db connection:
    'servername' => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname' => 'test',
    //table name:
    'table' => 'condor',
    //the column in which the date is written
    'date_column' => 'date',    
];
        
$statisticsFromDB=new FromDB($data,'2018-09-01','2020-09-01');//'2017-09-01', '2020-09-01'-start and end date of the period for which you want to receive statistics
$statDB=$statisticsFromDB->getStatistics();
echo "<p>$statDB</p>";