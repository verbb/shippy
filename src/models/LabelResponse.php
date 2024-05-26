<?php
namespace verbb\shippy\models;

class LabelResponse extends ResourceResponse
{
    // Properties
    // =========================================================================

    protected array $labels = [];


    // Public Methods
    // =========================================================================

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function setLabels(array $labels): LabelResponse
    {
        $this->labels = $labels;
        return $this;
    }
}
