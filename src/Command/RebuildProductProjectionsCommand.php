<?php

namespace App\Command;

use App\Projection\ProductProjectionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:rebuild-product-projections',
    description: 'Rebuild product projections from database',
)]
final class RebuildProductProjectionsCommand extends Command
{
    public function __construct(
        private readonly ProductProjectionService $projectionService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Rebuilding Product Projections');

        $io->section('Clearing existing projections...');
        $this->projectionService->rebuildAll();

        $io->success('Product projections rebuilt successfully!');

        return Command::SUCCESS;
    }
} 