<x-filament::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ trans('admin/navigation.administration.activity_log') }}
        </x-slot>

        <div class="space-y-4">
            @foreach($this->getActivities() as $activity)
                <div class="flex items-start gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $activity->actor?->name ?? 'System' }} 
                            <span class="font-normal text-gray-500">
                                {{ $activity->event }}
                            </span>
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            @foreach($activity->subjects as $subject)
                                {{ $subject->subject_type }}: {{ $subject->subject_id }} 
                            @endforeach
                            • {{ $activity->timestamp->diffForHumans() }}
                        </p>
                    </div>
                </div>
            @endforeach

            @if($this->getActivities()->isEmpty())
                <p class="text-sm text-gray-500 italic">
                    {{ trans('admin/index.no_activity') }}
                </p>
            @endif
        </div>
    </x-filament::section>
</x-filament::widget>
