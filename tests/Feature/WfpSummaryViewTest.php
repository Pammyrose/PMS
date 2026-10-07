<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\PhysicalPerformanceSummaryLayout;
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
            ->assertSee('"label":"Target"', false)
            ->assertSee('Financial Target', false)
            ->assertSee('"label":"Accomp"', false)
            ->assertSee('%Accomp', false)
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

    public function test_physical_accomplishment_omits_annual_while_other_summary_sections_keep_their_periods(): void
    {
        $html = (string) $this->view('components.financial_input_persistence', [
            'financialSector' => 'gass',
            'financials' => [],
            'financialAccomplishments' => [],
        ]);

        $sections = collect(PhysicalPerformanceSummaryLayout::sections())->keyBy('key');
        $this->assertSame(
            ['annual', 'quarter', 'to-date'],
            array_column($sections['physical-target']['periods'], 'key')
        );
        $this->assertSame(
            ['quarter', 'to-date'],
            array_column($sections['physical-accomplishment']['periods'], 'key')
        );
        $this->assertStringContainsString('const physicalSummarySections =', $html);
        $this->assertStringContainsString('...physicalSummarySections.map(section => ({', $html);
        $this->assertStringContainsString('const summaryPeriodsForSection = section => section.periods || summaryPeriods', $html);
        $this->assertStringContainsString('summaryGroup.colSpan = sectionPeriods.length', $html);
        $this->assertStringContainsString('summaryGroup.textContent = section.label', $html);

        foreach ([
            'physical-target',
            'physical-accomplishment',
            'physical-percentage',
            'financial-target',
            'financial-accomplishment',
            'financial-bur',
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

    public function test_financial_accomplishment_summary_omits_the_annual_column(): void
    {
        $html = (string) $this->view('components.financial_input_persistence', [
            'financialSector' => 'gass',
            'financials' => [],
            'financialAccomplishments' => [],
        ]);

        $this->assertStringContainsString(
            'const financialAccomplishmentSummaryPeriods = financialTargetSummaryPeriods',
            $html
        );
        $this->assertMatchesRegularExpression(
            "/key:\s*'financial-accomplishment',[\s\S]*?periods:\s*financialAccomplishmentSummaryPeriods/",
            $html
        );
    }

    public function test_financial_performance_uses_allotment_obligation_and_disbursement_labels(): void
    {
        $html = (string) $this->view('components.financial_input_persistence', [
            'financialSector' => 'gass',
            'financials' => [],
            'financialAccomplishments' => [],
        ]);

        $this->assertStringContainsString("? 'Allotment'", $html);
        $this->assertStringContainsString("? 'This Quarter'", $html);
        $this->assertMatchesRegularExpression(
            "/key:\s*'financial-target',[\s\S]*?label:\s*'Obligation'/",
            $html
        );
        $this->assertMatchesRegularExpression(
            "/key:\s*'financial-accomplishment',[\s\S]*?label:\s*'Disbursement'/",
            $html
        );
    }

    public function test_financial_performance_includes_the_three_budget_utilization_rates(): void
    {
        $html = (string) $this->view('components.financial_input_persistence', [
            'financialSector' => 'gass',
            'financials' => [],
            'financialAccomplishments' => [],
        ]);

        $this->assertStringContainsString("label: '% Budget Utilization Rate (BUR)'", $html);
        $this->assertStringContainsString("label: '(Oblig/Allot) *100'", $html);
        $this->assertStringContainsString("label: '(Disb/Allot) *100'", $html);
        $this->assertStringContainsString("label: '(Disb/Oblig) *100'", $html);
        $this->assertStringContainsString('const financialBurValue =', $html);
        $this->assertStringContainsString('return denominator > 0 ? (numerator / denominator) * 100 : 0', $html);
        $this->assertStringContainsString('if (section.isFinancialPercentage)', $html);
    }

    public function test_physical_percentage_columns_compare_to_date_accomplishment_with_to_date_and_annual_targets(): void
    {
        $html = (string) $this->view('components.financial_input_persistence', [
            'financialSector' => 'gass',
            'financials' => [],
            'financialAccomplishments' => [],
        ]);

        $percentageSection = collect(PhysicalPerformanceSummaryLayout::sections())
            ->firstWhere('key', 'physical-percentage');
        $this->assertSame('%Accomp', $percentageSection['label']);
        $this->assertSame('to-date', $percentageSection['periods'][0]['denominatorPeriod']);
        $this->assertSame('annual', $percentageSection['periods'][1]['denominatorPeriod']);
        $this->assertStringContainsString('"key":"physical-percentage"', $html);
        $this->assertStringContainsString('"label":"%Accomp"', $html);
        $this->assertStringContainsString('const physicalPercentageValue = (row, period, officeId = \'\', aggregateOfficeIds = []) =>', $html);
        $this->assertStringContainsString('return target > 0 ? (accomplishment / target) * 100 : 0;', $html);
        $this->assertStringContainsString('formatSummaryPercentage', $html);
        $this->assertStringContainsString("input.type = section.isPercentage ? 'text' : 'number'", $html);
    }

    public function test_physical_performance_title_is_a_separate_row_above_the_summary_groups(): void
    {
        $html = (string) $this->view('components.financial_input_persistence', [
            'financialSector' => 'gass',
            'financials' => [],
            'financialAccomplishments' => [],
        ]);

        $this->assertStringContainsString('title.textContent = "Physical Performance"', $html);
        $this->assertStringContainsString("title.colSpan = physicalColumns", $html);
        $this->assertStringContainsString("financialTitle.colSpan = financialColumns", $html);
        $this->assertStringContainsString("financialTitle.textContent = 'Financial Performance'", $html);
        $this->assertStringContainsString("financialTitle.className = 'financial-performance-title'", $html);
        $this->assertStringContainsString('groupHeader.parentNode.insertBefore(titleRow, groupHeader)', $html);
        $this->assertStringContainsString('showPhysicalPerformanceTitleRow(table, headerRow, groupRow)', $html);
        $this->assertStringContainsString('removePhysicalPerformanceTitleRow(table)', $html);
        $this->assertStringContainsString('#performanceTable .physical-performance-title-row + tr.group-row th', $html);
        $this->assertStringContainsString('var(--physical-performance-title-row-height, 28px)', $html);
        $this->assertStringContainsString("'--physical-performance-title-row-height'", $html);
        $this->assertStringContainsString('background: transparent !important;', $html);
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

    public function test_financial_summary_populates_the_default_car_ro_and_province_row(): void
    {
        $html = (string) $this->view('components.financial_input_persistence', [
            'financialSector' => 'gass',
            'financials' => [],
            'financialAccomplishments' => [],
        ]);

        $this->assertStringContainsString('addDefaultFinancialSummaryCells()', $html);
        $this->assertStringContainsString("section.key.startsWith('financial-')", $html);
        $this->assertStringContainsString(
            "input.className = 'month-box summary-box program-financial-summary-box'",
            $html
        );
        $this->assertStringContainsString("input.dataset.defaultFinancialUnit = officeUnit", $html);
        $this->assertStringContainsString('refreshDefaultFinancialSummaryInputs()', $html);
        $this->assertStringContainsString('const officeIdsForDefaultFinancialUnit =', $html);
        $this->assertStringContainsString('summaryAggregateValue(row, section, officeIds, period)', $html);
    }

    public function test_financial_summary_inputs_only_render_in_the_default_office_row(): void
    {
        $html = (string) $this->view('components.financial_input_persistence', [
            'financialSector' => 'gass',
            'financials' => [],
            'financialAccomplishments' => [],
        ]);

        $this->assertStringContainsString("cell.classList.add('financial-summary-detail-empty')", $html);
        $this->assertStringContainsString("if (section.key.startsWith('financial-'))", $html);
        $this->assertStringContainsString(
            "document.querySelectorAll('#performanceTable tbody tr.default-office-unit-row')",
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
