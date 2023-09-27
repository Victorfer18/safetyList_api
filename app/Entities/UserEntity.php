<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class UserEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = ['user_created'];
    protected $casts   = [
        'user_id' => 'int',
        'group_id' => 'int',
        'situation_id' => 'int',
        'client_id' => 'int',
    ];
    protected $attributes = [
        'user_id' => null,
        'user_name' => null,
        'user_email' => null,
        'user_created' => null,
        'group_id' => null,
        'situation_id' => null,
        'client_id' => null,
        'user_doc' => null,
        'user_password' => null,
    ];

    public function getUserId()
    {
        return $this->attributes['user_id'];
    }

    public function setUserId($user_id)
    {
        $this->attributes['user_id'] = $user_id;
    }

    public function getUserName()
    {
        return $this->attributes['user_name'];
    }

    public function setUserName($user_name)
    {
        $this->attributes['user_name'] = $user_name;
    }

    public function getUserEmail()
    {
        return $this->attributes['user_email'];
    }

    public function setUserEmail($user_email)
    {
        $this->attributes['user_email'] = $user_email;
    }

    public function getUserCreated()
    {
        return $this->attributes['user_created'];
    }

    public function setUserCreated($user_created)
    {
        $this->attributes['user_created'] = $user_created;
    }

    public function getGroupId()
    {
        return $this->attributes['group_id'];
    }

    public function setGroupId($group_id)
    {
        $this->attributes['group_id'] = $group_id;
    }

    public function getSituationId()
    {
        return $this->attributes['situation_id'];
    }

    public function setSituationId($situation_id)
    {
        $this->attributes['situation_id'] = $situation_id;
    }

    public function getClientId()
    {
        return $this->attributes['client_id'];
    }

    public function setClientId($client_id)
    {
        $this->attributes['client_id'] = $client_id;
    }

    public function getUserDoc()
    {
        return $this->attributes['user_doc'];
    }

    public function setUserDoc($user_doc)
    {
        $this->attributes['user_doc'] = $user_doc;
    }

    public function getUserPassword()
    {
        return $this->attributes['user_password'];
    }

    public function setUserPassword($user_password)
    {
        $this->attributes['user_password'] = $user_password;
    }
}
