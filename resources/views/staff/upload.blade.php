@extends('layouts.app')

@section('title', 'Bulk Staff Intake')
@section('page_header', 'Bulk Staff Intake (Teachers & Non-Teaching Staff)')

@section('content')
<div class="row justify-content-center animated-fade-in">
    <div class="col-lg-9 col-md-11 mb-4">
        <div class="glass-panel p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary border-opacity-10 pb-3">
                <div>
                    <h5 class="m-0"><i class="fa-solid fa-users-gear text-indigo me-2"></i>Staff Bulk Intake (Name Only)</h5>
                    <p class="text-secondary small m-0 mt-1">Easily import Teachers and Non-teaching staff via text copy-paste or spreadsheet upload</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('staff.create') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-user-plus me-1"></i> Quick Single Entry
                    </a>
                    <a href="{{ route('staff.index') }}" class="btn btn-secondary border-secondary border-opacity-25 btn-sm">
                        <i class="fa-solid fa-arrow-left me-1"></i> Staff Directory
                    </a>
                </div>
            </div>

            <!-- Tab Navigation -->
            <ul class="nav nav-pills nav-fill mb-4 p-1 bg-light rounded-3 border border-secondary border-opacity-10" id="importTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-semibold py-2" id="paste-tab" data-bs-toggle="pill" data-bs-target="#paste-pane" type="button" role="tab" aria-controls="paste-pane" aria-selected="true">
                        <i class="fa-solid fa-paste text-indigo me-2"></i>1. Copy & Paste Names in Text Area
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold py-2" id="file-tab" data-bs-toggle="pill" data-bs-target="#file-pane" type="button" role="tab" aria-controls="file-pane" aria-selected="false">
                        <i class="fa-solid fa-file-arrow-up text-primary me-2"></i>2. Upload CSV / Excel (.xlsx, .xls)
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="importTabContent">
                
                <!-- TAB 1: TEXTAREA COPY-PASTE -->
                <div class="tab-pane fade show active" id="paste-pane" role="tabpanel" aria-labelledby="paste-tab">
                    <div class="card border border-primary border-opacity-20 rounded-3 mb-4 bg-primary bg-opacity-10 p-3">
                        <div class="d-flex align-items-start gap-3">
                            <i class="fa-solid fa-wand-magic-sparkles text-primary fs-4 mt-1"></i>
                            <div>
                                <h6 class="m-0 fw-semibold text-primary">Smart Auto-Cleaning Textarea</h6>
                                <p class="text-secondary small m-0 mt-1">
                                    Copy a list of names from <strong>Word, PDF, Notepad, or Excel</strong> and paste below.
                                    The system automatically strips leading numbers (e.g. <code>1. </code>, <code>2) </code>), bullet points (e.g. <code>• </code>, <code>- </code>), and trims extra whitespace.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('staff.paste-preview') }}" method="POST">
                        @csrf
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-semibold">
                                    <i class="fa-solid fa-tags me-1 text-indigo"></i> Assign Staff Category to Pasted Names
                                </label>
                                <select name="default_staff_type" class="form-select fw-semibold" id="paste_staff_type">
                                    <option value="Teaching" selected>👨‍🏫 Teaching Staff (Teachers)</option>
                                    <option value="Non-teaching">👔 Non-Teaching Staff</option>
                                    <option value="Auto-detect">🔄 Auto-Detect (if typed as: Name, Category)</option>
                                </select>
                                <div class="form-text small">Applies to all names pasted below unless a category is specified per line.</div>
                            </div>
                            <div class="col-md-6 d-flex align-items-end justify-content-md-end">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-20 px-3 py-2 fs-6" id="paste-counter">
                                    <i class="fa-solid fa-list-ol me-1"></i> 0 names detected
                                </span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary small fw-semibold">
                                Paste Names List <span class="text-danger">*</span> (One name per line or comma-separated)
                            </label>
                            <textarea name="pasted_names" id="pasted_names" rows="10" class="form-control font-monospace" required
                                placeholder="Paste your names here, for example:
