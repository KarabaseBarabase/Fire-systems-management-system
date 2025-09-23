@extends('layouts.app')

@section('title', 'Добавление новой пожарной системы')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Добавление новой пожарной системы</h4>
                    </div>
                    <div class="card-body">
                        <form id="fireSystemForm" action="{{ route('fire-systems.store') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf

                            <!-- Основная информация -->
                            <div class="section-card mb-4">
                                <div class="section-header" data-bs-toggle="collapse" href="#basicInfo">
                                    <h5 class="section-title">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Основная информация
                                    </h5>
                                    <i class="fas fa-chevron-down toggle-icon"></i>
                                </div>
                                <div class="section-content collapse show" id="basicInfo">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="object_id" class="form-label required">Объект защиты</label>
                                            <select class="form-select" id="object_id" name="object_id" required>
                                                <option value="">Выберите объект</option>
                                                @foreach($protectionObjects as $object)
                                                    <option value="{{ $object->object_id }}"
                                                        data-branch="{{ $object->branch_id }}"
                                                        data-curator="{{ $object->curator_id }}">
                                                        {{ $object->name }} ({{ $object->short_name }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="subtype_id" class="form-label required">Тип системы</label>
                                            <select class="form-select" id="subtype_id" name="subtype_id" required>
                                                <option value="">Выберите тип системы</option>
                                                @foreach($systemSubtypes as $subtype)
                                                    <option value="{{ $subtype->subtype_id }}"
                                                        data-type="{{ $subtype->type_id }}">
                                                        {{ $subtype->systemType->name }} - {{ $subtype->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="system_inventory_number" class="form-label">Инвентарный
                                                номер</label>
                                            <input type="text" class="form-control" id="system_inventory_number"
                                                name="system_inventory_number" placeholder="Введите инвентарный номер">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="name" class="form-label required">Наименование системы</label>
                                            <input type="text" class="form-control" id="name" name="name" required
                                                placeholder="Например: АПС административного здания">
                                        </div>

                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_part_of_object"
                                                    name="is_part_of_object" value="1">
                                                <label class="form-check-label" for="is_part_of_object">
                                                    Система является частью объекта
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Документация -->
                            <div class="section-card mb-4">
                                <div class="section-header" data-bs-toggle="collapse" href="#documentation">
                                    <h5 class="section-title">
                                        <i class="fas fa-file-alt me-2"></i>
                                        Документация
                                    </h5>
                                    <i class="fas fa-chevron-down toggle-icon"></i>
                                </div>
                                <div class="section-content collapse show" id="documentation">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="manual_file" class="form-label">Руководство по эксплуатации</label>
                                            <input type="file" class="form-control" id="manual_file" name="manual_file"
                                                accept=".pdf,.doc,.docx">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="maintenance_schedule_file" class="form-label">График ТО</label>
                                            <input type="file" class="form-control" id="maintenance_schedule_file"
                                                name="maintenance_schedule_file" accept=".pdf,.doc,.docx">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="test_program_file" class="form-label">Программа испытаний</label>
                                            <input type="file" class="form-control" id="test_program_file"
                                                name="test_program_file" accept=".pdf,.doc,.docx">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Реализованные проекты -->
                            <div class="section-card mb-4">
                                <div class="section-header" data-bs-toggle="collapse" href="#projects">
                                    <h5 class="section-title">
                                        <i class="fas fa-project-diagram me-2"></i>
                                        Реализованные проекты
                                    </h5>
                                    <i class="fas fa-chevron-down toggle-icon"></i>
                                </div>
                                <div class="section-content collapse show" id="projects">
                                    <div id="projects-container">
                                        <div class="project-item border p-3 mb-3">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label required">Код проекта</label>
                                                    <input type="text" class="form-control" name="projects[0][project_code]"
                                                        required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label required">Год разработки</label>
                                                    <input type="number" class="form-control"
                                                        name="projects[0][development_year]" min="2000" max="2030" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label required">Проектная организация</label>
                                                    <select class="form-select" name="projects[0][design_org_id]" required>
                                                        <option value="">Выберите организацию</option>
                                                        @foreach($designOrganizations as $org)
                                                            <option value="{{ $org->org_id }}">{{ $org->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label required">Нормативный документ</label>
                                                    <select class="form-select" name="projects[0][regulation_id]" required>
                                                        <option value="">Выберите документ</option>
                                                        @foreach($regulations as $regulation)
                                                            <option value="{{ $regulation->regulation_id }}">
                                                                {{ $regulation->code }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Файл проекта</label>
                                                    <input type="file" class="form-control"
                                                        name="projects[0][project_file]">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-project">
                                        <i class="fas fa-plus me-1"></i>Добавить проект
                                    </button>
                                </div>
                            </div>

                            <!-- Оборудование -->
                            <div class="section-card mb-4">
                                <div class="section-header" data-bs-toggle="collapse" href="#equipment">
                                    <h5 class="section-title">
                                        <i class="fas fa-microchip me-2"></i>
                                        Оборудование
                                    </h5>
                                    <i class="fas fa-chevron-down toggle-icon"></i>
                                </div>
                                <div class="section-content collapse show" id="equipment">
                                    <div class="mb-3">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="equipment_source"
                                                id="equipment_new" value="new" checked>
                                            <label class="form-check-label" for="equipment_new">Добавить новое
                                                оборудование</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="equipment_source"
                                                id="equipment_existing" value="existing">
                                            <label class="form-check-label" for="equipment_existing">Выбрать
                                                существующее</label>
                                        </div>
                                    </div>

                                    <!-- Новое оборудование -->
                                    <div id="new-equipment-section">
                                        <div id="equipment-container">
                                            <div class="equipment-item border p-3 mb-3">
                                                <div class="row g-3">
                                                    <div class="col-md-3">
                                                        <label class="form-label required">Тип оборудования</label>
                                                        <select class="form-select equipment-type"
                                                            name="equipment[0][type_id]" required>
                                                            <option value="">Выберите тип</option>
                                                            @foreach($equipmentTypes as $type)
                                                                <option value="{{ $type->type_id }}">{{ $type->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label required">Модель</label>
                                                        <input type="text" class="form-control" name="equipment[0][model]"
                                                            required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Серийный номер</label>
                                                        <input type="text" class="form-control"
                                                            name="equipment[0][serial_number]">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Место установки</label>
                                                        <input type="text" class="form-control"
                                                            name="equipment[0][location]">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label required">Количество</label>
                                                        <input type="number" class="form-control"
                                                            name="equipment[0][quantity]" value="1" min="1" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label required">Год выпуска</label>
                                                        <input type="number" class="form-control"
                                                            name="equipment[0][production_year]" min="2000" max="2030"
                                                            required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Квартал выпуска</label>
                                                        <select class="form-select" name="equipment[0][production_quarter]">
                                                            <option value="">Не указан</option>
                                                            <option value="1">1 квартал</option>
                                                            <option value="2">2 квартал</option>
                                                            <option value="3">3 квартал</option>
                                                            <option value="4">4 квартал</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label required">Срок службы (лет)</label>
                                                        <input type="number" class="form-control"
                                                            name="equipment[0][service_life_years]" min="1" max="50"
                                                            required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Период контроля</label>
                                                        <input type="text" class="form-control"
                                                            name="equipment[0][control_period]">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Примечания</label>
                                                        <textarea class="form-control" name="equipment[0][notes]"
                                                            rows="2"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-equipment">
                                            <i class="fas fa-plus me-1"></i>Добавить оборудование
                                        </button>
                                    </div>

                                    <!-- Существующее оборудование -->
                                    <div id="existing-equipment-section" style="display: none;">
                                        <label class="form-label">Выберите оборудование из базы</label>
                                        <select class="form-select" id="existing-equipment-select" multiple size="5">
                                            @foreach($existingEquipment as $equip)
                                                <option value="{{ $equip->equipment_id }}">
                                                    {{ $equip->equipmentType->name }} - {{ $equip->model }}
                                                    ({{ $equip->serial_number }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-outline-success btn-sm"
                                                id="add-selected-equipment">
                                                <i class="fas fa-plus me-1"></i>Добавить выбранное
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Планы работ -->
                            <div class="section-card mb-4">
                                <div class="section-header" data-bs-toggle="collapse" href="#repairs">
                                    <h5 class="section-title">
                                        <i class="fas fa-tools me-2"></i>
                                        Планы ремонтных работ
                                    </h5>
                                    <i class="fas fa-chevron-down toggle-icon"></i>
                                </div>
                                <div class="section-content collapse show" id="repairs">
                                    <div id="repairs-container">
                                        <div class="repair-item border p-3 mb-3">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label required">Вид работ</label>
                                                    <select class="form-select" name="repairs[0][work_type]" required>
                                                        <option value="">Выберите вид работ</option>
                                                        <option value="КР">Капитальный ремонт (КР)</option>
                                                        <option value="ТР">Текущий ремонт (ТР)</option>
                                                        <option value="РС">Ремонт силами (РС)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label required">Способ выполнения</label>
                                                    <select class="form-select" name="repairs[0][execution_method]"
                                                        required>
                                                        <option value="">Выберите способ</option>
                                                        <option value="ХС">Хозяйственный способ (ХС)</option>
                                                        <option value="ПС">Подрядный способ (ПС)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label required">Плановый год</label>
                                                    <input type="number" class="form-control"
                                                        name="repairs[0][planned_year]" min="2024" max="2035" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Стоимость (руб.)</label>
                                                    <input type="number" class="form-control" name="repairs[0][cost]"
                                                        step="0.01" min="0">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Монтажная организация</label>
                                                    <select class="form-select" name="repairs[0][installation_org_id]">
                                                        <option value="">Выберите организацию</option>
                                                        @foreach($installationOrganizations as $org)
                                                            <option value="{{ $org->org_id }}">{{ $org->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Файл акта</label>
                                                    <input type="file" class="form-control" name="repairs[0][act_file]">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-repair">
                                        <i class="fas fa-plus me-1"></i>Добавить план работ
                                    </button>
                                </div>
                            </div>

                            <!-- Кнопки отправки -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('fire-systems.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-1"></i>Назад к списку
                                        </a>
                                        <div>
                                            <button type="submit" class="btn btn-success me-2">
                                                <i class="fas fa-save me-1"></i>Сохранить систему
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" id="save-draft">
                                                <i class="fas fa-file-alt me-1"></i>Сохранить черновик
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // JavaScript для динамического добавления полей будет здесь
    </script>
@endpush