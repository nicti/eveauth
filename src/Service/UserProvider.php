<?php

namespace App\Service;

use App\Eve\User;
use Exception;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{

    public function loadUserByUsername($username, $characterID = null)
    {
        if (is_null($characterID)) {
            throw new Exception('Cannot load user without characterID');
        }
        $user = new User($username);
        $user->setUid($characterID);
        return $user;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        return $this->loadUserByUsername(
            $response->getNickname(),
            $response->getData()['CharacterID']
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(\get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', \get_class($user)));
        }
        return $this->loadUserByUsername($user->getUsername(),$user->getUid());
    }

    public function supportsClass(string $class)
    {
        return 'App\\Eve\\User' === $class;
    }
}