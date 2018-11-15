<?php
namespace app\Models;

use Essence\Database\ORM\Record;

class UserPermission extends Record
{
    protected $writeable = [
        'slug'
    ];
}
?>