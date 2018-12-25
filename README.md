# 协程HttpClient

```
    $url = 'http://docker.local.com/test.php/?get1=get1';
    $test = new \EasySwoole\HttpClient\HttpClient($url);
    //$test->post();

    $test->addCookie('c1','c1')->addCookie('c2','c2');
    $test->post([
        'post1'=>'post1'
    ]);
    $test->setHeader('myHeader','myHeader');
    $test->addData('sasasas','test.file','text','test.file');

    //$test->postJSON(json_encode(['json'=>1]));

    $ret = $test->exec();
    var_dump($ret->getBody());
```