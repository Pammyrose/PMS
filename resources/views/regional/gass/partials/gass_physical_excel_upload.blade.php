<form method="POST" action="{{ route('admin.gass_physical.import_excel') }}" enctype="multipart/form-data"
    class="d-inline-flex align-items-center" id="gassExcelUploadForm"
    data-preview-url="{{ route('admin.gass_physical.import_excel.preview') }}">
    @csrf
    <input type="hidden" name="year" value="{{ $year ?? now()->year }}">
    <input type="file" name="excel_file" id="gassExcelUploadInput" class="d-none" accept=".xlsx"
        aria-label="Upload GASS Excel file">
    <button type="button" class="btn btn-success btn-sm" id="gassExcelUploadBtn"
        onclick="document.getElementById('gassExcelUploadInput').click()">
        <i class="fa fa-file-excel me-1"></i> Upload Excel
    </button>
</form>
