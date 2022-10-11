<?php

namespace Filament\Resources\Pages;

use Closure;
use Filament\Pages\Actions\Action;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\Concerns\UsesResourceForm;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ListRecords extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;
    use UsesResourceForm;

    protected static string $view = 'filament::resources.pages.list-records';

    protected $queryString = [
        'isTableReordering' => ['except' => false],
        'tableFilters',
        'tableSortColumn' => ['except' => ''],
        'tableSortDirection' => ['except' => ''],
        'tableSearchQuery' => ['except' => ''],
    ];

    public function mount(): void
    {
        static::authorizeResourceAccess();
    }

    public function getBreadcrumb(): ?string
    {
        return static::$breadcrumb ?? __('filament::resources/pages/list-records.breadcrumb');
    }

    protected function getResourceTable(): Table
    {
        return $this->table(Table::make());
    }

    protected function table(Table $table): Table
    {
        return static::getResource()::table($table);
    }

    protected function getTitle(): string
    {
        return static::$title ?? Str::headline(static::getResource()::getPluralModelLabel());
    }

    protected function configureAction(Action $action): void
    {
        match (true) {
            $action instanceof CreateAction => $this->configureCreateAction($action),
            default => null,
        };
    }

    protected function getCreateFormSchema(): array
    {
        return $this->getResourceForm(columns: 2)->getSchema();
    }

    protected function configureCreateAction(CreateAction $action): void
    {
        $resource = static::getResource();

        $action
            ->authorize($resource::canCreate())
            ->model($this->getModel())
            ->modelLabel($this->getModelLabel())
            ->form($this->getCreateFormSchema());

        if ($resource::hasPage('create')) {
            $action->url(fn (): string => $resource::getUrl('create'));
        }
    }

    protected function configureTableAction(Tables\Actions\Action $action): void
    {
        match (true) {
            $action instanceof Tables\Actions\DeleteAction => $this->configureDeleteAction($action),
            $action instanceof Tables\Actions\EditAction => $this->configureEditAction($action),
            $action instanceof Tables\Actions\ForceDeleteAction => $this->configureForceDeleteAction($action),
            $action instanceof Tables\Actions\ReplicateAction => $this->configureReplicateAction($action),
            $action instanceof Tables\Actions\RestoreAction => $this->configureRestoreAction($action),
            $action instanceof Tables\Actions\ViewAction => $this->configureViewAction($action),
            default => null,
        };
    }

    protected function configureDeleteAction(Tables\Actions\DeleteAction $action): void
    {
        $action
            ->authorize(fn (Model $record): bool => static::getResource()::canDelete($record));
    }

    protected function getEditFormSchema(): array
    {
        return $this->getResourceForm(columns: 2)->getSchema();
    }

    protected function configureEditAction(Tables\Actions\EditAction $action): void
    {
        $resource = static::getResource();

        $action
            ->authorize(fn (Model $record): bool => $resource::canEdit($record))
            ->form($this->getEditFormSchema());

        if ($resource::hasPage('edit')) {
            $action->url(fn (Model $record): string => $resource::getUrl('edit', ['record' => $record]));
        }
    }

    protected function configureForceDeleteAction(Tables\Actions\ForceDeleteAction $action): void
    {
        $action
            ->authorize(fn (Model $record): bool => static::getResource()::canForceDelete($record));
    }

    protected function configureReplicateAction(Tables\Actions\ReplicateAction $action): void
    {
        $action
            ->authorize(fn (Model $record): bool => static::getResource()::canReplicate($record));
    }

    protected function configureRestoreAction(Tables\Actions\RestoreAction $action): void
    {
        $action
            ->authorize(fn (Model $record): bool => static::getResource()::canRestore($record));
    }

    protected function getViewFormSchema(): array
    {
        return $this->getResourceForm(columns: 2, isDisabled: true)->getSchema();
    }

    protected function configureViewAction(Tables\Actions\ViewAction $action): void
    {
        $resource = static::getResource();

        $action
            ->authorize(fn (Model $record): bool => $resource::canView($record))
            ->form($this->getViewFormSchema());

        if ($resource::hasPage('view')) {
            $action->url(fn (Model $record): string => $resource::getUrl('view', ['record' => $record]));
        }
    }

    protected function configureTableBulkAction(BulkAction $action): void
    {
        match (true) {
            $action instanceof Tables\Actions\DeleteBulkAction => $this->configureDeleteBulkAction($action),
            $action instanceof Tables\Actions\ForceDeleteBulkAction => $this->configureForceDeleteBulkAction($action),
            $action instanceof Tables\Actions\RestoreBulkAction => $this->configureRestoreBulkAction($action),
            default => null,
        };
    }

    protected function configureDeleteBulkAction(Tables\Actions\DeleteBulkAction $action): void
    {
        $action
            ->authorize(static::getResource()::canDeleteAny());
    }

    protected function configureForceDeleteBulkAction(Tables\Actions\ForceDeleteBulkAction $action): void
    {
        $action
            ->authorize(static::getResource()::canForceDeleteAny());
    }

    protected function configureRestoreBulkAction(Tables\Actions\RestoreBulkAction $action): void
    {
        $action
            ->authorize(static::getResource()::canRestoreAny());
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return $this->getResourceTable()->getDefaultSortColumn();
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return $this->getResourceTable()->getDefaultSortDirection();
    }

    protected function getTableActions(): array
    {
        return $this->getResourceTable()->getActions();
    }

    protected function getTableActionsPosition(): ?string
    {
        return $this->getResourceTable()->getActionsPosition();
    }

    protected function getTableBulkActions(): array
    {
        return $this->getResourceTable()->getBulkActions();
    }

    protected function getTableColumns(): array
    {
        return $this->getResourceTable()->getColumns();
    }

    protected function getTableContentGrid(): ?array
    {
        return $this->getResourceTable()->getContentGrid();
    }

    protected function getTableFilters(): array
    {
        return $this->getResourceTable()->getFilters();
    }

    protected function getTableFiltersLayout(): ?string
    {
        return $this->getResourceTable()->getFiltersLayout();
    }

    protected function getTableHeaderActions(): array
    {
        return $this->getResourceTable()->getHeaderActions();
    }

    protected function getTableReorderColumn(): ?string
    {
        return $this->getResourceTable()->getReorderColumn();
    }

    protected function isTableReorderable(): bool
    {
        return filled($this->getTableReorderColumn()) && static::getResource()::canReorder();
    }

    protected function getTablePollingInterval(): ?string
    {
        return $this->getResourceTable()->getPollingInterval();
    }

    protected function getTableRecordUrlUsing(): ?Closure
    {
        return function (Model $record): ?string {
            foreach (['view', 'edit'] as $action) {
                $action = $this->getCachedTableAction($action);

                if (! $action) {
                    continue;
                }

                $action->record($record);

                if ($action->isHidden()) {
                    continue;
                }

                $url = $action->getUrl();

                if (! $url) {
                    continue;
                }

                return $url;
            }

            $resource = static::getResource();

            foreach (['view', 'edit'] as $action) {
                if (! $resource::hasPage($action)) {
                    continue;
                }

                if (! $resource::{'can' . ucfirst($action)}($record)) {
                    continue;
                }

                return $resource::getUrl($action, ['record' => $record]);
            }

            return null;
        };
    }

    protected function getTableRecordActionUsing(): ?Closure
    {
        return function (Model $record): ?string {
            foreach (['view', 'edit'] as $action) {
                $action = $this->getCachedTableAction($action);

                if (! $action) {
                    continue;
                }

                $action->record($record);

                if ($action->isHidden()) {
                    continue;
                }

                if ($action->getUrl()) {
                    continue;
                }

                return $action->getName();
            }

            return null;
        };
    }

    protected function getTableQuery(): Builder
    {
        return static::getResource()::getEloquentQuery();
    }

    protected function getMountedActionFormModel(): string
    {
        return $this->getModel();
    }

    public function getTableRecordTitle(Model $record): string
    {
        $resource = static::getResource();

        return $resource::getRecordTitle($record);
    }

    public function getModelLabel(): string
    {
        return static::getResource()::getModelLabel();
    }

    public function getPluralModelLabel(): string
    {
        return static::getResource()::getPluralModelLabel();
    }

    public function getTableModelLabel(): string
    {
        return $this->getModelLabel();
    }

    public function getTablePluralModelLabel(): string
    {
        return $this->getPluralModelLabel();
    }
}
