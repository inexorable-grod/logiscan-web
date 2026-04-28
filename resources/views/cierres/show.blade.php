@extends('layouts.app')

@section('title', 'Cierre - Ruta ' . $closure->route->route_number)

@php
    $statusSteps = [
        'pending_documents' => 0,
        'pending_ocr'       => 1,
        'pending_review'    => 2,
        'pending_approval'  => 3,
        'approved'          => 4,
        'rejected'          => 4,
    ];
    $currentStep = $statusSteps[$closure->status] ?? 0;

    $steps = [
        ['label' => 'Documentos',    'index' => 0],
        ['label' => 'Revision OCR',  'index' => 1],
        ['label' => 'Comparativo',   'index' => 2],
        ['label' => 'Aprobacion',    'index' => 3],
    ];

    $statusLabels = [
        'pending_documents' => 'Esperando documentos',
        'pending_ocr'       => 'Procesando OCR',
        'pending_review'    => 'Revision pendiente',
        'pending_approval'  => 'Aprobacion pendiente',
        'approved'          => 'Aprobado',
        'rejected'          => 'Rechazado',
    ];

    $statusColors = [
        'pending_documents' => 'bg-gray-100 text-gray-700',
        'pending_ocr'       => 'bg-blue-100 text-blue-700',
        'pending_review'    => 'bg-amber-100 text-amber-700',
        'pending_approval'  => 'bg-indigo-100 text-indigo-700',
        'approved'          => 'bg-green-100 text-green-700',
        'rejected'          => 'bg-red-100 text-red-700',
    ];
@endphp

