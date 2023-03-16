<?php

namespace ZiffMedia\NovaEloquentImagery\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class ExactDimensions implements InvokableRule
{
    public function __construct(
        protected int $width,
        protected int $height
    ) {
    }

    public function __invoke($attribute, $value, $fail)
    {
        $formData = json_decode($value, true);

        if (is_null($formData)) {
            return;
        }

        $images = array_is_list($formData) ? $formData : [$formData];

        foreach ($images as $image) {
            if (! isset($image['fileData'])) {
                continue;
            }

            [$width, $height] = getimagesize($image['fileData']);

            if ($width !== $this->width || $height !== $this->height) {
                $fail('The provided image is not the right size. Please submit an image that is exactly ' . $this->width . 'px x ' . $this->height . 'px.');
            }
        }
    }
}
