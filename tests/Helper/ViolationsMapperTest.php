<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Helper\ViolationsMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

final class ViolationsMapperTest extends TestCase
{
    public function testMapsViolationsToPropertyAndMessage(): void
    {
        $list = new ConstraintViolationList([
            new ConstraintViolation('Blank.', null, [], null, 'name', ''),
            new ConstraintViolation('Not positive.', null, [], null, 'price', '-1'),
        ]);

        self::assertSame([
            ['property' => 'name', 'message' => 'Blank.'],
            ['property' => 'price', 'message' => 'Not positive.'],
        ], (new ViolationsMapper())($list));
    }

    public function testEmptyListGivesEmptyArray(): void
    {
        self::assertSame([], (new ViolationsMapper())(new ConstraintViolationList()));
    }
}
