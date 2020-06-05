<?php

namespace App\Eve;

use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser;

class User extends OAuthUser
{

    protected string $uid = '';

    /**
     * @return string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * @param string $uid
     */
    public function setUid(string $uid): void
    {
        $this->uid = $uid;
    }


}