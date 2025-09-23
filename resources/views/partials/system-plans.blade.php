<div class="section">
    <h3>Планы работ</h3>

    <div class="table-responsive">
        <table class="info-table">
            <thead>
                <tr>
                    <th>Код проекта</th>
                    <th>Метод разработки</th>
                    <th>Плановый год</th>
                    <th>Статус</th>
                    <th>Проектная организация</th>
                    <th>Нормативный документ</th>
                    <th>Дата обновления</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans ?? [] as $plan)
                    <tr>
                        <td><code>{{ $plan['project_code'] ?? 'Не указан' }}</code></td>
                        <td>
                            <span
                                class="badge bg-{{ $plan['development_method'] === 'хозяйственный' ? 'info' : 'warning' }}">
                                {{ $plan['development_method'] === 'хозяйственный' ? 'ХС' : 'ПС' }}
                            </span>
                        </td>
                        <td>{{ $plan['planned_year'] ?? 'Не указан' }}</td>
                        <td>
                            <span class="status-badge status-{{ $plan['status'] }}">
                                {{ $plan['status'] }}
                            </span>
                        </td>
                        <td>
                            @if($plan['design_org_id'])
                                {{-- Здесь нужно получить название организации по ID --}}
                                Организация #{{ $plan['design_org_id'] }}
                            @else
                                Не указана
                            @endif
                        </td>
                        <td>{{ $plan['regulation_code'] ?? 'Не указан' }}</td>
                        <td>{{ $plan['updated_at'] ? \Carbon\Carbon::parse($plan['updated_at'])->format('d.m.Y') : 'Не указана' }}
                        </td>
                        <td>
                            <div class="action-buttons">
                                @if($plan['project_file_link'] ?? false)
                                    <a href="{{ $plan['project_file_link'] }}" target="_blank" class="btn-icon"
                                        title="Просмотр">
                                        <i class="icon-eye"></i>
                                    </a>
                                    <a href="{{ $plan['project_file_link'] }}" download class="btn-icon" title="Скачать">
                                        <i class="icon-download"></i>
                                    </a>
                                @endif
                                @if($canEdit ?? false)
                                    <button class="btn-icon" data-bs-toggle="modal" data-bs-target="#editPlanModal"
                                        data-item="{{ json_encode($plan) }}" title="Редактировать">
                                        <i class="icon-edit"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Планы работ не найдены</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($canEdit ?? false)
        <div class="section-actions">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                <i class="icon-plus"></i>Добавить план работ
            </button>
        </div>
    @endif
</div>