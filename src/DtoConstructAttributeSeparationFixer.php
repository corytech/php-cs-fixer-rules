<?php

declare(strict_types=1);

namespace Corytech\PhpCsFixerRules;


use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;

final class DtoConstructAttributeSeparationFixer implements FixerInterface, WhitespacesAwareFixerInterface
{
    public const string NAME = 'Corytech/dto_construct_attribute_separator';

    private WhitespacesFixerConfig $whitespacesConfig;

    #[\Override]
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_CLASS);
    }

    #[\Override]
    public function isRisky(): bool
    {
        return false;
    }

    #[\Override]
    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        $publics = [];
        foreach ($tokens as $index => $token) {
            if ($token->isGivenKind(CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC)) {
                $publics[$index] = $token;
            }
        }

        $lineEnding = $this->whitespacesConfig->getLineEnding();
        foreach ($publics as $index => $token) {
            $nextPublicIndex = $this->getNextPublicIndex($publics, $index);
            if ($nextPublicIndex && $this->isNeedSeparateLine($tokens, $index, $nextPublicIndex)) {
                $nextComma = $tokens->getNextTokenOfKind($index, [',']);
                if ($nextComma) {
                    $newTokenId = $nextComma + 1;
                    if ($tokens[$newTokenId]->isGivenKind(\T_WHITESPACE)) {
                        $tokens[$newTokenId] = new Token([\T_WHITESPACE, $lineEnding . $lineEnding]);
                    }
                }
            }
        }
    }

    #[\Override]
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            summary: 'DTO construct attributes must be separated with one or none blank line.',
            codeSamples: [
                new CodeSample(
                    code: <<<'PHP'
                    <?php

                    declare(strict_types=1);
                    
                    namespace Crosspay\DTO\Folder;
                    
                    use Corytech\OpenApi\DTO\ApiRequestDTOInterface;
                    use Symfony\Component\Validator\Constraints as Assert;
                    
                    readonly class SomeRequestDTO implements ApiRequestDTOInterface
                    {                    
                        public function __construct(
                            #[Assert\NotBlank]
                            public ?string $id = null,
                            
                            #[Assert\NotBlank]
                            #[Assert\DateTime(format: \DateTimeInterface::ATOM)]
                            public ?string $dateFrom,
                        ) {
                        }
                    PHP
                ),
            ],
        );
    }

    #[\Override]
    public function getName(): string
    {
        return self::NAME;
    }

    #[\Override]
    public function getPriority(): int
    {
        return 0;
    }

    #[\Override]
    public function supports(\SplFileInfo $file): bool
    {
        return mb_strtolower($file->getExtension()) === 'php'
            && str_contains(mb_strtolower($file->getBasename()), 'dto');
    }

    private function getNextPublicIndex(array $publics, int $index): ?int
    {
        $prevPublicKeys = array_filter(array_keys($publics), static function ($public) use ($index) {
            return $public > $index;
        });

        return \count($prevPublicKeys) ? array_shift($prevPublicKeys) : null;
    }

    private function isNeedSeparateLine(Tokens $tokens, int $index, int $nextIndex): bool
    {
        do {
            $index = $tokens->getNextMeaningfulToken($index);
            if ($index && $tokens[$index]->getContent() === '#[') {
                return true;
            }
        } while ($index < $nextIndex);

        return false;
    }

    #[\Override]
    public function setWhitespacesConfig(WhitespacesFixerConfig $config): void
    {
        $this->whitespacesConfig = $config;
    }
}
