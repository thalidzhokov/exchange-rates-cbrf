<?php

use PHPUnit\Framework\TestCase;

class ExchangeRatesCBRFTest extends TestCase
{
    /** @var ExchangeRatesCBRF */
    private static $historical;

    public static function setUpBeforeClass(): void
    {
        self::$historical = new ExchangeRatesCBRF('2024-01-15');
    }

    public function testHistoricalUsdRate()
    {
        $this->assertEqualsWithDelta(88.1324, self::$historical->GetRate('USD'), 0.0000001);
        $this->assertEqualsWithDelta(88.1324, self::$historical->GetRate('usd'), 0.0000001);
        $this->assertEqualsWithDelta(88.1324, self::$historical->GetRate(' USD '), 0.0000001);
        $this->assertEqualsWithDelta(88.1324, self::$historical->GetRate(840), 0.0000001);
        $this->assertEqualsWithDelta(88.1324, self::$historical->GetRate('840'), 0.0000001);
        $this->assertEqualsWithDelta(88.1324, self::$historical->GetRate(840.0), 0.0000001);
    }

    public function testRubleAndUnknownCode()
    {
        $this->assertSame(1, self::$historical->GetRate('RUB'));
        $this->assertSame(1, self::$historical->GetRate(643));
        $this->assertFalse(self::$historical->GetRate('ZZZ'));
        $this->assertFalse(self::$historical->GetRate(999));
        $this->assertFalse(self::$historical->GetRate(''));
    }

    public function testCrossRate()
    {
        $usd = self::$historical->GetRate('USD');
        $eur = self::$historical->GetRate('EUR');

        $this->assertGreaterThan(0, $eur);
        $this->assertEqualsWithDelta($usd / $eur, self::$historical->GetCrossRate('EUR', 'USD'), 0.0000001);
        $this->assertEqualsWithDelta($usd / $eur, self::$historical->GetCrossRate(978, 840), 0.0000001);
        $this->assertFalse(self::$historical->GetCrossRate('EUR', 'ZZZ'));
        $this->assertFalse(self::$historical->GetCrossRate('ZZZ', 'USD'));
    }

    public function testGetRates()
    {
        $all = self::$historical->GetRates();

        $this->assertEqualsWithDelta(88.1324, $all['byChCode']['USD'], 0.0000001);
        $this->assertEqualsWithDelta(88.1324, $all['byCode'][840], 0.0000001);
        $this->assertSame(1, $all['byChCode']['RUB']);
        $this->assertSame(1, $all['byCode'][643]);
    }

    public function testToday()
    {
        $rates = new ExchangeRatesCBRF();

        $this->assertGreaterThan(0, $rates->GetRate('USD'));
        $this->assertSame($rates->GetRate('USD'), $rates->GetRate(840));
        $this->assertSame(1, $rates->GetRate('RUB'));
    }

    public function testInvalidDate()
    {
        $this->expectException(InvalidArgumentException::class);

        new ExchangeRatesCBRF('not-a-date');
    }

    public function testDateWithoutRates()
    {
        $this->expectException(RuntimeException::class);

        new ExchangeRatesCBRF('1970-01-01');
    }
}
