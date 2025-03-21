<?php

namespace Guava\FilamentNestedResources\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\HasUnsavedDataChangesAlert;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;

use function Filament\Support\is_app_url;

/**
 * @property string $nestedResource
 */
class CreateRelatedRecord extends Page
{
    use HasUnsavedDataChangesAlert;
    use InteractsWithFormActions;
    use InteractsWithRecord {
        configureAction as configureActionRecord;
    }

    protected static string $view = 'filament-panels::resources.pages.create-record';

    public Model | int | string | null $ownerRecord;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    protected static bool $canCreateAnother = true;

    public ?string $previousUrl = null;

    public function mount(int | string $record): void
    {
        $this->ownerRecord = $this->resolveRecord($record);
        $this->record = null;

        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }

    public function getRelation(): Relation
    {
        $relationship = static::getRelationship();

        return $this->getOwnerRecord()->{$relationship}();
    }

    public static function getRelationship(): string
    {
        return static::$relationship;
    }

    public function mountCanAuthorizeAccess(): void
    {
        abort_unless(static::canAccess(['record' => $this->getOwnerRecord()]), 403);
    }

    public function getOwnerRecord(): Model
    {
        return $this->ownerRecord;
    }

    public function getRecord(): Model
    {
        return $this->record ?? $this->ownerRecord;
    }

    protected function authorizeAccess(): void
    {
        abort_unless($this->getNestedResource()::canCreate(), 403);
    }

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $this->form->fill();

        $this->callHook('afterFill');
    }

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        try {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeCreate($data);

            $this->callHook('beforeCreate');

            $this->record = $this->handleRecordCreation($data);

            $this->form->model($this->getRecord())->saveRelationships();

            $this->callHook('afterCreate');
        } catch (Halt $exception) {
            return;
        }

        $this->rememberData();

        $this->getCreatedNotification()?->send();

        if ($another) {
            // Ensure that the form record is anonymized so that relationships aren't loaded.
            $this->form->model($this->getRecord()::class);
            $this->record = null;

            $this->fillForm();

            return;
        }

        $redirectUrl = $this->getRedirectUrl();

        $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
    }

    protected function getCreatedNotification(): ?Notification
    {
        $title = $this->getCreatedNotificationTitle();

        if (blank($title)) {
            return null;
        }

        return Notification::make()
            ->success()
            ->title($title)
        ;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return $this->getCreatedNotificationMessage() ?? __('filament-panels::resources/pages/create-record.notifications.created.title');
    }

    /**
     * @deprecated Use `getCreatedNotificationTitle()` instead.
     */
    protected function getCreatedNotificationMessage(): ?string
    {
        return null;
    }

    public function createAnother(): void
    {
        $this->create(another: true);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $record = new ($this->getRelation()->getRelated())($data);

        if ($owner = $this->getOwnerRecord()) {
            $record = $this->associateRecordWithParent($record, $owner);
        }

        if (
            $this->getNestedResource($record)::isScopedToTenant() &&
            ($tenant = Filament::getTenant())
        ) {
            return $this->associateRecordWithTenant($record, $tenant);
        }
        $record->save();

        return $record;
    }

    protected function associateRecordWithParent(Model $record, Model $owner)
    {
        /** @var HasMany $relationship */
        if (($relationship = $this->getRelation()) instanceof HasMany) {
            $record->{$relationship->getForeignKeyName()} = $owner->getKey();
        }
        if (($relationship = $this->getRelation()) instanceof MorphMany) {
            $record->{$relationship->getForeignKeyName()} = $owner->getKey();
            $record->{$relationship->getMorphType()} = $owner::class;
        }

        return $record;
    }

    protected function associateRecordWithTenant(Model $record, Model $tenant): Model
    {
        $relationship = $this->getNestedResource($record)::getTenantRelationship($tenant);

        if ($relationship instanceof HasManyThrough) {
            $record->save();

            return $record;
        }

        return $relationship->save($record);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            ...(static::canCreateAnother() ? [$this->getCreateAnotherFormAction()] : []),
            $this->getCancelFormAction(),
        ];
    }

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label(__('filament-panels::resources/pages/create-record.form.actions.create.label'))
            ->submit('create')
            ->keyBindings(['mod+s'])
        ;
    }

    protected function getSubmitFormAction(): Action
    {
        return $this->getCreateFormAction();
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return Action::make('createAnother')
            ->label(__('filament-panels::resources/pages/create-record.form.actions.create_another.label'))
            ->action('createAnother')
            ->keyBindings(['mod+shift+s'])
            ->color('gray')
        ;
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label(__('filament-panels::resources/pages/create-record.form.actions.cancel.label'))
            ->url($this->previousUrl ?? static::getResource()::getUrl())
            ->color('gray')
        ;
    }

    public function getBreadcrumb(): string
    {
        return static::$breadcrumb ?? __('filament-panels::resources/pages/create-record.breadcrumb');
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        $resource = $this->getNestedResource();

        return [
            'form' => $this->form($resource::form(
                $this->makeForm()
                    ->operation('create')
                    ->model($this->getRelation()->getRelated()::class)
                    ->statePath($this->getFormStatePath())
                    ->columns($this->hasInlineLabels() ? 1 : 2)
                    ->inlineLabel($this->hasInlineLabels()),
            )),
        ];
    }

    public function getFormStatePath(): ?string
    {
        return 'data';
    }

    public function getNestedResource(?Model $record = null): string
    {
        if (property_exists(static::class, 'nestedResource')) {
            return static::$nestedResource;
        }

        return Filament::getModelResource($record ?? $this->getRelation()->getRelated());
    }

    public static function canCreateAnother(): bool
    {
        return static::$canCreateAnother;
    }

    public static function disableCreateAnother(): void
    {
        static::$canCreateAnother = false;
    }

    protected function getRedirectUrl(): string
    {
        $resource = $this->getNestedResource($this->getRecord());

        if ($resource::hasPage('view') && $resource::canView($this->getRecord())) {
            return $resource::getUrl('view', ['record' => $this->getRecord(), ...$this->getRedirectUrlParameters()]);
        }

        if ($resource::hasPage('edit') && $resource::canEdit($this->getRecord())) {
            return $resource::getUrl('edit', ['record' => $this->getRecord(), ...$this->getRedirectUrlParameters()]);
        }

        return $resource::getUrl('index');
    }

    protected function getRedirectUrlParameters(): array
    {
        return [];
    }
}
