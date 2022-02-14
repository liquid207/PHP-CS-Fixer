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

namespace PhpCsFixer\Tests\Fixer\FunctionNotation;

use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @internal
 * @covers \PhpCsFixer\Fixer\FunctionNotation\CreateFromFormatCallFixer
 */
final class CreateFromFormatCallFixerTest extends AbstractFixerTestCase
{
    /**
     * @dataProvider provideFixCases
     */
    public function testFix(string $expected, ?string $input = null): void
    {
        $this->doTest($expected, $input);
    }

    public function provideFixCases(): \Generator
    {
        yield [
            '<?php \DateTime::createFromFormat(\'!Y-m-d\', \'2022-02-11\');',
            '<?php \DateTime::createFromFormat(\'Y-m-d\', \'2022-02-11\');',
        ];

        yield [
            '<?php use DateTime; DateTime::createFromFormat(\'!Y-m-d\', \'2022-02-11\');',
            '<?php use DateTime; DateTime::createFromFormat(\'Y-m-d\', \'2022-02-11\');',
        ];

        yield [
            '<?php DateTime::createFromFormat(\'!Y-m-d\', \'2022-02-11\');',
            '<?php DateTime::createFromFormat(\'Y-m-d\', \'2022-02-11\');',
        ];

        yield [
            '<?php use \Example\DateTime; DateTime::createFromFormat(\'Y-m-d\', \'2022-02-11\');',
        ];

        yield [
            '<?php \DateTime::createFromFormat("!Y-m-d", \'2022-02-11\');',
            '<?php \DateTime::createFromFormat("Y-m-d", \'2022-02-11\');',
        ];

        yield [
            '<?php \DateTime::createFromFormat($foo, \'2022-02-11\');',
        ];

        yield [
            '<?php \DateTime::createFromFormat( "!Y-m-d", \'2022-02-11\');',
            '<?php \DateTime::createFromFormat( "Y-m-d", \'2022-02-11\');',
        ];

        yield [
            '<?php \DateTime::createFromFormat(/* aaa */ "!Y-m-d", \'2022-02-11\');',
            '<?php \DateTime::createFromFormat(/* aaa */ "Y-m-d", \'2022-02-11\');',
        ];
    }
}
