<?php

namespace App\Services\Subusers;

use App\Models\SubuserPreviewSession;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SubuserPreviewStateService
{
    /**
     * Mutate a preview session while holding a row lock so concurrent requests
     * cannot overwrite each other's changes.
     */
    public function update(SubuserPreviewContext $context, callable $callback): void
    {
        DB::transaction(function () use ($context, $callback) {
            $session = SubuserPreviewSession::query()
                ->whereKey($context->session()->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $state = $callback($session->state ?? ['power_status' => null, 'files' => []]);
            $encoded = json_encode($state, JSON_THROW_ON_ERROR);
            $maximum = (int) config('panel.files.max_preview_state_size');

            if ($maximum > 0 && strlen($encoded) > $maximum) {
                throw new BadRequestHttpException(trans('exceptions.subuser_preview.state_too_large'));
            }

            $session->state = $state;
            $session->save();
        });

        $context->session()->refresh();
    }
}
