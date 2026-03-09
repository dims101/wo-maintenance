<?php

namespace App\Console\Commands;

use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoCloseWorkOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-close-work-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $workOrders = WorkOrder::with([
            'maintenanceApproval.teamAssignments',
        ])
            ->where('status', 'Requested to be closed')
            ->where('updated_at', '<=', Carbon::now()->subDay())
            ->whereHas('maintenanceApproval', function ($q) {
                $q->whereNull('is_closed');
            })
            ->get();

        foreach ($workOrders as $wo) {

            $approval = $wo->maintenanceApproval;

            if (! $approval) {
                continue;
            }

            $teamAssignments = $approval->teamAssignments;

            if ($teamAssignments->isEmpty()) {
                continue;
            }

            // earliest start
            $startDate = $teamAssignments->min('start_date');

            // latest finish
            $finishDate = $teamAssignments->max('finish_date');

            // update maintenance approval
            $approval->update([
                'start' => $startDate,
                'finish' => $finishDate,
                'is_closed' => true,
            ]);

            // update work order
            $wo->update([
                'status' => 'Closed',
            ]);
        }

        $this->info('Auto close work orders executed successfully.');
    }
}
