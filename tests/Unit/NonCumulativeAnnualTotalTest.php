<?php

namespace Tests\Unit;

use App\Support\NonCumulativeAnnualTotal;
use App\Support\OfficialWfpTemplateWriter;
use App\Support\SimpleXlsxWriter;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class NonCumulativeAnnualTotalTest extends TestCase
{
    public function test_annual_uses_the_most_frequent_quarter_including_zero_and_decimal_values(): void
    {
        $this->assertSame(11.0, NonCumulativeAnnualTotal::calculate([11, 11, 69, 11]));
        $this->assertSame(0.0, NonCumulativeAnnualTotal::calculate([0, 0, 2, 0]));
        $this->assertSame(1.5, NonCumulativeAnnualTotal::calculate([1.5, 1.5, 9, 1.5]));
        $this->assertSame(11.0, NonCumulativeAnnualTotal::calculate([11, 11, 69]));
        $this->assertSame(0.0, NonCumulativeAnnualTotal::calculate([2, 0, 0]));
    }

    public function test_both_excel_formats_use_repeated_values_for_quarters_and_annual_without_changing_other_types(): void
    {
        $months = array_fill_keys(['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'], 11);
        $months['sep'] = 69;
        $months['annual_total'] = 69;
        foreach ([SimpleXlsxWriter::class, OfficialWfpTemplateWriter::class] as $class) {
            $method = new ReflectionMethod($class, 'summaryValues');
            $writer = new $class;
            $this->assertSame([11.0, 11.0, 33.0], $method->invoke($writer, $months, 9, 'non-cumulative'));
            $this->assertSame([190.0, 91.0, 157.0], $method->invoke($writer, $months, 9, 'cumulative'));
            $this->assertSame([91.0, 91.0, 91.0], $method->invoke($writer, $months, 9, 'semi-cumulative'));
        }
    }
}
