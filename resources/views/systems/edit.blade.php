<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование системы - {{ $system->name ?? 'Новая система' }}</title>
    <link rel="stylesheet" href="{{ asset('css/edit.css') }}">
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
                            @foreach($equipment as $index => $eq)
                            <div class="equipment-item" 
                                 style="margin-bottom: 2rem; padding: 1rem; border: 1px solid #ddd; border-radius: 4px;">
                                <h4 style="margin-top: 0;">Оборудование #{{ $index + 1 }}</h4>
                                <div class="form-grid">
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
                                            value="{{ old("equipment.{$index}.lastControlDate", isset($eq['lastControlDate']) && $eq['lastControlDate'] instanceof \DateTimeImmutable ? $eq['lastControlDate']->format('Y-m-d') : '') }}">
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
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно подтверждения удаления -->
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

    <script>
        let systemToDelete = null;

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

        // Обработка отправки формы через AJAX
        document.getElementById('systemForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Предотвращаем стандартную отправку
            
            const form = this;
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            // Показываем загрузку
            submitButton.disabled = true;
            submitButton.textContent = 'Сохранение...';
            
            // Собираем данные формы
            const formData = new FormData(form);
            const jsonData = {};
            
            // Конвертируем FormData в JSON
            for (let [key, value] of formData.entries()) {
                // Особенная обработка для чекбоксов
                if (key === 'isPartOfObject') {
                    jsonData[key] = true; // Если чекбокс отмечен, он попадает в FormData
                } else {
                    jsonData[key] = value;
                }
            }
            
            // Если чекбокс не отмечен, явно устанавливаем false
            if (!formData.has('isPartOfObject')) {
                jsonData['isPartOfObject'] = false;
            }
            
            console.log('Sending data:', jsonData);
            
            // Отправляем AJAX запрос
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
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    // Успешное сохранение - редирект на главную
                    alert(data.message || 'Система успешно обновлена');
                    window.location.href = '/systems';
                } else {
                    // Ошибка
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

        // Закрытие модального окна при клике вне его
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                cancelDelete();
            }
        });
    </script>
</body>
</html>