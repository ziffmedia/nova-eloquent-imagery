<?php

namespace ZiffMedia\NovaEloquentImagery\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use InvalidArgumentException;

class MaxDimensions implements InvokableRule
{
    public function __construct(
        protected ?int $maxWidth = null,
        protected ?int $maxHeight = null
    ) {
        if ($this->maxWidth === null && $this->maxHeight === null) {
            throw new InvalidArgumentException('One of $maxWidth or $maxHeight is required');
        }
    }

    public function __invoke($attribute, $value, $fail)
    {
        $formData = json_decode($value, true);

        if (is_null($formData)) {
            return;
        }

        $images = array_is_list($formData) ? $formData : [$formData];

        foreach ($images as $image) {
            if (!isset($image['fileData'])) {
                continue;
            }

            [$width, $height] = getimagesize($image['fileData']);

            if ($width > $this->maxWidth || ($this->maxHeight > 0 && $height > $this->maxHeight)) {
                $fail($attribute.' is too big. Please submit an image that is less than or equal to '.$this->maxWidth.'px x '.$this->maxHeight.'px.');
            }
        }
    }
}
