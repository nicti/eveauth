<?php

namespace App\Eve;

use App\Repository\AllianceRepository;
use App\Repository\CharacterRepository;
use App\Repository\CorporationRepository;
use GuzzleHttp\Client;

class CharacterProcessor
{
    const BASE_URI = 'https://esi.evetech.net/';
    protected string $characterID   = '';
    protected string $characterName = '';

    protected string $corpID        = '';
    protected string $corpName      = '';

    protected string $allianceID    = '';
    protected string $allianceName  = '';

    protected array $idCache = [
        '' => 'None'
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
     * CharacterProcessor constructor.
     * @param CharacterRepository $characterRepository
     * @param CorporationRepository $corporationRepository
     * @param AllianceRepository $allianceRepository
     */
    public function __construct(
        CharacterRepository $characterRepository,
        CorporationRepository $corporationRepository,
        AllianceRepository $allianceRepository
    )
    {
        $this->characterRepository = $characterRepository;
        $this->corporationRepository = $corporationRepository;
        $this->allianceRepository = $allianceRepository;
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

        $this->allianceName = '';
        $this->allianceID = '';

        if (!$this->client) {
            $this->client = new Client([
                'base_uri' => sprintf('%s', rtrim(self::BASE_URI, '/')),
                'headers' => [
                    'Content-Type'  => 'application/json',
                ],
            ]);
        }

        $response = $this->client->request('GET','/v4/characters/'.$characterID.'/');
        $response = json_decode($response->getBody(),true);

        $this->corpID = $response['corporation_id']??'';
        $this->allianceID = $response['alliance_id']??'';

        if (isset($this->idCache[$this->corpID])) {
            $this->corpName = $this->idCache[$this->corpID];
        }
        if (isset($this->idCache[$this->allianceID])) {
            $this->allianceName = $this->idCache[$this->allianceID];
        }
        if ($this->corpName == '' && $this->allianceName != '') {
            $ids = '['.$this->corpID.']';
        } else if ($this->corpName != '' && $this->allianceName == '') {
            $ids = '['.$this->allianceID.']';
        } else if($this->corpName == '' && $this->allianceName == '') {

            $ids = '['.$this->corpID.','.$this->allianceID.']';
        } else {
            $ids = '';
        }

        if ($ids != '') {
            $response = $this->client->request('POST','/v3/universe/names/',[
                'body' => $ids
            ]);
            $response = json_decode($response->getBody(),true);
            foreach ($response as $name) {
                if ($name['category'] == 'corporation') {
                    $this->corpName = $name['name'];
                }
                if ($name['category'] == 'alliance') {
                    $this->allianceName = $name['name'];
                }
            }
        }
        return [
            'char' => [
                'id' => $this->characterID,
                'name' => $this->characterName
            ],
            'corp' => [
                'id' => $this->corpID,
                'name' => $this->corpName
            ],
            'alli' => [
                'id' => $this->allianceID,
                'name' => $this->allianceName
            ]
        ];
    }

    public function getRolesArray(array $data)
    {
        $character = $this->characterRepository->findOneBy(['uid' => $data['char']['id']]);
        $corporation = $this->corporationRepository->findOneBy(['uid' => $data['corp']['id']]);
        $alliance = $this->allianceRepository->findOneBy(['uid' => $data['alli']['id']]);
        $roles = [];
        foreach ($character->getRoles() as $role) {
            $roles[] = $role;
        }
        foreach ($corporation->getRoles() as $role) {
            $roles[] = $role;
        }
        foreach ($alliance->getRoles() as $role) {
            $roles[] = $role;
        }
        return $roles;
    }


}