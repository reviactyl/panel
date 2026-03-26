<?php

namespace App\Filament\Widgets;

use App\Models\Node;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;

class NodeSelectorWidget extends BaseWidget
{
    protected static bool $isDiscoverable = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -1;

    public ?int $nodeId = null;

    public function mount(): void
    {
    }

    public function updatedNodeId(): void
    {
        $this->dispatch('nodeChanged', nodeId: $this->nodeId);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('nodeId')
                ->label(trans('admin/monitoring.selector.label'))
                ->placeholder(trans('admin/monitoring.selector.placeholder'))
                ->options(Node::query()->orderBy('name')->pluck('name', 'id'))
                ->live()
                ->afterStateUpdated(fn ($state) => $this->dispatch('nodeChanged', nodeId: $state)),
        ]);
    }
}
