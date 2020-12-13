<?php

namespace App\Eve;

use App\Controller\AdminController;
use App\Error\DiscordHandler;
use App\Repository\AllianceRepository;
use App\Repository\CharacterRepository;
use App\Repository\CorporationRepository;
use App\Repository\DiscordRoleRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class CharacterProcessor
{
    const BASE_URI = 'https://esi.evetech.net/';
    protected string $characterID   = '';
    protected string $characterName = '';

    protected string $corpID        = '';
    protected string $corpName      = '';
    protected string $corpTicker    = '';

    protected string $allianceID    = '';
    protected string $allianceName  = '';
    protected string $allianceTicker= '';

    protected array $idCache = [
        '' => [
            'name' => 'None',
            'ticker' => ''
        ]
    ];
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
     * CharacterProcessor constructor.
     * @param CharacterRepository $characterRepository
     * @param CorporationRepository $corporationRepository
     * @param AllianceRepository $allianceRepository
     * @param DiscordRoleRepository $discordRoleRepository
     * @param DiscordHandler $errorHandler
     */
    public function __construct(
        CharacterRepository $characterRepository,
        CorporationRepository $corporationRepository,
        AllianceRepository $allianceRepository,
        DiscordRoleRepository $discordRoleRepository,
        DiscordHandler $errorHandler
    )
    {
        $this->characterRepository = $characterRepository;
        $this->corporationRepository = $corporationRepository;
        $this->allianceRepository = $allianceRepository;
        $this->discordRoleRepository = $discordRoleRepository;
        $this->errorHandler = $errorHandler;
    }

    protected ?Client $client = null;

    /**
     * @param $characterID
     * @param $characterName
     */
    public function getInfo($characterID, $characterName) {
        $this->characterID = $characterID;
        $this->characterName = $characterName;

        $this->corpName = '';
        $this->corpID = '';
        $this->corpTicker = '';

        $this->allianceName = '';
        $this->allianceID = '';
        $this->allianceTicker = '';

        if (!$this->client) {
            $this->client = new Client([
                'base_uri' => sprintf('%s', rtrim(self::BASE_URI, '/')),
                'headers' => [
                    'Content-Type'  => 'application/json',
                ],
            ]);
        }
        try {
            $response = $this->client->request('GET','/v4/characters/'.$characterID.'/');
        } catch (RequestException $requestException) {
            $this->errorHandler->error([
                'title' => 'HTTP error '.$requestException->getCode(),
                'description' => "Encountered an http error while requesting corporations data:\r".$requestException->getMessage()
            ]);
            return null;
        }
        $response = json_decode($response->getBody(),true);

        $this->corpID = $response['corporation_id']??'';
        $this->allianceID = $response['alliance_id']??'';

        if (!isset($this->idCache[$this->corpID])) {
            try {
                $corpResponse = $this->client->request('GET','/v4/corporations/'.$this->corpID.'/');
            } catch (RequestException $requestException) {
                $this->errorHandler->error([
                    'title' => 'HTTP error '.$requestException->getCode(),
                    'description' => "Encountered an http error while requesting corporations data:\r".$requestException->getMessage()
                ]);
                return null;
            }
            $corpResponse = json_decode($corpResponse->getBody(),true);
            $this->idCache[$this->corpID] = array(
                'name' => $corpResponse['name'],
                'ticker' => $corpResponse['ticker']
            );
        }
        $this->corpName = $this->idCache[$this->corpID]['name'];
        $this->corpTicker = $this->idCache[$this->corpID]['ticker'];

        if (!isset($this->idCache[$this->allianceID])) {
            try {
                $allianceResponse = $this->client->request('GET','/v3/alliances/'.$this->allianceID.'/');
            } catch (RequestException $requestException) {
                $this->errorHandler->error([
                    'title' => 'HTTP error '.$requestException->getCode(),
                    'description' => "Encountered an http error while requesting alliances data:\r".$requestException->getMessage()
                ]);
                return null;
            }
            $allianceResponse = json_decode($allianceResponse->getBody(),true);
            $this->idCache[$this->allianceID] = array(
                'name' => $allianceResponse['name'],
                'ticker' => $allianceResponse['ticker']
            );
        }
        $this->allianceName = $this->idCache[$this->allianceID]['name'];
        $this->allianceTicker = $this->idCache[$this->allianceID]['ticker'];

        return [
            'char' => [
                'id' => $this->characterID,
                'name' => $this->characterName
            ],
            'corp' => [
                'id' => $this->corpID,
                'name' => $this->corpName,
                'ticker' => $this->corpTicker
            ],
            'alli' => [
                'id' => $this->allianceID,
                'name' => $this->allianceName,
                'ticker' => $this->allianceTicker
            ]
        ];
    }

    public function getRolesArray(array $data)
    {
        $character = $this->characterRepository->findOneBy(['uid' => $data['char']['id']]);
        $corporation = $this->corporationRepository->findOneBy(['uid' => $data['corp']['id']]);
        $alliance = $this->allianceRepository->findOneBy(['uid' => $data['alli']['id']]);
        $roles = [];
        if ($character) {
            foreach ($character->getRoles() as $role) {
                $roles[] = $role;
            }
            // Add Registered Role to all registered characters
            $roles[] = $this->discordRoleRepository->findOneBy(['Name' => AdminController::REGISTERED]);
        }
        if ($corporation) {
            foreach ($corporation->getRoles() as $role) {
                $roles[] = $role;
            }
        }
        if ($alliance) {
            foreach ($alliance->getRoles() as $role) {
                $roles[] = $role;
            }
        }
        return array_unique($roles);
    }

    public function getName(array $data)
    {
        if ($data['alli']['id'] !== '') {
            return '['.$data['alli']['ticker'].'] '.$data['char']['name'];
        }
        return '['.$data['corp']['ticker'].'] '.$data['char']['name'];
    }


}