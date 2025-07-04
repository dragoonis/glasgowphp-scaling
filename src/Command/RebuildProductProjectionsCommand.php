<?php

namespace App\Command;

use App\Entity\Product;
use App\Projection\ProductDbProjectionBuilder;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Projection\ProductProjectionService;
use App\Projection\ProductDbProjectionService;

#[AsCommand(
    name: 'app:rebuild-product-projections',
    description: 'Rebuilds all product DB projections from the main Product table.',
)]
class RebuildProductProjectionsCommand extends Command
{
    public function __construct(
        private readonly ProductProjectionService $redisProjectionService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Rebuilding Redis product projections...');
        $this->redisProjectionService->rebuildAll();
        $output->writeln('Done!');
        return Command::SUCCESS;
    }
} 