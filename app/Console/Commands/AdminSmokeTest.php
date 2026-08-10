<?php

namespace App\Console\Commands;

use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

/**
 * Renders every Filament resource page and relation manager as the admin user.
 * A resource that compiles but throws at render is the failure mode that looks
 * like success, so this exercises the actual Livewire components.
 */
class AdminSmokeTest extends Command
{
    protected $signature = 'admin:smoke {--user=1}';

    protected $description = 'Render every admin resource page and report failures';

    public function handle(): int
    {
        Auth::loginUsingId((int) $this->option('user'));
        Filament::setCurrentPanel('admin');

        $failures = 0;

        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            // Use the resource's own query so role-scoped resources (Customers)
            // are exercised with a record they can actually resolve.
            $record = $resource::getEloquentQuery()->first();

            foreach ($resource::getPages() as $key => $registration) {
                if (in_array($key, ['view', 'edit'], true) && $record === null) {
                    continue;
                }

                $params = in_array($key, ['view', 'edit'], true)
                    ? ['record' => $record->getRouteKey()]
                    : [];

                $label = class_basename($resource).'::'.$key;

                try {
                    Livewire::test($registration->getPage(), $params);
                    $this->line("<fg=green>OK  </> {$label}");
                } catch (\Throwable $e) {
                    $failures++;
                    $this->line("<fg=red>FAIL</> {$label} — ".substr($e->getMessage(), 0, 200));
                }
            }

            foreach ($resource::getRelations() as $relation) {
                if ($record === null) {
                    continue;
                }

                $label = class_basename($resource).' → '.class_basename($relation);

                $pages = $resource::getPages();
                $host = $pages['view'] ?? $pages['edit'] ?? null;

                if ($host === null) {
                    continue;
                }

                try {
                    Livewire::test($relation, [
                        'ownerRecord' => $record,
                        'pageClass' => $host->getPage(),
                    ]);
                    $this->line("<fg=green>OK  </> {$label}");
                } catch (\Throwable $e) {
                    $failures++;
                    $this->line("<fg=red>FAIL</> {$label} — ".substr($e->getMessage(), 0, 200));
                }
            }
        }

        $this->newLine();
        $this->{$failures === 0 ? 'info' : 'error'}("Failures: {$failures}");

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }
}
