<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recomendaciones IA - {{ $student->person?->full_name ?? 'Estudiante' }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4F46E5;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #4F46E5;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .student-info {
            background: #F3F4F6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .student-info h2 {
            margin-top: 0;
            color: #1F2937;
            font-size: 18px;
        }
        .recommendation {
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .recommendation h3 {
            color: #4F46E5;
            margin-top: 0;
            font-size: 16px;
        }
        .label {
            font-weight: bold;
            color: #374151;
        }
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        .status-pending { background: #FEF3C7; color: #92400E; }
        .status-viewed { background: #DBEAFE; color: #1E40AF; }
        .status-completed { background: #D1FAE5; color: #065F46; }
        .status-dismissed { background: #F3F4F6; color: #6B7280; }
        .activities {
            margin-top: 10px;
            padding-left: 20px;
        }
        .activities li {
            margin-bottom: 5px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #E5E7EB;
            text-align: center;
            font-size: 10px;
            color: #6B7280;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #E5E7EB;
        }
        th {
            background: #F9FAFB;
            font-weight: bold;
            color: #374151;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📚 Recomendaciones IA</h1>
        <p>Sistema Inteligente de Recomendaciones Académicas</p>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="student-info">
        <h2>Información del Estudiante</h2>
        <p><span class="label">Nombre:</span> {{ $student->person?->full_name ?? $student->name }}</p>
        <p><span class="label">Email:</span> {{ $student->email }}</p>
        <p><span class="label">Total de Recomendaciones:</span> {{ $recommendations->count() }}</p>
    </div>

    @if($recommendations->isEmpty())
        <div style="text-align: center; padding: 40px; color: #6B7280;">
            <p>No hay recomendaciones generadas para este estudiante.</p>
        </div>
    @else
        <h2 style="color: #1F2937; margin-top: 30px;">Historial de Recomendaciones</h2>

        @foreach($recommendations as $rec)
        <div class="recommendation">
            <h3>{{ $rec->course_name ?? 'General' }}</h3>

            <table>
                <tr>
                    <th style="width: 30%;">Campo</th>
                    <th>Información</th>
                </tr>
                <tr>
                    <td class="label">Fecha</td>
                    <td>{{ $rec->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">Material Recomendado</td>
                    <td>{{ $rec->book_title ?? $rec->material?->title ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Promedio de Sección</td>
                    <td>
                        @if($rec->average_grade)
                            {{ number_format($rec->average_grade, 2) }}%
                            @if($rec->average_grade < 60)
                                <span style="color: #DC2626;">(Bajo)</span>
                            @elseif($rec->average_grade < 75)
                                <span style="color: #F59E0B;">(Mejorable)</span>
                            @else
                                <span style="color: #10B981;">(Bueno)</span>
                            @endif
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Razón</td>
                    <td>{{ $rec->reason ?? 'Sin especificar' }}</td>
                </tr>
                <tr>
                    <td class="label">Relevancia</td>
                    <td>{{ $rec->relevance_score }}/100</td>
                </tr>
                <tr>
                    <td class="label">Estado</td>
                    <td>
                        <span class="status status-{{ $rec->status }}">
                            @switch($rec->status)
                                @case('pending') Pendiente @break
                                @case('viewed') Vista @break
                                @case('completed') Completada @break
                                @case('dismissed') Descartada @break
                            @endswitch
                        </span>
                    </td>
                </tr>
                @if($rec->professor_notes)
                <tr>
                    <td class="label">Notas del Profesor</td>
                    <td>{{ $rec->professor_notes }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Generado por</td>
                    <td>{{ $rec->generatedBy?->person?->full_name ?? $rec->generatedBy?->name ?? 'Sistema' }}</td>
                </tr>
            </table>

            @if($rec->activities_data && count($rec->activities_data) > 0)
            <div style="margin-top: 15px;">
                <p class="label">Actividades con Bajo Rendimiento:</p>
                <ul class="activities">
                    @foreach($rec->activities_data as $activity)
                    <li>
                        {{ $activity['title'] ?? 'Actividad' }} -
                        @if(isset($activity['score']))
                            {{ $activity['score'] }} puntos
                        @endif
                        @if(isset($activity['percentage']))
                            ({{ $activity['percentage'] }}%)
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
        @endforeach
    @endif

    <div class="footer">
        <p><strong>Sistema Inteligente de Recomendaciones Académicas (SIRA)</strong></p>
        <p>Este documento fue generado automáticamente por el sistema de IA.</p>
        <p>Para más información, contacta con tu profesor o administrador académico.</p>
        <p style="margin-top: 10px;">© {{ now()->year }} - Todos los derechos reservados</p>
    </div>
</body>
</html>

