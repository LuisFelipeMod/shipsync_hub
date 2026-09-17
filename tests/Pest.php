<?php

use Tests\TestCase;

/*
| Testes Feature bootam o Laravel. Infrastructure (TCP/LocalStack) não.
*/
pest()->extend(TestCase::class)->in('Feature');
