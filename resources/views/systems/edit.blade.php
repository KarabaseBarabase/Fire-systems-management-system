<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование системы - {{ $system->name ?? 'Новая система' }}</title>
    <link rel="stylesheet" href="{{ asset('css/edit.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <div class="header-left">
                <img src="#" alt="Логотип" class="logo">
                <h1>Мониторинг пожарных систем</h1>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <span class="user-name">{{ $userFullName }}</span>
                    <span class="user-role">{{ $userRole }}</span>
                </div>
                <button class="btn btn-secondary" onclick="window.history.back()">Назад</button>
            </div>
        </header>

        <div class="main-content">
            <nav class="sidebar">
                <ul class="menu">
                    <li class="menu-item" onclick="window.location.href='/'">
                        <span>Все филиалы</span>
                    </li>
                    <li class="menu-item">
                        <span>Аналитика</span>
                    </li>
                    <li class="menu-item active">
                        <span>Редактирование</span>
                    </li>
                    <li class="menu-item">
                        <span>Подтверждение</span>
                    </li>
                </ul>
            </nav>

            <div class="content-area">
                <div class="edit-form-container">
                    <div class="form-header">
                        <h2 class="form-title">
                            {{ isset($system) ? 'Редактирование системы' : 'Добавление новой системы' }}
                        </h2>
                    </div>

                    <form id="systemForm" 
                        action="{{ isset($system) ? route('system.update', $system->systemId) : route('system.create') }}" 
                        method="POST">
                        @csrf
                        @if(isset($system))
                            @method('PUT')
                        @endif
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" 
                                onclick="window.history.back()">
                                Отмена
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Сохранить
                            </button>
                            @if(isset($system))
                            <button type="button" class="btn btn-danger" 
                                onclick="confirmDelete('{{ $system->systemId }}', '{{ $system->name }}')">
                                Удалить
                            </button>
                            @endif
                        </div>

                        <!-- FireSystem: Основная информация -->
                        <div class="form-section">
                            <h3 class="section-title">Основная информация системы</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="name" class="form-label required">
                                        Название системы
                                    </label>
                                    <input type="text" 
                                        id="name" 
                                        name="name" 
                                        class="form-input" 
                                        value="{{ old('name', $system->name ?? '') }}" 
                                        required>
                                    @error('name')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Сначала выбор типа системы -->
                                <div class="form-group">
                                    <label for="systemType" class="form-label required">
                                        Категория системы
                                    </label>
                                    <select id="systemType" name="systemType" class="form-select" required>
                                        <option value="">Выберите категорию</option>
                                        @foreach($systemTypes as $type)
                                            @php
                                                $isSelected = old('systemType', $system->subtype->typeId ?? '') == $type->typeId;
                                            @endphp
                                            <option value="{{ $type->typeId }}" 
                                                {{ $isSelected ? 'selected' : '' }}>
                                                {{ $type->name }} - {{ $type->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('systemType')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Затем выбор подтипа системы -->
                                <div class="form-group">
                                    <label for="subtypeId" class="form-label required">
                                        Тип системы
                                    </label>
                                    <select id="subtypeId" name="subtypeId" class="form-select" required>
                                        <option value="">Сначала выберите категорию</option>
                                        @foreach($systemSubtypes as $subtype)
                                            @php
                                                $isSelected = old('subtypeId', $system->subtypeId ?? '') == $subtype->subtypeId;
                                            @endphp
                                            <option value="{{ $subtype->subtypeId }}" 
                                                {{ $isSelected ? 'selected' : '' }}
                                                data-type-id="{{ $subtype->typeId }}">
                                                {{ $subtype->name }} - {{ $subtype->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subtypeId')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="systemInventoryNumber" class="form-label">
                                        Инвентарный номер
                                    </label>
                                    <input type="text" 
                                        id="systemInventoryNumber" 
                                        name="system_inventory_number" 
                                        class="form-input" 
                                        value="{{ old('system_inventory_number', $system->systemInventoryNumber ?? '') }}">
                                    @error('system_inventory_number')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <input type="checkbox" 
                                            name="isPartOfObject" 
                                            value="1"
                                            {{ (old('isPartOfObject', $system->isPartOfObject ?? false) ? 'checked' : '') }}>
                                        Система является частью объекта
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- FireSystem: Принадлежность к объекту -->
                        <div class="form-section" id="objectSection">
                            <h3 class="section-title">Принадлежность к объекту</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="objectId" class="form-label">
                                        Объект защиты
                                    </label>
                                    <select id="objectId" name="objectId" class="form-select">
                                        <option value="">Выберите объект защиты</option>
                                        @foreach($protectionObjects as $protectionObject)
                                            @php
                                                $isSelected = old('objectId', $system->objectId ?? '') == $protectionObject->objectId;
                                            @endphp
                                            <option value="{{ $protectionObject->objectId }}" 
                                                {{ $isSelected ? 'selected' : '' }}
                                                data-branch-id="{{ $protectionObject->branchId }}"
                                                data-curator-id="{{ $protectionObject->curatorId }}"
                                                data-group-id="{{ $protectionObject->objectGroupId }}">
                                                {{ $protectionObject->name }} 
                                                @if($protectionObject->branch->shortName ?? false)
                                                    ({{ $protectionObject->branch->shortName }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('objectId')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Динамически заполняемые поля объекта -->
                                <div class="form-group">
                                    <label class="form-label">Филиал объекта</label>
                                    <div id="objectBranchInfo" class="form-info">
                                        {{ $object->branch->name ?? 'Не выбран' }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Куратор объекта</label>
                                    <div id="objectCuratorInfo" class="form-info">
                                        {{ $object->curator->name ?? 'Не выбран' }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Группа объекта</label>
                                    <div id="objectGroupInfo" class="form-info">
                                        {{ $object->objectGroup->name ?? 'Не выбран' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ProtectionObject: Создание нового объекта -->
                        <div class="form-section" id="newObjectSection" style="display: none;">
                            <h3 class="section-title">Создание нового объекта защиты</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="newObjectName" class="form-label required">
                                        Название объекта
                                    </label>
                                    <input type="text" 
                                           id="newObjectName" 
                                           name="newObjectName" 
                                           class="form-input" 
                                           value="{{ old('newObjectName') }}">
                                </div>

                                <div class="form-group">
                                    <label for="newObjectShortName" class="form-label">
                                        Короткое название
                                    </label>
                                    <input type="text" 
                                           id="newObjectShortName" 
                                           name="newObjectShortName" 
                                           class="form-input" 
                                           value="{{ old('newObjectShortName') }}">
                                </div>

                                <div class="form-group">
                                    <label for="newObjectBranchId" class="form-label required">
                                        Филиал
                                    </label>
                                    <select id="newObjectBranchId" name="newObjectBranchId" class="form-select">
                                        <option value="">Выберите филиал</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->branchId }}">
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="newObjectGroupId" class="form-label required">
                                        Группа объекта
                                    </label>
                                    <select id="newObjectGroupId" name="newObjectGroupId" class="form-select">
                                        <option value="">Выберите группу</option>
                                        @foreach($objectGroups as $group)
                                            <option value="{{ $group->groupId }}">
                                                {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="newObjectCuratorId" class="form-label required">
                                        Куратор
                                    </label>
                                    <select id="newObjectCuratorId" name="newObjectCuratorId" class="form-select">
                                        <option value="">Выберите куратора</option>
                                        @foreach($curators as $curator)
                                            <option value="{{ $curator->curatorId }}">
                                                {{ $curator->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="newObjectInventoryNumber" class="form-label">
                                        Инвентарный номер
                                    </label>
                                    <input type="text" 
                                           id="newObjectInventoryNumber" 
                                           name="newObjectInventoryNumber" 
                                           class="form-input" 
                                           value="{{ old('newObjectInventoryNumber') }}">
                                </div>

                                <div class="form-group full-width">
                                    <label for="newObjectNotes" class="form-label">
                                        Примечания
                                    </label>
                                    <textarea id="newObjectNotes" 
                                              name="newObjectNotes" 
                                              class="form-textarea" 
                                              rows="3">{{ old('newObjectNotes') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Кнопка для создания нового объекта -->
                        <div class="form-actions" style="justify-content: flex-start; margin-bottom: 20px;">
                            <button type="button" class="btn btn-outline" id="toggleNewObjectBtn">
                                + Создать новый объект защиты
                            </button>
                        </div>

                        <!-- FireSystem: Документация -->
                        <div class="form-section">
                            <h3 class="section-title">Документация системы</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="manualFileLink" class="form-label">
                                        Ссылка на руководство
                                    </label>
                                    <input type="text" 
                                           id="manualFileLink" 
                                           name="manualFileLink" 
                                           class="form-input" 
                                           value="{{ old('manualFileLink', $system->manualFileLink ?? '') }}">
                                </div>

                                <div class="form-group">
                                    <label for="maintenanceScheduleFileLink" class="form-label">
                                        Ссылка на график ТО
                                    </label>
                                    <input type="text" 
                                           id="maintenanceScheduleFileLink" 
                                           name="maintenanceScheduleFileLink" 
                                           class="form-input" 
                                           value="{{ old('maintenanceScheduleFileLink', $system->maintenanceScheduleFileLink ?? '') }}">
                                </div>

                                <div class="form-group">
                                    <label for="testProgramFileLink" class="form-label">
                                        Ссылка на программу испытаний
                                    </label>
                                    <input type="text" 
                                           id="testProgramFileLink" 
                                           name="testProgramFileLink" 
                                           class="form-input" 
                                           value="{{ old('testProgramFileLink', $system->testProgramFileLink ?? '') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Equipment: Оборудование системы -->
                        <div class="form-section">
                            <h3 class="section-title">Оборудование системы</h3>
                            
                            <div id="equipmentContainer">
                                @if(isset($equipment) && count($equipment) > 0)
                                    @foreach($equipment as $index => $eq)
                                    <div class="equipment-item" style="margin-bottom: 2rem; padding: 1rem; border: 1px solid #ddd; border-radius: 4px;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                            <h4 style="margin: 0;">Оборудование #{{ $index + 1 }}</h4>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="removeExistingEquipment('{{ $eq->equipmentId }}', '{{ $eq->model ?? 'оборудование' }}', this)">
                                                Удалить
                                            </button>
                                        </div>
                                        <div class="form-grid">
                                            <!-- Скрытое поле для идентификации существующего оборудования -->
                                            <input type="hidden" name="equipment[{{ $index }}][equipmentId]" value="{{ $eq->equipmentId }}">

                                            <div class="form-group">
                                                <label for="equipmentTypeId_{{ $index }}" class="form-label">
                                                    Тип оборудования
                                                </label>
                                                <select id="equipmentTypeId_{{ $index }}" 
                                                        name="equipment[{{ $index }}][typeId]" 
                                                        class="form-select">
                                                    <option value="">Выберите тип оборудования</option>
                                                    @foreach($equipmentTypes as $type)
                                                        @php
                                                            $isSelected = old("equipment.{$index}.typeId", $eq->typeId ?? '') == $type->typeId;
                                                        @endphp
                                                        <option value="{{ $type->typeId }}" 
                                                            {{ $isSelected ? 'selected' : '' }}>
                                                            {{ $type->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="equipmentModel_{{ $index }}" class="form-label">
                                                    Модель
                                                </label>
                                                <input type="text" 
                                                    id="equipmentModel_{{ $index }}" 
                                                    name="equipment[{{ $index }}][model]" 
                                                    class="form-input" 
                                                    value="{{ old("equipment.{$index}.model", $eq->model ?? '') }}">
                                            </div>

                                            <div class="form-group">
                                                <label for="serialNumber_{{ $index }}" class="form-label">
                                                    Серийный номер
                                                </label>
                                                <input type="text" 
                                                    id="serialNumber_{{ $index }}" 
                                                    name="equipment[{{ $index }}][serialNumber]" 
                                                    class="form-input" 
                                                    value="{{ old("equipment.{$index}.serialNumber", $eq->serialNumber ?? '') }}">
                                            </div>

                                            <div class="form-group">
                                                <label for="location_{{ $index }}" class="form-label">
                                                    Местоположение
                                                </label>
                                                <input type="text" 
                                                    id="location_{{ $index }}" 
                                                    name="equipment[{{ $index }}][location]" 
                                                    class="form-input" 
                                                    value="{{ old("equipment.{$index}.location", $eq->location ?? '') }}">
                                            </div>

                                            <div class="form-group">
                                                <label for="quantity_{{ $index }}" class="form-label">
                                                    Количество
                                                </label>
                                                <input type="number" 
                                                    id="quantity_{{ $index }}" 
                                                    name="equipment[{{ $index }}][quantity]" 
                                                    class="form-input" 
                                                    min="1" 
                                                    value="{{ old("equipment.{$index}.quantity", $eq->quantity ?? 1) }}">
                                            </div>

                                            <div class="form-group">
                                                <label for="productionYear_{{ $index }}" class="form-label">
                                                    Год производства
                                                </label>
                                                <input type="number" 
                                                    id="productionYear_{{ $index }}" 
                                                    name="equipment[{{ $index }}][productionYear]" 
                                                    class="form-input" 
                                                    min="2000" 
                                                    max="{{ date('Y') }}"
                                                    value="{{ old("equipment.{$index}.productionYear", $eq->productionYear ?? '') }}">
                                            </div>

                                            <div class="form-group">
                                                <label for="productionQuarter_{{ $index }}" class="form-label">
                                                    Квартал производства
                                                </label>
                                                <select id="productionQuarter_{{ $index }}" 
                                                        name="equipment[{{ $index }}][productionQuarter]" 
                                                        class="form-select">
                                                    <option value="">Выберите квартал</option>
                                                    @php
                                                        $quarter1 = old("equipment.{$index}.productionQuarter", $eq->productionQuarter ?? '') == 1;
                                                        $quarter2 = old("equipment.{$index}.productionQuarter", $eq->productionQuarter ?? '') == 2;
                                                        $quarter3 = old("equipment.{$index}.productionQuarter", $eq->productionQuarter ?? '') == 3;
                                                        $quarter4 = old("equipment.{$index}.productionQuarter", $eq->productionQuarter ?? '') == 4;
                                                    @endphp
                                                    <option value="1" {{ $quarter1 ? 'selected' : '' }}>1 квартал</option>
                                                    <option value="2" {{ $quarter2 ? 'selected' : '' }}>2 квартал</option>
                                                    <option value="3" {{ $quarter3 ? 'selected' : '' }}>3 квартал</option>
                                                    <option value="4" {{ $quarter4 ? 'selected' : '' }}>4 квартал</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="serviceLifeYears_{{ $index }}" class="form-label">
                                                    Срок службы (лет)
                                                </label>
                                                <input type="number" 
                                                    id="serviceLifeYears_{{ $index }}" 
                                                    name="equipment[{{ $index }}][serviceLifeYears]" 
                                                    class="form-input" 
                                                    min="1" 
                                                    max="50"
                                                    value="{{ old("equipment.{$index}.serviceLifeYears", $eq->serviceLifeYears ?? '') }}">
                                            </div>

                                            <div class="form-group">
                                                <label for="controlPeriod_{{ $index }}" class="form-label">
                                                    Период контроля
                                                </label>
                                                <input type="text" 
                                                    id="controlPeriod_{{ $index }}" 
                                                    name="equipment[{{ $index }}][controlPeriod]" 
                                                    class="form-input" 
                                                    value="{{ old("equipment.{$index}.controlPeriod", $eq->controlPeriod ?? '') }}">
                                            </div>

                                            <div class="form-group">
                                                <label for="lastControlDate_{{ $index }}" class="form-label">
                                                    Дата последнего контроля
                                                </label>
                                                <input type="date" 
                                                    id="lastControlDate_{{ $index }}" 
                                                    name="equipment[{{ $index }}][lastControlDate]" 
                                                    class="form-input" 
                                                    value="{{ old("equipment.{$index}.lastControlDate", isset($eq->lastControlDate) ? \Carbon\Carbon::parse($eq->lastControlDate)->format('Y-m-d') : '') }}">
                                            </div>

                                            <div class="form-group">
                                                <label for="controlResult_{{ $index }}" class="form-label">
                                                    Результат контроля
                                                </label>
                                                <input type="text" 
                                                    id="controlResult_{{ $index }}" 
                                                    name="equipment[{{ $index }}][controlResult]" 
                                                    class="form-input" 
                                                    value="{{ old("equipment.{$index}.controlResult", $eq->controlResult ?? '') }}">
                                            </div>

                                            <div class="form-group full-width">
                                                <label for="equipmentNotes_{{ $index }}" class="form-label">
                                                    Примечания к оборудованию
                                                </label>
                                                <textarea id="equipmentNotes_{{ $index }}" 
                                                        name="equipment[{{ $index }}][notes]" 
                                                        class="form-textarea" 
                                                        rows="2">{{ old("equipment.{$index}.notes", $eq->notes ?? '') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="empty-equipment" style="text-align: center; padding: 40px; color: #6c757d;">
                                        <p>Оборудование не добавлено</p>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="form-actions" style="justify-content: flex-start; margin-top: 20px;">
                                <button type="button" class="btn btn-success" id="addEquipmentBtn">
                                    + Добавить оборудование
                                </button>
                            </div>
                        </div>

                        <input type="hidden" name="deleted_equipment" id="deletedEquipment" value="">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Основные переменные
        let systemToDelete = null;
        let equipmentToDelete = null;
        let equipmentToDeleteButton = null;
        let deletedEquipmentIds = [];
        let newEquipmentCount = {{ $equipmentCount ?? 0 }};

        // Инициализация
        document.addEventListener('DOMContentLoaded', function() {
            initializeForm();
            setupEventListeners();
        });

        function initializeForm() {
            // Инициализация видимости секций
            toggleObjectSection();
            updateObjectInfo();
        }

        function setupEventListeners() {
            // Изменение выбора объекта
            document.getElementById('objectId')?.addEventListener('change', updateObjectInfo);
            
            // Переключение создания нового объекта
            document.getElementById('toggleNewObjectBtn')?.addEventListener('click', toggleNewObjectSection);
            
            // Добавление оборудования
            document.getElementById('addEquipmentBtn')?.addEventListener('click', addNewEquipment);
            
            // Управление чекбоксом "часть объекта"
            document.querySelector('input[name="isPartOfObject"]')?.addEventListener('change', toggleObjectSection);
        }

        // Обновление информации об объекте при выборе
        function updateObjectInfo() {
            const objectSelect = document.getElementById('objectId');
            const selectedOption = objectSelect?.options[objectSelect.selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                const branchId = selectedOption.getAttribute('data-branch-id');
                const curatorId = selectedOption.getAttribute('data-curator-id');
                const groupId = selectedOption.getAttribute('data-group-id');
                
                // Здесь можно добавить логику для отображения дополнительной информации
                // Например, через AJAX запрос за полными данными объекта
            }
        }

        // Переключение секции создания нового объекта
        function toggleNewObjectSection() {
            const newObjectSection = document.getElementById('newObjectSection');
            const toggleBtn = document.getElementById('toggleNewObjectBtn');
            
            if (newObjectSection.style.display === 'none') {
                newObjectSection.style.display = 'block';
                toggleBtn.textContent = '− Отменить создание объекта';
                // Сбрасываем выбор существующего объекта
                document.getElementById('objectId').value = '';
            } else {
                newObjectSection.style.display = 'none';
                toggleBtn.textContent = '+ Создать новый объект защиты';
            }
        }

        // Управление видимостью секции объекта
        function toggleObjectSection() {
            const objectCheckbox = document.querySelector('input[name="isPartOfObject"]');
            const objectSection = document.getElementById('objectSection');
            
            if (objectCheckbox?.checked) {
                objectSection.style.display = 'block';
            } else {
                objectSection.style.display = 'none';
                // Скрываем также секцию нового объекта
                document.getElementById('newObjectSection').style.display = 'none';
                document.getElementById('toggleNewObjectBtn').textContent = '+ Создать новый объект защиты';
            }
        }

        // Добавление нового оборудования
        // Добавление нового оборудования
function addNewEquipment() {
    const equipmentContainer = document.getElementById('equipmentContainer');
    const emptySection = equipmentContainer.querySelector('.empty-equipment');
    
    if (emptySection) {
        emptySection.remove();
    }
    
    // Получаем текущее количество оборудования (существующее + новое)
    const existingItems = document.querySelectorAll('.equipment-item');
    const newIndex = existingItems.length; // Используем общее количество
    
    const newEquipmentHTML = createEquipmentHTML(newIndex);
    
    equipmentContainer.insertAdjacentHTML('beforeend', newEquipmentHTML);
    
    // Обновляем номера всего оборудования
    updateEquipmentNumbers();
}

function createEquipmentHTML(index) {
    // Используем index + 1 для отображения (начинаем с 1)
    const displayNumber = index + 1;
    
    return `
        <div class="equipment-item" style="margin-bottom: 2rem; padding: 1rem; border: 1px solid #ddd; border-radius: 4px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h4 style="margin: 0;" class="equipment-title">Оборудование #${displayNumber}</h4>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeNewEquipment(this)">
                    Удалить
                </button>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="equipmentTypeId_${index}" class="form-label">
                        Тип оборудования
                    </label>
                    <select id="equipmentTypeId_${index}" 
                            name="equipment[${index}][typeId]" 
                            class="form-select">
                        <option value="">Выберите тип оборудования</option>
                        @foreach($equipmentTypes as $type)
                            <option value="{{ $type->typeId }}">
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="equipmentModel_${index}" class="form-label">
                        Модель
                    </label>
                    <input type="text" 
                        id="equipmentModel_${index}" 
                        name="equipment[${index}][model]" 
                        class="form-input" 
                        value="">
                </div>

                <div class="form-group">
                    <label for="serialNumber_${index}" class="form-label">
                        Серийный номер
                    </label>
                    <input type="text" 
                        id="serialNumber_${index}" 
                        name="equipment[${index}][serialNumber]" 
                        class="form-input" 
                        value="">
                </div>

                <div class="form-group">
                    <label for="location_${index}" class="form-label">
                        Местоположение
                    </label>
                    <input type="text" 
                        id="location_${index}" 
                        name="equipment[${index}][location]" 
                        class="form-input" 
                        value="">
                </div>

                <div class="form-group">
                    <label for="quantity_${index}" class="form-label">
                        Количество
                    </label>
                    <input type="number" 
                        id="quantity_${index}" 
                        name="equipment[${index}][quantity]" 
                        class="form-input" 
                        min="1" 
                        value="1">
                </div>

                <div class="form-group">
                    <label for="productionYear_${index}" class="form-label">
                        Год производства
                    </label>
                    <input type="number" 
                        id="productionYear_${index}" 
                        name="equipment[${index}][productionYear]" 
                        class="form-input" 
                        min="2000" 
                        max="{{ date('Y') }}"
                        value="">
                </div>

                <div class="form-group">
                    <label for="productionQuarter_${index}" class="form-label">
                        Квартал производства
                    </label>
                    <select id="productionQuarter_${index}" 
                            name="equipment[${index}][productionQuarter]" 
                            class="form-select">
                        <option value="">Выберите квартал</option>
                        <option value="1">1 квартал</option>
                        <option value="2">2 квартал</option>
                        <option value="3">3 квартал</option>
                        <option value="4">4 квартал</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="serviceLifeYears_${index}" class="form-label">
                        Срок службы (лет)
                    </label>
                    <input type="number" 
                        id="serviceLifeYears_${index}" 
                        name="equipment[${index}][serviceLifeYears]" 
                        class="form-input" 
                        min="1" 
                        max="50"
                        value="">
                </div>

                <div class="form-group">
                    <label for="controlPeriod_${index}" class="form-label">
                        Период контроля
                    </label>
                    <input type="text" 
                        id="controlPeriod_${index}" 
                        name="equipment[${index}][controlPeriod]" 
                        class="form-input" 
                        value="">
                </div>

                <div class="form-group">
                    <label for="lastControlDate_${index}" class="form-label">
                        Дата последнего контроля
                    </label>
                    <input type="date" 
                        id="lastControlDate_${index}" 
                        name="equipment[${index}][lastControlDate]" 
                        class="form-input" 
                        value="">
                </div>

                <div class="form-group">
                    <label for="controlResult_${index}" class="form-label">
                        Результат контроля
                    </label>
                    <input type="text" 
                        id="controlResult_${index}" 
                        name="equipment[${index}][controlResult]" 
                        class="form-input" 
                        value="">
                </div>

                <div class="form-group full-width">
                    <label for="equipmentNotes_${index}" class="form-label">
                        Примечания к оборудованию
                    </label>
                    <textarea id="equipmentNotes_${index}" 
                            name="equipment[${index}][notes]" 
                            class="form-textarea" 
                            rows="2"></textarea>
                </div>
            </div>
        </div>
    `;
}

// Функция для удаления нового оборудования
function removeNewEquipment(button) {
    if (confirm('Вы уверены, что хотите удалить это оборудование?')) {
        const equipmentItem = button.closest('.equipment-item');
        equipmentItem.remove();
        
        // Обновляем номера оставшегося оборудования
        updateEquipmentNumbers();
        
        // Если оборудования не осталось, показываем пустое состояние
        checkEmptyEquipment();
    }
}

        // Обновление номеров оборудования
        function updateEquipmentNumbers() {
            const equipmentItems = document.querySelectorAll('.equipment-item');
            equipmentItems.forEach((item, index) => {
                const title = item.querySelector('.equipment-title');
                if (title) {
                    title.textContent = `Оборудование #${index + 1}`;
                }
            });
        }

        // Проверка пустого состояния оборудования
        function checkEmptyEquipment() {
            const equipmentContainer = document.getElementById('equipmentContainer');
            if (equipmentContainer.children.length === 0) {
                equipmentContainer.innerHTML = `
                    <div class="empty-equipment">
                        <p>Оборудование не добавлено</p>
                    </div>
                `;
            }
        }

        // Обработка отправки формы
        document.getElementById('systemForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm();
        });

        async function submitForm() {
            const form = document.getElementById('systemForm');
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            // Показываем загрузку
            submitButton.disabled = true;
            submitButton.textContent = 'Сохранение...';
            
            try {
                const formData = new FormData(form);
                const jsonData = processFormData(formData);
                
                const response = await fetch(form.action, {
                    method: form.method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(jsonData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Система успешно сохранена');
                    window.location.href = data.redirect || '/systems';
                } else {
                    throw new Error(data.error || 'Ошибка сохранения');
                }
                
            } catch (error) {
                console.error('Error:', error);
                alert('Ошибка при сохранении: ' + error.message);
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        }

        function processFormData(formData) {
            const jsonData = {};
            
            // Базовые поля
            for (let [key, value] of formData.entries()) {
                if (key === 'isPartOfObject') {
                    jsonData[key] = true;
                } else {
                    jsonData[key] = value;
                }
            }
            
            // Обработка чекбокса
            if (!formData.has('isPartOfObject')) {
                jsonData['isPartOfObject'] = false;
            }
            
            // Обработка оборудования
            jsonData['equipment'] = processEquipmentData(formData);
            jsonData['deleted_equipment'] = deletedEquipmentIds;
            
            return jsonData;
        }

        function processEquipmentData(formData) {
            const equipmentMap = {};
            
            for (const [key, value] of formData.entries()) {
                const match = key.match(/equipment\[(.+?)\]\[(.+?)\]/);
                if (match) {
                    const index = match[1];
                    const field = match[2];
                    
                    if (!equipmentMap[index]) {
                        equipmentMap[index] = {};
                    }
                    equipmentMap[index][field] = value;
                }
            }
            
            return Object.values(equipmentMap);
        }

        // Функции для модальных окон
        function confirmDelete(systemId, systemName) {
            systemToDelete = systemId;
            document.getElementById('systemNameToDelete').textContent = systemName;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function cancelDelete() {
            systemToDelete = null;
            document.getElementById('deleteModal').style.display = 'none';
        }

        async function deleteSystem() {
            if (!systemToDelete) return;
            
            try {
                const response = await fetch(`/systems/${systemToDelete}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Система успешно удалена');
                    window.location.href = '/';
                } else {
                    throw new Error(data.error || 'Ошибка удаления');
                }
            } catch (error) {
                alert('Ошибка при удалении: ' + error.message);
            }
            
            cancelDelete();
        }

        function initializeForm() {
            // Инициализация видимости секций
            toggleObjectSection();
            updateObjectInfo();
            filterSubtypesByType(); // ← Добавьте эту строку
        }

        // Фильтрация подтипов по выбранному типу системы
        function filterSubtypesByType() {
            const typeSelect = document.getElementById('systemType');
            const subtypeSelect = document.getElementById('subtypeId');
            
            if (!typeSelect || !subtypeSelect) return;
            
            typeSelect.addEventListener('change', function() {
                const selectedTypeId = this.value;
                const allOptions = subtypeSelect.querySelectorAll('option');
                
                // Показываем/скрываем опции в зависимости от типа
                allOptions.forEach(option => {
                    if (option.value === '') {
                        // Заголовок всегда видим
                        option.style.display = '';
                        option.disabled = false;
                    } else {
                        const typeId = option.getAttribute('data-type-id');
                        if (typeId === selectedTypeId) {
                            option.style.display = '';
                            option.disabled = false;
                        } else {
                            option.style.display = 'none';
                            option.disabled = true;
                        }
                    }
                });
                
                // Сбрасываем выбор подтипа если он не соответствует типу
                const selectedSubtypeTypeId = subtypeSelect.options[subtypeSelect.selectedIndex]?.getAttribute('data-type-id');
                if (selectedSubtypeTypeId && selectedSubtypeTypeId !== selectedTypeId) {
                    subtypeSelect.value = '';
                }
            });
            
            // Инициализируем фильтр при загрузке
            typeSelect.dispatchEvent(new Event('change'));
        }

        function removeExistingEquipment(equipmentId, equipmentName, button) {
            if (confirm(`Вы уверены, что хотите удалить оборудование "${equipmentName}"?`)) {
                // Добавляем ID в массив удаляемого оборудования
                deletedEquipmentIds.push(equipmentId);
                document.getElementById('deletedEquipment').value = JSON.stringify(deletedEquipmentIds);
                
                // Удаляем элемент из DOM
                const equipmentItem = button.closest('.equipment-item');
                equipmentItem.remove();
                
                // Обновляем номера оставшегося оборудования
                updateEquipmentNumbers();
                
                // Если оборудования не осталось, показываем пустое состояние
                checkEmptyEquipment();
            }
        }
    </script>
</body>
</html>