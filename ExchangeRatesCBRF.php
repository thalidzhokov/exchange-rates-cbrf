<?php

/**
 * Exchange currency rates class
 *
 * The PHP class to gets exchange currency rates from webservice of Central Bank of Russia
 *
 * @author Aleksey Vaganov, Albert Thalidzhokov
 * @link https://github.com/thalidzhokov/exchange-rates-cbrf
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
        $date = self::normalizeDate($date);
        $client = self::createClient();

        $curs = $client->GetCursOnDate(array('On_date' => $date));
        if (!isset($curs->GetCursOnDateResult->any)) {
            throw new RuntimeException('Empty response from CBR');
        }

        $rates = new SimpleXMLElement($curs->GetCursOnDateResult->any);
        if (!isset($rates->ValuteData->ValuteCursOnDate)) {
            throw new RuntimeException('No exchange rates for ' . $date);
        }

        foreach ($rates->ValuteData->ValuteCursOnDate as $rate) {
            $nominal = (int)$rate->Vnom;
            if ($nominal === 0) {
                continue;
            }
            $r = (float)$rate->Vcurs / $nominal;
            $this->rates['byChCode'][trim((string)$rate->VchCode)] = $r;
            $this->rates['byCode'][(int)$rate->Vcode] = $r;
        }

        // Adding an exchange rate of Russian Ruble
        $this->rates['byChCode']['RUB'] = 1;
        $this->rates['byCode'][643] = 1;
    }

    /**
     * Calendar date in Moscow: CBR publishes rates for that timezone.
     *
     * @param string $date
     * @return string
     */
    private static function normalizeDate($date)
    {
        $tz = new DateTimeZone('Europe/Moscow');
        if ($date === '' || $date === null) {
            $date = 'now';
        }

        $dt = date_create($date, $tz);
        if ($dt === false) {
            throw new InvalidArgumentException('Invalid date');
        }

        return $dt->format('Y-m-d');
    }

    /**
     * HTTPS is the current endpoint. HTTP remains as a fallback when OpenSSL is unavailable.
     *
     * @return SoapClient
     */
    private static function createClient()
    {
        $urls = array(
            'https://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?WSDL',
            'http://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?WSDL',
        );
        $options = array(
            'exceptions' => true,
            'connection_timeout' => 10,
        );

        $last = null;
        foreach ($urls as $url) {
            try {
                return new SoapClient($url, $options);
            } catch (SoapFault $e) {
                $last = $e;
            }
        }

        throw $last;
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

        if (is_int($code) || (is_string($code) && preg_match('/^\d+$/', trim($code)))) {
            $num = (int)$code;
            $rtn = isset($this->rates['byCode'][$num])
                ? $this->rates['byCode'][$num]
                : false;
        } else if (is_string($code)) {
            $code = strtoupper(trim($code));
            $rtn = isset($this->rates['byChCode'][$code])
                ? $this->rates['byChCode'][$code]
                : false;
        } else if (is_numeric($code)) {
            $num = (int)$code;
            $rtn = isset($this->rates['byCode'][$num])
                ? $this->rates['byCode'][$num]
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