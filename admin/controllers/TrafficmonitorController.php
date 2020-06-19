<?php

namespace Admin\Controller;

use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Application\Request;
use Application\Config;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Database\Connectors\ConnectionFactory;

class TrafficmonitorController
{

    private $table;
    private static $dbConnection = null;
    private $dateRange = array();
    private $startDate;
    private $endDate;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->getDatabaseConnection();
        $this->table = Config::extensionsConfig('TrafficMonitor.table_name');
    }

    public static function getDatabaseConnection()
    {
        if (self::$dbConnection === null)
        {
            try
            {
                $factory = new ConnectionFactory();
                self::$dbConnection = $factory->make(array(
                    'driver' => 'mysql',
                    'host' => Config::settings('db_host'),
                    'username' => Config::settings('db_username'),
                    'password' => Config::settings('db_password'),
                    'database' => Config::settings('db_name'),
                    'charset' => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                ));
            }
            catch (Exception $ex)
            {
                Alert::insertData(array(
                    'identifier' => 'Membership User',
                    'text' => 'Please check your database credential',
                    'type' => 'error',
                    'alert_handler' => 'extensions',
                ));
                return false;
            }
        }
        return self::$dbConnection;
    }

    public function all($campaignType = '')
    {
        try
        {
            $orderByField = Request::form()->get('orderByField');
            $orderBy = Request::form()->get('orderBy');
            $startDate = Request::form()->get('startDate');
            $endDate = Request::form()->get('endDate');
            $this->startDate = date('Y-m-d', strtotime($startDate));
            $this->endDate = date('Y-m-d', strtotime($endDate));

            if (empty($orderByField) || empty($orderBy))
            {
                $orderByField = 'id';
                $orderBy = 'DESC';
            }


            $sql = 'SELECT `order_id`,`date`,`page_type`,COUNT(`id`) FROM `' . $this->table . '` WHERE `date` >= "' . $this->startDate . ' 00:00:00" AND `date` <= "' . $this->endDate . ' 23:59:59" AND `order_id` IS NOT NULL GROUP BY  `date`,`page_type`';


            if (Request::form()->get('limit') != 'all')
            {
                $sql .= ' LIMIT ' .
                        (int) Request::form()->get('offset') . ',' .
                        (int) Request::form()->get('limit');
            }

            $query = self::$dbConnection->query($sql);
            $data = $query->fetchAll();

            $clickBasedData = $this->getClickedData();

            $this->prepareDateRange();
            $this->dateRange = array_reverse($this->dateRange);
            if ($orderBy == 'ASC')
            {
                $this->dateRange = array_reverse($this->dateRange);
            }
            $result = array();


            foreach ($this->dateRange as $key => $value)
            {
                $upsell = '';
                $upsellPages = '';
                $upsellIndexes = array();
                for ($index = 0; $index < $clickBasedData['totalClickedBasedData']; $index++)
                {
                    if (!empty($data[$index]))
                    {
                        if ($value == date('d-m-Y', strtotime($data[$index]['date'])))
                        {
                            $result[$key]['date'] = $value;
                            if (preg_match("/upsell/i", $data[$index]['page_type']))
                            {
                                $upsellIndex = explode("upsellPage", $data[$index]['page_type']);
                                $pageType = 'Upsell' . ucfirst($this->numberTowords($upsellIndex[1]));
                                $upsellPages .= $pageType . ',';
                                $upsell .= $pageType . '(' . $data[$index]['COUNT(`id`)'] . '), ';
                                $result[$key][$pageType]['page'] = $data[$index]['page_type'];
                                $result[$key][$pageType]['date'] = $value;
                                $result[$key][$pageType]['visited'] = $data[$index]['COUNT(`id`)'];
                                $result[$key]['upsell'] = $upsell;
                               // $result[$key]['upsellPages'] = $upsellPages;
                                array_push($upsellIndexes, $pageType);
                                $result[$key]['upsellPages'] = $upsellIndexes;
                            }
                            else
                            {
                                $result[$key][$data[$index]['page_type']]['page'] = $data[$index]['page_type'];
                                $result[$key][$data[$index]['page_type']]['date'] = $value;
                                $result[$key][$data[$index]['page_type']]['visited'] = $data[$index]['COUNT(`id`)'];
                            }
                        }
                    }
                    if (!empty($clickBasedData[$value]))
                    {
                        $result[$key]['clickedBased'] = $clickBasedData[$value];
                        $clickBasedData[$value]['date'] = date('M j, Y', strtotime($clickBasedData[$value]['date']));
                    }
                }
            }
            $result = array_values($result);
         //   $result = $this->getUpsellRation($result);
//            echo "<pre>hhh";
        //    print_r($result);
//            echo "<pre>";
//            print_r($clickBasedData);
            return array(
                'success' => true,
                'data' => $result,
                'totalData' => count($result),
            );
        }
        catch (Exception $ex)
        {
            return array(
                'success' => false,
                'data' => array(),
                'error_message' => $ex->getMessage(),
            );
        }
    }

    private function getClickedData()
    {
        try
        {
            $orderByField = Request::form()->get('orderByField');
            $orderBy = Request::form()->get('orderBy');
            $startDate = Request::form()->get('startDate');
            $endDate = Request::form()->get('endDate');
            $this->startDate = date('Y-m-d', strtotime($startDate));
            $this->endDate = date('Y-m-d', strtotime($endDate));

            if (empty($orderByField) || empty($orderBy))
            {
                $orderByField = 'id';
                $orderBy = 'DESC';
            }

            $sql = 'SELECT `order_id`,`date`,`page_type`,COUNT(`id`) FROM `' . $this->table . '` WHERE `date` >= "' . $this->startDate . ' 00:00:00" AND `date` <= "' . $this->endDate . ' 23:59:59" AND `order_id` IS  NULL GROUP BY  `date`,`page_type`';
            if (Request::form()->get('limit') != 'all')
            {
                $sql .= ' LIMIT ' .
                        (int) Request::form()->get('offset') . ',' .
                        (int) Request::form()->get('limit');
            }

            $query = self::$dbConnection->query($sql);
            $data = $query->fetchAll();
            $totalClickedBasedData = count($data);
            $this->prepareDateRange();
            $this->dateRange = array_reverse($this->dateRange);
            if ($orderBy == 'ASC')
            {
                $this->dateRange = array_reverse($this->dateRange);
            }
            $result = array();
            foreach ($this->dateRange as $key => $value)
            {
                $upsell = '';
                foreach ($data as $trafficKey => $trafic)
                {
                    if ($value == date('d-m-Y', strtotime($trafic['date'])))
                    {
                        $result[$value]['date'] = $value;
                        if (preg_match("/upsell/i", $trafic['page_type']))
                        {
                            $upsellIndex = explode("upsellPage", $trafic['page_type']);
                            $pageType = 'Upsell' . ucfirst($this->numberTowords($upsellIndex[1]));
                            $upsell .= $pageType . '(' . $trafic['COUNT(`id`)'] . '), ';
                            $result[$value][$pageType]['page'] = $trafic['page_type'];
                            $result[$value][$pageType]['date'] = $value;
                            $result[$value][$pageType]['visited'] = $trafic['COUNT(`id`)'];
                            $result[$value]['upsell'] = $upsell;
                        }
                        else
                        {
                            $result[$value][$trafic['page_type']]['page'] = $trafic['page_type'];
                            $result[$value][$trafic['page_type']]['date'] = $value;
                            $result[$value][$trafic['page_type']]['visited'] = $trafic['COUNT(`id`)'];
                        }
                    }
                }
            }
            $result['totalClickedBasedData'] = $totalClickedBasedData;
            return $result;
        }
        catch (Exception $ex)
        {
            return false;
        }
    }

    private function getUpsellRation($result)
    {
        foreach ($result as $key => $value)
        {
            if (!empty($value['checkoutPage']) && !empty($value['upsellPages']))
            {
                $upsellIndexes = rtrim($value['upsellPages'], ',');
                $upsellPages = explode(',', $upsellIndexes);
                $upsellWithChkoutbsd = '';
                foreach ($upsellPages as $upsellIndx)
                {
                    $chkoutBsdLog = round(($value[$upsellIndx]['visited'] / $value['checkoutPage']['visited']) * 100);
                    $upsellWithChkoutbsd .= '<span>' . $upsellIndx . '(' . $value[$upsellIndx]['visited'] . ')</span>';
                    $upsellWithChkoutbsd .= '<br><span >' . $chkoutBsdLog . '%, </span><br>';
                }
                $result[$key]['upsell'] = $upsellWithChkoutbsd;
            }
        }
        return $result;
    }

    public function checkExtensions()
    {
        $result = array(
            'success' => true,
            'extensionTrafficMonitorActive' => false,
        );
        $extensions = Config::extensions();

        $extensions = Config::extensions();
        foreach ($extensions as $extension)
        {
            if ($extension['extension_slug'] !== 'TrafficMonitor')
            {
                continue;
            }
            if ($extension['active'] === true)
            {
                $result['extensionTrafficMonitorActive'] = true;
            }
            break;
        }


        return $result;
    }

    protected function prepareDateRange()
    {
        $this->dateRange = array();
        $date_from = strtotime($this->startDate);
        $date_to = strtotime($this->endDate);
        for ($i = $date_from; $i <= $date_to; $i += 86400)
        {
            array_push($this->dateRange, date('d-m-Y', $i));
        }
    }

    protected function numberTowords($num)
    {
        $ones = array(
            1 => "one",
            2 => "two",
            3 => "three",
            4 => "four",
            5 => "five",
            6 => "six",
            7 => "seven",
            8 => "eight",
            9 => "nine",
            10 => "ten",
            11 => "eleven",
            12 => "twelve",
            13 => "thirteen",
            14 => "fourteen",
            15 => "fifteen",
            16 => "sixteen",
            17 => "seventeen",
            18 => "eighteen",
            19 => "nineteen"
        );
        $tens = array(
            1 => "ten",
            2 => "twenty",
            3 => "thirty",
            4 => "forty",
            5 => "fifty",
            6 => "sixty",
            7 => "seventy",
            8 => "eighty",
            9 => "ninety"
        );
        $hundreds = array(
            "hundred",
            "thousand",
            "million",
            "billion",
            "trillion",
            "quadrillion"
        ); //limit t quadrillion 
        $num = number_format($num, 2, ".", ",");
        $num_arr = explode(".", $num);
        $wholenum = $num_arr[0];
        $decnum = $num_arr[1];
        $whole_arr = array_reverse(explode(",", $wholenum));
        krsort($whole_arr);
        $rettxt = "";
        foreach ($whole_arr as $key => $i)
        {
            if ($i < 20)
            {
                $rettxt .= $ones[$i];
            }
            elseif ($i < 100)
            {
                $rettxt .= $tens[substr($i, 0, 1)];
                $rettxt .= " " . $ones[substr($i, 1, 1)];
            }
            else
            {
                $rettxt .= $ones[substr($i, 0, 1)] . " " . $hundreds[0];
                $rettxt .= " " . $tens[substr($i, 1, 1)];
                $rettxt .= " " . $ones[substr($i, 2, 1)];
            }
            if ($key > 0)
            {
                $rettxt .= " " . $hundreds[$key] . " ";
            }
        }
        if ($decnum > 0)
        {
            $rettxt .= " and ";
            if ($decnum < 20)
            {
                $rettxt .= $ones[$decnum];
            }
            elseif ($decnum < 100)
            {
                $rettxt .= $tens[substr($decnum, 0, 1)];
                $rettxt .= " " . $ones[substr($decnum, 1, 1)];
            }
        }
        return $rettxt;
    }

}
