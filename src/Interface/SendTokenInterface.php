<?php

namespace App\Interface;

use App\Entity\User;

interface SendTokenInterface
{
    public function setUser(User $user) : self;

    public function sendToken() : void;
}
