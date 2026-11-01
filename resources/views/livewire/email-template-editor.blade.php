<div wire:ignore x-load x-load-src="{{ Vite::asset('resources/scripts/admin/emailTemplateEditor.ts') }}" x-data="emailTemplateEditor($wire, @js(trans('admin/email')), @js($previewValues))" class="email-template-editor" data-logo-url="{{ url(config('app.logo') ?: '/reviactyl/logo.png') }}" :inert="busy"
    @pointerup.window="stopFontSize()" @blur.window="stopFontSize()" @email-save.window="save()">
    <aside class="designify-editor__panel email-template-editor__inspector">
        <x-filament::section icon="tabler-mail" :heading="trans('admin/email.emails')">
            <label for="email-template-subject" class="email-field-label">{{ trans('admin/email.subject') }}</label>
            <x-filament::input.wrapper>
                <x-filament::input id="email-template-subject" x-model="draft.subject" @input="changed(false)" maxlength="255" />
            </x-filament::input.wrapper>
        </x-filament::section>

        <x-filament::section icon="tabler-typography" :heading="trans('admin/email.format')">
            <div class="email-fields">
                <div class="email-type-controls"><div><label class="email-field-label" for="email-font-size">{{ trans('admin/email.font_size') }}</label>
                <div class="email-font-size">
                    <button type="button" class="designify-editor__tool-button" aria-label="{{ trans('admin/email.decrease_size') }}" @mousedown.prevent="void 0" @pointerdown.prevent="startFontSize(-1, $event)" @pointercancel="stopFontSize()" @lostpointercapture="stopFontSize()" @click="if ($event.detail === 0) applyFontSize(-1)"><x-tabler-minus /></button>
                    <x-filament::input.wrapper>
                        <x-filament::input id="email-font-size" type="text" inputmode="decimal" x-model="fontSize" @change="applyFontSize()" @keydown.enter.prevent="applyFontSize()" />
                    </x-filament::input.wrapper>
                    <button type="button" class="designify-editor__tool-button" aria-label="{{ trans('admin/email.increase_size') }}" @mousedown.prevent="void 0" @pointerdown.prevent="startFontSize(1, $event)" @pointercancel="stopFontSize()" @lostpointercapture="stopFontSize()" @click="if ($event.detail === 0) applyFontSize(1)"><x-tabler-plus /></button>
                </div>
                </div><div><label class="email-field-label" for="email-text-color">{{ trans('admin/email.text_color') }}</label>
                <div class="email-color-field">
                    <input id="email-text-color" type="color" x-model="textColor" @change="focusSelection()" @input="styleSelection('color', $event.target.value)" />

                </div>
                </div>
                <div class="email-image-control"><button type="button" class="designify-editor__tool-button email-image-tool" aria-label="{{ trans('admin/email.image') }}" title="{{ trans('admin/email.image') }}" @mousedown.prevent="void 0" @click="openUrl('insertImage')"><x-tabler-photo /></button></div></div>
                <div class="email-format-grid" role="toolbar" aria-label="{{ trans('admin/email.format') }}">
                    @foreach (['bold' => 'bold', 'italic' => 'italic', 'underline' => 'underline', 'insertUnorderedList' => 'list'] as $command => $label)
                        <button type="button" class="designify-editor__tool-button" aria-label="{{ trans('admin/email.'.$label) }}" title="{{ trans('admin/email.'.$label) }}" @mousedown.prevent="void 0" @click="format('{{ $command }}')">
                            @switch($label)
                                @case('bold') <x-tabler-bold /> @break
                                @case('italic') <x-tabler-italic /> @break
                                @case('underline') <x-tabler-underline /> @break
                                @default <x-tabler-list />
                            @endswitch
                        </button>
                    @endforeach

                    @foreach (['justifyLeft' => 'left', 'justifyCenter' => 'center', 'justifyRight' => 'right'] as $command => $alignment)
                        <button type="button" class="designify-editor__tool-button" aria-label="{{ trans('admin/email.'.$alignment) }}" title="{{ trans('admin/email.'.$alignment) }}" @mousedown.prevent="void 0" @click="format('{{ $command }}')">
                            @switch($alignment)
                                @case('left') <x-tabler-align-left /> @break
                                @case('center') <x-tabler-align-center /> @break
                                @default <x-tabler-align-right />
                            @endswitch
                        </button>
                    @endforeach
                    <button type="button" class="designify-editor__tool-button" aria-label="{{ trans('admin/email.link') }}" title="{{ trans('admin/email.link') }}" @mousedown.prevent="void 0" @click="openUrl('createLink')"><x-tabler-link /></button>

                </div>
            </div>
        </x-filament::section>

        <x-filament::section icon="tabler-braces" :heading="trans('admin/email.variables')">
            <p class="email-field-help">{{ trans('admin/email.variables_help') }}</p>
            <div class="email-variable-list">
                <template x-for="token in placeholders" :key="token">
                    <button type="button" @mousedown.prevent="void 0" @click="insertPlaceholder(token)" class="email-variable">
                        <span x-text="labels[token]"></span>
                        <span class="email-insert-label">{{ trans('admin/email.insert') }}</span>
                    </button>
                </template>
            </div>
        </x-filament::section>

        <div class="email-html-action"><x-filament::button color="gray" icon="tabler-code" @click="openHtml()">{{ trans('admin/email.html') }}</x-filament::button></div>
        <dialog x-ref="htmlDialog" class="email-editor-dialog" aria-labelledby="email-html-title" @close="closeHtml()" @cancel.prevent="closeDialog($refs.htmlDialog)">
            <form @submit.prevent="applyHtml()">
                <h2 id="email-html-title">{{ trans('admin/email.html') }}</h2>
                <div x-ref="htmlEditor" class="email-html-editor"  :aria-busy="htmlLoading"></div>
                <div class="email-dialog-actions">
                    <x-filament::button color="gray" @click="closeDialog($refs.htmlDialog)">{{ trans('filament-actions::modal.actions.cancel.label') }}</x-filament::button>
                    <x-filament::button type="submit" x-bind:disabled="htmlLoading">{{ trans('filament-actions::edit.single.modal.actions.save.label') }}</x-filament::button>
                </div>
            </form>
        </dialog>
        <dialog x-ref="urlDialog" class="email-editor-dialog email-editor-dialog--url" @cancel.prevent="closeDialog($refs.urlDialog)" :aria-label="urlCommand === 'createLink' ? labels.link : labels.image">
            <form @submit.prevent="insertUrl()">
                <h2 x-text="urlCommand === 'createLink' ? labels.link : labels.image"></h2>
                <label class="sr-only" for="email-resource-url">URL</label>
                <x-filament::input.wrapper>
                    <x-filament::input id="email-resource-url" type="url" x-model="resourceUrl" required autofocus placeholder="https://" />
                </x-filament::input.wrapper>
                <template x-if="urlCommand === 'insertImage'">
                    <x-filament::button color="gray" size="sm" @click="resourceUrl = $el.closest('.email-template-editor').dataset.logoUrl">{{ trans('admin/email.panel_logo') }}</x-filament::button>
                </template>
                <div class="email-dialog-actions">
                    <x-filament::button color="gray" @click="closeDialog($refs.urlDialog)">{{ trans('filament-actions::modal.actions.cancel.label') }}</x-filament::button>
                    <x-filament::button type="submit">{{ trans('admin/email.insert') }}</x-filament::button>
                </div>
            </form>
        </dialog>
        <p role="status" x-text="status" class="email-editor-message"></p>
        <p role="alert" x-text="error" class="email-editor-message text-danger-600"></p>
    </aside>
    <section class="designify-editor__panel designify-editor__preview">
        <div class="designify-editor__preview-bar">
            <div class="designify-editor__preview-tools">
                <div class="email-type-picker">
                    <label for="email-template-type" class="sr-only">{{ trans('admin/email.type') }}</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select id="email-template-type" x-model="type" @change="selectTemplate()" :disabled="false" x-bind:disabled="busy">
                            @foreach ($types as $key => $label)
                                <option value="{{ $key }}">{{ trans('admin/email.'.$key) }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
                <div class="designify-editor__device-switcher">
                    @foreach (['desktop', 'tablet', 'mobile'] as $device)
                    <button type="button" class="designify-editor__tool-button" :aria-pressed="viewport === '{{ $device }}'" @click="setViewport('{{ $device }}')">
                        @switch($device)
                            @case('desktop') <x-tabler-device-desktop /> @break
                            @case('tablet') <x-tabler-device-tablet /> @break
                            @default <x-tabler-device-mobile />
                        @endswitch
                        <span>{{ trans('admin/settings.designify.editor-'.$device) }}</span>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="designify-editor__canvas">
            <div class="designify-editor__frame-shell" :data-viewport="viewport">
                <iframe x-ref="editor" title="{{ trans('admin/email.editor') }}" sandbox="allow-same-origin" class="designify-editor__frame" @load="bindEditor()"></iframe>
            </div>
        </div>
    </section>
</div>
