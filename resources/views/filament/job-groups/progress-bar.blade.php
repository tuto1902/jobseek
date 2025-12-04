@php
    use Filament\Resources\RelationManagers\RelationManager;

    // Access the relation manager instance to get the owner record (job group)
    $jobGroup = $this instanceof RelationManager ? $this->getOwnerRecord() : null;

    // Get current record ID if editing - use the getMountedTableActionRecord method
    $currentRecordId = null;
    if ($this instanceof RelationManager && method_exists($this, 'getMountedTableActionRecord')) {
        $currentRecord = $this->getMountedTableActionRecord();
        $currentRecordId = $currentRecord?->id;
    }

    // Calculate current total excluding the record being edited
    $currentTotal = $jobGroup
        ? $jobGroup->assignments()
            ->when($currentRecordId, fn($query) => $query->where('id', '!=', $currentRecordId))
            ->sum('weight_percentage')
        : 0;

    // Get the value being entered in the form (reactive)
    $enteredWeight = (float) ($get('weight_percentage') ?? 0);

    // Calculate new total
    $newTotal = $currentTotal + $enteredWeight;

    // Calculate remaining available weight
    $remaining = 100 - $currentTotal;

    // Determine if the new total is valid
    $isValid = $newTotal <= 100.01; // Allow small floating point tolerance

    // Calculate percentage for progress bar
    $progressPercentage = min($newTotal, 100);
@endphp

<div class="rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 p-4 mb-4">
    <div class="space-y-3">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Job Group Weight Distribution
            </h3>
            <span class="text-xs font-mono {{ $isValid ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                {{ number_format($newTotal, 2) }}% / 100%
            </span>
        </div>

        {{-- Progress Bar --}}
        <div class="relative w-full h-8 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
            {{-- Current Total (Blue) --}}
            @if($currentTotal > 0)
                <div
                    class="absolute top-0 left-0 h-full bg-primary-500 dark:bg-primary-600 transition-all duration-300"
                    style="width: {{ min($currentTotal, 100) }}%"
                ></div>
            @endif

            {{-- Entered Weight (Success/Danger) --}}
            @if($enteredWeight > 0)
                <div
                    class="absolute top-0 h-full transition-all duration-300 {{ $isValid ? 'bg-success-500 dark:bg-success-600' : 'bg-danger-500 dark:bg-danger-600' }}"
                    style="left: {{ min($currentTotal, 100) }}%; width: {{ min($enteredWeight, 100 - $currentTotal) }}%"
                ></div>
            @endif

            {{-- Percentage Text Overlay --}}
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-xs font-medium text-white drop-shadow">
                    @if($enteredWeight > 0)
                        {{ number_format($currentTotal, 2) }}% + {{ number_format($enteredWeight, 2) }}% = {{ number_format($newTotal, 2) }}%
                    @else
                        {{ number_format($currentTotal, 2) }}%
                    @endif
                </span>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-2 text-xs">
            <div class="text-center p-2 rounded bg-white dark:bg-gray-900">
                <div class="text-gray-500 dark:text-gray-400">Current Total</div>
                <div class="font-semibold text-primary-600 dark:text-primary-400">{{ number_format($currentTotal, 2) }}%</div>
            </div>
            <div class="text-center p-2 rounded bg-white dark:bg-gray-900">
                <div class="text-gray-500 dark:text-gray-400">Entering</div>
                <div class="font-semibold {{ $enteredWeight > 0 ? ($isValid ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400') : 'text-gray-600 dark:text-gray-400' }}">
                    {{ number_format($enteredWeight, 2) }}%
                </div>
            </div>
            <div class="text-center p-2 rounded bg-white dark:bg-gray-900">
                <div class="text-gray-500 dark:text-gray-400">Available</div>
                <div class="font-semibold {{ $remaining > 0 ? 'text-gray-600 dark:text-gray-400' : 'text-danger-600 dark:text-danger-400' }}">
                    {{ number_format(max($remaining - $enteredWeight, 0), 2) }}%
                </div>
            </div>
        </div>

        {{-- Warning Message --}}
        @if(!$isValid && $enteredWeight > 0)
            <div class="flex items-start gap-2 p-2 rounded bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800">
                <svg class="w-4 h-4 text-danger-600 dark:text-danger-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div class="text-xs text-danger-700 dark:text-danger-300">
                    <strong>Weight exceeds 100%!</strong> Maximum available: {{ number_format($remaining, 2) }}%
                </div>
            </div>
        @elseif($newTotal == 100 && $enteredWeight > 0)
            <div class="flex items-start gap-2 p-2 rounded bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800">
                <svg class="w-4 h-4 text-success-600 dark:text-success-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div class="text-xs text-success-700 dark:text-success-300">
                    <strong>Perfect!</strong> Total weight will equal 100%
                </div>
            </div>
        @endif
    </div>
</div>
