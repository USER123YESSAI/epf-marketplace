<?php

use Pest\Plugins\Parallel;

return [
    'parallel' => [
        'processes' => 4,
    ],
    'coverage' => [
        'include' => [
            'app/',
        ],
        'exclude' => [
            'app/Providers/',
            'app/Exceptions/',
        ],
        'report' => [
            'html' => 'coverage',
            'text' => true,
            'clover' => 'coverage.xml',
        ],
    ],
];