1. Dr. Rajesh Sharma
2. Prof. Anita Verma
• Dr. Kevin Peters
James Watson
Sarah Jenkins, Non-teaching
Ramesh Kumar, Non-teaching" style="font-size: 0.95rem; line-height: 1.5;"></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="submit" class="btn btn-primary px-4 py-2">
                                <i class="fa-solid fa-magnifying-glass-chart me-1"></i> Preview & Import Pasted Names
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('pasted_names').value=''; updatePasteCount();">
                                <i class="fa-solid fa-eraser me-1"></i> Clear Text
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TAB 2: CSV / EXCEL FILE UPLOAD -->
                <div class="tab-pane fade" id="file-pane" role="tabpanel" aria-labelledby="file-tab">
                    
                    <!-- Download Templates Card -->
                    <div class="card border border-secondary border-opacity-25 rounded-3 mb-4 bg-white shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metrics-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                        <i class="fa-solid fa-file-excel fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="m-0 fw-semibold">Ready-Made Templates</h6>
                                        <p class="text-secondary small m-0">Download sample format for error-free file uploading</p>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('staff.download-sample-excel') }}" class="btn btn-outline-success btn-sm">
                                        <i class="fa-solid fa-file-excel me-1"></i> Sample Excel (.xlsx)
                                    </a>
                                    <a href="{{ route('staff.download-sample') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fa-solid fa-file-csv me-1"></i> Sample CSV
                                    </a>
                                </div>
                            </div>
                            <div class="border-top border-secondary border-opacity-10 mt-3 pt-2 small text-secondary">
                                <i class="fa-solid fa-circle-info text-info me-1"></i> Supports <strong>1-column</strong> (Staff Name only) or <strong>2-columns</strong> (Staff Name, Staff Type).
                            </div>
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <form action="{{ route('staff.upload-preview') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-semibold">Default Category (for 1-column name files)</label>
                            <select name="default_staff_type" class="form-select w-auto">
                                <option value="Auto-detect" selected>🔄 Auto-detect from file header</option>
                                <option value="Teaching">👨‍🏫 All rows are Teaching Staff (Teachers)</option>
                                <option value="Non-teaching">👔 All rows are Non-Teaching Staff</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary small fw-semibold">Select CSV or Excel File <span class="text-danger">*</span></label>
                            <div class="border border-dashed border-secondary border-opacity-25 rounded-3 p-4 text-center bg-light position-relative" style="border-style: dashed !important;">
                                <i class="fa-solid fa-cloud-arrow-up text-indigo fs-1 mb-2"></i>
                                <h6 class="text-dark m-0">Drag & Drop file here or click to browse</h6>
                                <p class="text-secondary small mt-1 mb-0">Supports <strong>.csv</strong>, <strong>.xlsx</strong>, and <strong>.xls</strong> files up to 5MB</p>
                                <input type="file" name="file" class="form-control position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" accept=".csv,.xlsx,.xls" required id="file-input">
                            </div>
                            <div id="file-selected-name" class="text-indigo fw-semibold mt-2 small text-center" style="display: none;"></div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fa-solid fa-magnifying-glass-chart me-1"></i> Upload and Preview File
                            </button>
                            <a href="{{ route('staff.index') }}" class="btn btn-secondary border-secondary border-opacity-25">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Real-time counter for pasted names
    const pasteTextarea = document.getElementById('pasted_names');
    const pasteCounter = document.getElementById('paste-counter');

    function updatePasteCount() {
        if (!pasteTextarea) return;
        const text = pasteTextarea.value.trim();
        if (!text) {
            pasteCounter.innerHTML = '<i class="fa-solid fa-list-ol me-1"></i> 0 names detected';
            return;
        }

        const lines = text.split(/\r\n|\r|\n/).filter(line => line.trim().length > 0);
        let count = lines.length;

        // If single line with commas
        if (lines.length === 1 && text.includes(',')) {
            count = text.split(',').filter(item => item.trim().length > 0).length;
        }

        pasteCounter.innerHTML = `<i class="fa-solid fa-list-ol me-1"></i> <strong>${count}</strong> names detected`;
    }

    if (pasteTextarea) {
        pasteTextarea.addEventListener('input', updatePasteCount);
        pasteTextarea.addEventListener('paste', () => setTimeout(updatePasteCount, 50));
    }

    // File input label update
    const fileInput = document.getElementById('file-input');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : '';
            const nameDiv = document.getElementById('file-selected-name');
            if (fileName) {
                nameDiv.innerHTML = '<i class="fa-solid fa-file-check me-1"></i> Selected: <strong>' + fileName + '</strong>';
                nameDiv.style.display = 'block';
            } else {
                nameDiv.style.display = 'none';
            }
        });
    }
</script>
@endsection
