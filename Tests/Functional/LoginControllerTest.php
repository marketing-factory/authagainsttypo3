<?php
namespace Mfc\Authagainsttypo3\Tests\Functional;

class LoginControllerTest // extends \TYPO3\CMS\Core\Tests\FunctionalTestCase
{
    /**
     * @var string
     */
    public $loginUrl = 'http://ecolab.typo-dev.web-factory.de/index.php?id=1665&type=65537';

    /**
     * @var array
     */
    protected $loginData = array(
        'user' => 'testuser',
        'pass' => 'testpass',
        'logintype' => 'login',
        'serviceUser' => 'Service',
        'servicePass' => 'Pass',
    );

    /**
     * @test
     * @return void
     */
    public function login()
    {
        $curlHandler = curl_init();

        // set the target url
        curl_setopt($curlHandler, CURLOPT_URL, $this->loginUrl);
        // howmany parameter to post
        curl_setopt($curlHandler, CURLOPT_POST, 1);
        // the parameter 'username' with its value 'johndoe'
        curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $this->loginData);

        $result = curl_exec($curlHandler);
        curl_close($curlHandler);

        print($result);
        //$this->assertEquals($result, 'findRecordByImportSource');
    }
}

$test = new \Mfc\Authagainsttypo3\Tests\Functional\LoginControllerTest();
$test->loginUrl = 'http://ecolab.mfc.dev/index.php?id=1665&type=65537';
$test->login();
