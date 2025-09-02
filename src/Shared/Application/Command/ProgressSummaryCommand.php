<?php

namespace App\Shared\Application\Command;

use App\Users\Application\Queries\GetProgressForUserQuery;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'progress:summary',
    description: 'Display user progress summary for a course'
)]
final class ProgressSummaryCommand extends Command
{
    public function __construct(
        private readonly GetProgressForUserQuery $getUserProgressQuery
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('userId', InputArgument::REQUIRED, 'User ID')
            ->addArgument('courseId', InputArgument::REQUIRED, 'Course ID')
            ->setHelp('This command displays user progress summary for a specific course.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userId = (int) $input->getArgument('userId');
        $courseId = (int) $input->getArgument('courseId');

        try {
            $progressData = $this->getUserProgressQuery->execute($userId, $courseId);

            $completed = $progressData['completed'];
            $total = $progressData['total'];
            $percent = $progressData['percent'];

            $summary = sprintf('%d/%d (%d%%)', $completed, $total, $percent);
            $io->success($summary);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
