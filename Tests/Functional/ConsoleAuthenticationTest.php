<?php
namespace Mfc\Authagainsttypo3\Tests\Functional;

class ConsoleAuthenticationTest // extends \TYPO3\CMS\Core\Tests\FunctionalTestCase
{
    /**
     * @var string
     */
    public $command = '/usr/bin/php ###PATH###/typo3/cli_dispatch.phpsh authagainsttypo3';

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
    public function loginViaEnvironment()
    {
        $command = str_replace('###PATH###', realpath(__DIR__ . '/../../../../..'), $this->command);
        $arguments = ' authENV';
        putenv('USER=' . $this->loginData['user']);
        putenv('PASS=' . $this->loginData['pass']);
        $result = shell_exec($command . $arguments);

        print_r('authENV result ' . $result . chr(10));
        //$this->assertEquals($result, 'findRecordByImportSource');
    }

    /**
     * @test
     * @return void
     */
    public function loginViaCommandlineArgument()
    {
        $command = str_replace('###PATH###', realpath(__DIR__ . '/../../../../..'), $this->command);
        $arguments = ' authPARAM ' . $this->loginData['user'] . ' ' . $this->loginData['pass'];
        $result = shell_exec($command . $arguments);

        print_r('authPARAM result ' . $result . chr(10));
        //$this->assertEquals($result, 'findRecordByImportSource');
    }
}

$test = new \Mfc\Authagainsttypo3\Tests\Functional\ConsoleAuthenticationTest();
$test->loginViaEnvironment();
$test->loginViaCommandlineArgument();
