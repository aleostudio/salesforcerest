<?php

namespace AleoStudio\SalesForceRest;


interface SalesForceAuthInterface
{
    public function getAccessToken();

    public function getInstanceUrl();
}