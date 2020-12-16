<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace App\Command;

use App\Error\DiscordHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DiscordReportCommand extends Command
{

    protected static $defaultName = 'app:discord:report';
    /**
     * @var DiscordHandler
     */
    private DiscordHandler $discordHandler;

    /**
     * DiscordPullCommand constructor.
     * @param DiscordHandler $discordHandler
     * @param string|null $name
     */
    public function __construct(DiscordHandler $discordHandler,string $name = null)
    {
        parent::__construct($name);
        $this->discordHandler = $discordHandler;
    }

    protected function configure()
    {
        $this
            ->setDescription('Tests error pushing to discord')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->discordHandler->info([
            'title' => 'Service info',
            'description' => 'This is a test to confirm the connection is working!'
        ]);
        return 0;
    }
}
