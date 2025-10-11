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

                                <div class="form-group">
                                    <label for="subtypeId" class="form-label required">
                                        Тип системы
                                    </label>
                                    <select id="subtypeId" name="subtypeId" class="form-select" required>
                                        <option value="">Выберите тип системы</option>
                                        @foreach($systemSubtypes as $subtype)
                                            @php
                                                $isSelected = old('subtypeId', $system->subtypeId ?? '') == $subtype->subtypeId;
                                            @endphp
                                            <option value="{{ $subtype->subtypeId }}" 
                                                {{ $isSelected ? 'selected' : '' }}>
                                                {{ $subtype->name }}
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
                                                {{ $isSelected ? 'selected' : '' }}>
                                                {{ $protectionObject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('objectId')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
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

                        <!-- ProtectionObject: Информация об объекте защиты -->
                        @if(isset($object) && $object)
                        <div class="form-section">
                            <h3 class="section-title">Информация об объекте защиты</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="objectName" class="form-label">
                                        Название объекта
                                    </label>
                                    <input type="text" 
                                           id="objectName" 
                                           name="objectName" 
                                           class="form-input" 
                                           value="{{ old('objectName', $object->name ?? '') }}">
                                </div>

                                <div class="form-group">
                                    <label for="objectShortName" class="form-label">
                                        Короткое название
                                    </label>
                                    <input type="text" 
                                           id="objectShortName" 
                                           name="objectShortName" 
                                           class="form-input" 
                                           value="{{ old('objectShortName', $object->shortName ?? '') }}">
                                </div>

                                <div class="form-group">
                                    <label for="objectInventoryNumber" class="form-label">
                                        Инвентарный номер объекта
                                    </label>
                                    <input type="text" 
                                           id="objectInventoryNumber" 
                                           name="objectInventoryNumber" 
                                           class="form-input" 
                                           value="{{ old('objectInventoryNumber', $object->inventoryNumber ?? '') }}">
                                </div>

                                <div class="form-group">
                                    <label for="objectGroupId" class="form-label">
                                        Группа объекта
                                    </label>
                                    <select id="objectGroupId" name="objectGroupId" class="form-select">
                                        <option value="">Выберите группу объекта</option>
                                        @foreach($objectGroups as $group)
                                            @php
                                                $isSelected = old('objectGroupId', $object->objectGroupId ?? '') == $group->groupId;
                                            @endphp
                                            <option value="{{ $group->groupId }}" 
                                                {{ $isSelected ? 'selected' : '' }}>
                                                {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="curatorId" class="form-label">
                                        Куратор объекта
                                    </label>
                                    <select id="curatorId" name="curatorId" class="form-select">
                                        <option value="">Выберите куратора</option>
                                        @foreach($curators as $curator)
                                            @php
                                                $isSelected = old('curatorId', $object->curatorId ?? '') == $curator->curatorId;
                                            @endphp
                                            <option value="{{ $curator->curatorId }}" 
                                                {{ $isSelected ? 'selected' : '' }}>
                                                {{ $curator->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group full-width">
                                    <label for="objectNotes" class="form-label">
                                        Примечания к объекту
                                    </label>
                                    <textarea id="objectNotes" 
                                              name="objectNotes" 
                                              class="form-textarea" 
                                              rows="3">{{ old('objectNotes', $object->notes ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Equipment: Оборудование системы -->
                        @if(isset($equipment) && count($equipment) > 0)
                        <div class="form-section">
                            <h3 class="section-title">Оборудование системы</h3>
                            
                            <!-- Контейнер для существующего оборудования -->
                            <div id="equipmentContainer">
                                @foreach($equipment as $index => $eq)
                                <div class="equipment-item" 
                                     style="margin-bottom: 2rem; padding: 1rem; border: 1px solid #ddd; border-radius: 4px;">
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
                            </div>
                            
                            <!-- Кнопка добавить оборудование -->
                            <div class="form-actions" style="margin-top: 20px; justify-content: flex-start; border-top: 1px solid #e9ecef; padding-top: 20px;">
                                <button type="button" class="btn btn-success" id="addEquipmentBtn">
                                    <i class="icon-plus"></i> Добавить оборудование
                                </button>
                            </div>
                        </div>
                        @else
                        <!-- Если оборудования нет, показываем только кнопку -->
                        <div class="form-section">
                            <h3 class="section-title">Оборудование системы</h3>
                            
                            <!-- Контейнер для оборудования (пока пустой) -->
                            <div id="equipmentContainer">
                                <div class="empty-equipment" style="text-align: center; padding: 40px; color: #6c757d;">
                                    <p>Оборудование не добавлено</p>
                                </div>
                            </div>
                            
                            <!-- Кнопка добавить оборудование -->
                            <div class="form-actions" style="justify-content: flex-start; margin-top: 20px;">
                                <button type="button" class="btn btn-success" id="addEquipmentBtn">
                                    <i class="icon-plus"></i> Добавить оборудование
                                </button>
                            </div>
                        </div>
                        @endif

                        <!-- Скрытое поле для удаляемого оборудования -->
                        <input type="hidden" name="deleted_equipment" id="deletedEquipment" value="">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно подтверждения удаления системы -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Подтверждение удаления</h3>
            <p>Вы уверены, что хотите удалить систему "<span id="systemNameToDelete"></span>"?</p>
            <p style="color: #e74c3c;">Это действие нельзя отменить!</p>
            <div class="modal-buttons">
                <button onclick="cancelDelete()" class="btn btn-secondary">Отмена</button>
                <button onclick="deleteSystem()" class="btn btn-danger">Удалить</button>
            </div>
        </div>
    </div>

    <!-- Модальное окно подтверждения удаления оборудования -->
    <div id="deleteEquipmentModal" class="modal">
        <div class="modal-content">
            <h3>Подтверждение удаления</h3>
            <p>Вы уверены, что хотите удалить оборудование "<span id="equipmentNameToDelete"></span>"?</p>
            <p style="color: #e74c3c;">Это действие нельзя отменить!</p>
            <div class="modal-buttons">
                <button onclick="cancelDeleteEquipment()" class="btn btn-secondary">Отмена</button>
                <button onclick="confirmDeleteEquipment()" class="btn btn-danger">Удалить</button>
            </div>
        </div>
    </div>

    <script>
        let systemToDelete = null;
        let equipmentToDelete = null;
        let equipmentToDeleteButton = null;
        let deletedEquipmentIds = [];

        function confirmDelete(systemId, systemName) {
            systemToDelete = systemId;
            document.getElementById('systemNameToDelete').textContent = systemName;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function cancelDelete() {
            systemToDelete = null;
            document.getElementById('deleteModal').style.display = 'none';
        }

        function deleteSystem() {
            if (systemToDelete) {
                fetch(`/systems/${systemToDelete}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Система успешно удалена');
                        window.location.href = '/';
                    } else {
                        alert(data.error || 'Ошибка при удалении системы');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ошибка при удалении системы');
                });

                cancelDelete();
            }
        }

        // Функция для удаления существующего оборудования
        function removeExistingEquipment(equipmentId, equipmentName, button) {
            equipmentToDelete = equipmentId;
            equipmentToDeleteButton = button;
            document.getElementById('equipmentNameToDelete').textContent = equipmentName || 'оборудование';
            document.getElementById('deleteEquipmentModal').style.display = 'block';
        }

        function cancelDeleteEquipment() {
            equipmentToDelete = null;
            equipmentToDeleteButton = null;
            document.getElementById('deleteEquipmentModal').style.display = 'none';
        }

        function confirmDeleteEquipment() {
            if (equipmentToDelete) {
                // Добавляем ID в массив удаляемого оборудования
                deletedEquipmentIds.push(equipmentToDelete);
                document.getElementById('deletedEquipment').value = JSON.stringify(deletedEquipmentIds);
                
                // Удаляем элемент из DOM
                const equipmentItem = equipmentToDeleteButton.closest('.equipment-item');
                equipmentItem.remove();
                
                // Обновляем номера оставшегося оборудования
                updateEquipmentNumbers();
                
                // Если оборудования не осталось, показываем пустое состояние
                const equipmentContainer = document.getElementById('equipmentContainer');
                if (equipmentContainer.children.length === 0) {
                    equipmentContainer.innerHTML = `
                        <div class="empty-equipment" style="text-align: center; padding: 40px; color: #6c757d;">
                            <p>Оборудование не добавлено</p>
                        </div>
                    `;
                }
            }
            
            cancelDeleteEquipment();
        }

        // Обработка отправки формы через AJAX
        document.getElementById('systemForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            // Показываем загрузку
            submitButton.disabled = true;
            submitButton.textContent = 'Сохранение...';
            
            // Собираем данные в правильном формате для вашего контроллера
            const formData = new FormData(form);
            const jsonData = {};
            
            // Конвертируем FormData в плоский объект
            for (let [key, value] of formData.entries()) {
                // Особенная обработка для чекбоксов
                if (key === 'isPartOfObject') {
                    jsonData[key] = true;
                } else {
                    jsonData[key] = value;
                }
            }
            
            // Если чекбокс не отмечен, явно устанавливаем false
            if (!formData.has('isPartOfObject')) {
                jsonData['isPartOfObject'] = false;
            }
            
            // Добавляем удаленное оборудование
            jsonData['deleted_equipment'] = deletedEquipmentIds;
            
            // Обработка данных оборудования для правильной структуры
            processEquipmentData(jsonData);
            
            console.log('Sending JSON data:', jsonData);
            
            // Отправляем JSON данные как ожидает ваш контроллер
            fetch(form.action, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(jsonData)
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    alert(data.message || 'Система успешно обновлена');
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.href = '/systems';
                    }
                } else {
                    alert('Ошибка: ' + (data.error || 'Неизвестная ошибка'));
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка при сохранении: ' + error.message);
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            });
        });

        // Функция для обработки структуры данных оборудования
        function processEquipmentData(jsonData) {
            // Собираем все поля оборудования в правильную структуру
            const equipment = [];
            const equipmentMap = {};
            
            // Группируем поля оборудования по индексам
            for (const key in jsonData) {
                if (key.startsWith('equipment[') && key.includes(']')) {
                    const match = key.match(/equipment\[(\d+)\]\[(.+)\]/);
                    if (match) {
                        const index = match[1];
                        const field = match[2];
                        
                        if (!equipmentMap[index]) {
                            equipmentMap[index] = {};
                        }
                        equipmentMap[index][field] = jsonData[key];
                        
                        // Удаляем оригинальное поле
                        delete jsonData[key];
                    }
                }
            }
            
            // Преобразуем обратно в массив
            for (const index in equipmentMap) {
                equipment.push(equipmentMap[index]);
            }
            
            // Заменяем оборудование в основных данных
            jsonData.equipment = equipment;
        }

        // Управление видимостью секции объекта
        const objectCheckbox = document.querySelector('input[name="isPartOfObject"]');
        const objectSection = document.getElementById('objectSection');
        
        function toggleObjectSection() {
            if (objectCheckbox.checked) {
                objectSection.style.display = 'block';
            } else {
                objectSection.style.display = 'none';
            }
        }
        
        objectCheckbox.addEventListener('change', toggleObjectSection);
        toggleObjectSection();

        // Закрытие модальных окон при клике вне их
        window.addEventListener('click', function(event) {
            const deleteModal = document.getElementById('deleteModal');
            const deleteEquipmentModal = document.getElementById('deleteEquipmentModal');
            
            if (event.target === deleteModal) {
                cancelDelete();
            }
            if (event.target === deleteEquipmentModal) {
                cancelDeleteEquipment();
            }
        });

        // Обработка кнопки "Добавить оборудование"
        document.getElementById('addEquipmentBtn')?.addEventListener('click', function() {
            const equipmentCount = document.querySelectorAll('.equipment-item').length;
            const newIndex = equipmentCount;
            
            const newEquipmentHTML = `
                <div class="equipment-item" style="margin-bottom: 2rem; padding: 1rem; border: 1px solid #ddd; border-radius: 4px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h4 style="margin: 0;">Оборудование #${newIndex + 1}</h4>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeNewEquipment(this)">
                            Удалить
                        </button>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="equipmentTypeId_${newIndex}" class="form-label">
                                Тип оборудования
                            </label>
                            <select id="equipmentTypeId_${newIndex}" 
                                    name="equipment[${newIndex}][typeId]" 
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
                            <label for="equipmentModel_${newIndex}" class="form-label">
                                Модель
                            </label>
                            <input type="text" 
                                id="equipmentModel_${newIndex}" 
                                name="equipment[${newIndex}][model]" 
                                class="form-input" 
                                value="">
                        </div>

                        <div class="form-group">
                            <label for="serialNumber_${newIndex}" class="form-label">
                                Серийный номер
                            </label>
                            <input type="text" 
                                id="serialNumber_${newIndex}" 
                                name="equipment[${newIndex}][serialNumber]" 
                                class="form-input" 
                                value="">
                        </div>

                        <div class="form-group">
                            <label for="location_${newIndex}" class="form-label">
                                Местоположение
                            </label>
                            <input type="text" 
                                id="location_${newIndex}" 
                                name="equipment[${newIndex}][location]" 
                                class="form-input" 
                                value="">
                        </div>

                        <div class="form-group">
                            <label for="quantity_${newIndex}" class="form-label">
                                Количество
                            </label>
                            <input type="number" 
                                id="quantity_${newIndex}" 
                                name="equipment[${newIndex}][quantity]" 
                                class="form-input" 
                                min="1" 
                                value="1">
                        </div>

                        <div class="form-group">
                            <label for="productionYear_${newIndex}" class="form-label">
                                Год производства
                            </label>
                            <input type="number" 
                                id="productionYear_${newIndex}" 
                                name="equipment[${newIndex}][productionYear]" 
                                class="form-input" 
                                min="2000" 
                                max="{{ date('Y') }}"
                                value="">
                        </div>

                        <div class="form-group">
                            <label for="productionQuarter_${newIndex}" class="form-label">
                                Квартал производства
                            </label>
                            <select id="productionQuarter_${newIndex}" 
                                    name="equipment[${newIndex}][productionQuarter]" 
                                    class="form-select">
                                <option value="">Выберите квартал</option>
                                <option value="1">1 квартал</option>
                                <option value="2">2 квартал</option>
                                <option value="3">3 квартал</option>
                                <option value="4">4 квартал</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="serviceLifeYears_${newIndex}" class="form-label">
                                Срок службы (лет)
                            </label>
                            <input type="number" 
                                id="serviceLifeYears_${newIndex}" 
                                name="equipment[${newIndex}][serviceLifeYears]" 
                                class="form-input" 
                                min="1" 
                                max="50"
                                value="">
                        </div>

                        <div class="form-group">
                            <label for="controlPeriod_${newIndex}" class="form-label">
                                Период контроля
                            </label>
                            <input type="text" 
                                id="controlPeriod_${newIndex}" 
                                name="equipment[${newIndex}][controlPeriod]" 
                                class="form-input" 
                                value="">
                        </div>

                        <div class="form-group">
                            <label for="lastControlDate_${newIndex}" class="form-label">
                                Дата последнего контроля
                            </label>
                            <input type="date" 
                                id="lastControlDate_${newIndex}" 
                                name="equipment[${newIndex}][lastControlDate]" 
                                class="form-input" 
                                value="">
                        </div>

                        <div class="form-group">
                            <label for="controlResult_${newIndex}" class="form-label">
                                Результат контроля
                            </label>
                            <input type="text" 
                                id="controlResult_${newIndex}" 
                                name="equipment[${newIndex}][controlResult]" 
                                class="form-input" 
                                value="">
                        </div>

                        <div class="form-group full-width">
                            <label for="equipmentNotes_${newIndex}" class="form-label">
                                Примечания к оборудованию
                            </label>
                            <textarea id="equipmentNotes_${newIndex}" 
                                    name="equipment[${newIndex}][notes]" 
                                    class="form-textarea" 
                                    rows="2"></textarea>
                        </div>
                    </div>
                </div>
            `;
            
            // Находим контейнер для оборудования
            const equipmentContainer = document.getElementById('equipmentContainer');
            const emptySection = equipmentContainer.querySelector('.empty-equipment');
            
            // Если есть пустая секция - удаляем её
            if (emptySection) {
                emptySection.remove();
            }
            
            // Вставляем новое оборудование в КОНЕЦ контейнера
            equipmentContainer.insertAdjacentHTML('beforeend', newEquipmentHTML);
        });

        // Функция для удаления нового оборудования (которое еще не сохранено в БД)
        function removeNewEquipment(button) {
            if (confirm('Вы уверены, что хотите удалить это оборудование?')) {
                const equipmentItem = button.closest('.equipment-item');
                equipmentItem.remove();
                
                // Обновляем номера оставшегося оборудования
                updateEquipmentNumbers();
                
                // Если оборудования не осталось, показываем пустое состояние
                const equipmentContainer = document.getElementById('equipmentContainer');
                if (equipmentContainer.children.length === 0) {
                    equipmentContainer.innerHTML = `
                        <div class="empty-equipment" style="text-align: center; padding: 40px; color: #6c757d;">
                            <p>Оборудование не добавлено</p>
                        </div>
                    `;
                }
            }
        }

        // Функция для обновления номеров оборудования
        function updateEquipmentNumbers() {
            const equipmentItems = document.querySelectorAll('.equipment-item');
            equipmentItems.forEach((item, index) => {
                const title = item.querySelector('h4');
                if (title) {
                    title.textContent = `Оборудование #${index + 1}`;
                }
            });
        }
    </script>
</body>
</html>