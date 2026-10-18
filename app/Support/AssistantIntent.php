<?php

namespace App\Support;

class AssistantIntent
{
    public const GAPS = 'gaps';

    public const LETTER = 'letter';

    public const OFFER = 'offer';

    public const PROOFREAD = 'proofread';

    public const SUMMARY = 'summary';

    public const IMPROVE = 'improve';

    public static function detect(string $message): string
    {
        $text = mb_strtolower($message);

        if (preg_match('/lettre|motivation/u', $text) === 1) {
            return self::LETTER;
        }

        if (preg_match('/orthographe|faute|grammaire|corrige/u', $text) === 1) {
            return self::PROOFREAD;
        }

        if (preg_match('/faible|manque|incomplet|quoi compl[eé]ter/u', $text) === 1) {
            return self::GAPS;
        }

        if (preg_match('/offre d[\'’ ]emploi|cette offre|adapte.{0,48}offre/u', $text) === 1) {
            return self::OFFER;
        }

        if (preg_match('/r[eé]sum[eé]/u', $text) === 1) {
            return self::SUMMARY;
        }

        return self::IMPROVE;
    }
}
