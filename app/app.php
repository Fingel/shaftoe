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
    if(empty($email) || empty($key)){
        $ret = array (
            "msg"=>"Missing input. Please provide a valid email address and public key.",
            "success"=>false
        );
        return $app->json($ret);
    }
    $sql = "INSERT INTO pgweb.pgkeys (email, pgpkey) VALUES(?,?)";
    $stmt = $app['db']->prepare($sql);
    $stmt->bindValue(1, $email);
    $stmt->bindValue(2, $key);
    try {
        $stmt->execute();
    } catch(Exception $e){
        $ret = array (
            "msg"=>"Duplicate email!",
            "success"=>false
        );
        return $app->json($ret);
    }
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
    if(empty($email) || empty($message)){
        $ret = array (
            "msg"=>"Missing input. Please provide a valid email address and plaintext message.",
            "success"=>false
        );
        return $app->json($ret);
    }
    $sql = "SELECT pgpkey from pgweb.pgkeys WHERE email = ?";
    $key = $app['db']->fetchColumn($sql, array((string) $email));
    $key=  str_replace("\r", '', $key);   
    error_log($email);
    $gpg = new GPG();
    $pub_key = new GPG_Public_Key($key);
    if(empty($pub_key->user)){
        $ret = array (
            "msg"=>"Oh no! Encrypting of the message failed. Make sure your public key is correct. Ive deleted it for you.",
            "success"=>false
        );
        $sql = "DELETE from pgweb.pgkeys WHERE email = ?";
        $stmt = $app['db']->prepare($sql);
        $stmt->bindValue(1, $email);
        $stmt->execute();
        return $app->json($ret);
    }
    $encrypted = $gpg->encrypt($pub_key, $message);
    $ret = array (
        "msg"=>$encrypted,
        "success"=>true
    );
    return $app->json($ret);

});

return $app;