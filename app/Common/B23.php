<?php

namespace App\Common;

/**
 * @thanks USTC - LUG
 * @thanks https://t.me/slanterns
 * @thanks https://t.me/IAmNoLongerABot
 */
final readonly class B23
{
    const int XOR_CODE = 23_442_827_791_579;
    const int MASK_CODE = 2_251_799_813_685_247;
    const int MAX_AID = 2_251_799_813_685_248;
    const string ALPHABET = "FcwAPNKTMug3GV5Lj7EJnHpWsx4tb8haYeviqBz6rkCy12mUSDQX9RdoZf";

    /**
     * @param string $av
     * @return string
     */
    public static function AV2BV(string $av): string
    {
        $aid = substr($av, 2);
        $bvid = str_split(str_repeat(0, 9));
        $i = 8;
        $tmp = (self::MAX_AID | $aid) ^ self::XOR_CODE;
        while ($tmp > 0) {
            $r = $tmp % 58;
            $bvid[$i] = self::ALPHABET[$r];
            $tmp = bcdiv($tmp, 58);
            $i -= 1;
        }
        [$bvid[0], $bvid[6]] = [$bvid[6], $bvid[0]];
        [$bvid[1], $bvid[4]] = [$bvid[4], $bvid[1]];
        return 'BV1' . implode('', $bvid);
    }

    /**
     * @param string $bv
     * @return string
     */
    public static function BV2AV(string $bv): string
    {
        $bvid = str_split(substr($bv, 3));
        [$bvid[0], $bvid[6]] = [$bvid[6], $bvid[0]];
        [$bvid[1], $bvid[4]] = [$bvid[4], $bvid[1]];
        $tmp = 0;
        foreach ($bvid as $i) {
            $tmp = $tmp * 58 + strpos(self::ALPHABET, $i);
        }
        return 'av' . (($tmp & self::MASK_CODE) ^ self::XOR_CODE);
    }
}
