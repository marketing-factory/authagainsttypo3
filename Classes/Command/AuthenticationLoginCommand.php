<?php
declare(strict_types=1);

namespace Mfc\Authagainsttypo3\Command;

use Mfc\Authagainsttypo3\Services\CliAuthenticationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AuthenticationLoginCommand
 * @package Mfc\Authagainsttypo3\Command
 * @author Christian Spoo <cs@marketing-factory.de>
 */
class AuthenticationLoginCommand extends Command
{
    /**
     * @var CliAuthenticationService
     */
    private $cliAuthenticationService;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);

        $this->cliAuthenticationService = GeneralUtility::makeInstance(CliAuthenticationService::class);
    }

    protected function configure()
    {
        $this
            ->setDescription('Authenticate backend user through mod_authnz_external');
    }


    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $user = (string)getenv('USER');
            $password = (string)getenv('PASS');

            if ($this->cliAuthenticationService->authenticateUser($user, $password)) {
                exit(0);
            }
        } finally {
            exit(1);
        }
    }
}
