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
    public function authentication();
    public function getAccessToken();
    public function refreshAccessToken();
}