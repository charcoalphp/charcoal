<?php

declare(strict_types=1);

namespace Charcoal\Admin\Docs\Widget\FormGroup;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\FormGroupWidget;

/**
 *
 */
class DocFormGroup extends FormGroupWidget
{
    #[\Override]
    public function type(): string
    {
        return 'charcoal/admin/docs/widget/form-group-widget';
    }

    public function hidden(): bool
    {
        return !($this->description() || $this->notes() || count($this->groupProperties()));
    }
}
