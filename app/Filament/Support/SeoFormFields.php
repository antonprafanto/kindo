<?php

namespace App\Filament\Support;

use Closure;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\HtmlString;

class SeoFormFields
{
    public static function title(string $name, string $label, string $hintSuffix): TextInput
    {
        return self::configure(
            TextInput::make($name)->label($label),
            $name,
            70,
            $hintSuffix,
            "{$label} maksimal 70 karakter.",
        );
    }

    public static function description(string $name, string $label, string $hintSuffix): Textarea
    {
        return self::configure(
            Textarea::make($name)
                ->label($label)
                ->rows(3),
            $name,
            160,
            $hintSuffix,
            "{$label} maksimal 160 karakter.",
        );
    }

    /**
     * @template T of TextInput|Textarea
     *
     * @param  T  $field
     * @return T
     */
    private static function configure(
        TextInput|Textarea $field,
        string $name,
        int $max,
        string $hintSuffix,
        string $maxMessage,
    ): TextInput|Textarea {
        return $field
            ->maxLength($max)
            ->live()
            ->partiallyRenderAfterStateUpdated()
            ->afterStateHydrated(function (Set $set, ?string $state) use ($name, $max): void {
                if (! is_string($state) || mb_strlen($state) <= $max) {
                    return;
                }

                $set($name, mb_substr($state, 0, $max));
            })
            ->afterStateUpdated(function (Set $set, ?string $state) use ($name, $max): void {
                if (! is_string($state) || mb_strlen($state) <= $max) {
                    return;
                }

                $set($name, mb_substr($state, 0, $max));
            })
            ->belowContent(fn (Get $get): HtmlString => self::counter(
                $get($name),
                $max,
                $hintSuffix,
            ))
            ->rules([
                fn (): Closure => function (string $attribute, mixed $value, Closure $fail) use ($max, $maxMessage): void {
                    if (! is_string($value)) {
                        return;
                    }

                    if (mb_strlen($value) > $max) {
                        $fail($maxMessage);
                    }
                },
            ])
            ->validationMessages([
                'max' => $maxMessage,
            ])
            ->columnSpanFull();
    }

    private static function counter(?string $state, int $max, string $hintSuffix): HtmlString
    {
        $length = mb_strlen($state ?? '');
        $color = $length >= $max ? '#ef4444' : ($length >= (int) ($max * 0.9) ? '#f59e0b' : '#718096');

        return new HtmlString(
            '<p style="margin:.35rem 0 0;font-size:.75rem;color:'.$color.';">'
            .$length.'/'.$max.' karakter'
            .($hintSuffix !== '' ? ' — '.$hintSuffix : '')
            .($length >= $max ? ' <strong>(batas tercapai)</strong>' : '')
            .'</p>'
        );
    }
}
