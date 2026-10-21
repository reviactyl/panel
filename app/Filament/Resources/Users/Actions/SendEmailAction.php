<?php

namespace App\Filament\Resources\Users\Actions;

use App\Filament\AdminAccess;
use App\Models\User;
use App\Notifications\AdminMessage;
use App\Services\Acl\RolePermission;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;

class SendEmailAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'sendEmail';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(trans('admin/user.email_message.send'))
            ->icon('tabler-mail')
            ->authorize(fn (): bool => AdminAccess::allows(RolePermission::RESOURCE_USERS, RolePermission::SEND_EMAIL))
            ->modalDescription(fn (User $record): string => trans('admin/user.email_message.recipient', ['email' => $record->email]))
            ->modalSubmitActionLabel(trans('admin/user.email_message.send'))
            ->schema([
                TextInput::make('subject')
                    ->label(trans('admin/user.email_message.subject'))
                    ->required()
                    ->maxLength(255)
                    ->rules(['not_regex:/[\r\n]/']),
                RichEditor::make('message')
                    ->label(trans('admin/user.email_message.message'))
                    ->required()
                    ->maxLength(50000)
                    ->rule(fn (RichEditor $component): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($component): void {
                        if (mb_strlen($component->getTipTapEditor()->setContent($value)->getHtml()) > 50000) {
                            $fail('validation.max.string')->translate(['max' => 50000]);
                        }
                    })
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('email-images')
                    ->fileAttachmentsVisibility('public')
                    ->fileAttachmentsMaxSize(2048)
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'link'],
                        ['h2', 'h3', 'blockquote', 'bulletList', 'orderedList'],
                        ['table', 'attachFiles', 'undo', 'redo'],
                    ])
                    ->hintActions([
                        Action::make('editHtml')
                            ->label(trans('admin/user.email_message.edit_html'))
                            ->icon('tabler-code')
                            ->authorize(fn (): bool => AdminAccess::allows(RolePermission::RESOURCE_USERS, RolePermission::SEND_EMAIL))
                            ->fillForm(fn (RichEditor $component): array => ['html' => $component->getState()])
                            ->schema([
                                Textarea::make('html')
                                    ->label('HTML')
                                    ->required()
                                    ->maxLength(50000)
                                    ->rows(12),
                            ])
                            ->action(function (RichEditor $component, array $data): void {
                                $component->runCommands([
                                    EditorCommand::make('setContent', arguments: [AdminMessage::sanitize($data['html'])]),
                                ]);
                            }),
                        Action::make('previewEmail')
                            ->label(trans('admin/user.email_message.preview'))
                            ->icon('tabler-eye')
                            ->authorize(fn (): bool => AdminAccess::allows(RolePermission::RESOURCE_USERS, RolePermission::SEND_EMAIL))
                            ->modalWidth(Width::FourExtraLarge)
                            ->modalSubmitAction(false)
                            ->modalContent(fn (RichEditor $component, Get $get) => view('filament.users.email-preview', [
                                'html' => (new AdminMessage($get('subject') ?? '', $component->getState() ?? ''))->renderHtml(),
                            ])),
                    ]),
            ])
            ->action(function (User $record, array $data): void {
                abort_unless(AdminAccess::allows(RolePermission::RESOURCE_USERS, RolePermission::SEND_EMAIL), 403);

                try {
                    $record->notify(new AdminMessage($data['subject'], $data['message']));
                } catch (\Exception $exception) {
                    report($exception);

                    Notification::make()
                        ->title(trans('admin/user.email_message.failed'))
                        ->danger()
                        ->send();

                    $this->halt();
                }

                Notification::make()
                    ->title(trans('admin/user.email_message.sent'))
                    ->success()
                    ->send();
            });
    }
}
