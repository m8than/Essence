<?php
const APP_ROOT = __DIR__ . '/';
const IN_ESSENCE = true;
require_once './Essence/bootstrap.php';

use Essence\Application\EssenceApplication;
use Essence\Application\Autoloader;
use Essence\Database\Query\Query;

$app = new EssenceApplication(APP_ROOT . 'app');

$query = Query::create('Users');
/*
$yes->where('testc', 'test')
    ->where('testcol', 'LIKE', '%testval')
    ->Or()
    ->where(function($where) {
        $where->where('yes', 'no')
        ->Or()
        ->where('no', '!=' ,'yes')
        ->where('no', 'yes'); 
    })
    ->whereIn('test1', ['test1', 'test2']);
*/
$query->leftJoin('UserPermissions', 'id', 'user_id');

$query->leftJoin('UserPermissions', function($on) {
    $on->on('id', '!=', 'user_id')
       ->or()
       ->on('iddd', 'user_id')
       ->on(function($where) {
            $where->where('yes', 'no')
            ->or()
            ->where('no', '!=' ,'yes')
            ->whereIn('no', [1,2,3,2,3]);
        });
});


$joins = $query->test();
print_r($joins); 
print $joins[1]->getStr();
//$where = new Essence\Database\Query\QueryParts\Where();
?>