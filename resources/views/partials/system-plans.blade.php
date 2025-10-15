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
                    <th>Ссылка на документ</th>
                    <th>Код норматива</th>
                    <th>Название норматива</th>
                    <th>Название организации</th>
                    <th>Дата создания</th>
                    <th>Дата обновления</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans ?? [] as $plan)
                    @php
                        $planArray = [];
                        if (is_array($plan)) {
                            $planArray = $plan;
                        } elseif (is_object($plan) && method_exists($plan, 'toArray')) {
                            $planArray = $plan->toArray();
                        }
                    @endphp
                    <tr>
                        <td><code>{{ $planArray['project_code'] ?? 'Не указан' }}</code></td>
                        <td>
                            <span
                                class="badge bg-{{ ($planArray['development_method'] ?? '') === 'хозяйственный' ? 'info' : 'warning' }}">
                                {{ $planArray['development_method'] ?? 'Не указан' }}
                            </span>
                        </td>
                        <td>{{ $planArray['planned_year'] ?? 'Не указан' }}</td>
                        <td>
                            <span class="status-badge status-{{ $planArray['status'] ?? '' }}">
                                {{ $planArray['status'] ?? 'Не указан' }}
                            </span>
                        </td>
                        <td>
                            @if($planArray['project_file_link'] ?? false)
                                <a href="{{ $planArray['project_file_link'] }}" target="_blank">Ссылка</a>
                            @else
                                Нет
                            @endif
                        </td>
                        <td>{{ $planArray['regulation_code'] ?? 'Не указан' }}</td>
                        <td>{{ $planArray['regulation_name'] ?? 'Не указан' }}</td>
                        <td>{{ $planArray['design_org_short_name'] ?? 'Не указан' }}</td>
                        <td>
                            @if($planArray['created_at'] ?? false)
                                {{ \Carbon\Carbon::parse($planArray['created_at'])->format('d.m.Y H:i:s') }}
                            @else
                                Не указана
                            @endif
                        </td>
                        <td>
                            @if($planArray['updated_at'] ?? false)
                                {{ \Carbon\Carbon::parse($planArray['updated_at'])->format('d.m.Y H:i:s') }}
                            @else
                                Не указана
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                @if($planArray['project_file_link'] ?? false)
                                    <a href="{{ $planArray['project_file_link'] }}" target="_blank" class="btn-icon"
                                        title="Просмотр">
                                        <i class="icon-eye"></i>
                                    </a>
                                    <a href="{{ $planArray['project_file_link'] }}" download class="btn-icon" title="Скачать">
                                        <i class="icon-download"></i>
                                    </a>
                                @endif
                                @if($canEdit ?? false)
                                    <button class="btn-icon" data-bs-toggle="modal" data-bs-target="#editPlanModal"
                                        data-item="{{ json_encode($planArray) }}" title="Редактировать">
                                        <i class="icon-edit"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="19" class="text-center text-muted">Планы работ не найдены</td>
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