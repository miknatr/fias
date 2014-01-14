<?php

namespace Fias;

interface Reader
{
    public function getRows($maxCount = 1000);
}
