<?php

namespace App\Filament\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Support\HtmlString;

class ImageInput extends TextInput
{
    protected string $previewHeight = '24px';

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->live()
            ->suffix(function ($get) {
                $value = $get($this->getName());

                if (blank($value)) {
                    return null;
                }

                return new HtmlString(
                    '
                        <img
                            src="'.e($value).'"
                            style="
                                height:'.$this->previewHeight.';
                                object-fit:cover;
                                border-radius:4px;
                            "
                        >
                    '
                );
            });
    }

    public function previewHeight(string $height): static
    {
        $this->previewHeight = $height;

        return $this;
    }
}
