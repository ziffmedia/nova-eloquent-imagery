<?php

namespace ZiffMedia\NovaEloquentImagery\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class MaxDimensions implements InvokableRule
{
    public function __construct(
        protected int $maxWidth,
        protected ?int $maxHeight = null
    ) {}

    public function __invoke($attribute, $value, $fail)
    {
        $formData = json_decode($value, true);

        if (is_null($formData)) {
            return;
        }

        $images = array_is_list($formData) ? $formData : [$formData];

        foreach ($images as $image) {
            [$width, $height] = getimagesize($image['fileData']);

            if ($width > $this->maxWidth || $height > $this->maxHeight) {
                $fail($attribute.' is too big. Please submit an image that is less than '.$this->maxWidth.'px x '.$this->maxHeight.'px.');
            }
        }
    }
}
