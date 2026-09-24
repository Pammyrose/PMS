<?php

namespace App\Providers;

use App\Models\FinancialAccomplishment;
use App\Models\FinancialTarget;
use App\Models\Office;
use App\Models\PhysicalAccomplishment;
use App\Models\PhysicalTarget;
use App\Services\NotificationCountService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom([
            database_path('migrations/core'),
            database_path('migrations/sectors'),
            database_path('migrations/features'),
        ]);

        View::composer('components.sidebar', function ($view) {
            $user = auth()->user();
            $view->with(app(NotificationCountService::class)->for($user));
        });

        View::composer([
            'admin.*.*_physical',
            'regional.*.*_physical',
            'penro.*.*_physical',
            'users.*.*_physical',
        ], function ($view) {
            $nameParts = explode('.', $view->getName());
            $sector = strtolower((string) ($nameParts[1] ?? ''));
            $year = (int) ($view->getData()['year'] ?? now()->year);
            $officeId = (int) ($view->getData()['office_id'] ?? 0);

            $scopeToOffice = auth()->user()
                && ! auth()->user()->isAdmin()
                && ! auth()->user()->isRegionalOffice();
            $officeIds = [$officeId];

            if (auth()->user()?->isPenro()) {
                $penroServiceAreaOfficeIds = Office::serviceAreaOfficeIdsForPenro($officeId);

                if (! empty($penroServiceAreaOfficeIds)) {
                    $officeIds = $penroServiceAreaOfficeIds;
                }
            }

            $financialTargets = FinancialTarget::query()
                ->where('sector', $sector)
                ->where('year', $year)
                ->when(
                    $scopeToOffice,
                    fn ($query) => $query->whereIn('office_id', $officeIds)
                )
                ->get();
            $financialAccomplishmentRows = FinancialAccomplishment::query()
                ->where('sector', $sector)
                ->where('year', $year)
                ->when(
                    $scopeToOffice,
                    fn ($query) => $query->whereIn('office_id', $officeIds)
                )
                ->get();

            $reduceFinancialRows = static function ($rows): array {
                return $rows->reduce(function (array $carry, $row) {
                    $officeKey = (string) ((int) ($row->office_id ?? 0));
                    $carry[(string) $row->row_id][(string) $row->indicator_id][$officeKey] = $row->toArray();

                    return $carry;
                }, []);
            };

            $financials = $reduceFinancialRows($financialTargets);
            $financialAccomplishments = $reduceFinancialRows($financialAccomplishmentRows);

            $reducePhysicalRows = static function ($rows): array {
                return $rows->reduce(function (array $carry, $row) {
                    $officeKey = (string) ((int) ($row->office_id ?? 0));
                    $carry[(string) $row->row_id][(string) $row->indicator_id][$officeKey] = $row->toArray();

                    return $carry;
                }, []);
            };

            $physicalTargets = Schema::hasTable('physical_targets')
                ? $reducePhysicalRows(
                    PhysicalTarget::query()
                        ->where('sector', $sector)
                        ->where('year', $year)
                        ->when($scopeToOffice, fn ($query) => $query->whereIn('office_id', $officeIds))
                        ->get()
                )
                : ($view->getData()['targets'] ?? []);

            $physicalAccomplishments = Schema::hasTable('physical_accomplishments')
                ? $reducePhysicalRows(
                    PhysicalAccomplishment::query()
                        ->where('sector', $sector)
                        ->where('year', $year)
                        ->when($scopeToOffice, fn ($query) => $query->whereIn('office_id', $officeIds))
                        ->get()
                )
                : ($view->getData()['accomplishments'] ?? []);

            $view->with('financialSector', $sector);
            $view->with('financials', $financials);
            $view->with('financialAccomplishments', $financialAccomplishments);
            $view->with('targets', $physicalTargets);
            $view->with('accomplishments', $physicalAccomplishments);
        });
    }
}
