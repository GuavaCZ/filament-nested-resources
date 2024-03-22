<?php

namespace Guava\FilamentNestedResources\Concerns\CreateRelatedRecord;

use Filament\Facades\Filament;
use Filament\Resources\Concerns\HasActiveLocaleSwitcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

trait Translatable
{

    use HasActiveLocaleSwitcher;

    protected ?string $oldActiveLocale = null;

    #[Locked]
    public $otherLocaleData = [];

    public function mountTranslatable(): void
    {
        $resource = Filament::getModelResource($this->getRelation()->getRelated());

        $this->activeLocale = $resource::getDefaultTranslatableLocale();
    }

    public function getTranslatableLocales(): array
    {
        $resource = Filament::getModelResource($this->getRelation()->getRelated());

        return $resource::getTranslatableLocales();
    }

    protected function handleRecordCreation(array $data): Model
    {
        $model = $this->getRelation()->getRelated();
        $resource = Filament::getModelResource($model);

        $record = app($model::class);

        $translatableAttributes = $resource::getTranslatableAttributes();

        $record->fill(Arr::except($data, $translatableAttributes));

        foreach (Arr::only($data, $translatableAttributes) as $key => $value) {
            $record->setTranslation($key, $this->activeLocale, $value);
        }

        $originalData = $this->data;

        foreach ($this->otherLocaleData as $locale => $localeData) {
            $this->data = [
                ...$this->data,
                ...$localeData,
            ];

            try {
                $this->form->validate();
            } catch (ValidationException $exception) {
                continue;
            }

            $localeData = $this->mutateFormDataBeforeCreate($localeData);

            foreach (Arr::only($localeData, $translatableAttributes) as $key => $value) {
                $record->setTranslation($key, $locale, $value);
            }
        }

        $this->data = $originalData;

        if ($owner = $this->getOwnerRecord()) {
            $record = $this->associateRecordWithParent($record, $owner);
        }

        if (
            $resource::isScopedToTenant() &&
            ($tenant = Filament::getTenant())
        ) {
            return $this->associateRecordWithTenant($record, $tenant);
        }

        $record->save();

        return $record;
    }

    public function updatingActiveLocale(): void
    {
        $this->oldActiveLocale = $this->activeLocale;
    }

    public function updatedActiveLocale(string $newActiveLocale): void
    {
        if (blank($this->oldActiveLocale)) {
            return;
        }

        $this->resetValidation();
        $resource = Filament::getModelResource($this->getRelation()->getRelated());

        $translatableAttributes = $resource::getTranslatableAttributes();

        $this->otherLocaleData[$this->oldActiveLocale] = Arr::only($this->data, $translatableAttributes);

        $this->data = [
            ...Arr::except($this->data, $translatableAttributes),
            ...$this->otherLocaleData[$this->activeLocale] ?? [],
        ];

        unset($this->otherLocaleData[$this->activeLocale]);
    }
}