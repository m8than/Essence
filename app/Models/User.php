<?php
namespace app\Models;

use Essence\Database\ORM\Record;

class User extends Record
{
    protected $writeable = [
        'username'
    ];
    protected $relationships = [
        UserPermission::class => 'UserPermission_id'
    ];
}
?>