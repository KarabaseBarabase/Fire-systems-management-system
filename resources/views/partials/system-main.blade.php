<div class="section">
    <h3>Общая информация</h3>

    <!-- Отладочная информация -->
    <div style="display: none;">
        <p>Branch: {{ json_encode($branch) }}</p>
        <p>Object: {{ json_encode($object) }}</p>
        <p>Subtype: {{ json_encode($subtype) }}</p>
        <p>System Type: {{ json_encode($system_type) }}</p>
        <p>System objectId: {{ $system->objectId }}</p>
        <p>System subtypeId: {{ $system->subtypeId }}</p>
    </div>

    <div class="grid-2col">
        <div class="info-row">
            <span class="info-label">Филиал:</span>
            <span class="info-value">
                @if($branch && !empty($branch->name))
                    {{ $branch->name }}
                @elseif($object && $object->branchId && method_exists($object, 'getBranch'))
                    {{ $object->getBranch()->name ?? 'Не указан' }}
                @else
                    Не указан
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Объект защиты:</span>
            <span class="info-value">
                @if($object && !empty($object->name))
                    {{ $object->name }}
                @elseif($system->objectId)
                    Объект #{{ $system->objectId }}
                @else
                    Не указан
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Инвентарный номер системы:</span>
            <span class="info-value">{{ $system->systemInventoryNumber ?? 'Не указан' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Тип системы:</span>
            <span class="info-value">
                @if($system_type && !empty($system_type->name))
                    {{ $system_type->name }}
                @elseif($subtype && $subtype->typeId && method_exists($subtype, 'getType'))
                    {{ $subtype->getType()->name ?? 'Не указан' }}
                @else
                    Не указан
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Подтип системы:</span>
            <span class="info-value">
                @if($subtype && !empty($subtype->name))
                    {{ $subtype->name }}
                @elseif($system->subtypeId)
                    Подтип #{{ $system->subtypeId }}
                @else
                    Не указан
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Наименование:</span>
            <span class="info-value">{{ $system->name ?? 'Не указано' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">В составе объекта:</span>
            <span class="info-value">{{ $system->isPartOfObject ? 'Да' : 'Нет' }}</span>
        </div>

        @if($system->manualFileLink)
            <div class="info-row">
                <span class="info-label">Инструкция по эксплуатации:</span>
                <span class="info-value">
                    <a href="{{ $system->manualFileLink }}" target="_blank" class="text-primary">
                        <i class="icon-download"></i> Скачать
                    </a>
                </span>
            </div>
        @endif

        @if($system->maintenanceScheduleFileLink)
            <div class="info-row">
                <span class="info-label">График ТО:</span>
                <span class="info-value">
                    <a href="{{ $system->maintenanceScheduleFileLink }}" target="_blank" class="text-primary">
                        <i class="icon-download"></i> Скачать
                    </a>
                </span>
            </div>
        @endif

        @if($system->testProgramFileLink)
            <div class="info-row">
                <span class="info-label">Программа испытаний:</span>
                <span class="info-value">
                    <a href="{{ $system->testProgramFileLink }}" target="_blank" class="text-primary">
                        <i class="icon-download"></i> Скачать
                    </a>
                </span>
            </div>
        @endif

        @if($system->recordUuid)
            <div class="info-row">
                <span class="info-label">UUID системы:</span>
                <span class="info-value">
                    <code class="small">{{ $system->recordUuid }}</code>
                </span>
            </div>
        @endif

        <div class="info-row">
            <span class="info-label">Дата последнего обновления:</span>
            <span class="info-value">
                {{ $system->updatedAt ? $system->updatedAt->format('d.m.Y H:i') : 'Не указана' }}
            </span>
        </div>

        @if($system->updatedBy)
            <div class="info-row">
                <span class="info-label">Кем обновлено:</span>
                <span class="info-value">
                    Пользователь #{{ $system->updatedBy }}
                </span>
            </div>
        @endif

    </div>
</div>