@section('content')
<div x-data="{ showRejectModal: false }">

    {{-- Enlace de regreso --}}
    <div class="mb-4">
        <a href="/cierres" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition-colors">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Volver al listado
        </a>
    </div>

    {{-- ============================================================== --}}
    {{-- HEADER CARD                                                     --}}
    {{-- ============================================================== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Ruta {{ $closure->route->route_number }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">{{ $closure->route->description }}</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$closure->status] ?? 'bg-gray-100 text-gray-700' }}">
                {{ $statusLabels[$closure->status] ?? $closure->status }}
            </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-100">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Fecha Operacion</p>
                <p class="text-sm font-medium text-gray-800 mt-1">{{ \Carbon\Carbon::parse($closure->operation_date)->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Cerrado por</p>
                <p class="text-sm font-medium text-gray-800 mt-1">{{ $closure->closedBy->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Aprobado por</p>
                <p class="text-sm font-medium text-gray-800 mt-1">{{ $closure->approvedBy->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Creado</p>
                <p class="text-sm font-medium text-gray-800 mt-1">{{ $closure->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- STEP INDICATOR                                                  --}}
    {{-- ============================================================== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            @foreach($steps as $i => $step)
                @php
                    $isCompleted = $currentStep > $step['index'];
                    $isCurrent   = $currentStep === $step['index'];
                    $isRejected  = $closure->status === 'rejected' && $step['index'] === 3;
                @endphp

                {{-- Step circle + label --}}
                <div class="flex flex-col items-center flex-shrink-0" style="min-width: 80px;">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold
                        @if($isRejected)
                            bg-red-100 text-red-600 ring-2 ring-red-300
                        @elseif($isCompleted)
                            bg-green-500 text-white
                        @elseif($isCurrent)
                            bg-blue-500 text-white ring-2 ring-blue-300
                        @else
                            bg-gray-200 text-gray-500
                        @endif
                    ">
                        @if($isRejected)
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        @elseif($isCompleted)
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        @else
                            {{ $step['index'] + 1 }}
                        @endif
                    </div>
                    <span class="text-xs font-medium mt-2 text-center
                        @if($isRejected) text-red-600
                        @elseif($isCompleted) text-green-600
                        @elseif($isCurrent) text-blue-600
                        @else text-gray-400
                        @endif
                    ">{{ $step['label'] }}</span>
                </div>

                {{-- Connector line --}}
                @if($i < count($steps) - 1)
                    <div class="flex-1 h-0.5 mx-2
                        @if($currentStep > $step['index'])
                            bg-green-400
                        @else
                            bg-gray-200
                        @endif
                    "></div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- DOCUMENTOS                                                      --}}
    {{-- ============================================================== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Documentos</h2>

        @if($closure->documents && $closure->documents->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($closure->documents as $document)
                    <div class="border border-gray-200 rounded-xl p-4 flex items-start gap-3 hover:bg-gray-50 transition-colors">
                        {{-- Icon --}}
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $document->file_name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $document->created_at->format('d/m/Y H:i') }}</p>

                            {{-- OCR Status badge --}}
                            <div class="mt-2">
                                @if($document->ocr_status === 'completed')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                        OCR completado
                                    </span>
                                @elseif($document->ocr_status === 'failed')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                        OCR fallido
                                    </span>
                                @elseif($document->ocr_status === 'processing')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                        <svg class="w-3.5 h-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        Procesando
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                        Pendiente
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-400 text-center py-8">No se han cargado documentos para este cierre.</p>
        @endif
    </div>

    {{-- ============================================================== --}}
    {{-- MANIFEST ITEMS                                                  --}}
    {{-- ============================================================== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Items del Manifiesto</h2>

        @if($closure->manifestItems && $closure->manifestItems->count())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pedido</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre Local</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Cubetas</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Cajas/Bolsa</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Refr.</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Cont.</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Confianza</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($closure->manifestItems as $index => $item)
                            @php
                                $lowConfidence = isset($item->confidence_score) && $item->confidence_score < 0.6;
                            @endphp
                            <tr class="{{ $lowConfidence ? 'bg-amber-50' : 'hover:bg-gray-50' }} transition-colors">
                                <td class="px-4 py-3 text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    {{ $item->order->order_number ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $item->local_name ?? '—' }}
                                    @if($item->is_manually_corrected)
                                        <span class="inline-flex items-center ml-1.5 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-purple-100 text-purple-700" title="Corregido manualmente">
                                            CORREGIDO
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-gray-700">{{ $item->cubetas ?? 0 }}</td>
                                <td class="px-4 py-3 text-center text-gray-700">{{ $item->cajas_bolsa ?? 0 }}</td>
                                <td class="px-4 py-3 text-center text-gray-700">{{ $item->refrigerado ?? 0 }}</td>
                                <td class="px-4 py-3 text-center text-gray-700">{{ $item->controlado ?? 0 }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if(isset($item->confidence_score))
                                        @php
                                            $pct = round($item->confidence_score * 100);
                                            $barColor = $item->confidence_score >= 0.8 ? '#22c55e' : ($item->confidence_score >= 0.6 ? '#f59e0b' : '#ef4444');
                                        @endphp
                                        <div class="flex items-center gap-2 justify-center">
                                            <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full" style="width: {{ $pct }}%; background-color: {{ $barColor }};"></div>
                                            </div>
                                            <span class="text-xs font-medium {{ $lowConfidence ? 'text-red-600' : 'text-gray-600' }}">{{ $pct }}%</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-400 text-center py-8">No se han extraido items del manifiesto.</p>
        @endif
    </div>

    {{-- ============================================================== --}}
    {{-- COMPARATIVO MACH                                                --}}
    {{-- ============================================================== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Comparativo MACH</h2>

        @if(isset($comparison) && isset($comparison['summary']))
            {{-- KPI Cards --}}
            @php
                $summary = $comparison['summary'];
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                {{-- Completos --}}
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800">{{ $summary['complete'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Completos</p>
                    </div>
                </div>

                {{-- Incompletos --}}
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800">{{ $summary['incomplete'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Incompletos</p>
                    </div>
                </div>

                {{-- Pendientes --}}
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800">{{ $summary['pending'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Pendientes</p>
                    </div>
                </div>
            </div>

            {{-- Comparison Table --}}
            @if(isset($comparison['orders']) && count($comparison['orders']))
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pedido</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Esperado</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Escaneado</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Diferencia</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($comparison['orders'] as $order)
                                @php
                                    $diff = ($order['scanned'] ?? 0) - ($order['expected'] ?? 0);
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $order['order_number'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $order['client_name'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700">{{ $order['expected'] ?? 0 }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700">{{ $order['scanned'] ?? 0 }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($diff === 0)
                                            <span class="text-green-600 font-semibold">0</span>
                                        @elseif($diff > 0)
                                            <span class="text-blue-600 font-semibold">+{{ $diff }}</span>
                                        @else
                                            <span class="text-red-600 font-semibold">{{ $diff }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if(($order['status'] ?? '') === 'complete')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Completo</span>
                                        @elseif(($order['status'] ?? '') === 'incomplete')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Incompleto</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Unmatched Scans --}}
            @if(isset($comparison['unmatched_scans']) && count($comparison['unmatched_scans']))
                <div class="mt-6 border-t border-gray-200 pt-4">
                    <h3 class="text-sm font-semibold text-gray-600 mb-3">Escaneos sin coincidencia</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($comparison['unmatched_scans'] as $scan)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                {{ $scan }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <p class="text-sm text-gray-400 text-center py-8">No se ha generado el comparativo MACH para este cierre.</p>
        @endif
    </div>

    {{-- ============================================================== --}}
    {{-- APROBACION                                                      --}}
    {{-- ============================================================== --}}
    @if(in_array($closure->status, ['pending_review', 'pending_approval']))
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Aprobacion del Cierre</h2>
        <p class="text-sm text-gray-500 mb-6">Revise la informacion anterior y determine si el cierre cumple con los requisitos para ser aprobado.</p>

        <div class="flex flex-col sm:flex-row gap-3">
            {{-- Aprobar --}}
            <form action="/api/cierres/{{ $closure->id }}/approve" method="POST">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition-colors shadow-sm">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    Aprobar cierre
                </button>
            </form>

            {{-- Rechazar --}}
            <button @click="showRejectModal = true"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition-colors shadow-sm">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
                Rechazar
            </button>
        </div>
    </div>

    {{-- Modal Rechazar --}}
    <div x-show="showRejectModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div @click.outside="showRejectModal = false"
             class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 mx-4">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Rechazar Cierre de Ruta</h2>
            <form action="/api/cierres/{{ $closure->id }}/reject" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Motivo del rechazo <span class="text-red-500">*</span>
                    </label>
                    <textarea name="rejection_reason" rows="4" required
                              placeholder="Ingrese el motivo por el cual se rechaza este cierre..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showRejectModal = false"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 font-medium transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                        Confirmar Rechazo
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
