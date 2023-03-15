<?php

namespace ZiffMedia\NovaEloquentImagery\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class MaxFileSize implements InvokableRule
{
    public function __construct(
        protected int $maxFileSize
    ) {}

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

            $imageBase64 = $image['fileData'];

            $filesize = strlen(base64_decode($imageBase64));

            if ($filesize > $this->maxFileSize) {
                $fail('File is too big. Please submit an image that is less than '.$this->maxFileSize.' bytes.');
            }
        }
    }
}
