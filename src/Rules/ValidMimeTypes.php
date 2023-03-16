<?php

namespace ZiffMedia\NovaEloquentImagery\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Collection;

class ValidMimeTypes implements InvokableRule
{
    public function __construct(
        protected Collection|array $validMimeTypes
    ) {
        if (is_array($this->validMimeTypes)) {
            $this->validMimeTypes = new Collection($this->validMimeTypes);
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
            if (! isset($image['fileData'])) {
                continue;
            }

            $data = getimagesize($image['fileData']);

            if ($this->validMimeTypes->doesntContain($data['mime'])) {
                $fail('Invalid image type. Please submit one of: '
                    . $this->validMimeTypes->implode(', ')
                );
            }
        }
    }
}
