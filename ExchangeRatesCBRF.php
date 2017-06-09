<?php

/**
 * Exchange currency rates class
 *
 * The PHP class to gets exchange currency rates from webservice of Central Bank of Russia
 *
 * @author Aleksey Vaganov, Albert Thalidzhokov
 * @link http://www.idivision.ru/cbrf-exchange-rates-php-class/ OR https://github.com/handaehan/exchange-rates-cbrf
 * @version 2.1
 */
class ExchangeRatesCBRF
{
    /**
     * The exchange rates on defined date
     *
     * @var array
     */
    public $rates = array('byChCode' => array(), 'byCode' => array());

    /**
     * ExchangeRatesCBRF constructor.
     *
     * This method creates a connection to webservice of Central Bank of Russia
     * and obtains exchange rates, parse it and fills $rates property
     *
     * @param string $date The date on which exchange rates will be obtained (http://php.net/manual/ru/datetime.formats.date.php)
     */
    public function __construct($date = '')
    {
        $date = empty($date)
            ? date('Y-m-d')
            : date('Y-m-d', strtotime($date));

        $client = new SoapClient("http://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?WSDL");

        $curs = $client->GetCursOnDate(array("On_date" => $date));
        $rates = new SimpleXMLElement($curs->GetCursOnDateResult->any);

        foreach ($rates->ValuteData->ValuteCursOnDate as $rate) {
            $r = (float)$rate->Vcurs / (int)$rate->Vnom;
            $this->rates['byChCode'][(string)$rate->VchCode] = $r;
            $this->rates['byCode'][(int)$rate->Vcode] = $r;
        }

        // Adding an exchange rate of Russian Ruble
        $this->rates['byChCode']['RUB'] = 1;
        $this->rates['byCode'][643] = 1;
    }

    /**
     * This method returns exchange rate of given currency by its code
     *
     * @param mixed $code The alphabetic or numeric currency code
     *
     * @return float The exchange rate of given currency
     */
    public function GetRate($code = '')
    {
        $rtn = false;

        if (is_string($code)) {
            $code = strtoupper(trim($code));
            $rtn = isset($this->rates['byChCode'][$code])
                ? $this->rates['byChCode'][$code]
                : false;
        } else if (is_numeric($code)) {
            $rtn = isset($this->rates['byCode'][$code])
                ? $this->rates['byCode'][$code]
                : false;
        }

        return $rtn;
    }

    /**
     * This method returns exchange rate of given currency by its code
     *
     * @param mixed $CurCodeToSell The alphabetic or numeric currency code to sell
     * @param mixed $CurCodeToBuy The alphabetic or numeric currency code to buy
     *
     * @return float The cross exchange rate of given currencies
     */
    public function GetCrossRate($CurCodeToSell, $CurCodeToBuy)
    {
        $rtn = false;
        $CurToSellRate = $this->GetRate($CurCodeToSell);
        $CurToBuyRate = $this->GetRate($CurCodeToBuy);

        if ($CurToSellRate && $CurToBuyRate) {
            $rtn = $CurToBuyRate / $CurToSellRate;
        }

        return $rtn;
    }

    /**
     * This method returns the array of exchange rates
     *
     * @return array The exchange rates
     */
    public function GetRates()
    {
        return $this->rates;
    }
}