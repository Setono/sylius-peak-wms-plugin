<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Message\CommandHandler;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractProcessUploadRequestHandler
{
    protected static function stringifyMessage(RequestInterface|ResponseInterface|null $message): ?string
    {
        if (null === $message) {
            return null;
        }

        $result = '';
        if ($message instanceof RequestInterface) {
            $result = sprintf(
                "%s %s HTTP/%s\n",
                $message->getMethod(),
                $message->getUri(),
                $message->getProtocolVersion(),
            );
        }

        /**
         * @var string $name
         * @var list<string> $values
         */
        foreach ($message->getHeaders() as $name => $values) {
            $value = implode(', ', $values);

            if ('authorization' === strtolower($name)) {
                $value = self::mask($value);
            }

            $result .= sprintf("%s: %s\n", $name, $value);
        }

        $body = trim((string) $message->getBody());
        if ('' !== $body) {
            $result .= "\n\n" . $body;
        }

        return $result;
    }

    /**
     * Copied from here: https://stackoverflow.com/questions/44200823/replace-all-characters-of-a-string-with-asterisks-except-for-the-last-four-chara
     */
    protected static function mask(string $value): string
    {
        $length = strlen($value);

        $visibleCount = (int) floor($length / 4);
        $hiddenCount = $length - ($visibleCount * 2);

        return sprintf(
            '%s%s%s',
            substr($value, 0, $visibleCount),
            str_repeat('*', $hiddenCount),
            substr($value, $visibleCount * -1, $visibleCount),
        );
    }
}
