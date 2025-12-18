<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:mail:test',
    description: 'Send a test email via configured mailer.'
)]
class TestMailCommand extends Command
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('recipient', InputArgument::REQUIRED, 'Recipient email address')
            ->setHelp('This command sends a test email to the given recipient.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $recipient = $input->getArgument('recipient');
        $from = $_ENV['MAILER_FROM'] ?? 'noreply@helpdreams.pl';

        $email = (new Email())
            ->from($from)
            ->to($recipient)
            ->subject('Test email from HelpDreams')
            ->text('This is a test email sent via Symfony Mailer.')
            ->html('<p>This is a <strong>test email</strong> sent via Symfony Mailer.</p>');

        try {
            $this->mailer->send($email);
            $output->writeln('Test email sent to ' . $recipient);
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
