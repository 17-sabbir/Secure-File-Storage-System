<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PasswordStrengthController extends Controller
{
    /**
     * A small denylist of extremely common passwords / patterns.
     * Not exhaustive — just enough to catch the obvious cases.
     */
    private const COMMON_PASSWORDS = [
        'password', '123456', '123456789', 'qwerty', 'abc123', 'password1',
        '111111', '12345678', 'letmein', 'iloveyou', 'admin', 'welcome',
        'monkey', 'dragon', 'football', '123123', 'qwerty123', '1q2w3e4r',
    ];

    public function check(Request $request)
    {
        $request->validate(['password' => 'required|string']);
        $password = $request->input('password');

        $result = $this->evaluate($password);

        return response()->json($result);
    }

    public function evaluate(string $password): array
    {
        $length = strlen($password);
        $suggestions = [];
        $score = 0; // 0–100

        $hasLower   = (bool) preg_match('/[a-z]/', $password);
        $hasUpper   = (bool) preg_match('/[A-Z]/', $password);
        $hasDigit   = (bool) preg_match('/[0-9]/', $password);
        $hasSpecial = (bool) preg_match('/[^a-zA-Z0-9]/', $password);
        $isCommon   = in_array(strtolower($password), self::COMMON_PASSWORDS, true);
        $hasRepeats = (bool) preg_match('/(.)\1{2,}/', $password); // aaa, 111
        $hasSequence = $this->hasSequentialChars($password);

        // ── Length scoring (up to 40 points) ────────────────────────────────
        if ($length >= 16) {
            $score += 40;
        } elseif ($length >= 12) {
            $score += 30;
        } elseif ($length >= 8) {
            $score += 20;
        } elseif ($length >= 6) {
            $score += 10;
        } elseif ($length > 0) {
            $score += 4;
        }

        if ($length < 8) {
            $suggestions[] = 'Use at least 8 characters — 12 or more is stronger.';
        }

        // ── Character variety (up to 40 points) ─────────────────────────────
        $varietyCount = collect([$hasLower, $hasUpper, $hasDigit, $hasSpecial])->filter()->count();
        $score += $varietyCount * 10;

        if (!$hasLower)   $suggestions[] = 'Add a lowercase letter.';
        if (!$hasUpper)   $suggestions[] = 'Add an uppercase letter.';
        if (!$hasDigit)   $suggestions[] = 'Add a number.';
        if (!$hasSpecial) $suggestions[] = 'Add a special character (e.g. ! @ # $ %).';

        // ── Penalties ────────────────────────────────────────────────────────
        if ($isCommon) {
            $score = min($score, 10);
            $suggestions[] = 'This is one of the most commonly used passwords — avoid it entirely.';
        }

        if ($hasRepeats) {
            $score -= 15;
            $suggestions[] = 'Avoid repeating the same character three or more times in a row.';
        }

        if ($hasSequence) {
            $score -= 15;
            $suggestions[] = 'Avoid sequential patterns like "abcd" or "1234".';
        }

        $score = max(0, min(100, $score));

        if ($length === 0) {
            $label = 'empty';
        } elseif ($score < 30) {
            $label = 'weak';
        } elseif ($score < 55) {
            $label = 'fair';
        } elseif ($score < 80) {
            $label = 'good';
        } else {
            $label = 'strong';
        }

        if (empty($suggestions) && $label !== 'empty') {
            $suggestions[] = 'Great password!';
        }

        return [
            'score'       => $score,
            'label'       => $label,
            'suggestions' => array_values(array_unique($suggestions)),
            'checks'      => [
                'length'    => $length >= 8,
                'lowercase' => $hasLower,
                'uppercase' => $hasUpper,
                'digit'     => $hasDigit,
                'special'   => $hasSpecial,
                'not_common'=> !$isCommon,
            ],
        ];
    }

    private function hasSequentialChars(string $password): bool
    {
        $lower = strtolower($password);
        $sequences = ['abcdefghijklmnopqrstuvwxyz', '0123456789', 'qwertyuiop', 'asdfghjkl', 'zxcvbnm'];

        foreach ($sequences as $seq) {
            for ($i = 0; $i <= strlen($seq) - 4; $i++) {
                $chunk = substr($seq, $i, 4);
                if (str_contains($lower, $chunk) || str_contains($lower, strrev($chunk))) {
                    return true;
                }
            }
        }

        return false;
    }
}
