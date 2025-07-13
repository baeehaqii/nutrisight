<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Concerns\InteractsWithActions;

class ActionShortcuts extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function meetNow(): Action
    {
        return Action::make('meetNow')
            ->color('primary')
            ->label('Scan Produk')
            ->keyBindings(['command+m', 'ctrl+m'])
            ->extraAttributes(['class' => 'w-full'])
            ->url('/panel/scan-produks/create');
    }

    public function schedule(): Action
    {
        return Action::make('schedule')
            ->outlined()
            ->color('gray')
            ->label('Scan Dengan File')
            ->extraAttributes(['class' => 'w-full'])
            ->url('/panel/scan-produks/create');
    }
    public function render()
    {
        return <<<'HTML'
        <div class="flex flex-col gap-4 bg-amber-900">
            {{ $this->meetNow()}}
            {{ $this->schedule() }}
        </div>
        HTML;
    }
}
