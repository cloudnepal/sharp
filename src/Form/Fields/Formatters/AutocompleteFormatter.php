<?php

namespace Code16\Sharp\Form\Fields\Formatters;

use Code16\Sharp\Form\Fields\SharpFormField;
use Code16\Sharp\Utils\Transformers\ArrayConverter;

class AutocompleteFormatter extends SharpFieldFormatter
{
    /**
     * @return mixed
     */
    public function toFront(SharpFormField $field, $value)
    {
        $value = ArrayConverter::modelToArray($value);

        return is_null($value) || is_array($value)
            ? $value
            : [$field->itemIdAttribute() => $value];
    }

    /**
     * @return mixed
     */
    public function fromFront(SharpFormField $field, string $attribute, $value)
    {
        return is_array($value)
            ? $value[$field->itemIdAttribute()]
            : $value;
    }
}
