<?php

namespace App\Common;

class B23
{
    const int a2bAddEnc = 8728348608;
    const int a2bXorEnc = 0b1010100100111011001100100100;
    const array a2bEncIndex = [11, 10, 3, 8, 4, 6];
    const string a2bEncTable = "fZodR9XQDSUm21yCkr6zBqiveYah8bt4xsWpHnJE7jL5VG3guMTKNPAwcF";

    public static function AV2BV(string $av): string
    {
        str_starts_with($av, 'av') && $av = substr($av, 2);
        if (!is_numeric($av)) return 'Invaild AV ID.';
        $temp = "BV1@@4@1@7@@";
        for ($i = 0; $i < count(self::a2bEncIndex); $i++) {
            $temp = substr($temp, 0, self::a2bEncIndex[$i])
                . self::a2bEncTable[floor(
                    (
                        ($av ^ self::a2bXorEnc)
                        + self::a2bAddEnc
                    )
                    / pow(strlen(self::a2bEncTable), $i)
                )
                % strlen(self::a2bEncTable)]
                . substr($temp, self::a2bEncIndex[$i] + 1);
        }
        return $temp;
    }

    public static function BV2AV(string $bv): string
    {
        $temp = 0;
        for ($i = 0; $i < count(self::a2bEncIndex); $i++) {
            if (!str_contains(self::a2bEncTable, $bv[self::a2bEncIndex[$i]])) {
                return 'Invaild BV ID.';
            } else {
                $temp += strpos(self::a2bEncTable, $bv[self::a2bEncIndex[$i]]) * pow(strlen(self::a2bEncTable), $i);
            }
        }
        $temp = $temp - self::a2bAddEnc ^ self::a2bXorEnc;
        return 'av' . $temp;
    }

}
