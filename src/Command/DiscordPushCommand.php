<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace App\Command;

use App\Entity\DiscordRole;
use App\Error\DiscordHandler;
use App\Eve\CharacterProcessor;
use App\Repository\AllianceRepository;
use App\Repository\CharacterRepository;
use App\Repository\CorporationRepository;
use App\Repository\DiscordRoleRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DiscordPushCommand extends Command
{
    const BASE_URI = 'https://discord.com/api';
    const VERSION = 'v6';
    protected static $defaultName = 'app:discord:push';

    /**
     * @var Client|null
     */
    protected ?Client $client = null;
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
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

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
        LoggerInterface $logger,
        string $name = null)
    {
        parent::__construct($name);
        $this->characterRepository = $characterRepository;
        $this->corporationRepository = $corporationRepository;
        $this->allianceRepository = $allianceRepository;
        $this->discordRoleRepository = $discordRoleRepository;
        $this->errorHandler = $errorHandler;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription('Pushes updates to discord');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
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
                    '/api/' . self::VERSION . '/guilds/' . $_ENV['GUILD_ID'] . '/members?limit=1000&after=' . $latest
                );
                $response = json_decode($response->getBody(), true);
                $latest = $this->push($response);
            } while (count($response) === 1000);

            return 0;
        } catch (GuzzleException $e) {
            $this->errorHandler->error([
                'title' => 'Error while running app:discord:push:',
                'description' => $e->getMessage()
            ]);
            return 1;
        }
    }

    /**
     * @param $response
     * @return mixed|string
     * @throws GuzzleException
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
                    // User has roles to purge
                    if (count($user['roles'])) {
                        $this->patchUser($user['user']['id'],$user['user']['username'],[]);
                    }
                    continue;
                }
                /** @noinspection DuplicatedCode */
                $characterProcessor = new CharacterProcessor(
                    $this->characterRepository,
                    $this->corporationRepository,
                    $this->allianceRepository,
                    $this->discordRoleRepository
                );
                $characterData = $characterProcessor->getInfo($character->getUid(),$character->getName());
                $roles = $characterProcessor->getRolesArray($characterData);
                $roleArray = [];
                /** @var DiscordRole $role */
                foreach ($roles as $role) {
                    $roleArray[] = $role->getUid();
                }
                $name = $characterProcessor->getName($characterData);

                //Only run patch if data is different
                if (count(array_merge(array_diff($roleArray,$user['roles']),array_diff($user['roles'],$roleArray))) || $name !== $user['nick']) {
                    $this->patchUser($user['user']['id'],$name,$roleArray);
                }


            }
            $latest = $user['user']['id'];
        }
        return $latest;
    }

    private function patchUser($id, $nick, $roles)
    {
        $this->logger->debug('Patching '.$id.' to name '.$nick.' and roles '.implode(',',$roles));
        $this->client->request(
            'PATCH',
            '/api/'.self::VERSION.'/guilds/' . $_ENV['GUILD_ID'] . '/members/'.$id,
            [
                'json' =>  [
                    'nick' => $nick,
                    'roles' => $roles
                ]
            ]
        );
    }
}
