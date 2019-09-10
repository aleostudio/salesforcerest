<?php

namespace AleoStudio\SalesForceRest;


interface SalesForceAuthInterface
{
    public function authentication();
    public function getAccessToken();
    public function getInstanceUrl();
    public function refreshAccessToken();
}