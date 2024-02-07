<?php

namespace App\Common;

final readonly class B23
{
    private const int a2bAddEnc = 0b1000001000010000000000011111000000;
    private const int a2bXorEnc = 0b1010100100111011001100100100;
    private const array a2bEncIndex = [11, 10, 3, 8, 4, 6];
    private const string a2bEncTable = "fZodR9XQDSUm21yCkr6zBqiveYah8bt4xsWpHnJE7jL5VG3guMTKNPAwcF";
    private const int a2bEncTableLength = 58;
    private const int a2bEncIndexLength = 6;

    /**
     * @param string $av
     * @return string
     * @deprecated AVID larger than 2^27 will cause issue.
     */
    public static function AV2BV(string $av): string
    {
        str_starts_with($av, 'av') && $av = substr($av, 2);
        if (!is_numeric($av)) return 'Invaild AV ID.';
        $temp = "BV1@@4@1@7@@";
        for ($i = 0; $i < self::a2bEncIndexLength; $i++) {
            $temp = sprintf("%s%s%s",
                substr($temp, 0, self::a2bEncIndex[$i]),
                self::a2bEncTable[floor(
                    (
                        ($av ^ self::a2bXorEnc)
                        + self::a2bAddEnc
                    )
                    / (self::a2bEncTableLength ** $i)
                )
                % self::a2bEncTableLength],
                substr($temp, self::a2bEncIndex[$i] + 1)
            );
        }
        return $temp;
    }

    /**
     * @param string $bv
     * @return string
     * @deprecated AVID larger than 2^27 will cause issue.
     */
    public static function BV2AV(string $bv): string
    {
        $temp = 0;
        for ($i = 0; $i < self::a2bEncIndexLength; $i++) {
            if (!str_contains(self::a2bEncTable, $bv[self::a2bEncIndex[$i]])) {
                return 'Invaild BV ID.';
            } else {
                $temp += strpos(self::a2bEncTable, $bv[self::a2bEncIndex[$i]]) * (self::a2bEncTableLength ** $i);
            }
        }
        $temp = $temp - self::a2bAddEnc ^ self::a2bXorEnc;
        return 'av' . $temp;
    }
}
