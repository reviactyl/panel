<?php

namespace App\Filament\Components;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;

class Alert extends Component
{
    public const ACTIONS_SCHEMA_KEY = 'actions';

    /**
     * @var view-string
     */
    protected string $view = 'filament.components.alert';

    protected string|Htmlable|Closure|null $description = null;

    protected string|Htmlable|Closure|null $title = null;

    protected string|Closure $type = 'info';

    final public function __construct() {}

    public static function make(): static
    {
        $static = app(static::class);
        $static->configure();

        return $static;
    }

    public function description(string|Htmlable|Closure|null $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function title(string|Htmlable|Closure|null $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function type(string|Closure $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function info(): static
    {
        return $this->type('info');
    }

    public function success(): static
    {
        return $this->type('success');
    }

    public function warning(): static
    {
        return $this->type('warning');
    }

    public function danger(): static
    {
        return $this->type('danger');
    }

    /**
     * @param  array<Action | ActionGroup> | Closure  $actions
     */
    public function actions(array|Closure $actions): static
    {
        $this->childComponents(
            Actions::make($actions),
            static::ACTIONS_SCHEMA_KEY,
        );

        return $this;
    }

    public function getDescription(): string|Htmlable|null
    {
        return $this->evaluate($this->description);
    }

    public function getTitle(): string|Htmlable|null
    {
        return $this->evaluate($this->title);
    }

    public function getType(): string
    {
        $type = $this->evaluate($this->type);

        return in_array($type, ['info', 'success', 'warning', 'danger'], true) ? $type : 'info';
    }
}
