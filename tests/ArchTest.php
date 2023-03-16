<?php

it('will not use debugging functions')
    ->expect(['dd', 'dump', 'var_dump'])
    ->each->not->toBeUsed();
