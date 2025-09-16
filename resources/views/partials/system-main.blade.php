<div class="tab-content active" id="main">
    <div class="section">
        <h3>Общая информация</h3>
        <div class="grid-2col">
            <div class="info-row">
                <span class="info-label">Филиал:</span>
                <span class="info-value">{{ $system->object->branch->name ?? 'Не указан' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Объект защиты:</span>
                <span class="info-value">{{ $system->object->name ?? 'Не указан' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Инвентарный номер системы:</span>
                <span class="info-value">{{ $system->systemInventoryNumber ?? 'Не указан' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Тип системы:</span>
                <span class="info-value">{{ $system->subtype->type->name ?? 'Не указан' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Подтип системы:</span>
                <span class="info-value">{{ $system->subtype->name ?? 'Не указан' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Наименование:</span>
                <span class="info-value">{{ $system->name ?? 'Не указано' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">В составе объекта:</span>
                <span class="info-value">{{ $system->isPartOfObject ? 'Да' : 'Нет' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Дата последнего обновления:</span>
                <span
                    class="info-value">{{ $system->updatedAt ? $system->updatedAt->format('d.m.Y H:i') : 'Не указана' }}</span>
            </div>
        </div>
    </div>
</div>