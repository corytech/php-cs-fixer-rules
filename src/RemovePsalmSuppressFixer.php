<?php

declare(strict_types=1);

namespace Corytech\PhpCsFixerRules;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final readonly class RemovePsalmSuppressFixer implements FixerInterface
{
    public const string NAME = 'Corytech/remove_psalm_suppress';

    #[\Override]
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            summary: 'Removes @psalm-suppress annotations',
            codeSamples: [
                new CodeSample(
                    '/** @psalm-suppress SomeRule */',
                ),
            ],
        );
    }

    #[\Override]
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_DOC_COMMENT);
    }

    #[\Override]
    public function isRisky(): bool
    {
        return false;
    }

    #[\Override]
    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(\T_DOC_COMMENT)) {
                continue;
            }

            $content = $token->getContent();

            if (!str_contains($content, '@psalm-suppress')) {
                continue;
            }

            $newContent = preg_replace('#@psalm-suppress\s+[a-z-]*[^*]*#iu', ' ', $content);

            if ($newContent !== $content) {
                $tokens[$index] = new Token([\T_DOC_COMMENT, $newContent]);
            }
        }
    }

    #[\Override]
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * Must run before NoEmptyPhpdocFixer.
     */
    #[\Override]
    public function getPriority(): int
    {
        return 50;
    }

    #[\Override]
    public function supports(\SplFileInfo $file): bool
    {
        return mb_strtolower($file->getExtension()) === 'php';
    }
}
