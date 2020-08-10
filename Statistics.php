<?php

require_once 'Google.php';

abstract class Statistics
{
    protected $start_date;
    protected $end_date;

    function __construct($data, $start_date = '1980-12-12', $end_date = '2050-12-12')
    {
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $start_date))
        {
            $this->start_date = $start_date;
        }
        else
        {
            exit($this->getReport('start_date has an incorrect date format! The date format should be:YYYY-MM-DD'));
        }
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $end_date))
        {
            $this->end_date = $end_date;
        }
        else
        {
            exit($this->getReport('end_date has an incorrect date format! The date format should be:YYYY-MM-DD'));
        }
        $this->setParams($data);
    }

    protected function getReport($errorMessage,  $errorStatus = true, $data = [])
    {
        $re = [];
        $re['data'] = $data;
        $re['error'] = $errorStatus;
        $re['message'] = $errorMessage;
        $re['type'] = get_class($this);
        return json_encode($re);
    }

    protected abstract function setParams($data);

    public abstract function getStatistics();
}

class FromFile extends Statistics
{
    private $fileName;
    private $separator;
    private $newLine = true;
    private $date_offset;

    protected function setParams($data)
    {
        $this->fileName = $data['fileName'];
        $this->separator = $data['separator'];
        $this->newLine = $data['newLine'];
        $this->date_offset = $data['date_offset'];
    }

    public function getStatistics()
    {
        $myfile = fopen($this->fileName, "r") or die($this->getReport('Unable to open file: "' . $this->fileName . '"'));
        $result = [];
        $result['data'] = [];
        $result['data']['details'] = [];
        $fileContent = fread($myfile, filesize($this->fileName));
        $t = "/$this->newLine/";
        $data = preg_split("/$this->newLine/", $fileContent);

        $i = 0;
        foreach ($data as $k => $v)
        {
            if (strlen($v) > 0)
            {
                $details = explode($this->separator, $v);

                if (count($details) - 1 < $this->date_offset)
                {
                    return json_encode($this->getReport('Uncorrect time offset in row ' . $k . ' from ' . $this->fileName));
                }
                else
                {
                    if ($details[$this->date_offset] >= $this->start_date && $details[$this->date_offset] <= $this->end_date)
                    {
                        $result['data']['details'][$k] = $details;
                        $i++;
                    }
                }
            }
            else
            {
                return $this->getReport('THe row:"' . $k . '" from file "' . $this->fileName . '" is empty');
            }
        }

        fclose($myfile);
        return $this->getReport('', false, ['Positive Guys' => $i]);
    }
}

class FromGoogle extends Statistics
{
    private $ga;

    protected function setParams($data)
    {
        $this->ga = new GA($data['json_file']);
    }

    public function getStatistics()
    {
        $result = $this->ga->OutputData($this->start_date, $this->end_date);

        if (is_object($result) === true)
        {
            return $this->getReport('', false, $data = [
                        'Positive Guys' => $result['totalsForAllResults']['ga:sessions'],
            ]);
        }
        else
        {
            $error = json_decode($result);
            if ($error !== NULL)
            {
                $errorText = '';
                foreach ($error as $v)
                {
                    $errorText .= $v . ', ';
                }
                return $this->getReport($errorText);
            }
            else if (is_string($result))
            {
                return $this->getReport($result);
            }
            else
            {
                return $this->getReport('unknown error');
            }
        }
    }
}

class FromDB extends Statistics
{
    private $servername;
    private $username;
    private $password;
    private $dbname;
    private $table;
    private $date_column;

    protected function setParams($data)
    {

        $this->servername = $data['servername'];
        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->dbname = $data['dbname'];
        $this->table = $data['table'];
        $this->date_column = $data['date_column'];
        ////////////
    }

    public function getStatistics()
    {
        $mysqli = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        if ($mysqli->connect_error)
        {
            return($this->getReport('Connect Error: ' . $mysqli->connect_error));
        }
        $mysqli->set_charset('utf8');

        $sql = 'SELECT COUNT(*) FROM `' . $this->table . '` WHERE `' . $this->date_column . '` >= ? AND `' . $this->date_column . '` <= ? ;';

        $stmt = $mysqli->prepare($sql);

        try {
            $stmt->bind_param('ss', $this->start_date, $this->end_date);
        } catch (Throwable $e) {

            return($this->getReport($e->getMessage()));
        }

        $stmt->execute();
        $db_result = $stmt->get_result();
        $count = $db_result->fetch_row()[0];
        $stmt->close();
        $mysqli->close();

        //////////////
        return $this->getReport('', false, ['Positive Guys' => $count]);
    }
}
