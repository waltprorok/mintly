<?php

namespace App\Filament\Tables\Columns;

use Closure;
use Filament\Tables\Columns\ToggleColumn;

class ToggleColumnWithDescription extends ToggleColumn
{
    protected string|Closure|null $description = null;

    public function description(string|Closure|null $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function toEmbeddedHtml(): string
    {
        $html = parent::toEmbeddedHtml();

        $description = $this->evaluate($this->description, [
            'record' => $this->getRecord(),
        ]);

        if (blank($description)) {
            return $html;
        }

        return '
        <div style="display: inline-flex; flex-direction: column; align-items: center;">
            <div style="display: flex; align-items: center;">
                ' . $html . '
            </div>

            <span style="font-size: 11px; line-height: 1; color: #9ca3af; white-space: nowrap;">
                ' . e($description) . '
            </span>
        </div>
    ';
    }
}
