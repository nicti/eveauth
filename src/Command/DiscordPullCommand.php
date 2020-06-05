<?php

namespace App\Command;

use App\Entity\DiscordRole;
use App\Repository\DiscordRoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DiscordPullCommand extends Command
{
    const BASE_URI = 'https://discord.com/api';
    const VERSION = 'v6';
    protected static $defaultName = 'app:discord:pull';

    protected $client = null;
    /**
     * @var DiscordRoleRepository
     */
    private DiscordRoleRepository $discordRoleRepository;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * DiscordPullCommand constructor.
     * @param DiscordRoleRepository $discordRoleRepository
     * @param EntityManagerInterface $entityManager
     * @param string|null $name
     */
    public function __construct(DiscordRoleRepository $discordRoleRepository,EntityManagerInterface $entityManager, string $name = null)
    {
        parent::__construct($name);
        $this->discordRoleRepository = $discordRoleRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Pulls data from discord')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (is_null($this->client)) {
            $this->client = new Client([
                'base_uri' => sprintf('%s', rtrim(self::BASE_URI, '/')),
                'headers' => [
                    'Authorization' => sprintf('Bot %s', $_ENV['BOT_TOKEN']),
                    'Content-Type'  => 'application/json',
                ],
            ]);
        }
        $response = $this->client->request('GET','/api/'.self::VERSION.'/guilds/'.$_ENV['GUILD_ID'].'/roles');
        $response = json_decode($response->getBody(),true);
        foreach ($response as $role) {
            $dRole = $this->discordRoleRepository->findOneBy(['uid' => $role['id']]);
            if ($dRole) {
                $dRole->setName($role['name']);
            } else {
                $dRole = new DiscordRole();
                $dRole->setUid($role['id']);
                $dRole->setName($role['name']);
            }
            $this->entityManager->persist($dRole);
            $this->entityManager->flush();
        }
        return 0;
    }
}
