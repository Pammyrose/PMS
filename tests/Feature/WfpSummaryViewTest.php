<?php

namespace Tests\Feature;

use App\Models\User;
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

    public function test_accomplishment_borders_identify_due_targets_that_were_not_met(): void
    {
        $html = (string) $this->view('components.financial_input_persistence', [
            'financialSector' => 'gass',
            'financials' => [],
            'financialAccomplishments' => [],
        ]);

        $this->assertStringContainsString('#performanceTable .month-box.car-total-box', $html);
        $this->assertStringContainsString('border-color: #000 !important;', $html);
        $this->assertStringContainsString('#performanceTable .month-box.target-not-accomplished', $html);
        $this->assertStringContainsString('border: 2px solid #dc2626 !important;', $html);
        $this->assertStringContainsString('const dueMonthColumns = new Set(', $html);
        $this->assertStringContainsString('const missedTarget = target > 0 && accomplishment + 0.000001 < target;', $html);
        $this->assertStringContainsString("input.classList.toggle('target-not-accomplished', missedTarget)", $html);
    }

    public function test_locked_month_edits_collect_a_reason_for_review(): void
    {
        $html = (string) $this->view('components.financial_input_persistence', [
            'financialSector' => 'gass',
            'financials' => [],
            'financialAccomplishments' => [],
        ]);

        $this->assertStringContainsString('locked-change-request', $html);
        $this->assertStringContainsString("input.readOnly = true;", $html);
        $this->assertStringContainsString('id="lockedMonthEditConfirmModal"', $html);
        $this->assertStringContainsString('Edit Locked Accomplishment', $html);
        $this->assertStringContainsString('fa-lock-open me-1"></i> Yes', $html);
        $this->assertStringNotContainsString('Yes, Edit Month', $html);
        $this->assertStringContainsString('modal-header bg-primary text-white', $html);
        $this->assertStringContainsString('btn btn-primary" id="confirmLockedMonthEditBtn', $html);
        $this->assertStringContainsString('for="lockedMonthEditReason"', $html);
        $this->assertStringContainsString('id="lockedMonthEditReason"', $html);
        $this->assertStringContainsString('Reason for change', $html);
        $this->assertStringContainsString('Please enter a reason before continuing.', $html);
        $this->assertStringContainsString('Are you sure you want to edit this locked accomplishment month?', $html);
        $this->assertStringNotContainsString('This reason will be submitted for Regional Office/admin review when you save.', $html);
        $this->assertStringNotContainsString('Explain why this locked accomplishment must be changed', $html);
        $this->assertStringContainsString('bootstrap.Modal.getOrCreateInstance(lockedMonthModalElement)', $html);
        $this->assertStringContainsString("document.getElementById('confirmLockedMonthEditBtn')", $html);
        $this->assertStringContainsString("input.dataset.lockedEditConfirmed = '1'", $html);
        $this->assertStringContainsString('lockedChangeReasons.set(reasonKey, reason)', $html);
        $this->assertStringNotContainsString("window.confirm('This month is locked.", $html);
        $this->assertStringContainsString('entryChangesLockedMonth', $html);
        $this->assertStringContainsString("change_reason: lockedChangeReasons.get(reasonKey) || ''", $html);
        $this->assertStringNotContainsString('window.prompt(', $html);
        $this->assertStringContainsString('Locked-month change request submitted for Regional Office/admin approval.', $html);
    }

    public function test_admin_and_regional_office_bypass_locked_month_inputs_and_do_not_receive_the_dialog(): void
    {
        foreach (['admin', 'ro-office'] as $role) {
            $this->actingAs(new User(['role' => $role]));

            $html = (string) $this->view('components.financial_input_persistence', [
                'financialSector' => 'gass',
                'financials' => [],
                'financialAccomplishments' => [],
            ]);

            $this->assertStringNotContainsString('id="lockedMonthEditConfirmModal"', $html);
            $this->assertStringContainsString('bypassLockedMonths: true', $html);
            $this->assertStringContainsString('canRequestLockedChanges: false', $html);
        }
    }
}
