<?php
const APP_ROOT = __DIR__ . '/';
const IN_ESSENCE = true;
require_once './Essence/bootstrap.php';

use Essence\Application\EssenceApplication;
use Essence\Application\Autoloader;
use Essence\Database\Query\Query;

$app = new EssenceApplication(APP_ROOT . 'app');

$yes = Query::create('Yes');

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

$whereString = $yes->test();
print_r($whereString);

//$where = new Essence\Database\Query\QueryParts\Where();
?>