<?php

declare(strict_types=1);

namespace Corytech\PhpCsFixerRules\Tests;

use Corytech\PhpCsFixerRules\DtoConstructAttributeSeparationFixer;
use PHPUnit\Framework\Attributes\DataProvider;

final class DtoConstructAttributeSeparationFixerTest extends AbstractFixerTestCase
{
    protected string $className = DtoConstructAttributeSeparationFixer::class;

    #[DataProvider('fixCases')]
    public function testFix(string $expected, ?string $input = null): void
    {
        $this->doTest($expected, $input);
    }

    public static function fixCases(): \Generator
    {
        yield [
            'expected' => '<?php
class FooDto {
    public function __construct(
        public ?int $id,
        public ?string $name,  
    ) {}
}',
        ];

        yield [
            'expected' => '<?php
class FooDto {
    public function __construct(
        #[\SensitiveParameter]
        public ?int $id,

#[\SensitiveParameter]
        public ?string $name,  
    ) {}
}',
            'input' => '<?php
class FooDto {
    public function __construct(
        #[\SensitiveParameter]
        public ?int $id,
        #[\SensitiveParameter]
        public ?string $name,  
    ) {}
}',
        ];

        yield [
            'expected' => '<?php
class FooDto {
    public function __construct(
        #[\SensitiveParameter]
        public ?int $id,

#[\SensitiveParameter]
        public ?string $name,  
        public ?int $age,
    ) {}
}',
            'input' => '<?php
class FooDto {
    public function __construct(
        #[\SensitiveParameter]
        public ?int $id,
        #[\SensitiveParameter]
        public ?string $name,  
        public ?int $age,
    ) {}
}',
        ];
    }
}
