<?php

namespace App\Console\Commands;

use App\Models\Accounting\FeeStructure;
use App\Services\Accounting\StudentBillingService;
use Illuminate\Console\Command;

class GenerateTermBilling extends Command
{
    protected $signature = 'accounting:generate-term-billing {structure_id} {--class=} {--section=}';
    protected $description = 'Generate invoices for an entire fee structure (term billing)';

    public function __construct(protected StudentBillingService $billing)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $structure = FeeStructure::with('items')->find($this->argument('structure_id'));

        if (! $structure) {
            $this->error('Fee structure not found.');
            return self::FAILURE;
        }

        $filters = array_filter([
            'class_id' => $this->option('class'),
            'section_id' => $this->option('section'),
        ]);

        $invoices = $this->billing->generateForStructure($structure, $filters);

        $this->info(sprintf('Generated %d invoices for %s', count($invoices), $structure->name));

        return self::SUCCESS;
    }
}
