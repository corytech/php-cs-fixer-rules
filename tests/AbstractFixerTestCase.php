<?php

declare(strict_types=1);

namespace Corytech\PhpCsFixerRules\Tests;

use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\Linter\Linter;
use PhpCsFixer\Linter\LinterInterface;
use PhpCsFixer\Linter\ProcessLinter;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;
use PHPUnit\Framework\TestCase;

abstract class AbstractFixerTestCase extends TestCase
{
    protected string $className = '';

    private function getFixer(): FixerInterface
    {
        if (!$this->className) {
            throw new \RuntimeException('Class "'.$this->className.'" does not exist.');
        }

        $fixer = new $this->className();
        \assert($fixer instanceof FixerInterface);

        if ($fixer instanceof WhitespacesAwareFixerInterface) {
            $fixer->setWhitespacesConfig(new WhitespacesFixerConfig());
        }

        return $fixer;
    }

    private function lintSource(string $source): ?string
    {
        /** @var LinterInterface|null $linter */
        static $linter;

        if ($linter === null) {
            $linter = getenv('FAST_LINT_TEST_CASES') === '1' ? new Linter() : new ProcessLinter();
        }

        try {
            $linter->lintSource($source)->check();
        } catch (\Exception $exception) {
            return \sprintf('Linting "%s" failed with error: %s.', $source, $exception->getMessage());
        }

        return null;
    }

    private static function createSplFileInfoDouble(): \SplFileInfo
    {
        return new class(getcwd().\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'FixerFile.php') extends \SplFileInfo {
            public function __construct(string $filename)
            {
                parent::__construct($filename);
            }

            public function getRealPath(): string
            {
                return $this->getPathname();
            }
        };
    }

    private static function assertSameTokens(Tokens $expectedTokens, Tokens $inputTokens): void
    {
        self::assertCount($expectedTokens->count(), $inputTokens, 'Both collections must have the same size.');

        /** @var Token $expectedToken */
        foreach ($expectedTokens as $index => $expectedToken) {
            $inputToken = $inputTokens[$index];

            self::assertTrue(
                $expectedToken->equals($inputToken),
                \sprintf("Token at index %d must be:\n%s,\ngot:\n%s.", $index, $expectedToken->toJson(), $inputToken->toJson()),
            );
        }
    }

    final protected function doTest(
        string $expected,
        ?string $input = null,
        array $configuration = [],
        ?WhitespacesFixerConfig $whitespacesFixerConfig = null,
    ): void {
        $fixer = $this->getFixer();

        if ($fixer instanceof ConfigurableFixerInterface) {
            $fixer->configure($configuration);
        }

        if ($whitespacesFixerConfig instanceof WhitespacesFixerConfig) {
            self::assertInstanceOf(WhitespacesAwareFixerInterface::class, $fixer);
            $fixer->setWhitespacesConfig($whitespacesFixerConfig);
        }

        if ($expected === $input) {
            throw new \InvalidArgumentException('Expected must be different to input.');
        }

        self::assertNull($this->lintSource($expected));

        Tokens::clearCache();
        $expectedTokens = Tokens::fromCode($expected);

        if ($input !== null) {
            self::assertNull($this->lintSource($input));

            Tokens::clearCache();
            $inputTokens = Tokens::fromCode($input);

            self::assertTrue($fixer->isCandidate($inputTokens));

            $fixer->fix(self::createSplFileInfoDouble(), $inputTokens);
            $inputTokens->clearEmptyTokens();

            self::assertSame(
                $expected,
                $actual = $inputTokens->generateCode(),
                \sprintf(
                    "Expected code:\n```\n%s\n```\nGot:\n```\n%s\n```\n",
                    $expected,
                    $actual,
                ),
            );

            self::assertSameTokens($expectedTokens, $inputTokens);
        }

        $fixer->fix(self::createSplFileInfoDouble(), $expectedTokens);

        self::assertSame($expected, $expectedTokens->generateCode());

        self::assertFalse($expectedTokens->isChanged());
    }
}
