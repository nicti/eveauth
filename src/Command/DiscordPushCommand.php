<?php

namespace App\Command;

use App\Entity\DiscordRole;
use App\Error\DiscordHandler;
use App\Eve\CharacterProcessor;
use App\Repository\AllianceRepository;
use App\Repository\CharacterRepository;
use App\Repository\CorporationRepository;
use App\Repository\DiscordRoleRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DiscordPushCommand extends Command
{
    const BASE_URI = 'https://discord.com/api';
    const VERSION = 'v6';
    protected static $defaultName = 'app:discord:push';

    protected $client = null;
    /**
     * @var CharacterRepository
     */
    private CharacterRepository $characterRepository;
    /**
     * @var CorporationRepository
     */
    private CorporationRepository $corporationRepository;
    /**
     * @var AllianceRepository
     */
    private AllianceRepository $allianceRepository;
    /**
     * @var DiscordRoleRepository
     */
    private DiscordRoleRepository $discordRoleRepository;
    /**
     * @var DiscordHandler
     */
    private DiscordHandler $errorHandler;

    /**
     * DiscordPushCommand constructor.
     * @param CharacterRepository $characterRepository
     * @param CorporationRepository $corporationRepository
     * @param AllianceRepository $allianceRepository
     * @param DiscordRoleRepository $discordRoleRepository
     * @param DiscordHandler $errorHandler
     * @param string|null $name
     */
    public function __construct(
        CharacterRepository $characterRepository,
        CorporationRepository $corporationRepository,
        AllianceRepository $allianceRepository,
        DiscordRoleRepository $discordRoleRepository,
        DiscordHandler $errorHandler,
        string $name = null)
    {
        parent::__construct($name);
        $this->characterRepository = $characterRepository;
        $this->corporationRepository = $corporationRepository;
        $this->allianceRepository = $allianceRepository;
        $this->discordRoleRepository = $discordRoleRepository;
        $this->errorHandler = $errorHandler;
    }

    protected function configure()
    {
        $this
            ->setDescription('Pushes updates to discord');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (is_null($this->client)) {
            $this->client = new Client([
                'base_uri' => sprintf('%s', rtrim(self::BASE_URI, '/')),
                'headers' => [
                    'Authorization' => sprintf('Bot %s', $_ENV['BOT_TOKEN']),
                    'Content-Type' => 'application/json',
                ],
            ]);
        }
        
        $latest = 0;
        do {
            $response = $this->client->request(
                'GET',
                '/api/' . self::VERSION . '/guilds/' . $_ENV['GUILD_ID'] . '/members?limit=1000&after='.$latest
            );
            $response = json_decode($response->getBody(), true);
            $latest = $this->push($response);
        }
        while (count($response) === 1000);

        return 0;
    }

    /**
     * @param $response
     * @return mixed|string
     */
    private function push($response)
    {
        $latest = '';
        foreach ($response as $user) {
            if (isset($user['user']['bot']) && $user['user']['bot'] === true) {
                //do not handle bots
                continue;
            } else if($user['user']['id'] == $_ENV['GUILD_OWNER']){
                //do not handle owner
                continue;
            } else {
                $character = $this->characterRepository->findOneBy(['DiscordId' => $user['user']['id']]);
                if ($character === null) {
                    //skip users who are not auth'd
                    continue;
                }
                $characterProcessor = new CharacterProcessor(
                    $this->characterRepository,
                    $this->corporationRepository,
                    $this->allianceRepository,
                    $this->discordRoleRepository,
                    $this->errorHandler
                );
                $characterData = $characterProcessor->getInfo($character->getUid(),$character->getName());
                $roles = $characterProcessor->getRolesArray($characterData);
                $roleArray = [];
                /** @var DiscordRole $role */
                foreach ($roles as $role) {
                    $roleArray[] = $role->getUid();
                }
                try {
                    $this->client->request(
                        'PATCH',
                        '/api/'.self::VERSION.'/guilds/' . $_ENV['GUILD_ID'] . '/members/'.$user['user']['id'],
                        [
                            'json' =>  [
                                'nick' => $characterProcessor->getName($characterData),
                                'roles' => $roleArray
                            ]
                        ]
                    );
                } catch (RequestException $exception) {
                    $this->errorHandler->error([
                        'title' => 'HTTP error '.$exception->getCode(),
                        'description' => $exception->getMessage()
                    ]);
                }

            }
            $latest = $user['user']['id'];
        }
        return $latest;
    }
}
