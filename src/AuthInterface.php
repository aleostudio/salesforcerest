<?php
/**
 * This file is part of the SalesForceRest package.
 *
 * (c) Alessandro Orrù <alessandro.orru@aleostudio.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace AleoStudio\SalesForceRest;


interface AuthInterface
{
    public function authentication(): void;

    public function getAccessToken(): string;

    public function setAccessToken(string $accessToken): void;

    public function getRefreshToken(): string;

    public function setRefreshToken(string $refreshToken): void;

    public function refreshAccessToken(): string;
}