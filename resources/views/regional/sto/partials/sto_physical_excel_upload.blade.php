<form method="POST" action="{{ route('admin.sto_physical.import_excel') }}" enctype="multipart/form-data"
    class="d-inline-flex align-items-center" id="stoExcelUploadForm"
    data-preview-url="{{ route('admin.sto_physical.import_excel.preview') }}">
    @csrf
    <input type="hidden" name="year" value="{{ $year ?? now()->year }}">
    <input type="file" name="excel_file" id="stoExcelUploadInput" class="d-none" accept=".xlsx"
        aria-label="Upload STO Excel file">
    <button type="button" class="btn btn-success btn-sm" id="stoExcelUploadBtn"
        onclick="document.getElementById('stoExcelUploadInput').click()">
        <i class="fa fa-file-excel me-1"></i> Upload Excel
    </button>
</form>
