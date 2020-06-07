<?php

namespace App\Controller;

use App\Entity\Alliance;
use App\Entity\Character;
use App\Entity\Corporation;
use App\Entity\DiscordRole;
use App\Eve\CharacterProcessor;
use App\Repository\AllianceRepository;
use App\Repository\CharacterRepository;
use App\Repository\CorporationRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{

    const BASE_URI = 'https://discord.com/api';
    const VERSION = 'v6';
    /**
     * @var CharacterRepository
     */
    private CharacterRepository $characterRepository;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var CorporationRepository
     */
    private CorporationRepository $corporationRepository;
    /**
     * @var AllianceRepository
     */
    private AllianceRepository $allianceRepository;

    /**
     * IndexController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CharacterRepository $characterRepository
     * @param CorporationRepository $corporationRepository
     * @param AllianceRepository $allianceRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CharacterRepository $characterRepository,
        CorporationRepository $corporationRepository,
        AllianceRepository $allianceRepository
    )
    {
        $this->characterRepository = $characterRepository;
        $this->entityManager = $entityManager;
        $this->corporationRepository = $corporationRepository;
        $this->allianceRepository = $allianceRepository;
    }


    /**
     * @Route("/", name="index")
     */
    public function index(Request $request)
    {
        $user = $this->getUser();
        if (!$user) {
            return new RedirectResponse('sso');
        }
        $characterProcessor = new CharacterProcessor(
            $this->characterRepository,
            $this->corporationRepository,
            $this->allianceRepository
        );
        $characterData = $characterProcessor->getInfo($user->getUid(),$user->getUsername());

        $character = $this->characterRepository->findOneBy(['uid' => $characterData['char']['id']]);
        if (!$character) {
            $character = new Character();
            $character->setUid($characterData['char']['id']);
            $character->setName($characterData['char']['name']);
            $this->entityManager->persist($character);
            $this->entityManager->flush();
        }

        $corporation = $this->corporationRepository->findOneBy(['uid' => $characterData['corp']['id']]);
        if (!$corporation) {
            $corporation = new Corporation();
            $corporation->setUid($characterData['corp']['id']);
            $corporation->setName($characterData['corp']['name']);
            $this->entityManager->persist($corporation);
            $this->entityManager->flush();
        }

        $alliance = $this->allianceRepository->findOneBy(['uid' => $characterData['alli']['id']]);
        if (!$alliance) {
            $alliance = new Alliance();
            $alliance->setUid($characterData['alli']['id']);
            $alliance->setName($characterData['alli']['name']);
            $this->entityManager->persist($alliance);
            $this->entityManager->flush();
        }

        return $this->render('index/index.html.twig', [
            'data' => $characterData,
            'discord' => [
                'name' => $character->getDiscordName(),
                'mail' => $character->getDiscordMail(),
                'url' => 'https://discord.com/api/oauth2/authorize?client_id=717918091727601684&redirect_uri='.$request->getSchemeAndHttpHost().'/discord/callback&response_type=code&scope=identify%20email%20guilds.join'
            ],
            'roles' => $characterProcessor->getRolesArray($characterData)
        ]);
    }

    /**
     * @Route("/sso")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login()
    {
        return $this->render('login.html.twig');
    }

    /**
     * @Route("/discord/callback")
     * @param Request $request
     */
    public function discordCallback(Request $request)
    {
        $code = $request->get('code');
        $authClient = new Client([
            'base_uri' => sprintf('%s', rtrim(self::BASE_URI, '/')),
            'headers' => [
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
        ]);

        $response = $authClient->request('POST','/api/oauth2/token',[
            'form_params' => ['client_id'=> $_ENV['APP_ID'],
            'client_secret'=> $_ENV['APP_SECRET'],
            'grant_type'=> 'authorization_code',
            'code'=> $code,
            'redirect_uri' => $request->getSchemeAndHttpHost().'/discord/callback',
            'scopes' => 'identify email guilds.join']
        ]);
        $response = json_decode($response->getBody(),true);

        $client = new Client([
            'base_uri' => sprintf('%s', rtrim(self::BASE_URI, '/')),
            'headers' => [
                'Authorization' => 'Bearer '.$response['access_token'],
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
        ]);

        $userResponse = $client->request('GET','/api/'.self::VERSION.'/users/@me');
        $userResponse = json_decode($userResponse->getBody(),true);


        $character = $this->characterRepository->findOneBy(['uid' => $this->getUser()->getUid()]);
        $character->setDiscordId($userResponse['id']);
        $character->setDiscordMail($userResponse['email']);
        $character->setDiscordName($userResponse['username'].'#'.$userResponse['discriminator']);

        $this->entityManager->persist($character);
        $this->entityManager->flush();

        $characterProcessor = new CharacterProcessor(
            $this->characterRepository,
            $this->corporationRepository,
            $this->allianceRepository
        );
        $characterData = $characterProcessor->getInfo($character->getUid(),$character->getName());
        $roles = $characterProcessor->getRolesArray($characterData);
        $roleArray = [];
        /** @var DiscordRole $role */
        foreach ($roles as $role) {
            $roleArray[] = $role->getUid();
        }

        $joinClient = new Client([
            'base_uri' => sprintf('%s', rtrim(self::BASE_URI, '/')),
            'headers' => [
                'Authorization' => 'Bot '.$_ENV['BOT_TOKEN'],
                'Content-Type'  => 'application/json',
            ],
        ]);
        $joinReponse = $joinClient->request('PUT','/api/'.self::VERSION.'/guilds/'.$_ENV['GUILD_ID'].'/members/'.$character->getDiscordId(),[
            'json' => [
                'access_token' => $response['access_token'],
                'nick' => $characterProcessor->getName($characterData),
                'roles' => $roleArray
            ]
        ]);

        return new RedirectResponse('/');
    }
}
