<?php

namespace App\Controller;

use App\Entity\DiscordRole;
use App\Repository\DiscordRoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use GuzzleHttp\Client;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends EasyAdminController
{

    /**
     * @var KernelInterface
     */
    private KernelInterface $kernel;
    /**
     * @var DiscordRoleRepository
     */
    private DiscordRoleRepository $discordRoleRepository;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * AdminController constructor.
     * @param KernelInterface $kernel
     * @param DiscordRoleRepository $discordRoleRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        KernelInterface $kernel,
        DiscordRoleRepository $discordRoleRepository,
        EntityManagerInterface $entityManager
)
    {
        $this->kernel = $kernel;
        $this->discordRoleRepository = $discordRoleRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="easyadmin")
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     *
     * @throws ForbiddenActionException
     */
    public function indexAction(Request $request)
    {
        $admins = explode(',',$_ENV['ADMINS']);
        $user = $this->getUser();
        if (is_null($user) || !in_array($user->getUid(),$admins)) {
            return new RedirectResponse('/');
        }
        return parent::indexAction($request);
    }

    /**
     * @Route("/pullRoles")
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     */
    public function pullRolesAction(Request $request)
    {
        $application = new Application();
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'app:discord:pull'
        ]);

        $output = new NullOutput();

        $application->run($input, $output);

        return new RedirectResponse('/admin');
    }

    /**
     * @Route("/pushRoles")
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     */
    public function pushRolesAction(Request $request)
    {
        $application = new Application();
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'app:discord:push'
        ]);

        $output = new NullOutput();

        $application->run($input, $output);

        return new RedirectResponse('/admin');
    }

}