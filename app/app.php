<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/lib/GPG.php';
require_once __DIR__.'/config/config.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['debug'] = true;
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => $connectionParams
));

//POST a new key to the DB
$app->post('/key', function (Request $request) use ($app){
    $email = $request->get('email');
    $key = $request->get('publickey');
    $sql = "INSERT INTO pgweb.pgkeys (email, pgpkey) VALUES(?,?)";
    $stmt = $app['db']->prepare($sql);
    $stmt->bindValue(1, $email);
    $stmt->bindValue(2, $key);
    $stmt->execute();
    error_log($key);
    
    $ret = array (
        "msg"=>"key added successfully",
        "success"=>true
    );
    
    return $app->json($ret);
    
});

//GET a key from the db
$app->get('/key/{email}', function($email) use($app){
    $sql = "SELECT pgpkey from pgweb.pgkeys WHERE email = ?";
    $keys = $app['db']->fetchAssoc($sql, array((string) $email));
    $ret = array (
        "key"=> $keys['pgpkey'],
        "sucess"=> true
        
    );
    return $app->json($ret);
});

//encrypt a message using users key 
$app->post('/encrypt', function(Request $request) use ($app){
    $email = $request->get('email');
    $message = $request->get('message');
    $sql = "SELECT pgpkey from pgweb.pgkeys WHERE email = ?";
    $key = $app['db']->fetchColumn($sql, array((string) $email));
    $key=  str_replace("\r", '', $key);   
    error_log($email);
    $gpg = new GPG();
    $pub_key = new GPG_Public_Key($key);
    $encrypted = $gpg->encrypt($pub_key, "howdy");
    $ret = array (
        "msg"=>$encrypted,
        "success"=>true
    );
    return $app->json($ret);

});

return $app;
