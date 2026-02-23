<?php

declare(strict_types=1);

namespace Corytech\PhpCsFixerRules\Tests;

use Corytech\PhpCsFixerRules\RemovePsalmSuppressFixer;
use PHPUnit\Framework\Attributes\DataProvider;

final class RemovePsalmSuppressFixerTest extends AbstractFixerTestCase
{
    protected string $className = RemovePsalmSuppressFixer::class;

    #[DataProvider('fixCases')]
    public function testFix(string $expected, ?string $input = null): void
    {
        $this->doTest($expected, $input);
    }

    public static function fixCases(): \Generator
    {
        yield [
            <<<PHP
                <?php
                /**  */
                function foo(): void {
                    return;
                }
            PHP,
            <<<PHP
                <?php
                /** @psalm-suppress SomeRule */
                function foo(): void {
                    return;
                }
            PHP,
        ];
    }
}
