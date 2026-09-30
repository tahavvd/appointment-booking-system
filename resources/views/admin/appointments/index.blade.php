<x-admin-layout title="Appointments">
    @if (session('status'))
    <div class="mb-4 text-sm font-medium text-teal-700 bg-teal-50 border border-teal-200 rounded-lg px-4 py-2.5">
        {{ session('status') }}
    </div>
    @endif

    <x-admin.appointments-manager :appointments="$appointments" :date="$date" :show-date-nav="true" />
</x-admin-layout>