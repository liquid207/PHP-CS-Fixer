<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Fixer\FunctionNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class CreateFromFormatCallFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'The first argument of `DateTime::createFromFormat` method must start with `!`.',
            [
                new CodeSample("<?php \\DateTime::createFromFormat('Y-m-d', '2022-02-11');\n"),
            ],
            "Consider this code:
                `DateTime::createFromFormat('Y-m-d', '2022-02-11')`.
                What value will be return? '2022-01-11 00:00:00.0'? No, actual return value has 'H:i:s' section like '2022-02-11 16:55:37.0'.
                Change 'Y-m-d' to '!Y-m-d', return value will be '2022-01-11 00:00:00.0'.
                So add `!` to format string will make return value more intuitive."
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_DOUBLE_COLON);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $useDeclarations = (new NamespaceUsesAnalyzer())->getDeclarationsFromTokens($tokens);
        $argumentsAnalyzer = new ArgumentsAnalyzer();

        for ($index = 0; $index < \count($tokens); ++$index) {
            if (!$tokens[$index]->isGivenKind(T_DOUBLE_COLON)) {
                continue;
            }

            $functionNameIndex = $index + 1;

            if (!$tokens[$functionNameIndex]->equals([T_STRING, 'createFromFormat'], false)) {
                continue;
            }

            if (!$tokens[$functionNameIndex + 1]->equals('(')) {
                continue;
            }

            $classNamePreviousIndex = $tokens->getTokenNotOfKindsSibling($functionNameIndex, -1, [T_DOUBLE_COLON, T_NS_SEPARATOR, T_STRING]);
            $classNameIndex = $index - 1;
            $className = $tokens->generatePartialCode($classNamePreviousIndex + 1, $classNameIndex);

            foreach ($useDeclarations as $useDeclaration) {
                if ($useDeclaration->getShortName() === $className) {
                    $className = $useDeclaration->getFullName();

                    break;
                }
            }

            if (\DateTime::class !== str_replace('\\', '', $className)) {
                continue;
            }

            $openIndex = $tokens->getNextTokenOfKind($functionNameIndex, ['(']);
            $closeIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openIndex);
            $arguments = $argumentsAnalyzer->getArguments($tokens, $openIndex, $closeIndex);

            if (2 !== \count($arguments)) {
                continue;
            }

            $formatArgumentIndex = array_values($arguments)[0];
            $format = $tokens[$formatArgumentIndex]->getContent();

            if (!\in_array(substr($format, 0, 1), ['\'', '"'], true) || '!' === substr($format, 1, 1)) {
                continue;
            }

            $tokens->clearAt($formatArgumentIndex);
            $tokens->insertAt($formatArgumentIndex, new Token([T_CONSTANT_ENCAPSED_STRING, substr_replace($format, '!', 1, 0)]));
        }
    }
}
