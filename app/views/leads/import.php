<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-import me-2"></i>Import/Export Leads</h2>
    <a href="index.php?action=leads" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Leads
    </a>
</div>

<div class="row">
    <!-- Import Section -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Import Leads</h5>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= htmlspecialchars($_GET['success']) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="index.php?action=import_upload" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Select CSV/Excel File</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" 
                               accept=".csv,.xlsx,.xls" required>
                        <div class="form-text">
                            Supported formats: CSV, Excel (.xlsx, .xls)
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-2"></i>Import Leads
                    </button>
                </form>
                
                <hr>
                
                <h6>CSV Format Requirements:</h6>
                <p class="small text-muted">
                    Your CSV file should include the following columns (case-insensitive):
                </p>
                <ul class="small text-muted">
                    <li><strong>name</strong> - Lead's full name</li>
                    <li><strong>company</strong> - Company name</li>
                    <li><strong>email</strong> - Email address</li>
                    <li><strong>phone</strong> - Phone number</li>
                    <li><strong>linkedin</strong> - LinkedIn profile URL</li>
                    <li><strong>website</strong> - Company website</li>
                    <li><strong>clutch</strong> - Clutch profile URL</li>
                    <li><strong>notes</strong> - Additional notes</li>
                </ul>
                
                <div class="mt-3">
                    <a href="<?= base_url('tools/csv_sample.csv') ?>" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-download me-1"></i>Download Sample CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Export Section -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-download me-2"></i>Export Leads</h5>
            </div>
            <div class="card-body">
                <p>Export all leads to CSV or Excel format.</p>
                
                <div class="d-grid gap-2">
                    <a href="index.php?action=export_csv" class="btn btn-outline-success">
                        <i class="fas fa-file-csv me-2"></i>Export as CSV
                    </a>
                    <a href="index.php?action=export_excel" class="btn btn-outline-success">
                        <i class="fas fa-file-excel me-2"></i>Export as Excel
                    </a>
                </div>
                
                <hr>
                
                <h6>Export Information:</h6>
                <ul class="small text-muted">
                    <li>All lead data will be exported</li>
                    <li>Lead IDs are auto-generated during import</li>
                    <li>Duplicate status is automatically detected</li>
                    <li>Export includes all lead fields and notes</li>
                </ul>
                
                <?php if (auth_user()['role'] === 'sdr'): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> As an SDR, you can only export your own leads.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Import Tips -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Import Tips</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Best Practices:</h6>
                        <ul class="small">
                            <li>Use consistent formatting for phone numbers</li>
                            <li>Include full URLs for LinkedIn and websites</li>
                            <li>Clean your data before importing</li>
                            <li>Test with a small batch first</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Automatic Features:</h6>
                        <ul class="small">
                            <li>Lead IDs are auto-generated (SDR{ID}-00001)</li>
                            <li>Duplicate detection based on email, phone, LinkedIn, website</li>
                            <li>Status icons: ✅ Unique, 🔁 Duplicate, ⚠️ Incomplete</li>
                            <li>Data validation and error reporting</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>