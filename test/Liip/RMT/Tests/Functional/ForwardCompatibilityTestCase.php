<?php declare(strict_types=1);

namespace Liip\RMT\Tests\Functional;

use PHPUnit\Framework\TestCase;

abstract class ForwardCompatibilityTestCase extends TestCase
{
    public static function assertMatchesRegularExpression(string $pattern, string $string, string $message = ''): void
    {
        if (method_exists(parent::class, 'assertMatchesRegularExpression')) {
            parent::assertMatchesRegularExpression($pattern, $string, $message);
        } else {
            parent::assertRegExp($pattern, $string, $message);
        }
    }

    public static function assertFileDoesNotExist(string $filename, string $message = ''): void
    {
        if (method_exists(parent::class, 'assertFileDoesNotExist')) {
            parent::assertFileDoesNotExist($filename, $message);
        } else {
            parent::assertFileNotExists($filename, $message);
        }
    }
}
