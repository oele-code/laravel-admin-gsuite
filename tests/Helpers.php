<?php

namespace Tests;

use function Pest\Faker\faker;

/**
 * Get valid email account with gsuite hosted domain
 *
 * @param string $username
 * @return string
 */
function getEmailAccount(string $username = null)
{
    if ($username) {
        return $username . "@". config('gsuite.hosted_domain');
    }

    return faker()->username . "@". config('gsuite.hosted_domain');
}
