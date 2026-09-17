<?php

use Tests\TestCase;

/*
| Feature boota o Laravel. Domain e Infrastructure não.
*/
pest()->extend(TestCase::class)->in('Feature');
