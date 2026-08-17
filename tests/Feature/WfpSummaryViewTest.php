<?php

namespace Tests\Feature;

use Tests\TestCase;

class WfpSummaryViewTest extends TestCase
{
    public function test_summary_contains_all_requested_sections_and_periods(): void
    {
        $view = $this->view('components.financial_input_persistence', [
            'financialSector' => 'gass',
            'financials' => [],
            'financialAccomplishments' => [],
        ]);

        $view
            ->assertSee('Physical Target', false)
            ->assertSee('Financial Target', false)
            ->assertSee('Physical Accomplishment', false)
            ->assertSee('Financial Accomplishment', false)
            ->assertSee("key: 'annual'", false)
            ->assertSee("key: 'quarter'", false)
            ->assertSee("key: 'to-date'", false);
    }

    public function test_to_date_summary_accumulates_months_through_the_current_month(): void
    {
        $html = (string) $this->view('components.financial_input_persistence', [
            'financialSector' => 'gass',
            'financials' => [],
            'financialAccomplishments' => [],
        ]);

        $this->assertStringContainsString(
            '.slice(0, summaryMonthIndex + 1)',
            $html
        );
        $this->assertStringContainsString(
            'summaryQuarterColumns[summaryQuarterIndex]',
            $html
        );
    }

    public function test_annual_and_quarter_summaries_are_derived_from_monthly_values(): void
    {
        $html = (string) $this->view('components.financial_input_persistence', [
            'financialSector' => 'gass',
            'financials' => [],
            'financialAccomplishments' => [],
        ]);

        $this->assertStringContainsString(
            'const monthlyValues = summaryMonthColumns.map(',
            $html
        );
        $this->assertStringContainsString(
            "if (period.key === 'quarter')",
            $html
        );
        $this->assertStringContainsString(
            "if (period.key === 'annual')",
            $html
        );
        $this->assertStringNotContainsString(
            'return summaryColumnValue(row, summarySection, officeId, period.column)',
            $html
        );
    }

    public function test_each_summary_section_has_a_distinct_three_column_header(): void
    {
        $html = (string) $this->view('components.financial_input_persistence', [
            'financialSector' => 'gass',
            'financials' => [],
            'financialAccomplishments' => [],
        ]);

        $this->assertStringContainsString('summaryGroup.colSpan = summaryPeriods.length', $html);
        $this->assertStringContainsString('summaryGroup.textContent = section.label', $html);

        foreach ([
            'physical-target',
            'physical-accomplishment',
            'financial-target',
            'financial-accomplishment',
        ] as $section) {
            $this->assertStringContainsString(
                ".group-summary[data-summary-kind=\"{$section}\"]",
                $html
            );
            $this->assertStringContainsString(
                "th.summary-header[data-summary-kind=\"{$section}\"]",
                $html
            );
        }
    }

    public function test_summary_columns_use_the_compact_ui_width(): void
    {
        $html = (string) $this->view('components.financial_input_persistence', [
            'financialSector' => 'gass',
            'financials' => [],
            'financialAccomplishments' => [],
        ]);

        $this->assertStringContainsString('min-width: 96px !important;', $html);
        $this->assertStringContainsString('max-width: 96px !important;', $html);
        $this->assertStringContainsString('min-width: 82px !important;', $html);
        $this->assertStringContainsString('white-space: normal;', $html);
    }

    public function test_summary_renders_car_and_province_aggregate_inputs(): void
    {
        $html = (string) $this->view('components.financial_input_persistence', [
            'financialSector' => 'gass',
            'financials' => [],
            'financialAccomplishments' => [],
        ]);

        $this->assertStringContainsString('getSummaryAggregateLines', $html);
        $this->assertStringContainsString("kind: 'car'", $html);
        $this->assertStringContainsString("kind: 'province'", $html);
        $this->assertStringContainsString('return createSummaryInput(section, period, { aggregate })', $html);
        $this->assertStringContainsString('input.dataset.summaryOfficeIds = aggregate.officeIds.join', $html);
        $this->assertStringContainsString("input.classList.add(aggregate.kind === 'car' ? 'car-total-box' : 'group-total-box')", $html);
        $this->assertStringContainsString("if (input.dataset.summaryAggregate)", $html);
        $this->assertStringContainsString('const summaryAggregateValue = (row, summarySection, officeIds, period) =>', $html);
        $this->assertStringContainsString('const indicatorType = summaryIndicatorType(row, summarySection)', $html);
        $this->assertStringContainsString('const officeValues = officeIds.map(officeId => summaryColumnValue(', $html);
        $this->assertStringContainsString("return indicatorType === 'non-cumulative'", $html);
        $this->assertStringContainsString('? Math.max(0, ...officeValues)', $html);
        $this->assertStringContainsString(': officeValues.reduce((total, value) => total + value, 0)', $html);
        $this->assertStringContainsString('summaryValueFromMonthlyValues(row, summarySection, monthlyValues, period)', $html);
        $this->assertStringContainsString('summaryAggregateValue(row, section, aggregateOfficeIds, period)', $html);
    }

    public function test_financial_summary_aggregates_always_use_sums(): void
    {
        $html = (string) $this->view('components.financial_input_persistence', [
            'financialSector' => 'gass',
            'financials' => [],
            'financialAccomplishments' => [],
        ]);

        $this->assertStringContainsString(
            "const isFinancial = ['financial', 'financial-accomp'].includes(summarySection.inputSection)",
            $html
        );
        $this->assertStringContainsString(
            "return isFinancial || typeof getIndicatorTypeForRow !== 'function'",
            $html
        );
        $this->assertStringContainsString("? 'cumulative'", $html);
        $this->assertStringContainsString(
            ': officeValues.reduce((total, value) => total + value, 0)',
            $html
        );
    }
}
