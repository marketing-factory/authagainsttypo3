<?php
declare(strict_types=1);

namespace Mfc\Authagainsttypo3\Services;

use TYPO3\CMS\Core\Authentication\AbstractAuthenticationService;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CliAuthenticationService
 * @package Mfc\Authagainsttypo3\Services
 * @author Christian Spoo <cs@marketing-factory.de>
 */
class CliAuthenticationService
{
    /**
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function authenticateUser(string $username, string $password): bool
    {
        $loginData = [
            'uname' => $username,
            'uident_text' => $password,
            'status' => 'login',
            'table' => 'be_users',
            'db_user' => true
        ];

        $backendUser = GeneralUtility::makeInstance(BackendUserAuthentication::class);
        $authInfo = $backendUser->getAuthInfoArray();
        $isAuthenticated = false;

        // We intentionally do not use ::class here since that would require all listed classes to be actually present
        $blacklistedAuthServices = [
            'Mfc\\OAuth2\\Services\\OAuth2LoginService',
            'Mfc\\MfcBeloginCaptcha\\Service\\CaptchaService',
            'Mfc\\BeuserIprange\\Service\\AuthenticationService'
        ];

        /** @var AbstractAuthenticationService $authService */
        $authService = GeneralUtility::makeInstanceService('auth', 'authUserBE', implode(',', $blacklistedAuthServices));
        while (is_object($authService)) {
            try {
                $authService->initAuth('authUserBE', $loginData, $authInfo, $backendUser);
                $userData = $authService->getUser();
            } catch (\Throwable $ex) {
                return false;
            }

            if (is_array($userData)) {
                if (($ret = $authService->authUser($userData)) > 0) {
                    // If the service returns >=200 then no more checking is needed
                    // - useful for IP checking without password
                    if ((int)$ret >= 200) {
                        $isAuthenticated = true;
                        break;
                    } elseif ((int)$ret >= 100) {
                    } else {
                        $isAuthenticated = true;
                    }
                } else {
                    $isAuthenticated = false;
                    break;
                }

            }

            $blacklistedAuthServices[] = $authService->getServiceKey();
            /** @var AbstractAuthenticationService $authService */
            $authService = GeneralUtility::makeInstanceService(
                'auth',
                'authUserBE',
                implode(',', $blacklistedAuthServices)
            );
        }

        return $isAuthenticated;
    }
}
