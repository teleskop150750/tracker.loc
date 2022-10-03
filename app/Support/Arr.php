<?php

declare(strict_types=1);

namespace App\Support;

class Arr extends \Illuminate\Support\Arr
{
    /**
     * @param mixed[] $array
     *
     * @return mixed[]
     *
     * @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection
     */
    public static function unique(array $array): array
    {
        return array_unique($array);
    }

    /**
     * @param mixed[] $array
     *
     * @return array<int, mixed>
     *
     * @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection
     */
    public static function values(array $array): array
    {
        return array_values($array);
    }

    /**
     * @param mixed[] $array
     *
     * @return array-key[]
     *
     * @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection
     * @noinspection PhpDocSignatureInspection
     */
    public static function keys(array $array): array
    {
        return array_keys($array);
    }

    /**
     * @param mixed[] $array
     *
     * @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection
     */
    public static function shift(array &$array): mixed
    {
        return array_shift($array);
    }
}
